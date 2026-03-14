<?php
/**
 * Admin — Students Management
 * 
 * Full CRUD for students: list, search, create, update, delete.
 * Uses modal forms for create/edit operations.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole('admin');

$pageTitle = 'Students Management';
$base = basePath();

// ── Handle POST Actions ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'create') {
            // Validate inputs
            $matricule  = trim($_POST['matricule'] ?? '');
            $firstName  = trim($_POST['first_name'] ?? '');
            $lastName   = trim($_POST['last_name'] ?? '');
            $email      = trim($_POST['email'] ?? '');
            $birthDate  = trim($_POST['birth_date'] ?? '');
            $level      = trim($_POST['level'] ?? 'L1');
            $password   = trim($_POST['password'] ?? 'password');

            if (empty($matricule) || empty($firstName) || empty($lastName) || empty($email)) {
                setFlash('danger', 'Please fill in all required fields.');
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO students (matricule, first_name, last_name, email, birth_date, level, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$matricule, $firstName, $lastName, $email, $birthDate ?: null, $level, $hash]);
                setFlash('success', 'Student created successfully.');
            }

        } elseif ($action === 'update') {
            $id         = (int)($_POST['id'] ?? 0);
            $matricule  = trim($_POST['matricule'] ?? '');
            $firstName  = trim($_POST['first_name'] ?? '');
            $lastName   = trim($_POST['last_name'] ?? '');
            $email      = trim($_POST['email'] ?? '');
            $birthDate  = trim($_POST['birth_date'] ?? '');
            $level      = trim($_POST['level'] ?? 'L1');

            $stmt = $pdo->prepare("UPDATE students SET matricule=?, first_name=?, last_name=?, email=?, birth_date=?, level=? WHERE id=?");
            $stmt->execute([$matricule, $firstName, $lastName, $email, $birthDate ?: null, $level, $id]);
            setFlash('success', 'Student updated successfully.');

        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', 'Student deleted successfully.');
        }
    } catch (PDOException $e) {
        error_log("Students error: " . $e->getMessage());
        if ($e->getCode() == 23000) {
            setFlash('danger', 'Duplicate entry: matricule or email already exists.');
        } else {
            setFlash('danger', 'An error occurred. Please try again.');
        }
    }

    header("Location: students.php");
    exit;
}

// ── Fetch All Students ────────────────────────────────────────
$students = $pdo->query("SELECT * FROM students ORDER BY last_name, first_name")->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<!-- ── Page Header ─────────────────────────────────────── -->
<div class="page-header">
    <h1>Students</h1>
    <button class="btn btn-primary" onclick="openModal('student-modal')">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Student
    </button>
</div>

<!-- ── Students Table ──────────────────────────────────── -->
<div class="table-container">
    <div class="table-toolbar">
        <div class="search-box">
            <span class="search-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </span>
            <input type="text" placeholder="Search students..." data-search-table="students-table">
        </div>
        <span class="text-small"><?php echo count($students); ?> students total</span>
    </div>

    <table id="students-table" class="responsive-table">
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Name</th>
                <th>Email</th>
                <th>Level</th>
                <th>Birth Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($students)): ?>
                <tr><td colspan="6" class="empty-state"><p>No students found</p></td></tr>
            <?php else: ?>
                <?php foreach ($students as $s): ?>
                    <tr>
                        <td data-label="Matricule"><?php echo e($s['matricule']); ?></td>
                        <td data-label="Name"><?php echo e($s['first_name'] . ' ' . $s['last_name']); ?></td>
                        <td data-label="Email"><?php echo e($s['email']); ?></td>
                        <td data-label="Level"><?php echo e($s['level']); ?></td>
                        <td data-label="Birth Date"><?php echo $s['birth_date'] ? date('d/m/Y', strtotime($s['birth_date'])) : '—'; ?></td>
                        <td data-label="Actions">
                            <div class="action-btns">
                                <!-- Edit Button -->
                                <button class="btn-edit" onclick="editStudent(<?php echo htmlspecialchars(json_encode($s), ENT_QUOTES); ?>)" title="Edit">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <!-- Delete Button -->
                                <form method="POST" id="delete-student-<?php echo $s['id']; ?>" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                    <button type="button" class="btn-delete" onclick="confirmDelete('Delete this student? All their grades will also be removed.', 'delete-student-<?php echo $s['id']; ?>')" title="Delete">
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

<!-- ── Add/Edit Student Modal ──────────────────────────── -->
<div class="modal-overlay" id="student-modal">
    <div class="modal">
        <div class="modal-header">
            <h2 id="student-modal-title">Add Student</h2>
            <button class="modal-close" onclick="closeModal('student-modal')">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" data-validate id="student-form">
                <input type="hidden" name="action" value="create" id="student-action">
                <input type="hidden" name="id" value="" id="student-id">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="matricule">Matricule *</label>
                        <input type="text" id="matricule" name="matricule" class="form-control" placeholder="STU2024XXX" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="level">Level *</label>
                        <select id="level" name="level" class="form-control" required>
                            <option value="L1">L1</option>
                            <option value="L2">L2</option>
                            <option value="L3">L3</option>
                            <option value="M1">M1</option>
                            <option value="M2">M2</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" placeholder="First name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Last name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="student_email">Email *</label>
                    <input type="email" id="student_email" name="email" class="form-control" placeholder="student@university.dz" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="birth_date">Birth Date</label>
                    <input type="date" id="birth_date" name="birth_date" class="form-control">
                </div>

                <div class="form-group" id="password-group">
                    <label class="form-label" for="student_password">Password</label>
                    <input type="text" id="student_password" name="password" class="form-control" value="password" placeholder="Default password">
                    <div class="form-helper">Default password is "password". Students can change it later.</div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="student-submit-btn">Add Student</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('student-modal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
/**
 * Pre-fill the modal form for editing an existing student.
 */
function editStudent(student) {
    document.getElementById('student-modal-title').textContent = 'Edit Student';
    document.getElementById('student-action').value = 'update';
    document.getElementById('student-id').value = student.id;
    document.getElementById('matricule').value = student.matricule;
    document.getElementById('first_name').value = student.first_name;
    document.getElementById('last_name').value = student.last_name;
    document.getElementById('student_email').value = student.email;
    document.getElementById('birth_date').value = student.birth_date || '';
    document.getElementById('level').value = student.level;
    document.getElementById('student-submit-btn').textContent = 'Update Student';
    // Hide password field on edit
    document.getElementById('password-group').style.display = 'none';
    openModal('student-modal');
}

// Reset form when opening for "Add"
const addBtn = document.querySelector('[onclick="openModal(\'student-modal\')"]');
if (addBtn) {
    addBtn.addEventListener('click', () => {
        document.getElementById('student-modal-title').textContent = 'Add Student';
        document.getElementById('student-action').value = 'create';
        document.getElementById('student-id').value = '';
        document.getElementById('student-form').reset();
        document.getElementById('student-submit-btn').textContent = 'Add Student';
        document.getElementById('password-group').style.display = '';
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
