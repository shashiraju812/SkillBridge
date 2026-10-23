<?php
$pageTitle = 'How It Works';
include __DIR__ . '/includes/header.php';
?>
<div class="main-content">
<div class="container" style="max-width:900px;">
    <div class="page-header text-center"><h1>How SkillBridge Works</h1><p>A step-by-step guide for students and recruiters</p></div>

    <h2 class="mb-2">For Students</h2>
    <div class="card-grid-2 mb-4">
        <?php
        $studentSteps = [
            ['1. Create Your Profile', 'Register with your academic details — college, degree, branch, and graduation year.'],
            ['2. Add Your Skills', 'Select technical and soft skills from our catalogue and rate your proficiency (Beginner, Intermediate, Advanced).'],
            ['3. Take Assessments', 'Optionally test your knowledge with skill assessments to verify your proficiency and identify weak topics.'],
            ['4. Add Projects & Certificates', 'Showcase your practical experience with project descriptions, links, and certifications.'],
            ['5. Browse Matched Internships', 'See internships ranked by match score — how closely your skills align with their requirements.'],
            ['6. Understand the Match', 'For each internship, view matched skills (green), missing skills (red), and weak skills (amber) with clear explanations.'],
            ['7. Analyze Skill Gaps', 'Get a detailed gap analysis showing exactly what to learn, with a week-by-week learning roadmap.'],
            ['8. Apply & Track', 'Save interesting internships, apply directly, and track your application status in one place.'],
        ];
        foreach($studentSteps as $s): ?>
        <div class="card"><h4><?php echo $s[0]; ?></h4><p class="text-muted text-sm mb-0"><?php echo $s[1]; ?></p></div>
        <?php endforeach; ?>
    </div>

    <h2 class="mb-2">For Recruiters</h2>
    <div class="card-grid-2 mb-4">
        <?php
        $recruiterSteps = [
            ['1. Register & Add Company', 'Create a recruiter account and fill in your company details.'],
            ['2. Post Internships', 'Create listings with descriptions, eligibility, and required skills with importance levels.'],
            ['3. Review Applications', 'View applicants with their skill-match scores and detailed profile comparisons.'],
            ['4. Manage Candidates', 'Update application statuses, add notes, and manage your internship pipeline.'],
        ];
        foreach($recruiterSteps as $s): ?>
        <div class="card"><h4><?php echo $s[0]; ?></h4><p class="text-muted text-sm mb-0"><?php echo $s[1]; ?></p></div>
        <?php endforeach; ?>
    </div>

    <div class="card mb-3">
        <h3>How Matching Works</h3>
        <p>The matching algorithm compares your skills against each internship's requirements:</p>
        <p><strong>Match Score = Weighted Skills Matched ÷ Total Required Skills × 100</strong></p>
        <ul style="padding-left:1.5rem;">
            <li><strong>Required skills</strong> carry full weight (1.0×)</li>
            <li><strong>Preferred skills</strong> carry 0.6× weight</li>
            <li><strong>Nice-to-have skills</strong> carry 0.3× weight</li>
            <li>Skills you have at a lower proficiency than required get partial credit</li>
        </ul>
        <div class="alert alert-info mt-2" style="font-size:0.85rem;">These are project-defined scores for educational demonstration. A high score does not guarantee selection — it indicates how well your profile aligns with listed requirements.</div>
    </div>
</div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
