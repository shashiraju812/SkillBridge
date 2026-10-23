<?php
/**
 * Create Internship — SkillBridge
 */
$pageTitle='Create Internship';$currentPage='create-internship.php';
require_once __DIR__.'/../config/database.php';require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/csrf.php';require_once __DIR__.'/../includes/functions.php';
requireRole('recruiter');$db=getDB();$rpId=getRecruiterProfileId($db);

// Get all skills grouped
$allSkills=$db->query('SELECT s.id, s.name, sc.name AS cat_name FROM skills s JOIN skill_categories sc ON sc.id=s.category_id ORDER BY sc.display_order, s.name')->fetchAll();
$grouped=[];foreach($allSkills as $s)$grouped[$s['cat_name']][]=$s;

if($_SERVER['REQUEST_METHOD']==='POST'){
    requireValidCSRF();
    $title=cleanInput($_POST['title']??'');$desc=cleanInput($_POST['description']??'');
    if(empty($title)||empty($desc)){setFlash('error','Title and description are required.');header('Location:'.$_SERVER['REQUEST_URI']);exit;}
    
    try{$db->beginTransaction();
    $stmt=$db->prepare('INSERT INTO internships (recruiter_profile_id,title,description,responsibilities,eligibility,eligible_degrees,eligible_branches,min_graduation_year,max_graduation_year,location,work_mode,stipend_min,stipend_max,is_paid,duration,openings,application_deadline,career_category,application_instructions,status,is_sample) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,0)');
    $stmt->execute([$rpId,$title,$desc,cleanInput($_POST['responsibilities']??''),cleanInput($_POST['eligibility']??''),cleanInput($_POST['eligible_degrees']??''),cleanInput($_POST['eligible_branches']??''),
        ((int)$_POST['min_graduation_year'])?:null,((int)$_POST['max_graduation_year'])?:null,cleanInput($_POST['location']??''),cleanInput($_POST['work_mode']??'remote'),
        (int)($_POST['stipend_min']??0),(int)($_POST['stipend_max']??0),isset($_POST['is_paid'])?1:0,cleanInput($_POST['duration']??''),(int)($_POST['openings']??1),
        $_POST['application_deadline']?:null,cleanInput($_POST['career_category']??''),cleanInput($_POST['application_instructions']??''),cleanInput($_POST['status']??'active')]);
    $intId=$db->lastInsertId();
    
    // Add skills
    if(!empty($_POST['skills'])&&is_array($_POST['skills'])){
        $skillStmt=$db->prepare('INSERT INTO internship_skills (internship_id,skill_id,importance,min_proficiency) VALUES (?,?,?,?)');
        foreach($_POST['skills'] as $skId){
            $imp=cleanInput($_POST['importance_'.$skId]??'required');$prof=cleanInput($_POST['proficiency_'.$skId]??'beginner');
            $skillStmt->execute([$intId,(int)$skId,$imp,$prof]);
        }
    }
    $db->commit();setFlash('success','Internship created successfully.');header('Location:/wtproject/recruiter/manage-internships.php');exit;
    }catch(Exception $e){$db->rollBack();error_log($e->getMessage());setFlash('error','Failed to create internship.');header('Location:'.$_SERVER['REQUEST_URI']);exit;}
}

include __DIR__.'/../includes/header.php';
?>
<div class="main-content"><div class="container"><div class="dashboard-layout"><?php include __DIR__.'/sidebar.php'; ?>
<div><div class="page-header"><h1>Create Internship</h1></div>
<form method="POST" data-validate class="card"><?php echo csrfField(); ?>
<h3>Basic Information</h3>
<div class="form-group"><label>Title <span class="required">*</span></label><input type="text" name="title" class="form-control" required placeholder="e.g. Frontend Developer Intern"></div>
<div class="form-group"><label>Description <span class="required">*</span></label><textarea name="description" class="form-control" rows="4" required></textarea></div>
<div class="form-group"><label>Responsibilities</label><textarea name="responsibilities" class="form-control" rows="3" placeholder="One per line"></textarea></div>
<div class="form-group"><label>Eligibility</label><textarea name="eligibility" class="form-control" rows="2"></textarea></div>

