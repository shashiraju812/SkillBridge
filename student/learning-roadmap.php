<?php
/**
 * Learning Roadmap — SkillBridge
 */
$pageTitle='Learning Roadmap';$currentPage='learning-roadmap.php';
require_once __DIR__.'/../config/database.php';require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/csrf.php';require_once __DIR__.'/../includes/functions.php';
requireRole('student');$db=getDB();$profileId=getStudentProfileId($db);

$skillFilter=cleanInput($_GET['skill']??'');

// Handle status update (non-AJAX fallback)
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['task_id'])){
    requireValidCSRF();$taskId=(int)$_POST['task_id'];$status=cleanInput($_POST['status']??'');
    if(in_array($status,['not_started','in_progress','completed'])){
        $check=$db->prepare('SELECT id FROM student_learning_progress WHERE student_profile_id=? AND learning_task_id=?');$check->execute([$profileId,$taskId]);
        if($check->fetch()){
            $nowStr = date('Y-m-d H:i:s');
            $extra='';if($status==='in_progress')$extra=",started_at=COALESCE(started_at,'{$nowStr}')";if($status==='completed')$extra=",completed_at='{$nowStr}'";
            $db->prepare("UPDATE student_learning_progress SET status=?{$extra} WHERE student_profile_id=? AND learning_task_id=?")->execute([$status,$profileId,$taskId]);
        } else {
            $started=$status!=='not_started'?date('Y-m-d H:i:s'):null;$completed=$status==='completed'?date('Y-m-d H:i:s'):null;
            $db->prepare('INSERT INTO student_learning_progress (student_profile_id,learning_task_id,status,started_at,completed_at) VALUES (?,?,?,?,?)')->execute([$profileId,$taskId,$status,$started,$completed]);
        }
        setFlash('success','Progress updated.');
    }
    header('Location:'.$_SERVER['REQUEST_URI']);exit;
}

// Get tasks
$where='1=1';$params=[];
if($skillFilter){$where='s.name=?';$params[]=$skillFilter;}
$stmt=$db->prepare("SELECT lt.*, s.name AS skill_name, COALESCE(slp.status,'not_started') AS progress_status FROM learning_tasks lt JOIN skills s ON s.id=lt.skill_id LEFT JOIN student_learning_progress slp ON slp.learning_task_id=lt.id AND slp.student_profile_id=? WHERE $where ORDER BY s.name, lt.week_number, lt.task_order");
array_unshift($params,$profileId);$stmt->execute($params);$tasks=$stmt->fetchAll();

// Group by skill
$grouped=[];foreach($tasks as $t)$grouped[$t['skill_name']][]=$t;

// Get available skills with tasks
$availableSkills=$db->query('SELECT DISTINCT s.name FROM learning_tasks lt JOIN skills s ON s.id=lt.skill_id ORDER BY s.name')->fetchAll(PDO::FETCH_COLUMN);

include __DIR__.'/../includes/header.php';
?>
<div class="main-content"><div class="container"><div class="dashboard-layout"><?php include __DIR__.'/sidebar.php'; ?>
<div>
<div class="page-header"><h1>Learning Roadmap</h1><p><?php echo $skillFilter?"Roadmap for: $skillFilter":'Your personalized learning plan'; ?></p></div>

<!-- Skill filter -->
<div class="d-flex gap-1 flex-wrap mb-3">
<a href="/wtproject/student/learning-roadmap.php" class="btn btn-sm <?php echo !$skillFilter?'btn-primary':'btn-outline'; ?>">All Skills</a>
<?php foreach($availableSkills as $s): ?>
<a href="/wtproject/student/learning-roadmap.php?skill=<?php echo urlencode($s); ?>" class="btn btn-sm <?php echo $skillFilter===$s?'btn-primary':'btn-outline'; ?>"><?php echo e($s); ?></a>
<?php endforeach; ?>
</div>

<?php if(empty($grouped)): ?>
<div class="card"><div class="empty-state"><h3>No learning tasks available</h3><p>Check your skill gap analysis to find skills to learn.</p></div></div>
<?php else: foreach($grouped as $skillName=>$skillTasks):
    $total=count($skillTasks);$completed=count(array_filter($skillTasks,fn($t)=>$t['progress_status']==='completed'));
    $pct=$total>0?round(($completed/$total)*100):0;
?>
<div class="card mb-3">
    <div class="d-flex justify-between align-center mb-2">
        <h3 class="mb-0"><?php echo e($skillName); ?></h3>
        <span class="text-sm text-muted"><?php echo $completed; ?>/<?php echo $total; ?> completed</span>
    </div>
    <div class="progress-bar-container mb-2"><div class="progress-bar green" style="width:<?php echo $pct; ?>%"></div></div>

    <div class="timeline">
    <?php foreach($skillTasks as $t):$statusCls=$t['progress_status']==='completed'?'completed':($t['progress_status']==='in_progress'?'in-progress':''); ?>
    <div class="timeline-item <?php echo $statusCls; ?>">
        <div class="d-flex justify-between align-center">
            <div>
                <strong><?php echo e($t['title']); ?></strong>
                <span class="text-xs text-muted"> — Week <?php echo $t['week_number']; ?><?php echo $t['estimated_hours']?' · ~'.$t['estimated_hours'].'h':''; ?></span>
                <br><span class="text-sm text-muted"><?php echo e($t['description']); ?></span>
            </div>
            <form method="POST" class="d-flex gap-1" style="flex-shrink:0;">
                <?php echo csrfField(); ?><input type="hidden" name="task_id" value="<?php echo $t['id']; ?>">
                <select name="status" class="form-control" style="width:auto;padding:0.25rem 0.5rem;font-size:0.8rem;" onchange="this.form.submit()">
                    <?php foreach(['not_started'=>'Not Started','in_progress'=>'In Progress','completed'=>'Completed'] as $v=>$l): ?>
                    <option value="<?php echo $v; ?>" <?php echo $t['progress_status']===$v?'selected':''; ?>><?php echo $l; ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
</div>
<?php endforeach; endif; ?>

</div></div></div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>

