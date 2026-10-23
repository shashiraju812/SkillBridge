<?php
/**
 * Internship Details — SkillBridge (Public)
 */
$pageTitle = 'Internship Details';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/sample-data.php';

$db = getDBOrNull();
$id = (int)($_GET['id'] ?? 1); // Defaults to sample 1 if not passed
if (!$id) { header('Location: /wtproject/explore.php'); exit; }

$int = null;
$reqSkills = [];
$matchData = null; 
$eligibility = null; 
$isSaved = false; 
$hasApplied = false;
$studentProfileId = null;

if ($db !== null) {
    $stmt = $db->prepare('SELECT i.*, rp.company_name, rp.company_website, rp.company_description, rp.industry FROM internships i JOIN recruiter_profiles rp ON rp.id = i.recruiter_profile_id WHERE i.id = ?');
    $stmt->execute([$id]);
    $int = $stmt->fetch();
    if (!$int) { http_response_code(404); die('Internship not found.'); }

    // Required skills
    $stmt = $db->prepare('SELECT isr.*, s.name AS skill_name FROM internship_skills isr JOIN skills s ON s.id=isr.skill_id WHERE isr.internship_id=? ORDER BY CASE isr.importance WHEN \'required\' THEN 1 WHEN \'preferred\' THEN 2 WHEN \'nice_to_have\' THEN 3 ELSE 4 END');
    $stmt->execute([$id]);
    $reqSkills = $stmt->fetchAll();

    if (isLoggedIn() && getCurrentUserRole() === 'student') {
        $studentProfileId = getStudentProfileId($db);
        if ($studentProfileId) {
            $matchData = calculateMatchScore($db, $studentProfileId, $id);
            $eligibility = checkEligibility($db, $studentProfileId, $id);
            $s = $db->prepare('SELECT id FROM saved_internships WHERE student_profile_id=? AND internship_id=?');
            $s->execute([$studentProfileId, $id]);
            $isSaved = (bool)$s->fetch();
            $s = $db->prepare('SELECT id, status FROM applications WHERE student_profile_id=? AND internship_id=?');
            $s->execute([$studentProfileId, $id]);
            $application = $s->fetch();
            $hasApplied = (bool)$application;
        }
    }
} else {
    // Stage 2: Load from sample data
    $int = getSampleInternshipById($id);
    if (!$int) {
        $int = getSampleInternshipById(1); // Fallback to first sample
    }
    foreach ($int['required_skills'] as $r) {
        $reqSkills[] = [
            'skill_name' => $r['name'],
            'importance' => $r['importance'],
            'min_proficiency' => $r['min_proficiency']
        ];
    }
    $int['company_description'] = "Sample organization created for B.Tech Web Technologies Lab demonstration.";
}

$pageTitle = $int['title'];
$days = daysRemaining($int['application_deadline']);

