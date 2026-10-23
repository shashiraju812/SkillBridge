<?php
$pageTitle='Education';$currentPage='education.php';
require_once __DIR__.'/../config/database.php';require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/csrf.php';require_once __DIR__.'/../includes/functions.php';
requireRole('student');$db=getDB();$profileId=getStudentProfileId($db);
$stmt=$db->prepare('SELECT * FROM student_profiles WHERE id=?');$stmt->execute([$profileId]);$data=$stmt->fetch();
if($_SERVER['REQUEST_METHOD']==='POST'){requireValidCSRF();
    $college=cleanInput($_POST['college']??'');$degree=cleanInput($_POST['degree']??'');$branch=cleanInput($_POST['branch']??'');$grad=(int)($_POST['graduation_year']??0);
    $db->prepare('UPDATE student_profiles SET college=?,degree=?,branch=?,graduation_year=? WHERE id=?')->execute([$college,$degree,$branch,$grad?:null,$profileId]);
    setFlash('success','Education updated.');header('Location:/wtproject/student/education.php');exit;}
include __DIR__.'/../includes/header.php';
?>
<div class="main-content"><div class="container"><div class="dashboard-layout"><?php include __DIR__.'/sidebar.php'; ?>
<div><div class="page-header"><h1>Education Details</h1></div><div class="card">
<form method="POST" data-validate><?php echo csrfField(); ?>
<div class="form-row">
<div class="form-group"><label>College <span class="required">*</span></label><input type="text" name="college" class="form-control" required value="<?php echo e($data['college']??''); ?>"></div>
<div class="form-group"><label>Degree</label><select name="degree" class="form-control"><option value="">Select</option><?php foreach(['B.Tech','B.E.','BCA','B.Sc','MCA','M.Tech','M.Sc'] as $d): ?><option value="<?php echo $d; ?>" <?php echo ($data['degree']??'')===$d?'selected':''; ?>><?php echo $d; ?></option><?php endforeach; ?></select></div>
</div>
<div class="form-row">
<div class="form-group"><label>Branch</label><select name="branch" class="form-control"><option value="">Select</option><?php foreach(['Computer Science','Information Technology','Electronics','Electrical','Mechanical','Civil','Other'] as $b): ?><option value="<?php echo $b; ?>" <?php echo ($data['branch']??'')===$b?'selected':''; ?>><?php echo $b; ?></option><?php endforeach; ?></select></div>
<div class="form-group"><label>Graduation Year</label><input type="number" name="graduation_year" class="form-control" min="2020" max="2035" value="<?php echo e($data['graduation_year']??''); ?>"></div>
</div>
<button type="submit" class="btn btn-primary">Save Education</button>
</form></div></div></div></div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>
