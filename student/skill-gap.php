<?php
/**
 * Skill Gap Analysis — SkillBridge
 */
$pageTitle='Skill Gap Analysis';$currentPage='skill-gap.php';
require_once __DIR__.'/../config/database.php';require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/functions.php';
requireRole('student');$db=getDB();$profileId=getStudentProfileId($db);

$internshipId=(int)($_GET['internship_id']??0);

if($internshipId){
    $stmt=$db->prepare('SELECT i.*, rp.company_name FROM internships i JOIN recruiter_profiles rp ON rp.id=i.recruiter_profile_id WHERE i.id=?');
    $stmt->execute([$internshipId]);$int=$stmt->fetch();
    if(!$int){setFlash('error','Internship not found.');header('Location:/wtproject/student/internships.php');exit;}
    $gap=getSkillGapAnalysis($db,$profileId,$internshipId);
    $match=$gap['match'];$plan=$gap['learning_plan'];
    $pageTitle='Skill Gap: '.$int['title'];
} else {
    // General gap: show missing skills across top matches
    $topMatches=array_slice(getMatchedInternships($db,$profileId),0,5);
    $allMissing=[];
    foreach($topMatches as $m){
        foreach($m['missing_skills'] as $s)$allMissing[$s['name']]=($allMissing[$s['name']]??0)+1;
        foreach($m['weak_skills'] as $s)$allMissing[$s['name']]=($allMissing[$s['name']]??0)+1;
    }
    arsort($allMissing);
}

include __DIR__.'/../includes/header.php';
?>
<div class="main-content"><div class="container"><div class="dashboard-layout"><?php include __DIR__.'/sidebar.php'; ?>
<div>
<?php if($internshipId && isset($int)): ?>
<div class="page-header"><h1>Skill Gap Analysis</h1><p>For: <?php echo e($int['title']); ?> at <?php echo e($int['company_name']); ?></p></div>

<div class="card mb-3">
    <div class="d-flex justify-between align-center">
        <h3 class="mb-0">Overall Readiness: <?php echo $match['score']; ?>%</h3>
        <div class="match-score <?php echo $match['score']>=70?'high':($match['score']>=40?'medium':'low'); ?>"><?php echo $match['score']; ?>%</div>
    </div>
    <div class="progress-bar-container mt-2" style="height:12px;"><div class="progress-bar <?php echo $match['score']>=70?'green':($match['score']>=40?'':'red'); ?>" style="width:<?php echo $match['score']; ?>%"></div></div>
</div>

<?php if(!empty($match['matched_skills'])): ?>
<div class="card mb-3"><h3 class="text-success">✅ Matched Skills (<?php echo count($match['matched_skills']); ?>)</h3>
<div class="skill-chips"><?php foreach($match['matched_skills'] as $s): ?><span class="skill-tag"><?php echo e($s['name']); ?> (<?php echo ucfirst($s['student_level']); ?>)</span><?php endforeach; ?></div></div>
<?php endif; ?>

<?php if(!empty($match['weak_skills'])): ?>
<div class="card mb-3"><h3 style="color:var(--warning);">⚠️ Needs Improvement (<?php echo count($match['weak_skills']); ?>)</h3>
<table><thead><tr><th>Skill</th><th>Your Level</th><th>Required</th><th>Action</th></tr></thead><tbody>
<?php foreach($match['weak_skills'] as $s): ?>
<tr><td><?php echo e($s['name']); ?></td><td><?php echo ucfirst($s['student_level']); ?></td><td><?php echo ucfirst($s['required_level']); ?></td>
<td><a href="/wtproject/student/learning-roadmap.php?skill=<?php echo urlencode($s['name']); ?>" class="btn btn-sm btn-outline">Learn</a></td></tr>
<?php endforeach; ?></tbody></table></div>
<?php endif; ?>

<?php if(!empty($match['missing_skills'])): ?>
<div class="card mb-3"><h3 class="text-danger">❌ Missing Skills (<?php echo count($match['missing_skills']); ?>)</h3>
<table><thead><tr><th>Skill</th><th>Importance</th><th>Required Level</th><th>Action</th></tr></thead><tbody>
<?php foreach($match['missing_skills'] as $s): ?>
<tr><td><?php echo e($s['name']); ?></td><td><span class="badge badge-<?php echo $s['importance']==='required'?'danger':'warning'; ?>"><?php echo ucfirst($s['importance']); ?></span></td><td><?php echo ucfirst($s['required_level']); ?></td>
<td><a href="/wtproject/student/learning-roadmap.php?skill=<?php echo urlencode($s['name']); ?>" class="btn btn-sm btn-primary">Start Learning</a></td></tr>
<?php endforeach; ?></tbody></table></div>
<?php endif; ?>

<?php if(!empty($plan)): ?>
<div class="card mb-3"><h3>Suggested Learning Plan</h3>
<?php foreach($plan as $skillName=>$tasks): ?>
<h4 class="mt-2"><?php echo e($skillName); ?></h4>
<div class="timeline">
<?php foreach($tasks as $t): ?>
<div class="timeline-item"><strong class="text-sm"><?php echo e($t['title']); ?></strong> <span class="text-xs text-muted">(Week <?php echo $t['week_number']; ?><?php echo $t['estimated_hours']?' · ~'.$t['estimated_hours'].'h':''; ?>)</span><br><span class="text-sm text-muted"><?php echo e($t['description']); ?></span></div>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
<a href="/wtproject/student/learning-roadmap.php" class="btn btn-primary mt-2">Open Full Learning Roadmap</a>
</div>
<?php endif; ?>

<?php else: ?>
<!-- General gap overview -->
<div class="page-header"><h1>Skill Gap Overview</h1><p>Skills you need most across your top matching internships</p></div>
<?php if(empty($allMissing)): ?>
<div class="card"><div class="empty-state"><h3>Great job!</h3><p>You have all the skills required by your top matches, or you haven't been matched yet.</p></div></div>
<?php else: ?>
<div class="card"><h3>Most In-Demand Missing Skills</h3>
<table><thead><tr><th>Skill</th><th>Needed By</th><th>Action</th></tr></thead><tbody>
<?php foreach($allMissing as $name=>$count): ?>
<tr><td><strong><?php echo e($name); ?></strong></td><td><?php echo $count; ?> internship(s)</td>
<td><a href="/wtproject/student/learning-roadmap.php?skill=<?php echo urlencode($name); ?>" class="btn btn-sm btn-outline">Learn</a></td></tr>
<?php endforeach; ?></tbody></table></div>
<?php endif; ?>
<?php endif; ?>
</div></div></div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>