include __DIR__ . '/includes/header.php';
?>
<div class="main-content">
<div class="container" style="max-width:960px;">
    <a href="/wtproject/explore.php" class="text-muted text-sm">&larr; Back to Internships</a>

    <?php if ($int['is_sample']): ?>
    <div class="alert alert-warning mt-2" style="font-size:0.85rem;">This is <strong>sample data</strong> for demonstration purposes. This is not a real internship listing.</div>
    <?php endif; ?>

    <div class="card mt-2">
        <div class="d-flex justify-between flex-wrap gap-2" style="align-items:flex-start;">
            <div>
                <h1 style="margin-bottom:0.25rem;"><?php echo e($int['title']); ?></h1>
                <p class="text-muted mb-1"><?php echo e($int['company_name']); ?> <?php if($int['company_website']): ?>· <a href="<?php echo e($int['company_website']); ?>" target="_blank">Website</a><?php endif; ?></p>
                <div class="d-flex flex-wrap gap-1 text-sm">
                    <span class="badge badge-primary"><?php echo ucfirst(e($int['work_mode'])); ?></span>
                    <span class="badge badge-info"><?php echo e($int['location'] ?: 'Not specified'); ?></span>
                    <?php if($int['career_category']): ?><span class="badge badge-secondary"><?php echo e($int['career_category']); ?></span><?php endif; ?>
                    <?php if($days !== null && $days <= 0): ?><span class="badge badge-danger">Deadline Passed</span><?php endif; ?>
                </div>
            </div>
            <?php if ($matchData): $level = $matchData['score']>=70?'high':($matchData['score']>=40?'medium':'low'); ?>
            <div class="match-score <?php echo $level; ?>"><?php echo $matchData['score']; ?>%</div>
            <?php endif; ?>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem;margin-top:1.5rem;" class="dashboard-layout">
        <div>
            <div class="card mb-3"><h3>Description</h3><p style="white-space:pre-line;"><?php echo e($int['description']); ?></p></div>
            <?php if($int['responsibilities']): ?><div class="card mb-3"><h3>Responsibilities</h3><p style="white-space:pre-line;"><?php echo e($int['responsibilities']); ?></p></div><?php endif; ?>
            <?php if($int['eligibility']): ?><div class="card mb-3"><h3>Eligibility</h3><p style="white-space:pre-line;"><?php echo e($int['eligibility']); ?></p></div><?php endif; ?>

            <div class="card mb-3">
                <h3>Required Skills</h3>
                <?php if(empty($reqSkills)): ?><p class="text-muted">No specific skills listed.</p>
                <?php else: ?>
                <table><thead><tr><th>Skill</th><th>Importance</th><th>Min Level</th><?php if($matchData): ?><th>Your Status</th><?php endif; ?></tr></thead><tbody>
                <?php foreach($reqSkills as $rs):
                    $status = '';
                    $statusClass = '';
                    if ($matchData) {
                        $matched = array_filter($matchData['matched_skills'], fn($s) => $s['name']===$rs['skill_name']);
                        $missing = array_filter($matchData['missing_skills'], fn($s) => $s['name']===$rs['skill_name']);
                        $weak = array_filter($matchData['weak_skills'], fn($s) => $s['name']===$rs['skill_name']);
                        if (!empty($matched)) { $status = '✅ Matched'; $statusClass = 'text-success'; }
                        elseif (!empty($weak)) { $status = '⚠️ Needs Improvement'; $statusClass = 'text-danger'; }
                        elseif (!empty($missing)) { $status = '❌ Missing'; $statusClass = 'text-danger'; }
                    }
                ?>
                <tr><td><?php echo e($rs['skill_name']); ?></td>
                    <td><span class="badge badge-<?php echo $rs['importance']==='required'?'danger':($rs['importance']==='preferred'?'warning':'secondary'); ?>"><?php echo ucfirst(e($rs['importance'])); ?></span></td>
                    <td><?php echo ucfirst(e($rs['min_proficiency'])); ?></td>
                    <?php if($matchData): ?><td class="<?php echo $statusClass; ?>"><?php echo $status; ?></td><?php endif; ?>
                </tr>
                <?php endforeach; ?></tbody></table>
                <?php endif; ?>
            </div>

            <?php if($matchData): ?>
            <div class="card mb-3">
                <h3>Match Explanation</h3>
                <p><?php echo e($matchData['explanation']); ?></p>
                <?php if($studentProfileId): ?><a href="/wtproject/student/skill-gap.php?internship_id=<?php echo $id; ?>" class="btn btn-outline btn-sm">View Detailed Skill Gap Analysis →</a><?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div>
            <div class="card mb-3">
                <h4 class="mb-1">Details</h4>
                <table style="font-size:0.875rem;"><tbody>
                <tr><td class="text-muted" style="padding-right:1rem;">Stipend</td><td><strong><?php echo formatStipend($int['stipend_min'],$int['stipend_max'],$int['is_paid']); ?></strong></td></tr>
                <tr><td class="text-muted">Duration</td><td><?php echo e($int['duration'] ?: 'Not specified'); ?></td></tr>
                <tr><td class="text-muted">Openings</td><td><?php echo (int)$int['openings']; ?></td></tr>
                <tr><td class="text-muted">Deadline</td><td><?php echo formatDate($int['application_deadline']); ?></td></tr>
                <?php if($days !== null): ?><tr><td class="text-muted">Days Left</td><td><strong class="<?php echo $days<=0?'text-danger':($days<=7?'text-danger':'text-success'); ?>"><?php echo $days>0?$days.' days':'Expired'; ?></strong></td></tr><?php endif; ?>
                <?php if($int['eligible_degrees']): ?><tr><td class="text-muted">Degrees</td><td><?php echo e($int['eligible_degrees']); ?></td></tr><?php endif; ?>
                <?php if($int['eligible_branches']): ?><tr><td class="text-muted">Branches</td><td><?php echo e($int['eligible_branches']); ?></td></tr><?php endif; ?>
                </tbody></table>
            </div>

            <?php if ($eligibility): ?>
            <div class="card mb-3">
                <h4 class="mb-1">Eligibility Check</h4>
                <?php foreach($eligibility['reasons'] as $r): ?>
                <p class="text-sm <?php echo $eligibility['eligible']?'text-success':'text-danger'; ?>"><?php echo e($r); ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($studentProfileId): ?>
            <div class="card mb-3">
                <?php if ($hasApplied): ?>
                <p class="text-sm">Status: <span class="badge <?php echo statusBadgeClass($application['status']); ?>"><?php echo formatStatus($application['status']); ?></span></p>
                <?php elseif($days !== null && $days <= 0): ?>
                <p class="text-muted text-sm">Application deadline has passed.</p>
                <?php else: ?>
                <form method="POST" action="/wtproject/api/apply.php" style="margin-bottom:0.75rem;">
                    <input type="hidden" name="csrf_token" value="<?php echo e(generateCSRFToken()); ?>">
                    <input type="hidden" name="internship_id" value="<?php echo $id; ?>">
                    <textarea name="cover_note" class="form-control mb-1" rows="3" placeholder="Cover note (optional)"></textarea>
                    <button type="submit" class="btn btn-primary btn-block">Apply Now</button>
                </form>
                <?php endif; ?>
                <button class="btn btn-outline btn-block btn-sm" data-save-id="<?php echo $id; ?>" onclick="toggleSaveInternship(<?php echo $id; ?>, '<?php echo e(generateCSRFToken()); ?>')"><?php echo $isSaved ? '♥ Saved' : '♡ Save'; ?></button>
            </div>
            <?php elseif(!isLoggedIn()): ?>
            <div class="card mb-3"><p class="text-center text-muted text-sm">
                <a href="/wtproject/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">Log in</a> to see your match score and apply.
            </p></div>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
