<?php
$pageTitle='Edit Internship';$currentPage='manage-internships.php';
require_once __DIR__.'/../config/database.php';require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/csrf.php';require_once __DIR__.'/../includes/functions.php';
requireRole('recruiter');$db=getDB();$rpId=getRecruiterProfileId($db);
$id=(int)($_GET['id']??$_POST['id']??0);

$stmt=$db->prepare('SELECT * FROM internships WHERE id=? AND recruiter_profile_id=?');$stmt->execute([$id,$rpId]);$int=$stmt->fetch();
if(!$int){setFlash('error','Internship not found or access denied.');header('Location:/wtproject/recruiter/manage-internships.php');exit;}

$allSkills=$db->query('SELECT s.id,s.name,sc.name AS cat_name FROM skills s JOIN skill_categories sc ON sc.id=s.category_id ORDER BY sc.display_order,s.name')->fetchAll();
$grouped=[];foreach($allSkills as $s)$grouped[$s['cat_name']][]=$s;
$currentSkills=$db->prepare('SELECT * FROM internship_skills WHERE internship_id=?');$currentSkills->execute([$id]);$csMap=[];foreach($currentSkills->fetchAll() as $cs)$csMap[$cs['skill_id']]=$cs;

if($_SERVER['REQUEST_METHOD']==='POST'){requireValidCSRF();
    $db->beginTransaction();
    $db->prepare('UPDATE internships SET title=?,description=?,responsibilities=?,eligibility=?,eligible_degrees=?,eligible_branches=?,min_graduation_year=?,max_graduation_year=?,location=?,work_mode=?,stipend_min=?,stipend_max=?,is_paid=?,duration=?,openings=?,application_deadline=?,career_category=?,application_instructions=?,status=? WHERE id=?')->execute([
        cleanInput($_POST['title']),cleanInput($_POST['description']),cleanInput($_POST['responsibilities']??''),cleanInput($_POST['eligibility']??''),
        cleanInput($_POST['eligible_degrees']??''),cleanInput($_POST['eligible_branches']??''),((int)$_POST['min_graduation_year'])?:null,((int)$_POST['max_graduation_year'])?:null,
        cleanInput($_POST['location']??''),cleanInput($_POST['work_mode']??'remote'),(int)($_POST['stipend_min']??0),(int)($_POST['stipend_max']??0),
        isset($_POST['is_paid'])?1:0,cleanInput($_POST['duration']??''),(int)($_POST['openings']??1),$_POST['application_deadline']?:null,
        cleanInput($_POST['career_category']??''),cleanInput($_POST['application_instructions']??''),cleanInput($_POST['status']??'active'),$id]);
    $db->prepare('DELETE FROM internship_skills WHERE internship_id=?')->execute([$id]);
    if(!empty($_POST['skills'])&&is_array($_POST['skills'])){
        $ss=$db->prepare('INSERT INTO internship_skills (internship_id,skill_id,importance,min_proficiency) VALUES (?,?,?,?)');
        foreach($_POST['skills'] as $skId)$ss->execute([$id,(int)$skId,cleanInput($_POST['importance_'.$skId]??'required'),cleanInput($_POST['proficiency_'.$skId]??'beginner')]);
    }
    $db->commit();setFlash('success','Internship updated.');header('Location:/wtproject/recruiter/manage-internships.php');exit;
}

