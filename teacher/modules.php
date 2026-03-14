<?php
/**
 * Teacher — My Modules
 * 
 * Lists all modules assigned to the logged-in teacher
 * with details and student grades summary.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole('teacher');

$pageTitle = 'My Modules';
$teacherId = $_SESSION['user_id'];
$base = basePath();

// ── Fetch Assigned Modules ────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT m.*, 
           COUNT(DISTINCT g.student_id) AS student_count,
           ROUND(AVG(g.grade), 2) AS avg_grade
    FROM modules m
    LEFT JOIN grades g ON g.module_id = m.id
    WHERE m.teacher_id = ?
    GROUP BY m.id
    ORDER BY m.code
");
$stmt->execute([$teacherId]);
$modules = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<!-- ── Page Header ─────────────────────────────────────── -->
<div class="page-header">
    <h1>My Modules</h1>
</div>

<!-- ── Modules Table ───────────────────────────────────── -->
<div class="table-container">
    <table class="responsive-table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Module Name</th>
                <th class="text-center">Coefficient</th>
                <th class="text-center">Students</th>
                <th class="text-center">Avg Grade</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($modules)): ?>
                <tr><td colspan="6" class="empty-state"><p>No modules assigned to you yet.</p></td></tr>
            <?php else: ?>
                <?php foreach ($modules as $m): ?>
                    <tr>
                        <td data-label="Code"><strong><?php echo e($m['code']); ?></strong></td>
                        <td data-label="Module"><?php echo e($m['name']); ?></td>
                        <td data-label="Coefficient" class="text-center"><?php echo $m['coefficient']; ?></td>
                        <td data-label="Students" class="text-center"><?php echo $m['student_count']; ?></td>
                        <td data-label="Avg Grade" class="text-center">
                            <?php if ($m['avg_grade']): ?>
                                <span class="grade-value <?php echo $m['avg_grade'] >= 10 ? 'grade-pass' : 'grade-fail'; ?>">
                                    <?php echo $m['avg_grade']; ?>
                                </span>
                            <?php else: ?>
                                <span style="color:var(--gray-400)">—</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Actions">
                            <a href="<?php echo $base; ?>/teacher/grades.php?module_id=<?php echo $m['id']; ?>" class="btn btn-primary btn-sm">
                                Enter Grades
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
