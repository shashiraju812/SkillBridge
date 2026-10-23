<?php
/**
 * Candidate Details — SkillBridge (Recruiter)
 */
$pageTitle='Candidate Details';$currentPage='manage-internships.php';
require_once __DIR__.'/../config/database.php';require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/csrf.php';require_once __DIR__.'/../includes/functions.php';
requireRole('recruiter');$db=getDB();$rpId=getRecruiterProfileId($db);
$appId=(int)($_GET['application_id']??0);

// Verify access: application must be for recruiter's internship
$stmt=$db->prepare('SELECT a.*, i.title AS internship_title, i.id AS internship_id FROM applications a JOIN internships i ON i.id=a.internship_id WHERE a.id=? AND i.recruiter_profile_id=?');
$stmt->execute([$appId,$rpId]);$app=$stmt->fetch();
if(!$app){setFlash('error','Application not found or access denied.');header('Location:/wtproject/recruiter/manage-internships.php');exit;}

// Handle notes/status update
if($_SERVER['REQUEST_METHOD']==='POST'){requireValidCSRF();
    if(isset($_POST['new_status'])){$st=cleanInput($_POST['new_status']);$valid=['applied','under_review','shortlisted','interview_scheduled','selected','rejected'];
    if(in_array($st,$valid))$db->prepare('UPDATE applications SET status=? WHERE id=?')->execute([$st,$appId]);}
    if(isset($_POST['recruiter_notes']))$db->prepare('UPDATE applications SET recruiter_notes=? WHERE id=?')->execute([cleanInput($_POST['recruiter_notes']),$appId]);
    setFlash('success','Updated.');header('Location:'.$_SERVER['REQUEST_URI']);exit;
}

// Get student data
$stmt=$db->prepare('SELECT u.full_name,u.email,sp.* FROM users u JOIN student_profiles sp ON sp.user_id=u.id WHERE sp.id=?');$stmt->execute([$app['student_profile_id']]);$student=$stmt->fetch();
$match=calculateMatchScore($db,(int)$app['student_profile_id'],(int)$app['internship_id']);
$skills=$db->prepare('SELECT ss.proficiency,s.name FROM student_skills ss JOIN skills s ON s.id=ss.skill_id WHERE ss.student_profile_id=?');$skills->execute([$app['student_profile_id']]);$skills=$skills->fetchAll();
$projects=$db->prepare('SELECT * FROM projects WHERE student_profile_id=?');$projects->execute([$app['student_profile_id']]);$projects=$projects->fetchAll();
$certs=$db->prepare('SELECT * FROM certificates WHERE student_profile_id=?');$certs->execute([$app['student_profile_id']]);$certs=$certs->fetchAll();
$level=$match['score']>=70?'high':($match['score']>=40?'medium':'low');

// Re-fetch app after potential update
$stmt=$db->prepare('SELECT * FROM applications WHERE id=?');$stmt->execute([$appId]);$app=$stmt->fetch();

include __DIR__.'/../includes/header.php';
?>
<div class="main-content"><div class="container"><div class="dashboard-layout"><?php include __DIR__.'/sidebar.php'; ?>
<div>
<a href="/wtproject/recruiter/applicants.php?internship_id=<?php echo $app['internship_id']; ?>" class="text-sm text-muted">&larr; Back to Applicants</a>

<div class="card mt-2 mb-3">
    <div class="d-flex justify-between align-center flex-wrap gap-2">
        <div><h1 class="mb-0"><?php echo e($student['full_name']); ?></h1><p class="text-muted mb-0">Applied for: <?php echo e($app['internship_title']); ?></p></div>
        <div class="match-score <?php echo $level; ?>" style="width:80px;height:80px;font-size:1.5rem;"><?php echo $match['score']; ?>%</div>
    </div>
</div>

