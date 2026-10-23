<?php
$pageTitle='Settings';$currentPage='settings.php';
require_once __DIR__.'/../config/database.php';require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/csrf.php';require_once __DIR__.'/../includes/functions.php';
requireRole('student');$db=getDB();
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
    requireValidCSRF();$action=$_POST['action']??'';
    if($action==='change_password'){
        $current=$_POST['current_password']??'';$new=$_POST['new_password']??'';$confirm=$_POST['confirm_password']??'';
        $stmt=$db->prepare('SELECT password_hash FROM users WHERE id=?');$stmt->execute([getCurrentUserId()]);$user=$stmt->fetch();
        if(!password_verify($current,$user['password_hash'])){$errors[]='Current password is incorrect.';}
        else{$errors=array_merge($errors,validatePassword($new));if($new!==$confirm)$errors[]='New passwords do not match.';}
        if(empty($errors)){$db->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($new,PASSWORD_DEFAULT),getCurrentUserId()]);setFlash('success','Password changed.');header('Location:/wtproject/student/settings.php');exit;}
    }
    if($action==='change_email'){
        $newEmail=cleanInput($_POST['new_email']??'');
        if(!isValidEmail($newEmail)){$errors[]='Invalid email.';}
        else{$check=$db->prepare('SELECT id FROM users WHERE email=? AND id!=?');$check->execute([$newEmail,getCurrentUserId()]);
        if($check->fetch()){$errors[]='Email already in use.';}
        else{$db->prepare('UPDATE users SET email=? WHERE id=?')->execute([$newEmail,getCurrentUserId()]);$_SESSION['user_email']=$newEmail;setFlash('success','Email updated.');header('Location:/wtproject/student/settings.php');exit;}}
    }
}
include __DIR__.'/../includes/header.php';
?>
<div class="main-content"><div class="container"><div class="dashboard-layout"><?php include __DIR__.'/sidebar.php'; ?>
<div><div class="page-header"><h1>Account Settings</h1></div>
<?php if(!empty($errors)): ?><div class="alert alert-error"><ul style="margin:0;padding-left:1.25rem;"><?php foreach($errors as $e): ?><li><?php echo e($e); ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="card mb-3"><h3>Change Email</h3>
<form method="POST"><?php echo csrfField(); ?><input type="hidden" name="action" value="change_email">
<div class="form-group"><label>Current Email</label><input type="email" class="form-control" value="<?php echo e($_SESSION['user_email']??''); ?>" disabled></div>
<div class="form-group"><label>New Email</label><input type="email" name="new_email" class="form-control" required></div>
<button type="submit" class="btn btn-primary">Update Email</button></form></div>
<div class="card"><h3>Change Password</h3>
<form method="POST" data-validate><?php echo csrfField(); ?><input type="hidden" name="action" value="change_password">
<div class="form-group"><label>Current Password</label><input type="password" name="current_password" class="form-control" required></div>
<div class="form-group"><label>New Password</label><input type="password" id="password" name="new_password" class="form-control" required></div>
<div class="form-group"><label>Confirm New Password</label><input type="password" id="confirm_password" name="confirm_password" class="form-control" required></div>
<button type="submit" class="btn btn-primary">Change Password</button></form></div>
</div></div></div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>

