<?php
/**
 * Skills Management — SkillBridge
 */
$pageTitle='Skills';$currentPage='skills.php';
require_once __DIR__.'/../config/database.php';require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/csrf.php';require_once __DIR__.'/../includes/functions.php';
requireRole('student');$db=getDB();$profileId=getStudentProfileId($db);

// Handle add skill
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])){
    requireValidCSRF();
    if($_POST['action']==='add' && !empty($_POST['skill_id'])){
        $skillId=(int)$_POST['skill_id'];$prof=cleanInput($_POST['proficiency']??'beginner');
        if(!in_array($prof,['beginner','intermediate','advanced']))$prof='beginner';
        $check=$db->prepare('SELECT id FROM student_skills WHERE student_profile_id=? AND skill_id=?');$check->execute([$profileId,$skillId]);
        if(!$check->fetch()){
            $db->prepare('INSERT INTO student_skills (student_profile_id,skill_id,proficiency) VALUES (?,?,?)')->execute([$profileId,$skillId,$prof]);
            setFlash('success','Skill added.');
        } else { setFlash('info','Skill already added.'); }
    }
    if($_POST['action']==='remove' && !empty($_POST['skill_id'])){
        $db->prepare('DELETE FROM student_skills WHERE student_profile_id=? AND skill_id=?')->execute([$profileId,(int)$_POST['skill_id']]);
        setFlash('success','Skill removed.');
    }
    if($_POST['action']==='update' && !empty($_POST['skill_id'])){
        $prof=cleanInput($_POST['proficiency']??'beginner');
        if(in_array($prof,['beginner','intermediate','advanced'])){
            $db->prepare('UPDATE student_skills SET proficiency=? WHERE student_profile_id=? AND skill_id=?')->execute([$prof,$profileId,(int)$_POST['skill_id']]);
            setFlash('success','Proficiency updated.');
        }
    }
    header('Location:/wtproject/student/skills.php');exit;
}

// Get current skills
$stmt=$db->prepare('SELECT ss.*, s.name AS skill_name, sc.name AS category_name FROM student_skills ss JOIN skills s ON s.id=ss.skill_id JOIN skill_categories sc ON sc.id=s.category_id WHERE ss.student_profile_id=? ORDER BY sc.display_order, s.name');
$stmt->execute([$profileId]);$mySkills=$stmt->fetchAll();

// Get all available skills grouped by category
$allSkills=$db->query('SELECT s.id, s.name, sc.name AS category_name, sc.id AS cat_id FROM skills s JOIN skill_categories sc ON sc.id=s.category_id ORDER BY sc.display_order, s.name')->fetchAll();
$grouped=[];foreach($allSkills as $s){$grouped[$s['category_name']][]=$s;}
$mySkillIds=array_column($mySkills,'skill_id');

include __DIR__.'/../includes/header.php';
?>
<div class="main-content"><div class="container"><div class="dashboard-layout"><?php include __DIR__.'/sidebar.php'; ?>
<div>
<div class="page-header"><h1>My Skills</h1><p>You have <?php echo count($mySkills); ?> skills in your profile</p></div>

<!-- Current Skills -->
<div class="card mb-3">
<div class="card-header"><h3>Your Skills</h3></div>
<?php if(empty($mySkills)): ?>
<div class="empty-state"><p>You haven't added any skills yet. Add skills below to improve your match scores.</p></div>
<?php else: ?>
<table><thead><tr><th>Skill</th><th>Category</th><th>Proficiency</th><th>Actions</th></tr></thead><tbody>
<?php foreach($mySkills as $sk): ?>
<tr>
<td><strong><?php echo e($sk['skill_name']); ?></strong></td>
<td><span class="text-muted text-sm"><?php echo e($sk['category_name']); ?></span></td>
<td>
    <form method="POST" style="display:inline;" class="d-flex gap-1">
        <?php echo csrfField(); ?><input type="hidden" name="action" value="update"><input type="hidden" name="skill_id" value="<?php echo (int)$sk['skill_id']; ?>">
        <select name="proficiency" class="form-control" style="width:auto;padding:0.3rem 0.5rem;font-size:0.8rem;" onchange="this.form.submit()">
            <?php foreach(['beginner','intermediate','advanced'] as $p): ?><option value="<?php echo $p; ?>" <?php echo $sk['proficiency']===$p?'selected':''; ?>><?php echo ucfirst($p); ?></option><?php endforeach; ?>
        </select>
    </form>
</td>
<td>
    <form method="POST" style="display:inline;" onsubmit="return confirm('Remove this skill?')">
        <?php echo csrfField(); ?><input type="hidden" name="action" value="remove"><input type="hidden" name="skill_id" value="<?php echo (int)$sk['skill_id']; ?>">
        <button type="submit" class="btn btn-sm btn-danger">Remove</button>
    </form>
</td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>
</div>

<!-- Add Skills -->
<div class="card">
<div class="card-header"><h3>Add Skills</h3></div>
<?php foreach($grouped as $catName=>$skills): ?>
<h4 class="mt-2 mb-1" style="font-size:0.9rem;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.05em;"><?php echo e($catName); ?></h4>
<div class="d-flex flex-wrap gap-1 mb-2">
<?php foreach($skills as $s):
    $already=in_array($s['id'],$mySkillIds);
?>
<?php if(!$already): ?>
<form method="POST" style="display:inline;" class="d-flex gap-1 align-center">
    <?php echo csrfField(); ?><input type="hidden" name="action" value="add"><input type="hidden" name="skill_id" value="<?php echo (int)$s['id']; ?>">
    <select name="proficiency" style="font-size:0.75rem;padding:0.2rem;border:1px solid var(--gray-300);border-radius:4px;">
        <option value="beginner">Beginner</option><option value="intermediate">Intermediate</option><option value="advanced">Advanced</option>
    </select>
    <button type="submit" class="skill-chip" style="border-color:var(--primary);color:var(--primary);">+ <?php echo e($s['name']); ?></button>
</form>
<?php else: ?>
<span class="skill-chip selected" style="cursor:default;">✓ <?php echo e($s['name']); ?></span>
<?php endif; ?>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
</div>

</div></div></div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>