<div class="card-grid-2 mb-3">
    <div class="card"><h3>Student Info</h3>
    <table style="font-size:0.875rem;"><tbody>
    <tr><td class="text-muted">Email</td><td><?php echo e($student['email']); ?></td></tr>
    <tr><td class="text-muted">College</td><td><?php echo e($student['college']); ?></td></tr>
    <tr><td class="text-muted">Degree</td><td><?php echo e($student['degree'].' - '.$student['branch']); ?></td></tr>
    <tr><td class="text-muted">Graduation</td><td><?php echo e($student['graduation_year']); ?></td></tr>
    <?php if($student['github_url']): ?><tr><td class="text-muted">GitHub</td><td><a href="<?php echo e($student['github_url']); ?>" target="_blank">Profile</a></td></tr><?php endif; ?>
    <?php if($student['portfolio_url']): ?><tr><td class="text-muted">Portfolio</td><td><a href="<?php echo e($student['portfolio_url']); ?>" target="_blank">View</a></td></tr><?php endif; ?>
    </tbody></table>
    <?php if($student['bio']): ?><p class="text-sm mt-2"><?php echo e($student['bio']); ?></p><?php endif; ?>
    </div>

    <div class="card"><h3>Application Status</h3>
    <p>Current: <span class="badge <?php echo statusBadgeClass($app['status']); ?>"><?php echo formatStatus($app['status']); ?></span></p>
    <form method="POST"><?php echo csrfField(); ?>
    <div class="form-group"><label>Update Status</label>
    <select name="new_status" class="form-control"><?php foreach(['applied','under_review','shortlisted','interview_scheduled','selected','rejected'] as $st): ?><option value="<?php echo $st; ?>" <?php echo $app['status']===$st?'selected':''; ?>><?php echo formatStatus($st); ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label>Recruiter Notes</label><textarea name="recruiter_notes" class="form-control" rows="3"><?php echo e($app['recruiter_notes']??''); ?></textarea></div>
    <button type="submit" class="btn btn-primary">Update</button></form>
    <?php if($app['cover_note']): ?><h4 class="mt-2">Cover Note</h4><p class="text-sm"><?php echo e($app['cover_note']); ?></p><?php endif; ?>
    </div>
</div>

<div class="card mb-3"><h3>Skills (<?php echo count($skills); ?>)</h3>
<div class="skill-chips"><?php foreach($skills as $s): ?><span class="skill-tag"><?php echo e($s['name']); ?> <small>(<?php echo ucfirst($s['proficiency']); ?>)</small></span><?php endforeach; ?></div></div>

<div class="card mb-3"><h3>Match Breakdown</h3><p><?php echo e($match['explanation']); ?></p>
<table><thead><tr><th>Skill</th><th>Status</th></tr></thead><tbody>
<?php foreach($match['matched_skills'] as $s): ?><tr><td><?php echo e($s['name']); ?></td><td class="text-success">✅ Matched (<?php echo ucfirst($s['student_level']); ?>)</td></tr><?php endforeach; ?>
<?php foreach($match['weak_skills'] as $s): ?><tr><td><?php echo e($s['name']); ?></td><td class="text-danger">⚠️ Weak (<?php echo ucfirst($s['student_level']); ?> / need <?php echo ucfirst($s['required_level']); ?>)</td></tr><?php endforeach; ?>
<?php foreach($match['missing_skills'] as $s): ?><tr><td><?php echo e($s['name']); ?></td><td class="text-danger">❌ Missing</td></tr><?php endforeach; ?>
</tbody></table></div>

<?php if(!empty($projects)): ?><div class="card mb-3"><h3>Projects</h3>
<?php foreach($projects as $p): ?><div style="padding:0.5rem 0;border-bottom:1px solid var(--gray-100);"><strong><?php echo e($p['title']); ?></strong><br><span class="text-sm text-muted"><?php echo e($p['technologies']); ?></span></div><?php endforeach; ?></div><?php endif; ?>

</div></div></div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>

