<?php
$pageTitle='Recruiter Dashboard';$currentPage='dashboard.php';
require_once __DIR__.'/../config/database.php';require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/functions.php';
requireRole('recruiter');$db=getDB();$rpId=getRecruiterProfileId($db);

$stmt=$db->prepare('SELECT COUNT(*) FROM internships WHERE recruiter_profile_id=? AND status="active"');$stmt->execute([$rpId]);$activeCount=(int)$stmt->fetchColumn();
$stmt=$db->prepare('SELECT COUNT(*) FROM applications a JOIN internships i ON i.id=a.internship_id WHERE i.recruiter_profile_id=?');$stmt->execute([$rpId]);$totalApps=(int)$stmt->fetchColumn();
$stmt=$db->prepare('SELECT COUNT(*) FROM applications a JOIN internships i ON i.id=a.internship_id WHERE i.recruiter_profile_id=? AND a.status="shortlisted"');$stmt->execute([$rpId]);$shortlisted=(int)$stmt->fetchColumn();
$stmt=$db->prepare('SELECT COUNT(*) FROM applications a JOIN internships i ON i.id=a.internship_id WHERE i.recruiter_profile_id=? AND a.status="applied"');$stmt->execute([$rpId]);$pending=(int)$stmt->fetchColumn();

$stmt=$db->prepare('SELECT a.*, i.title, u.full_name FROM applications a JOIN internships i ON i.id=a.internship_id JOIN student_profiles sp ON sp.id=a.student_profile_id JOIN users u ON u.id=sp.user_id WHERE i.recruiter_profile_id=? ORDER BY a.applied_at DESC LIMIT 10');
$stmt->execute([$rpId]);$recentApps=$stmt->fetchAll();

$stmt=$db->prepare('SELECT i.*, (SELECT COUNT(*) FROM applications WHERE internship_id=i.id) AS app_count FROM internships i WHERE i.recruiter_profile_id=? AND i.status="active" ORDER BY i.created_at DESC');
$stmt->execute([$rpId]);$activeInternships=$stmt->fetchAll();

include __DIR__.'/../includes/header.php';
?>
<div class="main-content"><div class="container"><div class="dashboard-layout"><?php include __DIR__.'/sidebar.php'; ?>
<div>
<div class="page-header"><h1>Recruiter Dashboard</h1><p>Welcome, <?php echo e(getCurrentUserName()); ?></p></div>
<div class="card-grid-4 mb-3">
<div class="stat-card"><div class="stat-icon teal"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg></div><div class="stat-info"><h4>Active Listings</h4><div class="stat-value"><?php echo $activeCount; ?></div></div></div>
<div class="stat-card"><div class="stat-icon blue"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path></svg></div><div class="stat-info"><h4>Total Applications</h4><div class="stat-value"><?php echo $totalApps; ?></div></div></div>
<div class="stat-card"><div class="stat-icon green"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg></div><div class="stat-info"><h4>Shortlisted</h4><div class="stat-value"><?php echo $shortlisted; ?></div></div></div>
<div class="stat-card"><div class="stat-icon amber"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg></div><div class="stat-info"><h4>Pending Review</h4><div class="stat-value"><?php echo $pending; ?></div></div></div>
</div>

<div class="card-grid-2">
<div class="card"><div class="card-header"><h3>Recent Applications</h3></div>
<?php if(empty($recentApps)): ?><p class="text-muted">No applications yet.</p>
<?php else: ?><table><thead><tr><th>Student</th><th>Internship</th><th>Status</th></tr></thead><tbody>
<?php foreach($recentApps as $a): ?><tr><td><?php echo e($a['full_name']); ?></td><td class="text-sm"><?php echo e($a['title']); ?></td><td><span class="badge <?php echo statusBadgeClass($a['status']); ?>"><?php echo formatStatus($a['status']); ?></span></td></tr>
<?php endforeach; ?></tbody></table><?php endif; ?></div>

<div class="card"><div class="card-header"><h3>Active Internships</h3></div>
<?php if(empty($activeInternships)): ?><p class="text-muted">No active listings.</p><a href="/wtproject/recruiter/create-internship.php" class="btn btn-primary btn-sm mt-1">Create Internship</a>
<?php else: foreach($activeInternships as $i): ?>
<div style="padding:0.5rem 0;border-bottom:1px solid var(--gray-100);">
<strong class="text-sm"><?php echo e($i['title']); ?></strong> <span class="badge badge-info"><?php echo $i['app_count']; ?> apps</span><br>
<a href="/wtproject/recruiter/applicants.php?internship_id=<?php echo $i['id']; ?>" class="text-xs">View Applicants</a>
</div>
<?php endforeach; endif; ?></div>
</div>
</div></div></div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>

