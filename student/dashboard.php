<?php
/**
 * Student Dashboard — SkillBridge
 */
$pageTitle = 'Student Dashboard';
$currentPage = 'dashboard.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('student');
$db = getDB();
$profileId = getStudentProfileId($db);
$completion = calculateProfileCompletion($db, getCurrentUserId());

// Stats
$skillCount = (int)$db->prepare('SELECT COUNT(*) FROM student_skills WHERE student_profile_id=?')->execute([$profileId]) ? $db->prepare('SELECT COUNT(*) FROM student_skills WHERE student_profile_id=?') : 0;
$stmt = $db->prepare('SELECT COUNT(*) FROM student_skills WHERE student_profile_id=?'); $stmt->execute([$profileId]); $skillCount = (int)$stmt->fetchColumn();
$stmt = $db->prepare('SELECT COUNT(*) FROM applications WHERE student_profile_id=?'); $stmt->execute([$profileId]); $appCount = (int)$stmt->fetchColumn();
$stmt = $db->prepare('SELECT COUNT(*) FROM saved_internships WHERE student_profile_id=?'); $stmt->execute([$profileId]); $savedCount = (int)$stmt->fetchColumn();
$stmt = $db->prepare('SELECT COUNT(*) FROM assessment_results WHERE student_profile_id=?'); $stmt->execute([$profileId]); $assessCount = (int)$stmt->fetchColumn();

// Top matches
$topMatches = array_slice(getMatchedInternships($db, $profileId), 0, 3);

// Recent applications
$stmt = $db->prepare('SELECT a.*, i.title, rp.company_name FROM applications a JOIN internships i ON i.id=a.internship_id JOIN recruiter_profiles rp ON rp.id=i.recruiter_profile_id WHERE a.student_profile_id=? ORDER BY a.applied_at DESC LIMIT 5');
$stmt->execute([$profileId]);
$recentApps = $stmt->fetchAll();

// Upcoming deadlines
$stmt = $db->prepare('SELECT i.id, i.title, i.application_deadline, rp.company_name FROM saved_internships si JOIN internships i ON i.id=si.internship_id JOIN recruiter_profiles rp ON rp.id=i.recruiter_profile_id WHERE si.student_profile_id=? AND (i.application_deadline IS NULL OR i.application_deadline >= ?) ORDER BY i.application_deadline LIMIT 5');
$stmt->execute([$profileId, date('Y-m-d')]);
$deadlines = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="main-content">
<div class="container">
    <div class="dashboard-layout">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <div>
            <div class="page-header">
                <h1>Welcome back, <?php echo e(getCurrentUserName()); ?>! 👋</h1>
                <p>Here's your skill and internship overview</p>
            </div>

            <!-- Profile Completion -->
            <div class="card mb-3">
                <div class="d-flex justify-between align-center mb-1">
                    <h4 class="mb-0">Profile Completion</h4>
                    <span class="text-sm text-muted"><?php echo $completion; ?>%</span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar <?php echo $completion>=80?'green':($completion>=50?'':'amber'); ?>" style="width:<?php echo $completion; ?>%"></div>
                </div>
                <?php if($completion < 100): ?><p class="text-sm text-muted mt-1"><a href="/wtproject/student/profile.php">Complete your profile</a> to improve your match accuracy.</p><?php endif; ?>
            </div>

            <!-- Stats Grid -->
            <div class="card-grid-4 mb-3">
                <div class="stat-card"><div class="stat-icon teal"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg></div><div class="stat-info"><h4>Skills</h4><div class="stat-value"><?php echo $skillCount; ?></div></div></div>
                <div class="stat-card"><div class="stat-icon blue"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg></div><div class="stat-info"><h4>Applications</h4><div class="stat-value"><?php echo $appCount; ?></div></div></div>
                <div class="stat-card"><div class="stat-icon amber"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg></div><div class="stat-info"><h4>Saved</h4><div class="stat-value"><?php echo $savedCount; ?></div></div></div>
                <div class="stat-card"><div class="stat-icon green"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg></div><div class="stat-info"><h4>Assessments</h4><div class="stat-value"><?php echo $assessCount; ?></div></div></div>
            </div>

            <!-- Top Matches -->
            <div class="card mb-3">
                <div class="card-header"><h3>Top Matching Internships</h3><a href="/wtproject/student/internships.php" class="btn btn-sm btn-outline">View All</a></div>
                <?php if(empty($topMatches)): ?>
                <div class="empty-state"><p>Add more skills to see matching internships.</p></div>
                <?php else: ?>
                <?php foreach($topMatches as $m): $level = $m['match_score']>=70?'high':($m['match_score']>=40?'medium':'low'); ?>
                <div class="d-flex justify-between align-center gap-2" style="padding:0.75rem 0;border-bottom:1px solid var(--gray-100);">
                    <div><strong><a href="/wtproject/student/internship-match.php?id=<?php echo (int)$m['id']; ?>"><?php echo e($m['title']); ?></a></strong><br><span class="text-sm text-muted"><?php echo e($m['company_name']); ?> · <?php echo e($m['location']); ?></span></div>
                    <div class="match-score <?php echo $level; ?>" style="width:56px;height:56px;font-size:1rem;"><?php echo $m['match_score']; ?>%</div>
                </div>
                <?php endforeach; endif; ?>
            </div>

            <div class="card-grid-2">
                <!-- Recent Applications -->
                <div class="card">
                    <div class="card-header"><h3>Recent Applications</h3></div>
                    <?php if(empty($recentApps)): ?><p class="text-muted text-sm">No applications yet.</p>
                    <?php else: foreach($recentApps as $a): ?>
                    <div style="padding:0.5rem 0;border-bottom:1px solid var(--gray-100);">
                        <strong class="text-sm"><?php echo e($a['title']); ?></strong><br>
                        <span class="text-xs text-muted"><?php echo e($a['company_name']); ?> · <?php echo formatDate($a['applied_at']); ?></span>
                        <span class="badge <?php echo statusBadgeClass($a['status']); ?>" style="float:right;"><?php echo formatStatus($a['status']); ?></span>
                    </div>
                    <?php endforeach; endif; ?>
                </div>

                <!-- Upcoming Deadlines -->
                <div class="card">
                    <div class="card-header"><h3>Upcoming Deadlines</h3></div>
                    <?php if(empty($deadlines)): ?><p class="text-muted text-sm">No upcoming deadlines.</p>
                    <?php else: foreach($deadlines as $d): $dl = daysRemaining($d['application_deadline']); ?>
                    <div style="padding:0.5rem 0;border-bottom:1px solid var(--gray-100);">
                        <strong class="text-sm"><?php echo e($d['title']); ?></strong><br>
                        <span class="text-xs text-muted"><?php echo e($d['company_name']); ?></span>
                        <span class="text-sm <?php echo $dl<=3?'text-danger':'text-success'; ?>" style="float:right;"><?php echo $dl; ?> days</span>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
