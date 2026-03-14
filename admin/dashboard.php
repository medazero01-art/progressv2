<?php
/**
 * Admin Dashboard
 * 
 * Displays key statistics: total students, teachers, modules.
 * Provides quick-action links to manage each section.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole('admin');

$pageTitle = 'Admin Dashboard';

// ── Fetch Statistics ──────────────────────────────────────────
try {
    $totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $totalTeachers = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
    $totalModules  = $pdo->query("SELECT COUNT(*) FROM modules")->fetchColumn();
    $totalGrades   = $pdo->query("SELECT COUNT(*) FROM grades")->fetchColumn();
} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $totalStudents = $totalTeachers = $totalModules = $totalGrades = 0;
}

$base = basePath();

// Include layout components
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<!-- ── Page Header ────────────────────────────────────── -->
<div class="page-header">
    <h1>Dashboard</h1>
    <span class="text-small">Welcome back, <?php echo e(getCurrentUserName()); ?></span>
</div>

<!-- ── Statistics Grid ────────────────────────────────── -->
<div class="stats-grid">
    <!-- Total Students -->
    <div class="stat-card">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <div class="stat-number"><?php echo $totalStudents; ?></div>
        <div class="stat-label">Total Students</div>
    </div>

    <!-- Total Teachers -->
    <div class="stat-card">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
        </div>
        <div class="stat-number"><?php echo $totalTeachers; ?></div>
        <div class="stat-label">Total Teachers</div>
    </div>

    <!-- Total Modules -->
    <div class="stat-card">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
            </svg>
        </div>
        <div class="stat-number"><?php echo $totalModules; ?></div>
        <div class="stat-label">Total Modules</div>
    </div>
</div>

<!-- ── Quick Actions ──────────────────────────────────── -->
<h2 class="mb-24">Quick Actions</h2>
<div class="quick-actions">
    <a href="<?php echo $base; ?>/admin/students.php" class="card quick-action-card">
        <div class="icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
        </div>
        <span>Manage Students</span>
    </a>
    <a href="<?php echo $base; ?>/admin/teachers.php" class="card quick-action-card">
        <div class="icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <span>Manage Teachers</span>
    </a>
    <a href="<?php echo $base; ?>/admin/modules.php" class="card quick-action-card">
        <div class="icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        </div>
        <span>Manage Modules</span>
    </a>
    <a href="<?php echo $base; ?>/admin/grades.php" class="card quick-action-card">
        <div class="icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        </div>
        <span>Manage Grades</span>
    </a>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
