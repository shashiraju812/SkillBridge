<?php
/**
 * Projects & Certificates — SkillBridge
 */
$pageTitle='Projects & Certificates';$currentPage='projects.php';
require_once __DIR__.'/../config/database.php';require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/csrf.php';require_once __DIR__.'/../includes/functions.php';
requireRole('student');$db=getDB();$profileId=getStudentProfileId($db);

if($_SERVER['REQUEST_METHOD']==='POST'){
    requireValidCSRF();
    $action=$_POST['action']??'';
    if($action==='add_project'){
        $db->prepare('INSERT INTO projects (student_profile_id,title,description,technologies,project_url,github_url,start_date,end_date) VALUES (?,?,?,?,?,?,?,?)')->execute([
            $profileId,cleanInput($_POST['title']),cleanInput($_POST['description']),cleanInput($_POST['technologies']),
            cleanInput($_POST['project_url']),cleanInput($_POST['github_url']),$_POST['start_date']?:null,$_POST['end_date']?:null
        ]);setFlash('success','Project added.');
    } elseif($action==='delete_project'){
        $db->prepare('DELETE FROM projects WHERE id=? AND student_profile_id=?')->execute([(int)$_POST['project_id'],$profileId]);
        setFlash('success','Project removed.');
    } elseif($action==='add_cert'){
        $db->prepare('INSERT INTO certificates (student_profile_id,title,issuing_org,issue_date,credential_url) VALUES (?,?,?,?,?)')->execute([
            $profileId,cleanInput($_POST['title']),cleanInput($_POST['issuing_org']),$_POST['issue_date']?:null,cleanInput($_POST['credential_url'])
        ]);setFlash('success','Certificate added.');
    } elseif($action==='delete_cert'){
        $db->prepare('DELETE FROM certificates WHERE id=? AND student_profile_id=?')->execute([(int)$_POST['cert_id'],$profileId]);
        setFlash('success','Certificate removed.');
    }
    header('Location:/wtproject/student/projects.php');exit;
}
$projects=$db->prepare('SELECT * FROM projects WHERE student_profile_id=? ORDER BY start_date DESC');$projects->execute([$profileId]);$projects=$projects->fetchAll();
$certs=$db->prepare('SELECT * FROM certificates WHERE student_profile_id=? ORDER BY issue_date DESC');$certs->execute([$profileId]);$certs=$certs->fetchAll();
include __DIR__.'/../includes/header.php';
?>
<div class="main-content"><div class="container"><div class="dashboard-layout"><?php include __DIR__.'/sidebar.php'; ?>
<div>
<div class="page-header"><h1>Projects & Certificates</h1></div>

<div class="tabs">
    <button class="tab active" data-tab="tab-projects">Projects (<?php echo count($projects); ?>)</button>
    <button class="tab" data-tab="tab-certs">Certificates (<?php echo count($certs); ?>)</button>
</div>

<!-- Projects Tab -->
<div id="tab-projects" class="tab-content active">
<?php foreach($projects as $p): ?>
<div class="card mb-2">
    <div class="d-flex justify-between align-center">
        <div><h4 class="mb-0"><?php echo e($p['title']); ?></h4>
        <span class="text-sm text-muted"><?php echo e($p['technologies']); ?> · <?php echo formatDate($p['start_date'],'M Y'); ?> – <?php echo $p['end_date']?formatDate($p['end_date'],'M Y'):'Present'; ?></span></div>
        <form method="POST" onsubmit="return confirm('Delete this project?')"><?php echo csrfField(); ?><input type="hidden" name="action" value="delete_project"><input type="hidden" name="project_id" value="<?php echo $p['id']; ?>"><button class="btn btn-sm btn-danger">Delete</button></form>
    </div>
    <?php if($p['description']): ?><p class="text-sm mt-1"><?php echo e($p['description']); ?></p><?php endif; ?>
    <?php if($p['project_url']): ?><a href="<?php echo e($p['project_url']); ?>" class="text-sm" target="_blank">Live Demo</a><?php endif; ?>
    <?php if($p['github_url']): ?> · <a href="<?php echo e($p['github_url']); ?>" class="text-sm" target="_blank">GitHub</a><?php endif; ?>
</div>
<?php endforeach; ?>

<div class="card mt-2"><h4>Add New Project</h4>
<form method="POST" data-validate><?php echo csrfField(); ?><input type="hidden" name="action" value="add_project">
<div class="form-group"><label>Title <span class="required">*</span></label><input type="text" name="title" class="form-control" required></div>
<div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
<div class="form-row">
<div class="form-group"><label>Technologies Used</label><input type="text" name="technologies" class="form-control" placeholder="e.g. HTML, CSS, PHP"></div>
<div class="form-group"><label>Project URL</label><input type="url" name="project_url" class="form-control"></div>
</div>
<div class="form-row">
<div class="form-group"><label>GitHub URL</label><input type="url" name="github_url" class="form-control"></div>
<div class="form-group"><label>Start Date</label><input type="date" name="start_date" class="form-control"></div>
</div>
<div class="form-group"><label>End Date</label><input type="date" name="end_date" class="form-control"></div>
<button type="submit" class="btn btn-primary">Add Project</button>
</form></div>
</div>

<!-- Certificates Tab -->
<div id="tab-certs" class="tab-content">
<?php foreach($certs as $c): ?>
<div class="card mb-2">
    <div class="d-flex justify-between align-center">
        <div><h4 class="mb-0"><?php echo e($c['title']); ?></h4><span class="text-sm text-muted"><?php echo e($c['issuing_org']); ?> · <?php echo formatDate($c['issue_date'],'M Y'); ?></span>
        <?php if($c['credential_url']): ?><br><a href="<?php echo e($c['credential_url']); ?>" class="text-sm" target="_blank">View Credential</a><?php endif; ?></div>
        <form method="POST" onsubmit="return confirm('Delete?')"><?php echo csrfField(); ?><input type="hidden" name="action" value="delete_cert"><input type="hidden" name="cert_id" value="<?php echo $c['id']; ?>"><button class="btn btn-sm btn-danger">Delete</button></form>
    </div>
</div>
<?php endforeach; ?>
<div class="card mt-2"><h4>Add Certificate</h4>
<form method="POST" data-validate><?php echo csrfField(); ?><input type="hidden" name="action" value="add_cert">
<div class="form-row"><div class="form-group"><label>Title <span class="required">*</span></label><input type="text" name="title" class="form-control" required></div>
<div class="form-group"><label>Issuing Organization</label><input type="text" name="issuing_org" class="form-control"></div></div>
<div class="form-row"><div class="form-group"><label>Issue Date</label><input type="date" name="issue_date" class="form-control"></div>
<div class="form-group"><label>Credential URL</label><input type="url" name="credential_url" class="form-control"></div></div>
<button type="submit" class="btn btn-primary">Add Certificate</button>
</form></div>
</div>

</div></div></div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>

