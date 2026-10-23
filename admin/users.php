<?php
$pageTitle = 'Manage Users';
$currentPage = 'users.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');
$db = getDB();

// Handle Actions (Toggle status, change role)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    requireValidCSRF();
    $targetUserId = (int)($_POST['user_id'] ?? 0);

    // Prevent action on current admin's own account
    if ($targetUserId === getCurrentUserId()) {
        setFlash('error', 'You cannot modify your own administrative account.');
    } else {
        if ($_POST['action'] === 'toggle_status') {
            $stmt = $db->prepare('SELECT is_active FROM users WHERE id = ?');
            $stmt->execute([$targetUserId]);
            $current = $stmt->fetchColumn();
            if ($current !== false) {
                $newStatus = $current ? 0 : 1;
                $db->prepare('UPDATE users SET is_active = ? WHERE id = ?')->execute([$newStatus, $targetUserId]);
                setFlash('success', 'User status updated successfully.');
            }
        } elseif ($_POST['action'] === 'change_role') {
            $newRole = cleanInput($_POST['new_role'] ?? '');
            if (in_array($newRole, ['student', 'recruiter', 'admin'], true)) {
                $db->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$newRole, $targetUserId]);
                setFlash('success', 'User role updated successfully.');
            }
        }
    }
    header('Location: /wtproject/admin/users.php');
    exit;
}

// Filters & Search
$roleFilter = cleanInput($_GET['role'] ?? '');
$search = cleanInput($_GET['search'] ?? '');

$where = ['1=1'];
$params = [];

if ($roleFilter) {
    $where[] = 'role = ?';
    $params[] = $roleFilter;
}
if ($search) {
    $where[] = '(full_name LIKE ? OR email LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSQL = implode(' AND ', $where);
$stmt = $db->prepare("SELECT * FROM users WHERE $whereSQL ORDER BY created_at DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="main-content">
<div class="container">
    <div class="dashboard-layout">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <div>
            <div class="page-header">
                <h1>Manage Users</h1>
                <p><?php echo count($users); ?> registered accounts found</p>
            </div>

            <!-- Filters -->
            <form class="filters-bar" method="GET">
                <div class="form-group">
                    <label>Search</label>
                    <input type="text" name="search" class="form-control" value="<?php echo e($search); ?>" placeholder="Name or email...">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" class="form-control">
                        <option value="">All Roles</option>
                        <option value="student" <?php echo $roleFilter==='student'?'selected':''; ?>>Student</option>
                        <option value="recruiter" <?php echo $roleFilter==='recruiter'?'selected':''; ?>>Recruiter</option>
                        <option value="admin" <?php echo $roleFilter==='admin'?'selected':''; ?>>Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>

            <div class="card">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Change Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <strong><?php echo e($u['full_name']); ?></strong><br>
                                    <span class="text-xs text-muted"><?php echo e($u['email']); ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $u['role']==='admin'?'purple':($u['role']==='recruiter'?'warning':'info'); ?>">
                                        <?php echo ucfirst($u['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $u['is_active']?'success':'danger'; ?>">
                                        <?php echo $u['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="text-sm"><?php echo formatDate($u['created_at']); ?></td>
                                <td>
                                    <?php if ($u['id'] !== getCurrentUserId()): ?>
                                    <form method="POST" class="d-flex gap-1" style="align-items:center;">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="action" value="change_role">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <select name="new_role" class="form-control" style="width:auto;padding:0.25rem 0.5rem;font-size:0.8rem;" onchange="this.form.submit()">
                                            <option value="student" <?php echo $u['role']==='student'?'selected':''; ?>>Student</option>
                                            <option value="recruiter" <?php echo $u['role']==='recruiter'?'selected':''; ?>>Recruiter</option>
                                            <option value="admin" <?php echo $u['role']==='admin'?'selected':''; ?>>Admin</option>
                                        </select>
                                    </form>
                                    <?php else: ?>
                                    <span class="text-xs text-muted">Current Admin</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($u['id'] !== getCurrentUserId()): ?>
                                    <form method="POST" style="display:inline;">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <button type="submit" class="btn btn-sm <?php echo $u['is_active']?'btn-danger':'btn-success'; ?>" onclick="return confirm('Change active status for this user?')">
                                            <?php echo $u['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>

