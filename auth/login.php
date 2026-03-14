<?php
/**
 * Login Page
 * 
 * Handles authentication for all roles (admin, teacher, student).
 * Uses a role selector to determine which table to query.
 * Passwords are verified with password_verify() against bcrypt hashes.
 */

session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $dashboards = [
        'admin'   => '../admin/dashboard.php',
        'teacher' => '../teacher/dashboard.php',
        'student' => '../student/dashboard.php',
    ];
    header('Location: ' . ($dashboards[getCurrentRole()] ?? '../index.php'));
    exit;
}

$error = '';
$email = '';
$selectedRole = 'admin';

// ── Handle Login Form Submission ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email        = trim($_POST['email'] ?? '');
    $password     = $_POST['password'] ?? '';
    $selectedRole = $_POST['role'] ?? 'admin';

    // Input validation
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Determine which table to query based on selected role
        $tables = [
            'admin'   => 'admins',
            'teacher' => 'teachers',
            'student' => 'students',
        ];
        $table = $tables[$selectedRole] ?? null;

        if (!$table) {
            $error = 'Invalid role selected.';
        } else {
            try {
                // Query user by email using prepared statement
                $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE email = :email LIMIT 1");
                $stmt->execute(['email' => $email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    // Authentication successful — set session
                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['role']      = $selectedRole;

                    // Set display name based on role
                    if ($selectedRole === 'student') {
                        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                        $_SESSION['matricule'] = $user['matricule'];
                    } else {
                        $_SESSION['user_name'] = $user['name'];
                    }
                    $_SESSION['user_email'] = $user['email'];

                    // Regenerate session ID for security
                    session_regenerate_id(true);

                    // Redirect to role-specific dashboard
                    $redirects = [
                        'admin'   => '../admin/dashboard.php',
                        'teacher' => '../teacher/dashboard.php',
                        'student' => '../student/dashboard.php',
                    ];
                    header('Location: ' . $redirects[$selectedRole]);
                    exit;
                } else {
                    $error = 'Invalid email or password.';
                }
            } catch (PDOException $e) {
                error_log("Login error: " . $e->getMessage());
                $error = 'A system error occurred. Please try again.';
            }
        }
    }
}

$base = basePath();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — University Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/style.css">
</head>
<body>
    <div class="login-page">
        <div class="card login-card">
            <!-- Logo -->
            <div style="text-align:center; margin-bottom:24px;">
                <a href="<?php echo $base; ?>/index.php" class="header-logo" style="justify-content:center;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 2 4 3 6 3s6-1 6-3v-5"/>
                    </svg>
                    <span class="logo-text">UniPortal</span>
                </a>
            </div>

            <h2>Welcome Back</h2>
            <p class="login-subtitle">Sign in to your account to continue</p>

            <!-- Error Alert -->
            <?php if ($error): ?>
                <div class="alert alert-danger" style="margin-bottom:24px;">
                    <?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" data-validate>
                <!-- Role Selector -->
                <div class="form-group">
                    <label class="form-label">I am a</label>
                    <div class="role-selector">
                        <label class="role-option <?php echo $selectedRole === 'admin' ? 'active' : ''; ?>">
                            <input type="radio" name="role" value="admin" <?php echo $selectedRole === 'admin' ? 'checked' : ''; ?>>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin:0 auto 4px; display:block;"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                            Admin
                        </label>
                        <label class="role-option <?php echo $selectedRole === 'teacher' ? 'active' : ''; ?>">
                            <input type="radio" name="role" value="teacher" <?php echo $selectedRole === 'teacher' ? 'checked' : ''; ?>>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin:0 auto 4px; display:block;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Teacher
                        </label>
                        <label class="role-option <?php echo $selectedRole === 'student' ? 'active' : ''; ?>">
                            <input type="radio" name="role" value="student" <?php echo $selectedRole === 'student' ? 'checked' : ''; ?>>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin:0 auto 4px; display:block;"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 2 4 3 6 3s6-1 6-3v-5"/></svg>
                            Student
                        </label>
                    </div>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?php echo e($email); ?>" placeholder="you@university.dz" required>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" class="form-control"
                               placeholder="Enter your password" required>
                        <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Submit -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Sign In</button>
                </div>
            </form>

            <div class="login-links">
                <a href="<?php echo $base; ?>/index.php">&larr; Back to Home</a>
            </div>
        </div>
    </div>

    <script src="<?php echo $base; ?>/assets/js/script.js"></script>
</body>
</html>
