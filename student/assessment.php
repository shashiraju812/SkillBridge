<?php
/**
 * Skill Assessment — SkillBridge
 */
$pageTitle='Assessments';$currentPage='assessment.php';
require_once __DIR__.'/../config/database.php';require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/csrf.php';require_once __DIR__.'/../includes/functions.php';
requireRole('student');$db=getDB();$profileId=getStudentProfileId($db);

$assessmentId=(int)($_GET['id']??0);

// Handle submission
if($_SERVER['REQUEST_METHOD']==='POST' && $assessmentId){
    requireValidCSRF();
    $stmt=$db->prepare('SELECT aq.* FROM assessment_questions aq WHERE aq.assessment_id=?');$stmt->execute([$assessmentId]);$questions=$stmt->fetchAll();
    $total=count($questions);$correct=0;$answers=[];$weakTopics=[];
    foreach($questions as $q){
        $selected=$_POST['q_'.$q['id']]??'';
        $answers[$q['id']]=$selected;
        if($selected===$q['correct_option']){$correct++;}
        else{if($q['topic']&&!in_array($q['topic'],$weakTopics))$weakTopics[]=$q['topic'];}
    }
    $score=$total>0?round(($correct/$total)*100):0;
    $stmt=$db->prepare('INSERT INTO assessment_results (student_profile_id,assessment_id,score,total_questions,correct_answers,answers_json,weak_topics) VALUES (?,?,?,?,?,?,?)');
    $stmt->execute([$profileId,$assessmentId,$score,$total,$correct,json_encode($answers),implode(',',$weakTopics)]);
    $resultId=$db->lastInsertId();
    header('Location:/wtproject/student/assessment-results.php?id='.$resultId);exit;
}

// Show specific assessment
if($assessmentId){
    $stmt=$db->prepare('SELECT a.*, s.name AS skill_name FROM assessments a JOIN skills s ON s.id=a.skill_id WHERE a.id=? AND a.is_active=1');
    $stmt->execute([$assessmentId]);$assessment=$stmt->fetch();
    if(!$assessment){setFlash('error','Assessment not found.');header('Location:/wtproject/student/assessment.php');exit;}
    $stmt=$db->prepare('SELECT * FROM assessment_questions WHERE assessment_id=? ORDER BY id');$stmt->execute([$assessmentId]);$questions=$stmt->fetchAll();
    $pageTitle=$assessment['title'];
}

// List assessments
if(!$assessmentId){
    $assessments=$db->query('SELECT a.*, s.name AS skill_name, sc.name AS cat_name FROM assessments a JOIN skills s ON s.id=a.skill_id JOIN skill_categories sc ON sc.id=s.category_id WHERE a.is_active=1 ORDER BY sc.display_order, s.name')->fetchAll();
    // Get last results for each
    $stmt=$db->prepare('SELECT assessment_id, MAX(score) AS best_score, COUNT(*) AS attempts FROM assessment_results WHERE student_profile_id=? GROUP BY assessment_id');
    $stmt->execute([$profileId]);$results=[];foreach($stmt->fetchAll() as $r)$results[$r['assessment_id']]=$r;
}

include __DIR__.'/../includes/header.php';
?>
<div class="main-content"><div class="container"><div class="dashboard-layout"><?php include __DIR__.'/sidebar.php'; ?>
<div>
<?php if($assessmentId && isset($assessment)): ?>
<!-- Take Assessment -->
<div class="page-header"><h1><?php echo e($assessment['title']); ?></h1><p>Skill: <?php echo e($assessment['skill_name']); ?> · <?php echo count($questions); ?> questions · <?php echo $assessment['time_limit_minutes']; ?> min</p></div>
<div class="alert alert-info text-sm">These are educational sample questions for demonstration. They are not official certification tests.</div>
<form method="POST" data-validate><?php echo csrfField(); ?>
<?php foreach($questions as $i=>$q): ?>
<div class="question-card">
    <div class="question-number">Question <?php echo $i+1; ?> of <?php echo count($questions); ?> <span class="badge badge-<?php echo $q['difficulty']==='easy'?'success':($q['difficulty']==='hard'?'danger':'warning'); ?>"><?php echo ucfirst($q['difficulty']); ?></span></div>
    <div class="question-text"><?php echo e($q['question_text']); ?></div>
    <?php foreach(['a','b','c','d'] as $opt): if(!empty($q['option_'.$opt])): ?>
    <label class="option-label"><input type="radio" name="q_<?php echo $q['id']; ?>" value="<?php echo $opt; ?>" required> <?php echo e($q['option_'.$opt]); ?></label>
    <?php endif; endforeach; ?>
</div>
<?php endforeach; ?>
<button type="submit" class="btn btn-primary btn-lg">Submit Assessment</button>
</form>

<?php else: ?>
<!-- List Assessments -->
<div class="page-header"><h1>Skill Assessments</h1><p>Test your knowledge and identify areas for improvement</p></div>
<?php if(empty($assessments)): ?><div class="empty-state"><p>No assessments available.</p></div>
<?php else: ?>
<div class="card-grid-2">
<?php foreach($assessments as $a):$r=$results[$a['id']]??null; ?>
<div class="card">
    <h4 class="mb-0"><?php echo e($a['title']); ?></h4>
    <span class="text-sm text-muted"><?php echo e($a['skill_name']); ?> · <?php echo e($a['cat_name']); ?></span>
    <?php if($a['description']): ?><p class="text-sm mt-1"><?php echo e($a['description']); ?></p><?php endif; ?>
    <div class="d-flex justify-between align-center mt-2">
        <span class="text-xs text-muted"><?php echo $a['time_limit_minutes']; ?> min · Pass: <?php echo $a['pass_percentage']; ?>%</span>
        <?php if($r): ?><span class="badge <?php echo $r['best_score']>=$a['pass_percentage']?'badge-success':'badge-danger'; ?>">Best: <?php echo $r['best_score']; ?>% (<?php echo $r['attempts']; ?> attempt<?php echo $r['attempts']>1?'s':''; ?>)</span><?php endif; ?>
    </div>
    <a href="/wtproject/student/assessment.php?id=<?php echo $a['id']; ?>" class="btn btn-sm btn-outline mt-2"><?php echo $r?'Retake':'Start'; ?> Assessment</a>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php endif; ?>
</div></div></div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>

