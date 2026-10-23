<?php
/**
 * Application Tracker — SkillBridge
 */
$pageTitle='Applications';$currentPage='applications.php';
require_once __DIR__.'/../config/database.php';require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/csrf.php';require_once __DIR__.'/../includes/functions.php';
requireRole('student');$db=getDB();$profileId=getStudentProfileId($db);

if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['action'])&&$_POST['action']==='withdraw'){
    requireValidCSRF();$appId=(int)$_POST['application_id'];
    $stmt=$db->prepare('SELECT status FROM applications WHERE id=? AND student_profile_id=?');$stmt->execute([$appId,$profileId]);$app=$stmt->fetch();
    if($app&&in_array($app['status'],['applied','under_review'])){
        $db->prepare('UPDATE applications SET status="withdrawn" WHERE id=?')->execute([$appId]);
        setFlash('success','Application withdrawn.');
    } else {setFlash('error','Cannot withdraw this application.');}
    header('Location:/wtproject/student/applications.php');exit;
}

$statusFilter=cleanInput($_GET['status']??'');
$where='a.student_profile_id=?';$params=[$profileId];
if($statusFilter){$where.=' AND a.status=?';$params[]=$statusFilter;}
$stmt=$db->prepare("SELECT a.*, i.title, i.application_deadline, rp.company_name FROM applications a JOIN internships i ON i.id=a.internship_id JOIN recruiter_profiles rp ON rp.id=i.recruiter_profile_id WHERE $where ORDER BY a.applied_at DESC");
$stmt->execute($params);$apps=$stmt->fetchAll();

include __DIR__.'/../includes/header.php';
?>
<div class="main-content"><div class="container"><div class="dashboard-layout"><?php include __DIR__.'/sidebar.php'; ?>
<div>
<div class="page-header"><h1>Application Tracker</h1><p><?php echo count($apps); ?> applications</p></div>

<div class="d-flex gap-1 flex-wrap mb-3">
<a href="/wtproject/student/applications.php" class="btn btn-sm <?php echo !$statusFilter?'btn-primary':'btn-outline'; ?>">All</a>
<?php foreach(['applied','under_review','shortlisted','interview_scheduled','selected','rejected','withdrawn'] as $st): ?>
<a href="/wtproject/student/applications.php?status=<?php echo $st; ?>" class="btn btn-sm <?php echo $statusFilter===$st?'btn-primary':'btn-outline'; ?>"><?php echo formatStatus($st); ?></a>
<?php endforeach; ?>
</div>

<?php if(empty($apps)): ?><div class="card"><div class="empty-state"><h3>No applications</h3><p>Start exploring and applying to internships!</p><a href="/wtproject/student/internships.php" class="btn btn-primary mt-2">Explore Internships</a></div></div>
<?php else: ?>
<div class="table-responsive"><table><thead><tr><th>Internship</th><th>Company</th><th>Applied</th><th>Deadline</th><th>Status</th><th>Action</th></tr></thead><tbody>
<?php foreach($apps as $a):$days=daysRemaining($a['application_deadline']); ?>
<tr>
<td><a href="/wtproject/student/internship-match.php?id=<?php echo (int)$a['internship_id']; ?>"><strong><?php echo e($a['title']); ?></strong></a></td>
<td><?php echo e($a['company_name']); ?></td>
<td class="text-sm"><?php echo formatDate($a['applied_at']); ?></td>
<td class="text-sm"><?php echo formatDate($a['application_deadline']); ?></td>
<td><span class="badge <?php echo statusBadgeClass($a['status']); ?>"><?php echo formatStatus($a['status']); ?></span></td>
<td><?php if(in_array($a['status'],['applied','under_review'])): ?>
<form method="POST" style="display:inline;" onsubmit="return confirm('Withdraw application?')"><?php echo csrfField(); ?><input type="hidden" name="action" value="withdraw"><input type="hidden" name="application_id" value="<?php echo $a['id']; ?>"><button class="btn btn-sm btn-danger">Withdraw</button></form>
<?php else: ?>—<?php endif; ?></td>
</tr>
<?php endforeach; ?></tbody></table></div>
<?php endif; ?>
</div></div></div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>

