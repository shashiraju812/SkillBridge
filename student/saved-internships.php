<?php
/**
 * Saved Internships — SkillBridge
 */
$pageTitle='Saved Internships';$currentPage='saved-internships.php';
require_once __DIR__.'/../config/database.php';require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/csrf.php';require_once __DIR__.'/../includes/functions.php';
requireRole('student');$db=getDB();$profileId=getStudentProfileId($db);

if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['action'])&&$_POST['action']==='unsave'){
    requireValidCSRF();
    $db->prepare('DELETE FROM saved_internships WHERE student_profile_id=? AND internship_id=?')->execute([$profileId,(int)$_POST['internship_id']]);
    setFlash('success','Removed from saved.');header('Location:/wtproject/student/saved-internships.php');exit;
}

$stmt=$db->prepare('SELECT i.*, rp.company_name, si.saved_at FROM saved_internships si JOIN internships i ON i.id=si.internship_id JOIN recruiter_profiles rp ON rp.id=i.recruiter_profile_id WHERE si.student_profile_id=? ORDER BY si.saved_at DESC');
$stmt->execute([$profileId]);$saved=$stmt->fetchAll();

include __DIR__.'/../includes/header.php';
?>
<div class="main-content"><div class="container"><div class="dashboard-layout"><?php include __DIR__.'/sidebar.php'; ?>
<div>
<div class="page-header"><h1>Saved Internships</h1><p><?php echo count($saved); ?> saved</p></div>
<?php if(empty($saved)): ?><div class="card"><div class="empty-state"><h3>No saved internships</h3><p>Browse internships and save ones you're interested in.</p><a href="/wtproject/student/internships.php" class="btn btn-primary mt-2">Explore Internships</a></div></div>
<?php else: ?>
<?php foreach($saved as $s):$match=calculateMatchScore($db,$profileId,(int)$s['id']);$days=daysRemaining($s['application_deadline']);$level=$match['score']>=70?'high':($match['score']>=40?'medium':'low'); ?>
<div class="internship-card mb-2">
    <div class="internship-card-top"><div class="internship-card-info"><h3><a href="/wtproject/student/internship-match.php?id=<?php echo (int)$s['id']; ?>"><?php echo e($s['title']); ?></a></h3><span class="company"><?php echo e($s['company_name']); ?></span></div>
    <div class="match-score <?php echo $level; ?>"><?php echo $match['score']; ?>%</div></div>
    <div class="internship-card-meta"><span>📍 <?php echo e($s['location']?:'Remote'); ?></span><span>💰 <?php echo formatStipend($s['stipend_min'],$s['stipend_max'],$s['is_paid']); ?></span>
    <?php if($days!==null): ?><span><?php echo $days>0?"⏰ {$days}d left":'❌ Expired'; ?></span><?php endif; ?></div>
    <div class="internship-card-actions">
        <a href="/wtproject/student/internship-match.php?id=<?php echo (int)$s['id']; ?>" class="btn btn-sm btn-outline">View Match</a>
        <form method="POST" style="display:inline;"><?php echo csrfField(); ?><input type="hidden" name="action" value="unsave"><input type="hidden" name="internship_id" value="<?php echo (int)$s['id']; ?>"><button type="submit" class="btn btn-sm btn-danger">Remove</button></form>
    </div>
</div>
<?php endforeach; endif; ?>
</div></div></div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>

