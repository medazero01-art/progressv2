<?php
/**
 * Admin — Teachers Management
 * 
 * CRUD for teacher accounts: list, create, update, delete.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole('admin');

$pageTitle = 'Teachers Management';
$base = basePath();

// ── Handle POST Actions ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'create') {
            $name     = trim($_POST['name'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? 'password');

            if (empty($name) || empty($email)) {
                setFlash('danger', 'Please fill in all required fields.');
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO teachers (name, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$name, $email, $hash]);
                setFlash('success', 'Teacher created successfully.');
            }

        } elseif ($action === 'update') {
            $id    = (int)($_POST['id'] ?? 0);
            $name  = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');

            $stmt = $pdo->prepare("UPDATE teachers SET name=?, email=? WHERE id=?");
            $stmt->execute([$name, $email, $id]);
            setFlash('success', 'Teacher updated successfully.');

        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            // Set teacher_id to NULL on modules before deleting
            $stmt = $pdo->prepare("UPDATE modules SET teacher_id = NULL WHERE teacher_id = ?");
            $stmt->execute([$id]);
            $stmt = $pdo->prepare("DELETE FROM teachers WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', 'Teacher deleted successfully.');
        }
    } catch (PDOException $e) {
        error_log("Teachers error: " . $e->getMessage());
        if ($e->getCode() == 23000) {
            setFlash('danger', 'Email already exists.');
        } else {
            setFlash('danger', 'An error occurred. Please try again.');
        }
    }

    header("Location: teachers.php");
    exit;
}

// ── Fetch All Teachers with Module Count ──────────────────────
$teachers = $pdo->query("
    SELECT t.*, COUNT(m.id) AS module_count
    FROM teachers t
    LEFT JOIN modules m ON m.teacher_id = t.id
    GROUP BY t.id
    ORDER BY t.name
")->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<!-- ── Page Header ─────────────────────────────────────── -->
<div class="page-header">
    <h1>Teachers</h1>
    <button class="btn btn-primary" onclick="openModal('teacher-modal')">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Teacher
    </button>
</div>

<!-- ── Teachers Table ──────────────────────────────────── -->
<div class="table-container">
    <div class="table-toolbar">
        <div class="search-box">
            <span class="search-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </span>
            <input type="text" placeholder="Search teachers..." data-search-table="teachers-table">
        </div>
        <span class="text-small"><?php echo count($teachers); ?> teachers total</span>
    </div>

    <table id="teachers-table" class="responsive-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th class="text-center">Modules</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($teachers)): ?>
                <tr><td colspan="5" class="empty-state"><p>No teachers found</p></td></tr>
            <?php else: ?>
                <?php foreach ($teachers as $t): ?>
                    <tr>
                        <td data-label="ID"><?php echo $t['id']; ?></td>
                        <td data-label="Name"><?php echo e($t['name']); ?></td>
                        <td data-label="Email"><?php echo e($t['email']); ?></td>
                        <td data-label="Modules" class="text-center"><?php echo $t['module_count']; ?></td>
                        <td data-label="Actions">
                            <div class="action-btns">
                                <button class="btn-edit" onclick='editTeacher(<?php echo json_encode($t); ?>)' title="Edit">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <form method="POST" id="delete-teacher-<?php echo $t['id']; ?>" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                    <button type="button" class="btn-delete" onclick="confirmDelete('Delete this teacher? Their modules will be unassigned.', 'delete-teacher-<?php echo $t['id']; ?>')" title="Delete">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ── Add/Edit Teacher Modal ──────────────────────────── -->
<div class="modal-overlay" id="teacher-modal">
    <div class="modal">
        <div class="modal-header">
            <h2 id="teacher-modal-title">Add Teacher</h2>
            <button class="modal-close" onclick="closeModal('teacher-modal')">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" data-validate id="teacher-form">
                <input type="hidden" name="action" value="create" id="teacher-action">
                <input type="hidden" name="id" value="" id="teacher-id">

                <div class="form-group">
                    <label class="form-label" for="teacher_name">Full Name *</label>
                    <input type="text" id="teacher_name" name="name" class="form-control" placeholder="Dr. Full Name" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="teacher_email">Email *</label>
                    <input type="email" id="teacher_email" name="email" class="form-control" placeholder="teacher@university.dz" required>
                </div>

                <div class="form-group" id="teacher-password-group">
                    <label class="form-label" for="teacher_password">Password</label>
                    <input type="text" id="teacher_password" name="password" class="form-control" value="password" placeholder="Default: password">
                    <div class="form-helper">Default password is "password".</div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="teacher-submit-btn">Add Teacher</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('teacher-modal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editTeacher(teacher) {
    document.getElementById('teacher-modal-title').textContent = 'Edit Teacher';
    document.getElementById('teacher-action').value = 'update';
    document.getElementById('teacher-id').value = teacher.id;
    document.getElementById('teacher_name').value = teacher.name;
    document.getElementById('teacher_email').value = teacher.email;
    document.getElementById('teacher-submit-btn').textContent = 'Update Teacher';
    document.getElementById('teacher-password-group').style.display = 'none';
    openModal('teacher-modal');
}

const addTeacherBtn = document.querySelector('[onclick="openModal(\'teacher-modal\')"]');
if (addTeacherBtn) {
    addTeacherBtn.addEventListener('click', () => {
        document.getElementById('teacher-modal-title').textContent = 'Add Teacher';
        document.getElementById('teacher-action').value = 'create';
        document.getElementById('teacher-id').value = '';
        document.getElementById('teacher-form').reset();
        document.getElementById('teacher-submit-btn').textContent = 'Add Teacher';
        document.getElementById('teacher-password-group').style.display = '';
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