include __DIR__.'/../includes/header.php';
?>
<div class="main-content"><div class="container"><div class="dashboard-layout"><?php include __DIR__.'/sidebar.php'; ?>
<div><div class="page-header"><h1>Edit: <?php echo e($int['title']); ?></h1></div>
<form method="POST" data-validate class="card"><input type="hidden" name="id" value="<?php echo $id; ?>"><?php echo csrfField(); ?>
<div class="form-group"><label>Title <span class="required">*</span></label><input type="text" name="title" class="form-control" required value="<?php echo e($int['title']); ?>"></div>
<div class="form-group"><label>Description <span class="required">*</span></label><textarea name="description" class="form-control" rows="4" required><?php echo e($int['description']); ?></textarea></div>
<div class="form-group"><label>Responsibilities</label><textarea name="responsibilities" class="form-control" rows="3"><?php echo e($int['responsibilities']); ?></textarea></div>
<div class="form-group"><label>Eligibility</label><textarea name="eligibility" class="form-control" rows="2"><?php echo e($int['eligibility']); ?></textarea></div>
<div class="form-row"><div class="form-group"><label>Eligible Degrees</label><input type="text" name="eligible_degrees" class="form-control" value="<?php echo e($int['eligible_degrees']); ?>"></div>
<div class="form-group"><label>Eligible Branches</label><input type="text" name="eligible_branches" class="form-control" value="<?php echo e($int['eligible_branches']); ?>"></div></div>
<div class="form-row"><div class="form-group"><label>Min Grad Year</label><input type="number" name="min_graduation_year" class="form-control" value="<?php echo e($int['min_graduation_year']); ?>"></div>
<div class="form-group"><label>Max Grad Year</label><input type="number" name="max_graduation_year" class="form-control" value="<?php echo e($int['max_graduation_year']); ?>"></div></div>
<div class="form-row"><div class="form-group"><label>Location</label><input type="text" name="location" class="form-control" value="<?php echo e($int['location']); ?>"></div>
<div class="form-group"><label>Work Mode</label><select name="work_mode" class="form-control"><?php foreach(['remote','hybrid','onsite'] as $m): ?><option value="<?php echo $m; ?>" <?php echo $int['work_mode']===$m?'selected':''; ?>><?php echo ucfirst($m); ?></option><?php endforeach; ?></select></div></div>
<div class="form-row"><div class="form-group"><label>Stipend Min</label><input type="number" name="stipend_min" class="form-control" value="<?php echo $int['stipend_min']; ?>"></div>
<div class="form-group"><label>Stipend Max</label><input type="number" name="stipend_max" class="form-control" value="<?php echo $int['stipend_max']; ?>"></div></div>
<div class="form-row"><div class="form-group"><label class="form-check"><input type="checkbox" name="is_paid" <?php echo $int['is_paid']?'checked':''; ?>> Paid</label></div>
<div class="form-group"><label>Duration</label><input type="text" name="duration" class="form-control" value="<?php echo e($int['duration']); ?>"></div></div>
<div class="form-row"><div class="form-group"><label>Openings</label><input type="number" name="openings" class="form-control" value="<?php echo $int['openings']; ?>"></div>
<div class="form-group"><label>Deadline</label><input type="date" name="application_deadline" class="form-control" value="<?php echo e($int['application_deadline']); ?>"></div></div>
<div class="form-row"><div class="form-group"><label>Category</label><input type="text" name="career_category" class="form-control" value="<?php echo e($int['career_category']); ?>"></div>
<div class="form-group"><label>Status</label><select name="status" class="form-control"><?php foreach(['active','draft','closed'] as $st): ?><option value="<?php echo $st; ?>" <?php echo $int['status']===$st?'selected':''; ?>><?php echo ucfirst($st); ?></option><?php endforeach; ?></select></div></div>
<div class="form-group"><label>Instructions</label><textarea name="application_instructions" class="form-control" rows="2"><?php echo e($int['application_instructions']); ?></textarea></div>

<h3 class="mt-3">Required Skills</h3>
<?php foreach($grouped as $catName=>$skills): ?><h4 style="font-size:0.85rem;color:var(--gray-500);" class="mt-2"><?php echo e($catName); ?></h4>
<?php foreach($skills as $s):$cs=$csMap[$s['id']]??null; ?>
<div class="d-flex align-center gap-1 mb-1" style="padding:0.375rem;border:1px solid var(--gray-100);border-radius:6px;">
<label class="form-check" style="margin-bottom:0;flex:1;"><input type="checkbox" name="skills[]" value="<?php echo $s['id']; ?>" <?php echo $cs?'checked':''; ?>> <?php echo e($s['name']); ?></label>
<select name="importance_<?php echo $s['id']; ?>" style="font-size:0.75rem;padding:0.2rem;"><?php foreach(['required','preferred','nice_to_have'] as $imp): ?><option value="<?php echo $imp; ?>" <?php echo ($cs['importance']??'')===$imp?'selected':''; ?>><?php echo ucfirst(str_replace('_',' ',$imp)); ?></option><?php endforeach; ?></select>
<select name="proficiency_<?php echo $s['id']; ?>" style="font-size:0.75rem;padding:0.2rem;"><?php foreach(['beginner','intermediate','advanced'] as $p): ?><option value="<?php echo $p; ?>" <?php echo ($cs['min_proficiency']??'')===$p?'selected':''; ?>><?php echo ucfirst($p); ?></option><?php endforeach; ?></select>
</div>
<?php endforeach; endforeach; ?>
<button type="submit" class="btn btn-primary btn-lg mt-3">Update Internship</button>
</form></div></div></div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>

