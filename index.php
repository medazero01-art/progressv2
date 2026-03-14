<?php
/**
 * Home / Landing Page
 * 
 * Public entry point. Displays a hero section with a login CTA.
 * If the user is already logged in, redirect to their dashboard.
 */

session_start();
require_once __DIR__ . '/includes/auth.php';

// Redirect logged-in users to their dashboard
if (isLoggedIn()) {
    $dashboards = [
        'admin'   => 'admin/dashboard.php',
        'teacher' => 'teacher/dashboard.php',
        'student' => 'student/dashboard.php',
    ];
    $role = getCurrentRole();
    header('Location: ' . ($dashboards[$role] ?? 'auth/login.php'));
    exit;
}

$base = basePath();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Student School Management System — Manage students, modules, and grades efficiently.">
    <title>Student School Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/style.css">
</head>
<body>
    <!-- ── Landing Page ──────────────────────────────────── -->
    <div class="landing-page">
        <div class="card landing-card">
            <!-- University Logo -->
            <div class="landing-logo">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 2 4 3 6 3s6-1 6-3v-5"/>
                </svg>
            </div>

            <h1>Student School Management System</h1>
            <p>Manage students, modules, grades efficiently. A unified portal for administrators, teachers, and students.</p>

            <a href="<?php echo $base; ?>/auth/login.php" class="btn btn-primary" style="width:100%; padding:16px; font-size:18px;">
                Login to Portal
            </a>
        </div>

        <footer class="landing-footer">
            &copy; <?php echo date('Y'); ?> University Portal &mdash; Student School Management System v1.0
        </footer>
    </div>
</body>
</html>
