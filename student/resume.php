<?php
/**
 * Resume Management — SkillBridge
 */
$pageTitle='Resume';$currentPage='resume.php';
require_once __DIR__.'/../config/database.php';require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/csrf.php';require_once __DIR__.'/../includes/functions.php';
requireRole('student');$db=getDB();$profileId=getStudentProfileId($db);
$stmt=$db->prepare('SELECT resume_path FROM student_profiles WHERE id=?');$stmt->execute([$profileId]);$currentResume=$stmt->fetchColumn();

if($_SERVER['REQUEST_METHOD']==='POST'){
    requireValidCSRF();
    if(isset($_POST['action']) && $_POST['action']==='delete' && $currentResume){
        $path=__DIR__.'/../uploads/resumes/'.$currentResume;
        if(file_exists($path))unlink($path);
        $db->prepare('UPDATE student_profiles SET resume_path=NULL WHERE id=?')->execute([$profileId]);
        setFlash('success','Resume deleted.');header('Location:/wtproject/student/resume.php');exit;
    }
    if(isset($_FILES['resume']) && $_FILES['resume']['error']===UPLOAD_ERR_OK){
        $file=$_FILES['resume'];
        $allowed=['application/pdf'];$maxSize=2*1024*1024;
        if(!in_array($file['type'],$allowed)){setFlash('error','Only PDF files are allowed.');header('Location:/wtproject/student/resume.php');exit;}
        if($file['size']>$maxSize){setFlash('error','File size must be under 2MB.');header('Location:/wtproject/student/resume.php');exit;}
        $ext=pathinfo($file['name'],PATHINFO_EXTENSION);
        $newName='resume_'.getCurrentUserId().'_'.time().'.'.$ext;
        $uploadDir=__DIR__.'/../uploads/resumes/';
        if(!is_dir($uploadDir))mkdir($uploadDir,0755,true);
        if($currentResume){$old=$uploadDir.$currentResume;if(file_exists($old))unlink($old);}
        move_uploaded_file($file['tmp_name'],$uploadDir.$newName);
        $db->prepare('UPDATE student_profiles SET resume_path=? WHERE id=?')->execute([$newName,$profileId]);
        setFlash('success','Resume uploaded successfully.');header('Location:/wtproject/student/resume.php');exit;
    } else { setFlash('error','Please select a file to upload.'); header('Location:/wtproject/student/resume.php'); exit; }
}
include __DIR__.'/../includes/header.php';
?>
<div class="main-content"><div class="container"><div class="dashboard-layout"><?php include __DIR__.'/sidebar.php'; ?>
<div><div class="page-header"><h1>Resume</h1></div>
<div class="card">
<?php if($currentResume): ?>
<div class="alert alert-success">
    <strong>Current Resume:</strong> <?php echo e($currentResume); ?>
    <a href="/wtproject/uploads/resumes/<?php echo e($currentResume); ?>" target="_blank" class="btn btn-sm btn-outline" style="margin-left:1rem;">Download</a>
</div>
<form method="POST" style="display:inline"><?php echo csrfField(); ?><input type="hidden" name="action" value="delete"><button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete resume?')">Delete Resume</button></form>
<hr style="margin:1.5rem 0;">
<?php endif; ?>
<h4><?php echo $currentResume?'Replace':'Upload'; ?> Resume</h4>
<form method="POST" enctype="multipart/form-data"><?php echo csrfField(); ?>
<div class="form-group"><label>Select PDF File (max 2MB)</label><input type="file" name="resume" class="form-control" accept=".pdf" required></div>
<button type="submit" class="btn btn-primary"><?php echo $currentResume?'Replace':'Upload'; ?> Resume</button>
</form>
</div></div></div></div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>

