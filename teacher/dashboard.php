<?php
/**
 * Teacher Dashboard
 * 
 * Shows modules assigned to the logged-in teacher as cards,
 * with student count per module.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole('teacher');

$pageTitle = 'Teacher Dashboard';
$teacherId = $_SESSION['user_id'];
$base = basePath();

// ── Fetch Assigned Modules with Student Counts ────────────────
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

<!-- ── Page Header ────────────────────────────────────── -->
<div class="page-header">
    <h1>Dashboard</h1>
    <span class="text-small">Welcome, <?php echo e(getCurrentUserName()); ?></span>
</div>

<!-- ── Statistics ──────────────────────────────────────── -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        </div>
        <div class="stat-number"><?php echo count($modules); ?></div>
        <div class="stat-label">My Modules</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div class="stat-number"><?php echo array_sum(array_column($modules, 'student_count')); ?></div>
        <div class="stat-label">Total Students</div>
    </div>
</div>

<!-- ── My Modules Grid ─────────────────────────────────── -->
<h2 class="mb-24">My Modules</h2>

<?php if (empty($modules)): ?>
    <div class="card">
        <div class="empty-state">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            <p>No modules assigned to you yet.</p>
        </div>
    </div>
<?php else: ?>
    <div class="modules-grid">
        <?php foreach ($modules as $m): ?>
            <div class="card module-card">
                <h3><?php echo e($m['name']); ?></h3>
                <div class="module-code"><?php echo e($m['code']); ?></div>
                <div class="module-meta">
                    <span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        <?php echo $m['student_count']; ?> students
                    </span>
                    <span>Coef: <?php echo $m['coefficient']; ?></span>
                    <?php if ($m['avg_grade']): ?>
                        <span>Avg: <?php echo $m['avg_grade']; ?>/20</span>
                    <?php endif; ?>
                </div>
                <a href="<?php echo $base; ?>/teacher/grades.php?module_id=<?php echo $m['id']; ?>" class="btn btn-primary btn-sm">
                    Enter Grades
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
