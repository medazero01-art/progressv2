<?php
/**
 * Sidebar Navigation Component
 * 
 * Renders the left sidebar with role-specific navigation links.
 * Active state is determined by the current page.
 */

$base = basePath();
$role = getCurrentRole();
$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');

// ── Navigation items per role ─────────────────────────────────
$navItems = [];

if ($role === 'admin') {
    $navItems = [
        ['url' => "$base/admin/dashboard.php", 'label' => 'Dashboard', 'icon' => 'dashboard', 'page' => 'dashboard'],
        ['url' => "$base/admin/students.php",  'label' => 'Students',  'icon' => 'students',  'page' => 'students'],
        ['url' => "$base/admin/teachers.php",  'label' => 'Teachers',  'icon' => 'teachers',  'page' => 'teachers'],
        ['url' => "$base/admin/modules.php",   'label' => 'Modules',   'icon' => 'modules',   'page' => 'modules'],
        ['url' => "$base/admin/grades.php",    'label' => 'Grades',    'icon' => 'grades',    'page' => 'grades'],
    ];
} elseif ($role === 'teacher') {
    $navItems = [
        ['url' => "$base/teacher/dashboard.php", 'label' => 'Dashboard', 'icon' => 'dashboard', 'page' => 'dashboard'],
        ['url' => "$base/teacher/modules.php",   'label' => 'My Modules','icon' => 'modules',   'page' => 'modules'],
        ['url' => "$base/teacher/grades.php",    'label' => 'Grades',    'icon' => 'grades',    'page' => 'grades'],
    ];
} elseif ($role === 'student') {
    $navItems = [
        ['url' => "$base/student/dashboard.php",  'label' => 'Dashboard',  'icon' => 'dashboard',  'page' => 'dashboard'],
        ['url' => "$base/student/grades.php",      'label' => 'My Grades', 'icon' => 'grades',     'page' => 'grades'],
        ['url' => "$base/student/transcript.php",  'label' => 'Transcript','icon' => 'transcript', 'page' => 'transcript'],
    ];
}

// ── SVG icons map ─────────────────────────────────────────────
$icons = [
    'dashboard'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
    'students'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'teachers'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
    'modules'    => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
    'grades'     => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
    'transcript' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>',
];
?>

<!-- ── Sidebar Overlay (mobile) ──────────────────────────── -->
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<!-- ── Sidebar Navigation ────────────────────────────────── -->
<aside class="sidebar" id="sidebar">
    <nav class="sidebar-nav">
        <ul class="nav-list">
            <?php foreach ($navItems as $item): ?>
                <li class="nav-item">
                    <a href="<?php echo $item['url']; ?>"
                       class="nav-link <?php echo ($currentPage === $item['page']) ? 'active' : ''; ?>">
                        <span class="nav-icon"><?php echo $icons[$item['icon']]; ?></span>
                        <span class="nav-label"><?php echo e($item['label']); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <a href="<?php echo $base; ?>/auth/logout.php" class="nav-link nav-logout">
            <span class="nav-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </span>
            <span class="nav-label">Logout</span>
        </a>
    </div>
</aside>

<!-- ── Main Content Wrapper Start ────────────────────────── -->
<main class="main-content" id="main-content">
