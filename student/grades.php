<?php
/**
 * Student — My Grades
 * 
 * Detailed view of all the student's grades
 * with module info and weighted average.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole('student');

$pageTitle = 'My Grades';
$studentId = $_SESSION['user_id'];
$base = basePath();

// ── Fetch Grades ──────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT g.grade, m.code, m.name AS module_name, m.coefficient, t.name AS teacher_name
    FROM grades g
    JOIN modules m ON m.id = g.module_id
    LEFT JOIN teachers t ON t.id = m.teacher_id
    WHERE g.student_id = ?
    ORDER BY m.code
");
$stmt->execute([$studentId]);
$grades = $stmt->fetchAll();

// ── Calculate Weighted Average ────────────────────────────────
$totalWeight = 0;
$totalPoints = 0;
foreach ($grades as $g) {
    $totalPoints += $g['grade'] * $g['coefficient'];
    $totalWeight += $g['coefficient'];
}
$weightedAverage = $totalWeight > 0 ? round($totalPoints / $totalWeight, 2) : 0;

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<!-- ── Page Header ─────────────────────────────────────── -->
<div class="page-header">
    <h1>My Grades</h1>
    <div class="average-badge <?php echo $weightedAverage >= 10 ? 'pass' : 'fail'; ?>">
        Average: <?php echo $weightedAverage; ?>/20
    </div>
</div>

<!-- ── Grades Table ────────────────────────────────────── -->
<div class="table-container">
    <table class="responsive-table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Module</th>
                <th>Teacher</th>
                <th class="text-center">Coefficient</th>
                <th class="text-center">Grade</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($grades)): ?>
                <tr><td colspan="6" class="empty-state"><p>No grades available yet.</p></td></tr>
            <?php else: ?>
                <?php foreach ($grades as $g): ?>
                    <tr>
                        <td data-label="Code"><strong><?php echo e($g['code']); ?></strong></td>
                        <td data-label="Module"><?php echo e($g['module_name']); ?></td>
                        <td data-label="Teacher"><?php echo $g['teacher_name'] ? e($g['teacher_name']) : '—'; ?></td>
                        <td data-label="Coefficient" class="text-center"><?php echo $g['coefficient']; ?></td>
                        <td data-label="Grade" class="text-center">
                            <span class="grade-value <?php echo $g['grade'] >= 10 ? 'grade-pass' : 'grade-fail'; ?>">
                                <?php echo number_format($g['grade'], 2); ?>
                            </span>
                        </td>
                        <td data-label="Status" class="text-center">
                            <?php if ($g['grade'] >= 10): ?>
                                <span style="color:var(--success); font-weight:500;">✓ Pass</span>
                            <?php else: ?>
                                <span style="color:var(--danger); font-weight:500;">✗ Fail</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <!-- Average Row -->
                <tr class="total-row">
                    <td colspan="4" data-label=""><strong>Weighted Average</strong></td>
                    <td class="text-center" data-label="Average">
                        <strong class="<?php echo $weightedAverage >= 10 ? 'grade-pass' : 'grade-fail'; ?>">
                            <?php echo $weightedAverage; ?>/20
                        </strong>
                    </td>
                    <td class="text-center">
                        <strong class="<?php echo $weightedAverage >= 10 ? 'grade-pass' : 'grade-fail'; ?>">
                            <?php echo $weightedAverage >= 10 ? '✓ Pass' : '✗ Fail'; ?>
                        </strong>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
