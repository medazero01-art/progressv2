<?php
/**
 * Teacher — Grades Entry
 * 
 * Per-module grade entry: lists all students with their current grade
 * and provides inline input to enter/update grades.
 * Teachers can only manage grades for their own modules.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole('teacher');

$pageTitle = 'Enter Grades';
$teacherId = $_SESSION['user_id'];
$base = basePath();

// ── Handle POST: Save Grade ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action'] ?? '';
    $moduleId = (int)($_POST['module_id'] ?? 0);

    // Verify the module belongs to this teacher
    $stmt = $pdo->prepare("SELECT id FROM modules WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$moduleId, $teacherId]);
    if (!$stmt->fetch()) {
        setFlash('danger', 'You are not authorized to grade this module.');
        header("Location: grades.php");
        exit;
    }

    try {
        if ($action === 'save_grade') {
            $studentId = (int)($_POST['student_id'] ?? 0);
            $grade     = floatval($_POST['grade'] ?? 0);

            if ($grade < 0 || $grade > 20) {
                setFlash('danger', 'Grade must be between 0 and 20.');
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO grades (student_id, module_id, grade)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE grade = VALUES(grade), updated_at = CURRENT_TIMESTAMP
                ");
                $stmt->execute([$studentId, $moduleId, $grade]);
                setFlash('success', 'Grade saved successfully.');
            }

        } elseif ($action === 'save_all') {
            // Batch save all grades
            $grades = $_POST['grades'] ?? [];
            $stmt = $pdo->prepare("
                INSERT INTO grades (student_id, module_id, grade)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE grade = VALUES(grade), updated_at = CURRENT_TIMESTAMP
            ");

            $count = 0;
            foreach ($grades as $studentId => $grade) {
                $grade = floatval($grade);
                if ($grade >= 0 && $grade <= 20 && !empty($grade)) {
                    $stmt->execute([(int)$studentId, $moduleId, $grade]);
                    $count++;
                }
            }
            setFlash('success', "$count grades saved successfully.");
        }
    } catch (PDOException $e) {
        error_log("Teacher grades error: " . $e->getMessage());
        setFlash('danger', 'An error occurred. Please try again.');
    }

    header("Location: grades.php?module_id=$moduleId");
    exit;
}

// ── Get Teacher's Modules ─────────────────────────────────────
$stmt = $pdo->prepare("SELECT id, code, name, coefficient FROM modules WHERE teacher_id = ? ORDER BY code");
$stmt->execute([$teacherId]);
$modules = $stmt->fetchAll();

// ── Get Selected Module's Students & Grades ───────────────────
$selectedModule = isset($_GET['module_id']) ? (int)$_GET['module_id'] : 0;
$moduleInfo = null;
$studentGrades = [];

if ($selectedModule > 0) {
    // Verify module belongs to teacher
    $stmt = $pdo->prepare("SELECT * FROM modules WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$selectedModule, $teacherId]);
    $moduleInfo = $stmt->fetch();

    if ($moduleInfo) {
        // Fetch all students with their grade for this module
        $stmt = $pdo->prepare("
            SELECT s.id, s.matricule, s.first_name, s.last_name, g.grade
            FROM students s
            LEFT JOIN grades g ON g.student_id = s.id AND g.module_id = ?
            ORDER BY s.last_name, s.first_name
        ");
        $stmt->execute([$selectedModule]);
        $studentGrades = $stmt->fetchAll();
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<!-- ── Page Header ─────────────────────────────────────── -->
<div class="page-header">
    <h1>Enter Grades</h1>
</div>

<!-- ── Module Selector ─────────────────────────────────── -->
<div class="card mb-32">
    <div class="form-group">
        <label class="form-label">Select Module</label>
        <select class="form-control" onchange="window.location='grades.php?module_id='+this.value" style="max-width:400px;">
            <option value="">— Choose a module —</option>
            <?php foreach ($modules as $m): ?>
                <option value="<?php echo $m['id']; ?>" <?php echo $selectedModule == $m['id'] ? 'selected' : ''; ?>>
                    <?php echo e($m['code'] . ' — ' . $m['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<?php if ($moduleInfo && !empty($studentGrades)): ?>
    <!-- ── Grades Entry Table ──────────────────────────────── -->
    <div class="table-container">
        <div class="table-toolbar">
            <h3 style="margin:0;">
                <?php echo e($moduleInfo['name']); ?>
                <span class="text-small" style="margin-left:8px;">(<?php echo e($moduleInfo['code']); ?> — Coef: <?php echo $moduleInfo['coefficient']; ?>)</span>
            </h3>
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="save_all">
            <input type="hidden" name="module_id" value="<?php echo $selectedModule; ?>">

            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>Matricule</th>
                        <th>Student Name</th>
                        <th class="text-center">Current Grade</th>
                        <th class="text-center">New Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($studentGrades as $sg): ?>
                        <tr>
                            <td data-label="Matricule"><?php echo e($sg['matricule']); ?></td>
                            <td data-label="Student"><?php echo e($sg['first_name'] . ' ' . $sg['last_name']); ?></td>
                            <td data-label="Current Grade" class="text-center">
                                <?php if ($sg['grade'] !== null): ?>
                                    <span class="grade-value <?php echo $sg['grade'] >= 10 ? 'grade-pass' : 'grade-fail'; ?>">
                                        <?php echo number_format($sg['grade'], 2); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color:var(--gray-400)">—</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="New Grade" class="text-center">
                                <input type="number" name="grades[<?php echo $sg['id']; ?>]"
                                       class="form-control grade-input"
                                       min="0" max="20" step="0.25"
                                       value="<?php echo $sg['grade'] !== null ? number_format($sg['grade'], 2) : ''; ?>"
                                       placeholder="0-20"
                                       style="display:inline-block; margin:0 auto;">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="padding:16px 24px; display:flex; justify-content:flex-end;">
                <button type="submit" class="btn btn-success">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    Save All Grades
                </button>
            </div>
        </form>
    </div>

<?php elseif ($moduleInfo && empty($studentGrades)): ?>
    <div class="card">
        <div class="empty-state">
            <p>No students found in the system.</p>
        </div>
    </div>
<?php elseif ($selectedModule > 0 && !$moduleInfo): ?>
    <div class="alert alert-danger">This module is not assigned to you.</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
