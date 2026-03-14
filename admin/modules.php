<?php
/**
 * Admin — Modules Management
 * 
 * CRUD for modules: create, assign teacher, edit, delete.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole('admin');

$pageTitle = 'Modules Management';
$base = basePath();

// ── Handle POST Actions ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'create') {
            $code        = trim($_POST['code'] ?? '');
            $name        = trim($_POST['name'] ?? '');
            $coefficient = (int)($_POST['coefficient'] ?? 1);
            $teacherId   = !empty($_POST['teacher_id']) ? (int)$_POST['teacher_id'] : null;

            if (empty($code) || empty($name)) {
                setFlash('danger', 'Code and name are required.');
            } else {
                $stmt = $pdo->prepare("INSERT INTO modules (code, name, coefficient, teacher_id) VALUES (?, ?, ?, ?)");
                $stmt->execute([$code, $name, $coefficient, $teacherId]);
                setFlash('success', 'Module created successfully.');
            }

        } elseif ($action === 'update') {
            $id          = (int)($_POST['id'] ?? 0);
            $code        = trim($_POST['code'] ?? '');
            $name        = trim($_POST['name'] ?? '');
            $coefficient = (int)($_POST['coefficient'] ?? 1);
            $teacherId   = !empty($_POST['teacher_id']) ? (int)$_POST['teacher_id'] : null;

            $stmt = $pdo->prepare("UPDATE modules SET code=?, name=?, coefficient=?, teacher_id=? WHERE id=?");
            $stmt->execute([$code, $name, $coefficient, $teacherId, $id]);
            setFlash('success', 'Module updated successfully.');

        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM modules WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', 'Module deleted successfully.');
        }
    } catch (PDOException $e) {
        error_log("Modules error: " . $e->getMessage());
        if ($e->getCode() == 23000) {
            setFlash('danger', 'Module code already exists.');
        } else {
            setFlash('danger', 'An error occurred. Please try again.');
        }
    }

    header("Location: modules.php");
    exit;
}

// ── Fetch All Modules with Teacher Name ───────────────────────
$modules = $pdo->query("
    SELECT m.*, t.name AS teacher_name
    FROM modules m
    LEFT JOIN teachers t ON t.id = m.teacher_id
    ORDER BY m.code
")->fetchAll();

// Fetch teachers for the dropdown
$teachers = $pdo->query("SELECT id, name FROM teachers ORDER BY name")->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<!-- ── Page Header ─────────────────────────────────────── -->
<div class="page-header">
    <h1>Modules</h1>
    <button class="btn btn-primary" onclick="openModal('module-modal')">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Module
    </button>
</div>

<!-- ── Modules Table ───────────────────────────────────── -->
<div class="table-container">
    <div class="table-toolbar">
        <div class="search-box">
            <span class="search-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </span>
            <input type="text" placeholder="Search modules..." data-search-table="modules-table">
        </div>
        <span class="text-small"><?php echo count($modules); ?> modules total</span>
    </div>

    <table id="modules-table" class="responsive-table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Teacher</th>
                <th class="text-center">Coefficient</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($modules)): ?>
                <tr><td colspan="5" class="empty-state"><p>No modules found</p></td></tr>
            <?php else: ?>
                <?php foreach ($modules as $m): ?>
                    <tr>
                        <td data-label="Code"><strong><?php echo e($m['code']); ?></strong></td>
                        <td data-label="Name"><?php echo e($m['name']); ?></td>
                        <td data-label="Teacher"><?php echo $m['teacher_name'] ? e($m['teacher_name']) : '<span style="color:var(--gray-400)">Unassigned</span>'; ?></td>
                        <td data-label="Coefficient" class="text-center"><?php echo $m['coefficient']; ?></td>
                        <td data-label="Actions">
                            <div class="action-btns">
                                <button class="btn-edit" onclick='editModule(<?php echo json_encode($m); ?>)' title="Edit">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <form method="POST" id="delete-module-<?php echo $m['id']; ?>" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $m['id']; ?>">
                                    <button type="button" class="btn-delete" onclick="confirmDelete('Delete this module? Associated grades will also be removed.', 'delete-module-<?php echo $m['id']; ?>')" title="Delete">
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

<!-- ── Add/Edit Module Modal ───────────────────────────── -->
<div class="modal-overlay" id="module-modal">
    <div class="modal">
        <div class="modal-header">
            <h2 id="module-modal-title">Add Module</h2>
            <button class="modal-close" onclick="closeModal('module-modal')">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" data-validate id="module-form">
                <input type="hidden" name="action" value="create" id="module-action">
                <input type="hidden" name="id" value="" id="module-id">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="module_code">Module Code *</label>
                        <input type="text" id="module_code" name="code" class="form-control" placeholder="INFO101" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="module_coefficient">Coefficient *</label>
                        <input type="number" id="module_coefficient" name="coefficient" class="form-control" value="1" min="1" max="10" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="module_name">Module Name *</label>
                    <input type="text" id="module_name" name="name" class="form-control" placeholder="Module full name" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="module_teacher">Assigned Teacher</label>
                    <select id="module_teacher" name="teacher_id" class="form-control">
                        <option value="">— No teacher assigned —</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?php echo $t['id']; ?>"><?php echo e($t['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="module-submit-btn">Add Module</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('module-modal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editModule(mod) {
    document.getElementById('module-modal-title').textContent = 'Edit Module';
    document.getElementById('module-action').value = 'update';
    document.getElementById('module-id').value = mod.id;
    document.getElementById('module_code').value = mod.code;
    document.getElementById('module_name').value = mod.name;
    document.getElementById('module_coefficient').value = mod.coefficient;
    document.getElementById('module_teacher').value = mod.teacher_id || '';
    document.getElementById('module-submit-btn').textContent = 'Update Module';
    openModal('module-modal');
}

const addModuleBtn = document.querySelector('[onclick="openModal(\'module-modal\')"]');
if (addModuleBtn) {
    addModuleBtn.addEventListener('click', () => {
        document.getElementById('module-modal-title').textContent = 'Add Module';
        document.getElementById('module-action').value = 'create';
        document.getElementById('module-id').value = '';
        document.getElementById('module-form').reset();
        document.getElementById('module-submit-btn').textContent = 'Add Module';
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
