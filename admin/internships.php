<?php
$pageTitle = 'Moderate Internships';
$currentPage = 'internships.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    requireValidCSRF();
    $intId = (int)($_POST['internship_id'] ?? 0);
    
    if ($_POST['action'] === 'close') {
        $db->prepare('UPDATE internships SET status = "closed" WHERE id = ?')->execute([$intId]);
        setFlash('success', 'Internship marked as closed.');
    } elseif ($_POST['action'] === 'activate') {
        $db->prepare('UPDATE internships SET status = "active" WHERE id = ?')->execute([$intId]);
        setFlash('success', 'Internship activated.');
    } elseif ($_POST['action'] === 'delete') {
        $db->prepare('DELETE FROM internships WHERE id = ?')->execute([$intId]);
        setFlash('success', 'Internship listing removed from platform.');
    }
    header('Location: /wtproject/admin/internships.php');
    exit;
}

$statusFilter = cleanInput($_GET['status'] ?? '');
$search = cleanInput($_GET['search'] ?? '');

$where = ['1=1'];
$params = [];

if ($statusFilter) {
    $where[] = 'i.status = ?';
    $params[] = $statusFilter;
}
if ($search) {
    $where[] = '(i.title LIKE ? OR rp.company_name LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSQL = implode(' AND ', $where);
$stmt = $db->prepare("SELECT i.*, rp.company_name, (SELECT COUNT(*) FROM applications WHERE internship_id=i.id) AS app_count FROM internships i JOIN recruiter_profiles rp ON rp.id = i.recruiter_profile_id WHERE $whereSQL ORDER BY i.created_at DESC");
$stmt->execute($params);
$internships = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="main-content">
<div class="container">
    <div class="dashboard-layout">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <div>
            <div class="page-header">
                <h1>Moderate Listings</h1>
                <p><?php echo count($internships); ?> internships on the platform</p>
            </div>

            <!-- Filters -->
            <form class="filters-bar" method="GET">
                <div class="form-group">
                    <label>Search</label>
                    <input type="text" name="search" class="form-control" value="<?php echo e($search); ?>" placeholder="Title or company...">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="active" <?php echo $statusFilter==='active'?'selected':''; ?>>Active</option>
                        <option value="draft" <?php echo $statusFilter==='draft'?'selected':''; ?>>Draft</option>
                        <option value="closed" <?php echo $statusFilter==='closed'?'selected':''; ?>>Closed</option>
                        <option value="expired" <?php echo $statusFilter==='expired'?'selected':''; ?>>Expired</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>

            <div class="card">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Title / Company</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Apps</th>
                                <th>Deadline</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($internships as $i): ?>
                            <tr>
                                <td>
                                    <a href="/wtproject/internship-details.php?id=<?php echo $i['id']; ?>" target="_blank">
                                        <strong><?php echo e($i['title']); ?></strong>
                                    </a><br>
                                    <span class="text-xs text-muted"><?php echo e($i['company_name']); ?></span>
                                </td>
                                <td>
                                    <?php if ($i['is_sample']): ?>
                                    <span class="badge badge-secondary">Sample</span>
                                    <?php else: ?>
                                    <span class="badge badge-info">Verified</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $i['status']==='active'?'success':($i['status']==='draft'?'secondary':'danger'); ?>">
                                        <?php echo ucfirst($i['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo (int)$i['app_count']; ?></td>
                                <td class="text-sm"><?php echo formatDate($i['application_deadline']); ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <?php if ($i['status'] === 'active'): ?>
                                        <form method="POST" style="display:inline;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="action" value="close">
                                            <input type="hidden" name="internship_id" value="<?php echo $i['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline">Close</button>
                                        </form>
                                        <?php else: ?>
                                        <form method="POST" style="display:inline;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="action" value="activate">
                                            <input type="hidden" name="internship_id" value="<?php echo $i['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-success">Activate</button>
                                        </form>
                                        <?php endif; ?>

                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Permanently delete this listing?')">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="internship_id" value="<?php echo $i['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
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

