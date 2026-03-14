<?php
/**
 * Admin — Grades Management
 * 
 * Select student & module, enter/update grades.
 * Shows automatic average calculation.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole('admin');

$pageTitle = 'Grades Management';
$base = basePath();

// ── Handle POST: Assign/Update Grade ──────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'assign') {
            $studentId = (int)($_POST['student_id'] ?? 0);
            $moduleId  = (int)($_POST['module_id'] ?? 0);
            $grade     = floatval($_POST['grade'] ?? 0);

            if ($grade < 0 || $grade > 20) {
                setFlash('danger', 'Grade must be between 0 and 20.');
            } elseif ($studentId <= 0 || $moduleId <= 0) {
                setFlash('danger', 'Please select a student and module.');
            } else {
                // Use INSERT ... ON DUPLICATE KEY UPDATE for upsert
                $stmt = $pdo->prepare("
                    INSERT INTO grades (student_id, module_id, grade)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE grade = VALUES(grade), updated_at = CURRENT_TIMESTAMP
                ");
                $stmt->execute([$studentId, $moduleId, $grade]);
                setFlash('success', 'Grade assigned successfully.');
            }

        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM grades WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', 'Grade removed.');
        }
    } catch (PDOException $e) {
        error_log("Grades error: " . $e->getMessage());
        setFlash('danger', 'An error occurred. Please try again.');
    }

    header("Location: grades.php" . (isset($_POST['student_id']) ? "?student_id=" . (int)$_POST['student_id'] : ''));
    exit;
}

// ── Fetch Data ────────────────────────────────────────────────
$students = $pdo->query("SELECT id, matricule, first_name, last_name FROM students ORDER BY last_name")->fetchAll();
$modules  = $pdo->query("SELECT id, code, name, coefficient FROM modules ORDER BY code")->fetchAll();

// If a student is selected, fetch their grades and average
$selectedStudent = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$studentGrades = [];
$weightedAverage = 0;

if ($selectedStudent > 0) {
    $stmt = $pdo->prepare("
        SELECT g.id, g.grade, m.code, m.name AS module_name, m.coefficient
        FROM grades g
        JOIN modules m ON m.id = g.module_id
        WHERE g.student_id = ?
        ORDER BY m.code
    ");
    $stmt->execute([$selectedStudent]);
    $studentGrades = $stmt->fetchAll();

    // Calculate weighted average
    $totalWeight = 0;
    $totalPoints = 0;
    foreach ($studentGrades as $g) {
        $totalPoints += $g['grade'] * $g['coefficient'];
        $totalWeight += $g['coefficient'];
    }
    $weightedAverage = $totalWeight > 0 ? round($totalPoints / $totalWeight, 2) : 0;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<!-- ── Page Header ─────────────────────────────────────── -->
<div class="page-header">
    <h1>Grades</h1>
</div>

<!-- ── Grade Assignment Form ───────────────────────────── -->
<div class="card mb-32">
    <h3 class="mb-16">Assign Grade</h3>
    <form method="POST" data-validate>
        <input type="hidden" name="action" value="assign">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="grade_student">Select Student *</label>
                <select id="grade_student" name="student_id" class="form-control" required onchange="window.location='grades.php?student_id='+this.value">
                    <option value="">— Choose a student —</option>
                    <?php foreach ($students as $s): ?>
                        <option value="<?php echo $s['id']; ?>" <?php echo $selectedStudent == $s['id'] ? 'selected' : ''; ?>>
                            <?php echo e($s['matricule'] . ' — ' . $s['first_name'] . ' ' . $s['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="grade_module">Select Module *</label>
                <select id="grade_module" name="module_id" class="form-control" required>
                    <option value="">— Choose a module —</option>
                    <?php foreach ($modules as $m): ?>
                        <option value="<?php echo $m['id']; ?>">
                            <?php echo e($m['code'] . ' — ' . $m['name'] . ' (Coef: ' . $m['coefficient'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="grade_value">Grade (0–20) *</label>
                <input type="number" id="grade_value" name="grade" class="form-control" data-grade
                       min="0" max="20" step="0.25" placeholder="0.00" required>
            </div>
            <div class="form-group" style="display:flex; align-items:flex-end;">
                <button type="submit" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    Assign Grade
                </button>
            </div>
        </div>
    </form>
</div>

<?php if ($selectedStudent > 0): ?>
    <!-- ── Student's Grades Table ──────────────────────────── -->
    <div class="table-container">
        <div class="table-toolbar">
            <h3 style="margin:0;">
                Grades for
                <?php
                    foreach ($students as $s) {
                        if ($s['id'] == $selectedStudent) {
                            echo e($s['first_name'] . ' ' . $s['last_name']);
                            break;
                        }
                    }
                ?>
            </h3>
            <div class="average-badge <?php echo $weightedAverage >= 10 ? 'pass' : 'fail'; ?>">
                Average: <?php echo $weightedAverage; ?>/20
            </div>
        </div>

        <table class="responsive-table">
            <thead>
                <tr>
                    <th>Module</th>
                    <th>Code</th>
                    <th class="text-center">Coefficient</th>
                    <th class="text-center">Grade</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($studentGrades)): ?>
                    <tr><td colspan="5" class="empty-state"><p>No grades assigned yet</p></td></tr>
                <?php else: ?>
                    <?php foreach ($studentGrades as $g): ?>
                        <tr>
                            <td data-label="Module"><?php echo e($g['module_name']); ?></td>
                            <td data-label="Code"><?php echo e($g['code']); ?></td>
                            <td data-label="Coefficient" class="text-center"><?php echo $g['coefficient']; ?></td>
                            <td data-label="Grade" class="text-center">
                                <span class="grade-value <?php echo $g['grade'] >= 10 ? 'grade-pass' : 'grade-fail'; ?>">
                                    <?php echo number_format($g['grade'], 2); ?>
                                </span>
                            </td>
                            <td data-label="Actions">
                                <form method="POST" id="delete-grade-<?php echo $g['id']; ?>" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $g['id']; ?>">
                                    <input type="hidden" name="student_id" value="<?php echo $selectedStudent; ?>">
                                    <button type="button" class="btn-delete" onclick="confirmDelete('Remove this grade?', 'delete-grade-<?php echo $g['id']; ?>')" title="Delete">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <!-- Average Row -->
                    <tr class="total-row">
                        <td colspan="3" data-label=""><strong>Weighted Average</strong></td>
                        <td class="text-center" data-label="Average">
                            <strong class="<?php echo $weightedAverage >= 10 ? 'grade-pass' : 'grade-fail'; ?>">
                                <?php echo $weightedAverage; ?>/20
                            </strong>
                        </td>
                        <td></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
