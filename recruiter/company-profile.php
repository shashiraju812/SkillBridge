<?php
$pageTitle='Company Profile';$currentPage='company-profile.php';
require_once __DIR__.'/../config/database.php';require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/csrf.php';require_once __DIR__.'/../includes/functions.php';
requireRole('recruiter');$db=getDB();$rpId=getRecruiterProfileId($db);
$stmt=$db->prepare('SELECT * FROM recruiter_profiles WHERE id=?');$stmt->execute([$rpId]);$data=$stmt->fetch();

if($_SERVER['REQUEST_METHOD']==='POST'){requireValidCSRF();
    $db->prepare('UPDATE recruiter_profiles SET company_name=?,company_website=?,company_description=?,industry=?,company_size=?,location=?,phone=? WHERE id=?')->execute([
        cleanInput($_POST['company_name']),cleanInput($_POST['company_website']),cleanInput($_POST['company_description']),
        cleanInput($_POST['industry']),cleanInput($_POST['company_size']),cleanInput($_POST['location']),cleanInput($_POST['phone']),$rpId
    ]);setFlash('success','Company profile updated.');header('Location:/wtproject/recruiter/company-profile.php');exit;}

include __DIR__.'/../includes/header.php';
?>
<div class="main-content"><div class="container"><div class="dashboard-layout"><?php include __DIR__.'/sidebar.php'; ?>
<div><div class="page-header"><h1>Company Profile</h1></div><div class="card">
<form method="POST" data-validate><?php echo csrfField(); ?>
<div class="form-row">
<div class="form-group"><label>Company Name <span class="required">*</span></label><input type="text" name="company_name" class="form-control" required value="<?php echo e($data['company_name']??''); ?>"></div>
<div class="form-group"><label>Website</label><input type="url" name="company_website" class="form-control" value="<?php echo e($data['company_website']??''); ?>"></div></div>
<div class="form-group"><label>Description</label><textarea name="company_description" class="form-control" rows="4"><?php echo e($data['company_description']??''); ?></textarea></div>
<div class="form-row">
<div class="form-group"><label>Industry</label><input type="text" name="industry" class="form-control" value="<?php echo e($data['industry']??''); ?>"></div>
<div class="form-group"><label>Company Size</label><select name="company_size" class="form-control"><option value="">Select</option><?php foreach(['1-10','11-50','50-100','100-500','500+'] as $s): ?><option value="<?php echo $s; ?>" <?php echo ($data['company_size']??'')===$s?'selected':''; ?>><?php echo $s; ?></option><?php endforeach; ?></select></div></div>
<div class="form-row">
<div class="form-group"><label>Location</label><input type="text" name="location" class="form-control" value="<?php echo e($data['location']??''); ?>"></div>
<div class="form-group"><label>Phone</label><input type="text" name="phone" class="form-control" value="<?php echo e($data['phone']??''); ?>"></div></div>
<button type="submit" class="btn btn-primary">Save Company Profile</button>
</form></div></div></div></div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>

