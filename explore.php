<?php
/**
 * Public Internship Explorer — SkillBridge
 */
$pageTitle = 'Explore Internships';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/sample-data.php';

$db = getDBOrNull();

// Filters
$search   = cleanInput($_GET['search'] ?? '');
$location = cleanInput($_GET['location'] ?? '');
$mode     = cleanInput($_GET['work_mode'] ?? '');
$category = cleanInput($_GET['career_category'] ?? '');

$internships = [];
$categories = [];
$studentProfileId = null;

if ($db !== null) {
    $where = ['i.status = ?'];
    $params = ['active'];
    if ($search)   { $where[] = '(i.title LIKE ? OR rp.company_name LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
    if ($location) { $where[] = 'i.location LIKE ?'; $params[] = "%$location%"; }
    if ($mode)     { $where[] = 'i.work_mode = ?'; $params[] = $mode; }
    if ($category) { $where[] = 'i.career_category = ?'; $params[] = $category; }
    $whereSQL = implode(' AND ', $where);

    $stmt = $db->prepare("SELECT i.*, rp.company_name FROM internships i JOIN recruiter_profiles rp ON rp.id = i.recruiter_profile_id WHERE $whereSQL ORDER BY i.created_at DESC");
    $stmt->execute($params);
    $internships = $stmt->fetchAll();

    if (isLoggedIn() && getCurrentUserRole() === 'student') {
        $studentProfileId = getStudentProfileId($db);
    }
    $categories = $db->query("SELECT DISTINCT career_category FROM internships WHERE career_category IS NOT NULL AND career_category != '' ORDER BY career_category")->fetchAll(PDO::FETCH_COLUMN);
} else {
    // Stage 2: In-memory sample data filtering
    $allSample = getSampleInternships();
    foreach ($allSample as $item) {
        if ($search && stripos($item['title'], $search) === false && stripos($item['company_name'], $search) === false) continue;
        if ($location && stripos($item['location'], $location) === false) continue;
        if ($mode && strtolower($item['work_mode']) !== strtolower($mode)) continue;
        if ($category && strtolower($item['career_category']) !== strtolower($category)) continue;

        // Flatten required skills for card presentation
        $item['skills_list'] = array_column($item['required_skills'], 'name');
        $internships[] = $item;
    }
    $categories = array_unique(array_column($allSample, 'career_category'));
}

include __DIR__ . '/includes/header.php';
?>
<div class="main-content">
<div class="container">
    <div class="page-header">
        <h1>Explore Internships</h1>
        <p>Browse educational sample internship listings with requirement breakdowns</p>
    </div>

    <!-- Educational Disclaimer Banner -->
    <div class="alert alert-info" style="margin-bottom: 1.5rem;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
        <div>
            <strong>Educational Sample Data Notice:</strong>
            The opportunities listed below are sample data designed to demonstrate skill-matching, requirement filtering, and gap analysis for B.Tech Web Technologies Lab. They are not active employment vacancies.
        </div>
    </div>

    <!-- Filters -->
    <form class="filters-bar" method="GET">
        <div class="form-group"><label>Search</label><input type="text" name="search" class="form-control" value="<?php echo e($search); ?>" placeholder="Title or company"></div>
        <div class="form-group"><label>Location</label><input type="text" name="location" class="form-control" value="<?php echo e($location); ?>" placeholder="City"></div>
        <div class="form-group"><label>Work Mode</label>
            <select name="work_mode" class="form-control"><option value="">All</option>
            <?php foreach(['remote','hybrid','onsite'] as $m): ?><option value="<?php echo $m; ?>" <?php echo $mode===$m?'selected':''; ?>><?php echo ucfirst($m); ?></option><?php endforeach; ?></select>
        </div>
        <div class="form-group"><label>Category</label>
            <select name="career_category" class="form-control"><option value="">All</option>
            <?php foreach($categories as $c): ?><option value="<?php echo e($c); ?>" <?php echo $category===$c?'selected':''; ?>><?php echo e($c); ?></option><?php endforeach; ?></select>
        </div>
        <div class="form-group"><button type="submit" class="btn btn-primary">Filter</button></div>
    </form>

    <?php if (empty($internships)): ?>
    <div class="empty-state"><h3>No internships found</h3><p>Try adjusting your filters</p></div>
    <?php else: ?>
    <div class="card-grid-2">
        <?php foreach($internships as $int):
            if ($db !== null) {
                $skills = $db->prepare('SELECT s.name FROM internship_skills isr JOIN skills s ON s.id=isr.skill_id WHERE isr.internship_id=?');
                $skills->execute([$int['id']]);
                $skillNames = $skills->fetchAll(PDO::FETCH_COLUMN);
            } else {
                $skillNames = $int['skills_list'] ?? [];
            }
            $matchScore = null;
            $matchData = null;
            if ($studentProfileId && $db !== null) {
                $matchData = calculateMatchScore($db, $studentProfileId, (int)$int['id']);
                $matchScore = $matchData['score'];
            }
            $days = daysRemaining($int['application_deadline']);
        ?>
        <div class="internship-card">
            <div class="internship-card-top">
                <div class="internship-card-info">
                    <h3><a href="/wtproject/internship-details.php?id=<?php echo (int)$int['id']; ?>"><?php echo e($int['title']); ?></a></h3>
                    <span class="company"><?php echo e($int['company_name']); ?></span>
                </div>
                <?php if ($matchScore !== null):
                    $level = $matchScore >= 70 ? 'high' : ($matchScore >= 40 ? 'medium' : 'low');
                ?>
                <div class="match-score <?php echo $level; ?>"><?php echo $matchScore; ?>%</div>
                <?php endif; ?>
            </div>
            <div class="internship-card-meta">
                <span>📍 <?php echo e($int['location'] ?: 'Not specified'); ?></span>
                <span>💼 <?php echo ucfirst(e($int['work_mode'])); ?></span>
                <span>💰 <?php echo formatStipend($int['stipend_min'], $int['stipend_max'], $int['is_paid']); ?></span>
                <?php if ($days !== null): ?>
                <span><?php echo $days > 0 ? "⏰ {$days} days left" : '❌ Deadline passed'; ?></span>
                <?php endif; ?>
            </div>
            <div class="internship-card-skills">
                <?php foreach($skillNames as $sn): ?>
                <span class="skill-tag <?php echo ($matchData && in_array($sn, array_column($matchData['missing_skills'],'name'))) ? 'missing' : ''; ?>"><?php echo e($sn); ?></span>
                <?php endforeach; ?>
            </div>
            <div class="internship-card-actions">
                <a href="/wtproject/internship-details.php?id=<?php echo (int)$int['id']; ?>" class="btn btn-sm btn-outline">View Details</a>
                <?php if ($int['is_sample']): ?><span class="badge badge-secondary">Sample Data</span><?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
