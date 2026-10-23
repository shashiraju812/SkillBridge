<?php
/**
 * Internship Match Details — SkillBridge
 */
$pageTitle='Match Details';$currentPage='internships.php';
require_once __DIR__.'/../config/database.php';require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/csrf.php';require_once __DIR__.'/../includes/functions.php';
requireRole('student');$db=getDB();$profileId=getStudentProfileId($db);
$id=(int)($_GET['id']??0);if(!$id){header('Location:/wtproject/student/internships.php');exit;}

$stmt=$db->prepare('SELECT i.*, rp.company_name FROM internships i JOIN recruiter_profiles rp ON rp.id=i.recruiter_profile_id WHERE i.id=?');
$stmt->execute([$id]);$int=$stmt->fetch();if(!$int){setFlash('error','Internship not found.');header('Location:/wtproject/student/internships.php');exit;}

$match=calculateMatchScore($db,$profileId,$id);$elig=checkEligibility($db,$profileId,$id);

// Check if applied
$stmt=$db->prepare('SELECT * FROM applications WHERE student_profile_id=? AND internship_id=?');$stmt->execute([$profileId,$id]);$app=$stmt->fetch();
// Check if saved
$stmt=$db->prepare('SELECT id FROM saved_internships WHERE student_profile_id=? AND internship_id=?');$stmt->execute([$profileId,$id]);$isSaved=(bool)$stmt->fetch();

$reqSkills=$db->prepare('SELECT isr.*, s.name AS skill_name FROM internship_skills isr JOIN skills s ON s.id=isr.skill_id WHERE isr.internship_id=?');$reqSkills->execute([$id]);$reqSkills=$reqSkills->fetchAll();

// Student skills lookup
$stmt=$db->prepare('SELECT s.name, ss.proficiency FROM student_skills ss JOIN skills s ON s.id=ss.skill_id WHERE ss.student_profile_id=?');$stmt->execute([$profileId]);
$mySkills=[];foreach($stmt->fetchAll() as $s)$mySkills[$s['name']]=$s['proficiency'];

$level=$match['score']>=70?'high':($match['score']>=40?'medium':'low');$days=daysRemaining($int['application_deadline']);
$pageTitle=$int['title'].' — Match';
include __DIR__.'/../includes/header.php';
?>
<div class="main-content"><div class="container"><div class="dashboard-layout"><?php include __DIR__.'/sidebar.php'; ?>
<div>
<a href="/wtproject/student/internships.php" class="text-sm text-muted">&larr; Back to Internships</a>

<div class="card mt-2 mb-3">
    <div class="d-flex justify-between align-center flex-wrap gap-2">
        <div><h1 style="margin-bottom:0.25rem;"><?php echo e($int['title']); ?></h1><p class="text-muted mb-0"><?php echo e($int['company_name']); ?> · <?php echo e($int['location']?:'Remote'); ?> · <?php echo ucfirst(e($int['work_mode'])); ?></p></div>
        <div class="match-score <?php echo $level; ?>" style="width:80px;height:80px;font-size:1.5rem;"><?php echo $match['score']; ?>%</div>
    </div>
</div>

<!-- Skill Comparison Table -->
<div class="card mb-3">
    <h3>Skill Comparison</h3>
    <table><thead><tr><th>Skill</th><th>Importance</th><th>Required Level</th><th>Your Level</th><th>Status</th></tr></thead><tbody>
    <?php foreach($reqSkills as $rs):
        $has=isset($mySkills[$rs['skill_name']]);$studentLvl=$mySkills[$rs['skill_name']]??'—';
        $profMap=['beginner'=>1,'intermediate'=>2,'advanced'=>3];
        $status='❌ Missing';$cls='text-danger';
        if($has){$sLvl=$profMap[$studentLvl]??0;$rLvl=$profMap[$rs['min_proficiency']]??0;
            if($sLvl>=$rLvl){$status='✅ Matched';$cls='text-success';}else{$status='⚠️ Weak';$cls='text-danger';}
        }
    ?>
    <tr><td><strong><?php echo e($rs['skill_name']); ?></strong></td>
    <td><span class="badge badge-<?php echo $rs['importance']==='required'?'danger':($rs['importance']==='preferred'?'warning':'secondary'); ?>"><?php echo ucfirst(e($rs['importance'])); ?></span></td>
    <td><?php echo ucfirst(e($rs['min_proficiency'])); ?></td>
    <td><?php echo $has?ucfirst(e($studentLvl)):'<span class="text-muted">—</span>'; ?></td>
    <td class="<?php echo $cls; ?>"><?php echo $status; ?></td></tr>
    <?php endforeach; ?></tbody></table>
</div>

<!-- Explanation -->
<div class="card mb-3"><h3>Match Explanation</h3><p><?php echo e($match['explanation']); ?></p></div>

<!-- Eligibility -->
<div class="card mb-3"><h3>Eligibility</h3>
<?php foreach($elig['reasons'] as $r): ?><p class="text-sm <?php echo $elig['eligible']?'text-success':'text-danger'; ?>"><?php echo e($r); ?></p><?php endforeach; ?>
</div>

<!-- Actions -->
<div class="card mb-3 d-flex gap-2 flex-wrap">
    <?php if($app): ?><span class="badge <?php echo statusBadgeClass($app['status']); ?>"><?php echo formatStatus($app['status']); ?></span>
    <?php elseif($days!==null&&$days<=0): ?><span class="text-muted">Deadline passed</span>
    <?php else: ?>
    <form method="POST" action="/wtproject/api/apply.php" class="d-flex gap-1" style="flex:1;">
        <input type="hidden" name="csrf_token" value="<?php echo e(generateCSRFToken()); ?>"><input type="hidden" name="internship_id" value="<?php echo $id; ?>">
        <input type="text" name="cover_note" class="form-control" placeholder="Cover note (optional)" style="flex:1;">
        <button type="submit" class="btn btn-primary">Apply</button>
    </form>
    <?php endif; ?>
    <a href="/wtproject/student/skill-gap.php?internship_id=<?php echo $id; ?>" class="btn btn-outline">View Skill Gap</a>
    <button class="btn btn-outline" data-save-id="<?php echo $id; ?>" onclick="toggleSaveInternship(<?php echo $id; ?>,'<?php echo e(generateCSRFToken()); ?>')"><?php echo $isSaved?'♥ Saved':'♡ Save'; ?></button>
</div>

<!-- Details -->
<div class="card mb-3"><h3>Internship Details</h3>
<p><strong>Stipend:</strong> <?php echo formatStipend($int['stipend_min'],$int['stipend_max'],$int['is_paid']); ?></p>
<p><strong>Duration:</strong> <?php echo e($int['duration']?:'Not specified'); ?></p>
<p><strong>Deadline:</strong> <?php echo formatDate($int['application_deadline']); ?></p>
<p style="white-space:pre-line;"><?php echo e($int['description']); ?></p>
<?php if($int['is_sample']): ?><div class="alert alert-warning text-sm">This is sample data for demonstration purposes.</div><?php endif; ?>
</div>

</div></div></div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>

