<?php
/**
 * Home Page — SkillBridge
 */
$pageTitle = 'Home';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/sample-data.php';

$db = getDBOrNull();
if ($db !== null) {
    $studentCount = (int)$db->query('SELECT COUNT(*) FROM users WHERE role="student"')->fetchColumn();
    $internshipCount = (int)$db->query('SELECT COUNT(*) FROM internships WHERE status="active"')->fetchColumn();
    $skillCount = (int)$db->query('SELECT COUNT(*) FROM skills')->fetchColumn();
} else {
    // Stage 2 Frontend fallback with educational sample data
    $stats = getSampleStats();
    $studentCount = $stats['student_count'];
    $internshipCount = $stats['internship_count'];
    $skillCount = $stats['skills_count'];
}

include __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero">
    <div class="container">
        <h1>Bridge the Gap Between Your Skills and Your Dream Internship</h1>
        <p>SkillBridge matches students with internships based on their actual skills, shows exactly what's missing, and provides a roadmap to get there.</p>
        <div>
            <a href="/wtproject/register.php" class="btn btn-lg" style="background:#fff;color:var(--primary);">Get Started Free</a>
            <a href="/wtproject/explore.php" class="btn btn-lg btn-outline" style="border-color:#fff;color:#fff;">Explore Internships</a>
        </div>
    </div>
</section>

<!-- Stats -->
<section style="background:var(--white);padding:2rem 0;border-bottom:1px solid var(--gray-200);">
    <div class="container">
        <div class="card-grid-3" style="text-align:center;">
            <div><h3 style="font-size:2rem;color:var(--primary);margin-bottom:0;"><?php echo $studentCount; ?></h3><p class="text-muted mb-0">Registered Students</p></div>
            <div><h3 style="font-size:2rem;color:var(--primary);margin-bottom:0;"><?php echo $internshipCount; ?></h3><p class="text-muted mb-0">Active Internships</p></div>
            <div><h3 style="font-size:2rem;color:var(--primary);margin-bottom:0;"><?php echo $skillCount; ?></h3><p class="text-muted mb-0">Skills Tracked</p></div>
        </div>
    </div>
</section>

<!-- Features -->
<section class="features">
    <div class="container">
        <h2>Why SkillBridge?</h2>
        <p class="subtitle">Everything you need to go from "I don't know where to start" to "I'm ready to apply."</p>
        <div class="card-grid-3">
            <div class="feature-card card">
                <div class="feature-icon" style="background:var(--primary-light);color:var(--primary);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </div>
                <h4>Skill Profiling</h4>
                <p class="text-muted">Build a comprehensive profile with your skills, projects, certificates, and assessments.</p>
            </div>
            <div class="feature-card card">
                <div class="feature-icon" style="background:#dbeafe;color:var(--info);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
                </div>
                <h4>Smart Matching</h4>
                <p class="text-muted">Get a transparent match score showing exactly how your skills align with each internship.</p>
            </div>
            <div class="feature-card card">
                <div class="feature-icon" style="background:#fef3c7;color:var(--warning);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </div>
                <h4>Gap Analysis</h4>
                <p class="text-muted">See exactly which skills you're missing and which need improvement for each opportunity.</p>
            </div>
            <div class="feature-card card">
                <div class="feature-icon" style="background:#d1fae5;color:var(--success);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                </div>
                <h4>Learning Roadmap</h4>
                <p class="text-muted">Follow a week-by-week plan to build the missing skills and track your progress.</p>
            </div>
            <div class="feature-card card">
                <div class="feature-icon" style="background:#ede9fe;color:var(--purple);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                </div>
                <h4>Skill Assessments</h4>
                <p class="text-muted">Test your knowledge with assessments and identify topic-level strengths and weaknesses.</p>
            </div>
            <div class="feature-card card">
                <div class="feature-icon" style="background:#fee2e2;color:var(--danger);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                </div>
                <h4>Application Tracker</h4>
                <p class="text-muted">Save, apply, and track all your internship applications from one dashboard.</p>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section style="background:var(--white);padding:4rem 0;">
    <div class="container">
        <h2 class="text-center">How It Works</h2>
        <p class="subtitle text-center">Four simple steps to your next internship</p>
        <div class="card-grid-4" style="margin-top:2rem;">
            <?php 
            $steps = [
                ['1','Create Profile','Sign up, add your education, and build your student profile.','var(--primary)'],
                ['2','Add Skills','Select your technical and soft skills with proficiency levels.','var(--info)'],
                ['3','Get Matched','See internships ranked by how well your skills match their requirements.','var(--success)'],
                ['4','Apply & Grow','Apply directly, track status, and follow learning roadmaps for missing skills.','var(--purple)']
            ];
            foreach($steps as $s): ?>
            <div class="card text-center">
                <div style="width:48px;height:48px;border-radius:50%;background:<?php echo $s[3]; ?>;color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.25rem;font-weight:700;margin:0 auto 1rem;"><?php echo $s[0]; ?></div>
                <h4><?php echo $s[1]; ?></h4>
                <p class="text-muted text-sm"><?php echo $s[2]; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section style="background:var(--primary);color:#fff;padding:4rem 0;text-align:center;">
    <div class="container">
        <h2 style="color:#fff;">Ready to Find Your Perfect Internship Match?</h2>
        <p style="opacity:0.9;max-width:500px;margin:0 auto 2rem;">Join SkillBridge today and start your journey from skills to career.</p>
        <a href="/wtproject/register.php" class="btn btn-lg" style="background:#fff;color:var(--primary);">Create Free Account</a>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
