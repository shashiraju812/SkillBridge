<?php
$pageTitle='Manage Internships';$currentPage='manage-internships.php';
require_once __DIR__.'/../config/database.php';require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/csrf.php';require_once __DIR__.'/../includes/functions.php';
requireRole('recruiter');$db=getDB();$rpId=getRecruiterProfileId($db);

if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['action'])){requireValidCSRF();
    $intId=(int)$_POST['internship_id'];
    // Verify ownership
    $check=$db->prepare('SELECT id FROM internships WHERE id=? AND recruiter_profile_id=?');$check->execute([$intId,$rpId]);
    if($check->fetch()){
        if($_POST['action']==='close')$db->prepare('UPDATE internships SET status="closed" WHERE id=?')->execute([$intId]);
        if($_POST['action']==='activate')$db->prepare('UPDATE internships SET status="active" WHERE id=?')->execute([$intId]);
        if($_POST['action']==='delete'){$db->prepare('DELETE FROM internships WHERE id=?')->execute([$intId]);}
        setFlash('success','Internship updated.');
    }
    header('Location:/wtproject/recruiter/manage-internships.php');exit;
}

$stmt=$db->prepare('SELECT i.*, (SELECT COUNT(*) FROM applications WHERE internship_id=i.id) AS app_count FROM internships i WHERE i.recruiter_profile_id=? ORDER BY i.created_at DESC');
$stmt->execute([$rpId]);$internships=$stmt->fetchAll();

include __DIR__.'/../includes/header.php';
?>
<div class="main-content"><div class="container"><div class="dashboard-layout"><?php include __DIR__.'/sidebar.php'; ?>
<div><div class="page-header d-flex justify-between align-center"><div><h1>Manage Internships</h1><p><?php echo count($internships); ?> listings</p></div><a href="/wtproject/recruiter/create-internship.php" class="btn btn-primary">+ Create New</a></div>
<?php if(empty($internships)): ?><div class="card"><div class="empty-state"><h3>No internships yet</h3><p>Create your first internship listing.</p></div></div>
<?php else: ?>
<div class="table-responsive"><table><thead><tr><th>Title</th><th>Status</th><th>Applications</th><th>Deadline</th><th>Actions</th></tr></thead><tbody>
<?php foreach($internships as $i): ?>
<tr><td><strong><?php echo e($i['title']); ?></strong></td>
<td><span class="badge badge-<?php echo $i['status']==='active'?'success':($i['status']==='draft'?'secondary':'danger'); ?>"><?php echo ucfirst($i['status']); ?></span></td>
<td><a href="/wtproject/recruiter/applicants.php?internship_id=<?php echo $i['id']; ?>"><?php echo $i['app_count']; ?> applicants</a></td>
<td class="text-sm"><?php echo formatDate($i['application_deadline']); ?></td>
<td class="d-flex gap-1">
<a href="/wtproject/recruiter/edit-internship.php?id=<?php echo $i['id']; ?>" class="btn btn-sm btn-outline">Edit</a>
<?php if($i['status']==='active'): ?><form method="POST" style="display:inline"><?php echo csrfField(); ?><input type="hidden" name="action" value="close"><input type="hidden" name="internship_id" value="<?php echo $i['id']; ?>"><button class="btn btn-sm btn-danger">Close</button></form>
<?php elseif($i['status']!=='active'): ?><form method="POST" style="display:inline"><?php echo csrfField(); ?><input type="hidden" name="action" value="activate"><input type="hidden" name="internship_id" value="<?php echo $i['id']; ?>"><button class="btn btn-sm btn-success">Activate</button></form><?php endif; ?>
</td></tr>
<?php endforeach; ?></tbody></table></div>
<?php endif; ?>
</div></div></div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>

