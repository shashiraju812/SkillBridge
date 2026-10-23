<?php
/**
 * Assessment Results — SkillBridge
 */
$pageTitle='Assessment Results';$currentPage='assessment.php';
require_once __DIR__.'/../config/database.php';require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/functions.php';
requireRole('student');$db=getDB();$profileId=getStudentProfileId($db);

$resultId=(int)($_GET['id']??0);
$stmt=$db->prepare('SELECT ar.*, a.title AS assessment_title, a.pass_percentage, s.name AS skill_name FROM assessment_results ar JOIN assessments a ON a.id=ar.assessment_id JOIN skills s ON s.id=a.skill_id WHERE ar.id=? AND ar.student_profile_id=?');
$stmt->execute([$resultId,$profileId]);$result=$stmt->fetch();
if(!$result){setFlash('error','Result not found.');header('Location:/wtproject/student/assessment.php');exit;}

$answers=json_decode($result['answers_json'],true)?:[];
$stmt=$db->prepare('SELECT * FROM assessment_questions WHERE assessment_id=? ORDER BY id');$stmt->execute([$result['assessment_id']]);$questions=$stmt->fetchAll();
$passed=$result['score']>=$result['pass_percentage'];

include __DIR__.'/../includes/header.php';
?>
<div class="main-content"><div class="container"><div class="dashboard-layout"><?php include __DIR__.'/sidebar.php'; ?>
<div>
<div class="page-header"><h1><?php echo e($result['assessment_title']); ?> — Results</h1><p>Skill: <?php echo e($result['skill_name']); ?> · Taken on <?php echo formatDate($result['attempted_at']); ?></p></div>

<div class="card mb-3">
    <div class="d-flex justify-between align-center flex-wrap gap-2">
        <div>
            <h2 style="font-size:2.5rem;color:<?php echo $passed?'var(--success)':'var(--danger)'; ?>;margin-bottom:0;"><?php echo $result['score']; ?>%</h2>
            <p class="mb-0"><?php echo $result['correct_answers']; ?>/<?php echo $result['total_questions']; ?> correct · <span class="badge <?php echo $passed?'badge-success':'badge-danger'; ?>"><?php echo $passed?'PASSED':'NEEDS IMPROVEMENT'; ?></span></p>
        </div>
        <div style="width:200px;">
            <div class="progress-bar-container" style="height:12px;"><div class="progress-bar <?php echo $passed?'green':'red'; ?>" style="width:<?php echo $result['score']; ?>%"></div></div>
            <div class="progress-label"><span>0%</span><span>Pass: <?php echo $result['pass_percentage']; ?>%</span><span>100%</span></div>
        </div>
    </div>
    <?php if($result['weak_topics']): ?>
    <div class="mt-2"><strong class="text-sm">Weak Topics:</strong>
    <?php foreach(explode(',',$result['weak_topics']) as $t): ?><span class="badge badge-warning" style="margin:0.125rem;"><?php echo e(trim($t)); ?></span><?php endforeach; ?>
    </div><?php endif; ?>
</div>

<h3 class="mb-2">Question Review</h3>
<?php foreach($questions as $i=>$q):
    $selected=$answers[$q['id']]??'';$isCorrect=$selected===$q['correct_option'];
?>
<div class="question-card" style="border-left:4px solid <?php echo $isCorrect?'var(--success)':'var(--danger)'; ?>;">
    <div class="question-number">Q<?php echo $i+1; ?> <?php echo $isCorrect?'✅':'❌'; ?></div>
    <div class="question-text"><?php echo e($q['question_text']); ?></div>
    <?php foreach(['a','b','c','d'] as $opt): if(!empty($q['option_'.$opt])): 
        $cls='';if($opt===$q['correct_option'])$cls='border-color:var(--success);background:#d1fae5;';
        elseif($opt===$selected&&!$isCorrect)$cls='border-color:var(--danger);background:#fee2e2;';
    ?>
    <div class="option-label" style="<?php echo $cls; ?>cursor:default;"><strong><?php echo strtoupper($opt); ?>.</strong> <?php echo e($q['option_'.$opt]); ?>
    <?php if($opt===$q['correct_option']): ?> <span class="badge badge-success" style="margin-left:auto;">Correct</span><?php endif; ?>
    <?php if($opt===$selected&&!$isCorrect): ?> <span class="badge badge-danger" style="margin-left:auto;">Your Answer</span><?php endif; ?>
    </div>
    <?php endif; endforeach; ?>
    <?php if($q['explanation']): ?><p class="text-sm text-muted mt-1"><strong>Explanation:</strong> <?php echo e($q['explanation']); ?></p><?php endif; ?>
</div>
<?php endforeach; ?>

<div class="d-flex gap-2 mt-3">
    <a href="/wtproject/student/assessment.php?id=<?php echo $result['assessment_id']; ?>" class="btn btn-outline">Retake Assessment</a>
    <a href="/wtproject/student/assessment.php" class="btn btn-primary">All Assessments</a>
</div>
</div></div></div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>

