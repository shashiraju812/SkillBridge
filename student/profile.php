<?php
$pageTitle = 'My Profile'; $currentPage = 'profile.php';
require_once __DIR__.'/../config/database.php'; require_once __DIR__.'/../includes/auth.php'; require_once __DIR__.'/../includes/csrf.php'; require_once __DIR__.'/../includes/functions.php';
requireRole('student'); $db = getDB(); $profileId = getStudentProfileId($db);

$stmt = $db->prepare('SELECT u.full_name, u.email, sp.* FROM users u JOIN student_profiles sp ON sp.user_id=u.id WHERE u.id=?');
$stmt->execute([getCurrentUserId()]); $data = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    requireValidCSRF();
    $name = cleanInput($_POST['full_name']??'');
    $phone = cleanInput($_POST['phone']??'');
    $bio = cleanInput($_POST['bio']??'');
    $github = cleanInput($_POST['github_url']??'');
    $portfolio = cleanInput($_POST['portfolio_url']??'');
    $pref_role = cleanInput($_POST['preferred_role']??'');
    $pref_loc = cleanInput($_POST['preferred_location']??'');
    $pref_mode = cleanInput($_POST['preferred_mode']??'');
    if(empty($name)){setFlash('error','Name is required.');header('Location:'.$_SERVER['REQUEST_URI']);exit;}
    $db->prepare('UPDATE users SET full_name=? WHERE id=?')->execute([$name,getCurrentUserId()]);
    $_SESSION['user_name'] = $name;
    $db->prepare('UPDATE student_profiles SET phone=?,bio=?,github_url=?,portfolio_url=?,preferred_role=?,preferred_location=?,preferred_mode=? WHERE id=?')->execute([$phone,$bio,$github,$portfolio,$pref_role,$pref_loc,$pref_mode?:null,$profileId]);
    setFlash('success','Profile updated successfully.');
    header('Location:/wtproject/student/profile.php'); exit;
}
include __DIR__.'/../includes/header.php';
?>
<div class="main-content"><div class="container"><div class="dashboard-layout">
<?php include __DIR__.'/sidebar.php'; ?>
<div>
    <div class="page-header"><h1>My Profile</h1></div>
    <div class="card">
        <form method="POST" data-validate><?php echo csrfField(); ?>
        <div class="form-row">
            <div class="form-group"><label>Full Name <span class="required">*</span></label><input type="text" name="full_name" class="form-control" required value="<?php echo e($data['full_name']); ?>"></div>
            <div class="form-group"><label>Email</label><input type="email" class="form-control" value="<?php echo e($data['email'] ?? ''); ?>" disabled><div class="form-text">Change email in Settings</div></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Phone</label><input type="text" name="phone" class="form-control" value="<?php echo e($data['phone']??''); ?>"></div>
            <div class="form-group"><label>Preferred Role</label><input type="text" name="preferred_role" class="form-control" value="<?php echo e($data['preferred_role']??''); ?>"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Preferred Location</label><input type="text" name="preferred_location" class="form-control" value="<?php echo e($data['preferred_location']??''); ?>"></div>
            <div class="form-group"><label>Preferred Mode</label><select name="preferred_mode" class="form-control"><option value="">No Preference</option><?php foreach(['remote','hybrid','onsite'] as $m): ?><option value="<?php echo $m; ?>" <?php echo ($data['preferred_mode']??'')===$m?'selected':''; ?>><?php echo ucfirst($m); ?></option><?php endforeach; ?></select></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>GitHub URL</label><input type="url" name="github_url" class="form-control" value="<?php echo e($data['github_url']??''); ?>"></div>
            <div class="form-group"><label>Portfolio URL</label><input type="url" name="portfolio_url" class="form-control" value="<?php echo e($data['portfolio_url']??''); ?>"></div>
        </div>
        <div class="form-group"><label>Bio</label><textarea name="bio" class="form-control" rows="3"><?php echo e($data['bio']??''); ?></textarea></div>
        <button type="submit" class="btn btn-primary">Save Profile</button>
        </form>
    </div>
</div>
</div></div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>