<h3 class="mt-3">Requirements</h3>
<div class="form-row">
<div class="form-group"><label>Eligible Degrees</label><input type="text" name="eligible_degrees" class="form-control" placeholder="e.g. B.Tech, BCA, MCA"></div>
<div class="form-group"><label>Eligible Branches</label><input type="text" name="eligible_branches" class="form-control" placeholder="e.g. CSE, IT"></div></div>
<div class="form-row">
<div class="form-group"><label>Min Graduation Year</label><input type="number" name="min_graduation_year" class="form-control" min="2020" max="2035"></div>
<div class="form-group"><label>Max Graduation Year</label><input type="number" name="max_graduation_year" class="form-control" min="2020" max="2035"></div></div>

<h3 class="mt-3">Details</h3>
<div class="form-row">
<div class="form-group"><label>Location</label><input type="text" name="location" class="form-control"></div>
<div class="form-group"><label>Work Mode</label><select name="work_mode" class="form-control"><option value="remote">Remote</option><option value="hybrid">Hybrid</option><option value="onsite">Onsite</option></select></div></div>
<div class="form-row">
<div class="form-group"><label>Stipend Min (₹/month)</label><input type="number" name="stipend_min" class="form-control" min="0" value="0"></div>
<div class="form-group"><label>Stipend Max (₹/month)</label><input type="number" name="stipend_max" class="form-control" min="0" value="0"></div></div>
<div class="form-row">
<div class="form-group"><label class="form-check"><input type="checkbox" name="is_paid" checked> Paid Internship</label></div>
<div class="form-group"><label>Duration</label><input type="text" name="duration" class="form-control" placeholder="e.g. 3 months"></div></div>
<div class="form-row">
<div class="form-group"><label>Openings</label><input type="number" name="openings" class="form-control" min="1" value="1"></div>
<div class="form-group"><label>Application Deadline</label><input type="date" name="application_deadline" class="form-control"></div></div>
<div class="form-row">
<div class="form-group"><label>Career Category</label><input type="text" name="career_category" class="form-control" placeholder="e.g. Web Development"></div>
<div class="form-group"><label>Status</label><select name="status" class="form-control"><option value="active">Active</option><option value="draft">Draft</option></select></div></div>
<div class="form-group"><label>Application Instructions</label><textarea name="application_instructions" class="form-control" rows="2"></textarea></div>

<h3 class="mt-3">Required Skills</h3>
<p class="text-sm text-muted">Check skills required for this internship and set importance/level.</p>
<?php foreach($grouped as $catName=>$skills): ?>
<h4 class="mt-2" style="font-size:0.85rem;color:var(--gray-500);"><?php echo e($catName); ?></h4>
<?php foreach($skills as $s): ?>
<div class="d-flex align-center gap-1 mb-1" style="padding:0.375rem;border:1px solid var(--gray-100);border-radius:6px;">
<label class="form-check" style="margin-bottom:0;flex:1;"><input type="checkbox" name="skills[]" value="<?php echo $s['id']; ?>"> <?php echo e($s['name']); ?></label>
<select name="importance_<?php echo $s['id']; ?>" style="font-size:0.75rem;padding:0.2rem;border:1px solid var(--gray-300);border-radius:4px;"><option value="required">Required</option><option value="preferred">Preferred</option><option value="nice_to_have">Nice to Have</option></select>
<select name="proficiency_<?php echo $s['id']; ?>" style="font-size:0.75rem;padding:0.2rem;border:1px solid var(--gray-300);border-radius:4px;"><option value="beginner">Beginner</option><option value="intermediate">Intermediate</option><option value="advanced">Advanced</option></select>
</div>
<?php endforeach; endforeach; ?>

<button type="submit" class="btn btn-primary btn-lg mt-3">Create Internship</button>
</form>
</div></div></div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>

