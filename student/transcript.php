<?php
/**
 * Student — Transcript Page
 * 
 * Formal academic transcript with all grades, coefficients,
 * and weighted average. Designed for printing/PDF export.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole('student');

$pageTitle = 'Academic Transcript';
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

<!-- ── Page Header ─────────────────────────────────────── -->
<div class="page-header">
    <h1>Academic Transcript</h1>
    <button class="btn btn-primary" onclick="printTranscript()">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>
        </svg>
        Print Transcript
    </button>
</div>

<!-- ── Transcript Document ─────────────────────────────── -->
<div class="card" style="padding:0; overflow:hidden;">
    <!-- Transcript Header -->
    <div class="transcript-header">
        <h1>University — Academic Transcript</h1>
        <p>Official Student Academic Record — <?php echo date('Y'); ?></p>
    </div>

    <!-- Student Information -->
    <dl class="transcript-info">
        <div>
            <dt>Full Name</dt>
            <dd><?php echo e($student['first_name'] . ' ' . $student['last_name']); ?></dd>
        </div>
        <div>
            <dt>Matricule</dt>
            <dd><?php echo e($student['matricule']); ?></dd>
        </div>
        <div>
            <dt>Email</dt>
            <dd><?php echo e($student['email']); ?></dd>
        </div>
        <div>
            <dt>Level</dt>
            <dd><?php echo e($student['level']); ?></dd>
        </div>
        <?php if ($student['birth_date']): ?>
            <div>
                <dt>Date of Birth</dt>
                <dd><?php echo date('d/m/Y', strtotime($student['birth_date'])); ?></dd>
            </div>
        <?php endif; ?>
        <div>
            <dt>Date Issued</dt>
            <dd><?php echo date('d/m/Y'); ?></dd>
        </div>
    </dl>

    <!-- Grades Table -->
    <table class="transcript-table">
        <thead>
            <tr>
                <th>Module Code</th>
                <th>Module Name</th>
                <th class="text-center">Coefficient</th>
                <th class="text-center">Grade (/20)</th>
                <th class="text-center">Weighted</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($grades)): ?>
                <tr><td colspan="5" class="empty-state"><p>No grades recorded.</p></td></tr>
            <?php else: ?>
                <?php foreach ($grades as $g): ?>
                    <tr>
                        <td><?php echo e($g['code']); ?></td>
                        <td><?php echo e($g['module_name']); ?></td>
                        <td class="text-center"><?php echo $g['coefficient']; ?></td>
                        <td class="text-center">
                            <span class="<?php echo $g['grade'] >= 10 ? 'grade-pass' : 'grade-fail'; ?>">
                                <?php echo number_format($g['grade'], 2); ?>
                            </span>
                        </td>
                        <td class="text-center"><?php echo number_format($g['grade'] * $g['coefficient'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2"><strong>Total / Weighted Average</strong></td>
                <td class="text-center"><strong><?php echo $totalWeight; ?></strong></td>
                <td class="text-center">
                    <strong class="<?php echo $weightedAverage >= 10 ? 'grade-pass' : 'grade-fail'; ?>">
                        <?php echo $weightedAverage; ?>/20
                    </strong>
                </td>
                <td class="text-center"><strong><?php echo number_format($totalPoints, 2); ?></strong></td>
            </tr>
            <tr>
                <td colspan="5" class="text-center" style="font-size:14px; padding:16px;">
                    <strong>Overall Result:
                        <span class="<?php echo $weightedAverage >= 10 ? 'grade-pass' : 'grade-fail'; ?>">
                            <?php echo $weightedAverage >= 10 ? 'PASSED' : 'FAILED'; ?>
                        </span>
                    </strong>
                </td>
            </tr>
        </tfoot>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
