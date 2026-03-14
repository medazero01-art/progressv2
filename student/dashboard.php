<?php
/**
 * Student Dashboard
 * 
 * Shows the logged-in student's profile info, their grades,
 * and their weighted average.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole('student');

$pageTitle = 'Student Dashboard';
$studentId = $_SESSION['user_id'];
$base = basePath();

// ── Fetch Student Profile ─────────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$studentId]);
$student = $stmt->fetch();

// ── Fetch Grades ──────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT g.grade, m.code, m.name AS module_name, m.coefficient
    FROM grades g
    JOIN modules m ON m.id = g.module_id
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

<!-- ── Page Header ────────────────────────────────────── -->
<div class="page-header">
    <h1>Dashboard</h1>
</div>

<!-- ── Profile Card ───────────────────────────────────── -->
<div class="card mb-32">
    <div class="profile-card">
        <div class="profile-avatar">
            <?php echo strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)); ?>
        </div>
        <div class="profile-info">
            <h2><?php echo e($student['first_name'] . ' ' . $student['last_name']); ?></h2>
            <div class="profile-meta">
                <span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="18" rx="2"/><path d="M16 3v4M8 3v4M2 9h20"/></svg>
                    <?php echo e($student['matricule']); ?>
                </span>
                <span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <?php echo e($student['email']); ?>
                </span>
                <span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 2 4 3 6 3s6-1 6-3v-5"/></svg>
                    Level: <?php echo e($student['level']); ?>
                </span>
                <?php if ($student['birth_date']): ?>
                    <span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Born: <?php echo date('d/m/Y', strtotime($student['birth_date'])); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ── Grades Overview ─────────────────────────────────── -->
<div class="flex justify-between items-center flex-wrap gap-16 mb-24">
    <h2 style="margin-bottom:0;">My Grades</h2>
    <div class="average-badge <?php echo $weightedAverage >= 10 ? 'pass' : 'fail'; ?>">
        Average: <?php echo $weightedAverage; ?>/20
    </div>
</div>

<div class="table-container">
    <table class="responsive-table">
        <thead>
            <tr>
                <th>Module</th>
                <th>Code</th>
                <th class="text-center">Coefficient</th>
                <th class="text-center">Grade</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($grades)): ?>
                <tr><td colspan="4" class="empty-state"><p>No grades available yet.</p></td></tr>
            <?php else: ?>
                <?php foreach ($grades as $g): ?>
                    <tr>
                        <td data-label="Module"><?php echo e($g['module_name']); ?></td>
                        <td data-label="Code"><?php echo e($g['code']); ?></td>
                        <td data-label="Coefficient" class="text-center"><?php echo $g['coefficient']; ?></td>
                        <td data-label="Grade" class="text-center">
                            <span class="grade-value <?php echo $g['grade'] >= 10 ? 'grade-pass' : 'grade-fail'; ?>">
                                <?php echo number_format($g['grade'], 2); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <!-- Average Row -->
                <tr class="total-row">
                    <td colspan="3" data-label=""><strong>Weighted Average</strong></td>
                    <td class="text-center" data-label="Average">
                        <strong class="<?php echo $weightedAverage >= 10 ? 'grade-pass' : 'grade-fail'; ?>">
                            <?php echo $weightedAverage; ?>/20
                        </strong>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ── Quick Links ─────────────────────────────────────── -->
<div class="mt-32">
    <a href="<?php echo $base; ?>/student/transcript.php" class="btn btn-secondary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        View Transcript
    </a>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
