<?php
/**
 * Header Component
 * 
 * Fixed top header bar with logo, page title, and user menu.
 * Included at the top of every authenticated page.
 */

// Ensure auth helpers are available
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/auth.php';
}

$base = basePath();
$userName = getCurrentUserName();
$userRole = ucfirst(getCurrentRole());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Student School Management System — Manage students, modules, grades efficiently.">
    <title><?php echo e($pageTitle ?? 'School Management'); ?> — University Portal</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/style.css">
</head>
<body>
    <!-- ── Top Header Bar ────────────────────────────────────── -->
    <header class="main-header" id="main-header">
        <div class="header-left">
            <!-- Mobile hamburger toggle -->
            <button class="hamburger-btn" id="sidebar-toggle" aria-label="Toggle navigation">
                <span></span><span></span><span></span>
            </button>
            <a href="<?php echo $base; ?>/index.php" class="header-logo">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 2 4 3 6 3s6-1 6-3v-5"/>
                </svg>
                <span class="logo-text">UniPortal</span>
            </a>
        </div>
        <div class="header-right">
            <div class="user-menu">
                <div class="user-info">
                    <span class="user-name"><?php echo e($userName); ?></span>
                    <span class="user-role"><?php echo e($userRole); ?></span>
                </div>
                <div class="user-avatar" title="<?php echo e($userName); ?>">
                    <?php echo strtoupper(substr($userName, 0, 1)); ?>
                </div>
            </div>
            <a href="<?php echo $base; ?>/auth/logout.php" class="btn-logout" title="Logout">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </a>
        </div>
    </header>

    <!-- Flash Messages -->
    <?php
    $flash = getFlash();
    if ($flash): ?>
        <div class="toast toast-<?php echo e($flash['type']); ?>" id="flash-toast">
            <?php echo e($flash['message']); ?>
            <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif; ?>
