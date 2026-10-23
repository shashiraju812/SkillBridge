<?php
$pageTitle = 'Reports & Moderation';
$currentPage = 'reports.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    requireValidCSRF();
    $reportId = (int)($_POST['report_id'] ?? 0);
    $status = cleanInput($_POST['status'] ?? 'reviewed');
    $notes = cleanInput($_POST['admin_notes'] ?? '');

    if (in_array($status, ['pending', 'reviewed', 'resolved', 'dismissed'], true)) {
        $stmt = $db->prepare('UPDATE reports SET status = ?, admin_notes = ? WHERE id = ?');
        $stmt->execute([$status, $notes, $reportId]);
        setFlash('success', 'Report status updated.');
    }
    header('Location: /wtproject/admin/reports.php');
    exit;
}

$reports = $db->query('SELECT r.*, u.full_name AS reporter_name, u.email AS reporter_email FROM reports r LEFT JOIN users u ON u.id = r.reporter_user_id ORDER BY r.created_at DESC')->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="main-content">
<div class="container">
    <div class="dashboard-layout">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <div>
            <div class="page-header">
                <h1>Reported Content & Flags</h1>
                <p><?php echo count($reports); ?> reports received</p>
            </div>

            <div class="card">
                <?php if (empty($reports)): ?>
                    <div class="empty-state">
                        <h3>No flagged content</h3>
                        <p>No reports currently submitted by students or users.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Type</th>
                                    <th>Reported By</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Admin Notes / Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports as $r): ?>
                                <tr>
                                    <td>#<?php echo $r['id']; ?></td>
                                    <td><span class="badge badge-info"><?php echo ucfirst($r['reported_type']); ?></span></td>
                                    <td class="text-sm">
                                        <?php echo e($r['reporter_name'] ?: 'Anonymous'); ?><br>
                                        <span class="text-xs text-muted"><?php echo e($r['reporter_email']); ?></span>
                                    </td>
                                    <td class="text-sm" style="max-width:280px;"><?php echo e($r['reason']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $r['status']==='resolved'?'success':($r['status']==='pending'?'danger':'warning'); ?>">
                                            <?php echo ucfirst($r['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-flex flex-wrap gap-1">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="action" value="update_report">
                                            <input type="hidden" name="report_id" value="<?php echo $r['id']; ?>">
                                            <select name="status" class="form-control" style="font-size:0.75rem;padding:0.2rem;width:auto;">
                                                <option value="pending" <?php echo $r['status']==='pending'?'selected':''; ?>>Pending</option>
                                                <option value="reviewed" <?php echo $r['status']==='reviewed'?'selected':''; ?>>Reviewed</option>
                                                <option value="resolved" <?php echo $r['status']==='resolved'?'selected':''; ?>>Resolved</option>
                                                <option value="dismissed" <?php echo $r['status']==='dismissed'?'selected':''; ?>>Dismissed</option>
                                            </select>
                                            <input type="text" name="admin_notes" class="form-control" style="font-size:0.75rem;padding:0.2rem;width:120px;" placeholder="Notes" value="<?php echo e($r['admin_notes'] ?? ''); ?>">
                                            <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>

