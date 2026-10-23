<?php
/**
 * View Applicants — SkillBridge (Recruiter)
 */
$pageTitle='Applicants';$currentPage='manage-internships.php';
require_once __DIR__.'/../config/database.php';require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/csrf.php';require_once __DIR__.'/../includes/functions.php';
requireRole('recruiter');$db=getDB();$rpId=getRecruiterProfileId($db);
$intId=(int)($_GET['internship_id']??0);

$stmt=$db->prepare('SELECT * FROM internships WHERE id=? AND recruiter_profile_id=?');$stmt->execute([$intId,$rpId]);$int=$stmt->fetch();
if(!$int){setFlash('error','Internship not found or access denied.');header('Location:/wtproject/recruiter/manage-internships.php');exit;}

// Handle status update
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['app_id'])){requireValidCSRF();
    $newStatus=cleanInput($_POST['new_status']??'');$appId=(int)$_POST['app_id'];
    $valid=['applied','under_review','shortlisted','interview_scheduled','selected','rejected'];
    if(in_array($newStatus,$valid)){
        // Verify this application belongs to recruiter's internship
        $check=$db->prepare('SELECT a.id FROM applications a JOIN internships i ON i.id=a.internship_id WHERE a.id=? AND i.recruiter_profile_id=?');$check->execute([$appId,$rpId]);
        if($check->fetch()){$db->prepare('UPDATE applications SET status=? WHERE id=?')->execute([$newStatus,$appId]);setFlash('success','Status updated.');}
    }
    header('Location:/wtproject/recruiter/applicants.php?internship_id='.$intId);exit;
}

$stmt=$db->prepare('SELECT a.*, u.full_name, u.email, sp.college, sp.degree, sp.branch FROM applications a JOIN student_profiles sp ON sp.id=a.student_profile_id JOIN users u ON u.id=sp.user_id WHERE a.internship_id=? ORDER BY a.applied_at DESC');
$stmt->execute([$intId]);$applicants=$stmt->fetchAll();

include __DIR__.'/../includes/header.php';
?>
<div class="main-content"><div class="container"><div class="dashboard-layout"><?php include __DIR__.'/sidebar.php'; ?>
<div>
<div class="page-header"><h1>Applicants: <?php echo e($int['title']); ?></h1><p><?php echo count($applicants); ?> applications received</p></div>

<?php if(empty($applicants)): ?><div class="card"><div class="empty-state"><h3>No applications yet</h3></div></div>
<?php else: ?>
<div class="table-responsive"><table><thead><tr><th>Student</th><th>College</th><th>Match</th><th>Applied</th><th>Status</th><th>Update</th><th>Details</th></tr></thead><tbody>
<?php foreach($applicants as $a):$match=calculateMatchScore($db,(int)$a['student_profile_id'],$intId);$level=$match['score']>=70?'high':($match['score']>=40?'medium':'low'); ?>
<tr>
<td><strong><?php echo e($a['full_name']); ?></strong><br><span class="text-xs text-muted"><?php echo e($a['email']); ?></span></td>
<td class="text-sm"><?php echo e($a['college']); ?><br><?php echo e($a['degree'].' - '.$a['branch']); ?></td>
<td><span class="match-score <?php echo $level; ?>" style="width:44px;height:44px;font-size:0.8rem;"><?php echo $match['score']; ?>%</span></td>
<td class="text-sm"><?php echo formatDate($a['applied_at']); ?></td>
<td><span class="badge <?php echo statusBadgeClass($a['status']); ?>"><?php echo formatStatus($a['status']); ?></span></td>
<td><form method="POST" class="d-flex gap-1"><?php echo csrfField(); ?><input type="hidden" name="app_id" value="<?php echo $a['id']; ?>">
<select name="new_status" class="form-control" style="width:auto;padding:0.25rem;font-size:0.75rem;">
<?php foreach(['applied','under_review','shortlisted','interview_scheduled','selected','rejected'] as $st): ?><option value="<?php echo $st; ?>" <?php echo $a['status']===$st?'selected':''; ?>><?php echo formatStatus($st); ?></option><?php endforeach; ?>
</select><button class="btn btn-sm btn-primary">Set</button></form></td>
<td><a href="/wtproject/recruiter/candidate-details.php?application_id=<?php echo $a['id']; ?>" class="btn btn-sm btn-outline">View</a></td>
</tr>
<?php endforeach; ?></tbody></table></div>
<?php endif; ?>
</div></div></div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>

