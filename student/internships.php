<?php
/**
 * Student Internship Explorer (with match scores) — SkillBridge
 */
$pageTitle='Explore Internships';$currentPage='internships.php';
require_once __DIR__.'/../config/database.php';require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/csrf.php';require_once __DIR__.'/../includes/functions.php';
requireRole('student');$db=getDB();$profileId=getStudentProfileId($db);

$filters=['search'=>cleanInput($_GET['search']??''),'location'=>cleanInput($_GET['location']??''),'work_mode'=>cleanInput($_GET['work_mode']??''),'career_category'=>cleanInput($_GET['career_category']??''),'skill'=>cleanInput($_GET['skill']??'')];
$internships=getMatchedInternships($db,$profileId,$filters);
$categories=$db->query("SELECT DISTINCT career_category FROM internships WHERE career_category IS NOT NULL AND career_category!='' ORDER BY career_category")->fetchAll(PDO::FETCH_COLUMN);

include __DIR__.'/../includes/header.php';
?>
<div class="main-content"><div class="container"><div class="dashboard-layout"><?php include __DIR__.'/sidebar.php'; ?>
<div>
<div class="page-header"><h1>Internships For You</h1><p><?php echo count($internships); ?> opportunities sorted by match score</p></div>

<form class="filters-bar" method="GET">
    <div class="form-group"><label>Search</label><input type="text" name="search" class="form-control" value="<?php echo e($filters['search']); ?>" placeholder="Title or company"></div>
    <div class="form-group"><label>Skill</label><input type="text" name="skill" class="form-control" value="<?php echo e($filters['skill']); ?>" placeholder="e.g. React"></div>
    <div class="form-group"><label>Location</label><input type="text" name="location" class="form-control" value="<?php echo e($filters['location']); ?>"></div>
    <div class="form-group"><label>Mode</label><select name="work_mode" class="form-control"><option value="">All</option><?php foreach(['remote','hybrid','onsite'] as $m): ?><option value="<?php echo $m; ?>" <?php echo $filters['work_mode']===$m?'selected':''; ?>><?php echo ucfirst($m); ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label>Category</label><select name="career_category" class="form-control"><option value="">All</option><?php foreach($categories as $c): ?><option value="<?php echo e($c); ?>" <?php echo $filters['career_category']===$c?'selected':''; ?>><?php echo e($c); ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><button type="submit" class="btn btn-primary">Filter</button></div>
</form>

<?php if(empty($internships)): ?><div class="empty-state"><h3>No matching internships</h3><p>Try different filters or add more skills to your profile.</p></div>
<?php else: ?>
<div class="card-grid-2">
<?php foreach($internships as $m): $level=$m['match_score']>=70?'high':($m['match_score']>=40?'medium':'low'); $days=daysRemaining($m['application_deadline']); ?>
<div class="internship-card">
    <div class="internship-card-top">
        <div class="internship-card-info"><h3><a href="/wtproject/student/internship-match.php?id=<?php echo (int)$m['id']; ?>"><?php echo e($m['title']); ?></a></h3><span class="company"><?php echo e($m['company_name']); ?></span></div>
        <div class="match-score <?php echo $level; ?>"><?php echo $m['match_score']; ?>%</div>
    </div>
    <div class="internship-card-meta">
        <span>📍 <?php echo e($m['location']?:'Remote'); ?></span><span>💼 <?php echo ucfirst(e($m['work_mode'])); ?></span>
        <span>💰 <?php echo formatStipend($m['stipend_min'],$m['stipend_max'],$m['is_paid']); ?></span>
        <?php if($days!==null): ?><span><?php echo $days>0?"⏰ {$days}d":"❌ Expired"; ?></span><?php endif; ?>
    </div>
    <div class="internship-card-skills">
        <?php foreach($m['matched_skills'] as $s): ?><span class="skill-tag"><?php echo e($s['name']); ?></span><?php endforeach; ?>
        <?php foreach($m['missing_skills'] as $s): ?><span class="skill-tag missing"><?php echo e($s['name']); ?></span><?php endforeach; ?>
        <?php foreach($m['weak_skills'] as $s): ?><span class="skill-tag weak"><?php echo e($s['name']); ?></span><?php endforeach; ?>
    </div>
    <div class="internship-card-actions">
        <a href="/wtproject/student/internship-match.php?id=<?php echo (int)$m['id']; ?>" class="btn btn-sm btn-outline">View Match Details</a>
        <button class="btn btn-sm btn-outline" data-save-id="<?php echo (int)$m['id']; ?>" onclick="toggleSaveInternship(<?php echo (int)$m['id']; ?>,'<?php echo e(generateCSRFToken()); ?>')">♡ Save</button>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div></div></div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>

