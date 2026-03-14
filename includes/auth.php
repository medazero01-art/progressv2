<?php
/**
 * Authentication Helper
 * 
 * Provides session management, login verification, and role-based
 * access control. Include this file at the top of every protected page.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if the user is logged in.
 * Redirects to login page if not authenticated.
 */
function requireLogin(): void
{
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header('Location: /auth/login.php');
        exit;
    }
}

/**
 * Require a specific role to access the page.
 * Redirects to the user's own dashboard if role doesn't match.
 *
 * @param string $role  Expected role: 'admin', 'teacher', or 'student'
 */
function requireRole(string $role): void
{
    requireLogin();

    if ($_SESSION['role'] !== $role) {
        // Redirect to the user's own dashboard
        $dashboards = [
            'admin'   => '/admin/dashboard.php',
            'teacher' => '/teacher/dashboard.php',
            'student' => '/student/dashboard.php',
        ];
        $redirect = $dashboards[$_SESSION['role']] ?? '/auth/login.php';
        header("Location: $redirect");
        exit;
    }
}

/**
 * Get the base path for URL generation (handles subdirectory installations).
 * Adjust this if the project is not in the server root.
 */
function basePath(): string
{
    // Detect the project root dynamically
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    // Walk up to find the project root (where config/ exists)
    $parts = explode('/', trim($scriptDir, '/'));
    
    // For XAMPP / WAMP: the project is likely at /PWEB or similar
    // Return empty string if at root, otherwise the base directory
    $docRoot = $_SERVER['DOCUMENT_ROOT'];
    $projectRoot = dirname(__DIR__); // Go up one level from /includes
    
    $base = str_replace(str_replace('\\', '/', $docRoot), '', str_replace('\\', '/', $projectRoot));
    return rtrim($base, '/');
}

/**
 * Check if current user is logged in (without redirect).
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

/**
 * Get current user's display name.
 */
function getCurrentUserName(): string
{
    return $_SESSION['user_name'] ?? 'User';
}

/**
 * Get current user's role.
 */
function getCurrentRole(): string
{
    return $_SESSION['role'] ?? '';
}

/**
 * Sanitize output to prevent XSS.
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Set a flash message to display after redirect.
 *
 * @param string $type    'success', 'danger', or 'info'
 * @param string $message The message text
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Retrieve and clear flash message.
 */
function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
