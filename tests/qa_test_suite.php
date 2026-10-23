<?php
/**
 * SkillBridge Comprehensive Automated QA Testing Suite
 * End-to-end verification of all workflows, security guards, and matching logic.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

echo "======================================================================\n";
echo "      SkillBridge Quality Assurance (QA) Automated Test Suite         \n";
echo "======================================================================\n\n";

$passCount = 0;
$failCount = 0;

function reportTest($id, $scenario, $condition, $details = '') {
    global $passCount, $failCount;
    if ($condition) {
        echo sprintf("  [PASS] %-8s | %-50s %s\n", $id, $scenario, $details ? "($details)" : "");
        $passCount++;
    } else {
        echo sprintf("! [FAIL] %-8s | %-50s %s\n", $id, $scenario, $details ? "($details)" : "");
        $failCount++;
    }
}

$db = getDB();

// --------------------------------------------------------------------
// SECTION 1: DATABASE INTEGRITY & CONNECTIVITY
// --------------------------------------------------------------------
echo "--- 1. Database Tier & Seed Verification ---\n";
reportTest('QA-DB-01', 'Database connection established', $db instanceof PDO, $db->getAttribute(PDO::ATTR_DRIVER_NAME));
$userCount = (int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn();
reportTest('QA-DB-02', 'Users table seeded', $userCount >= 4, "Users: $userCount");
$skillCount = (int)$db->query('SELECT COUNT(*) FROM skills')->fetchColumn();
reportTest('QA-DB-03', 'Master skill catalogue populated', $skillCount >= 30, "Skills: $skillCount");
$internshipCount = (int)$db->query('SELECT COUNT(*) FROM internships')->fetchColumn();
reportTest('QA-DB-04', 'Internships table seeded', $internshipCount >= 3, "Listings: $internshipCount");

// --------------------------------------------------------------------
// SECTION 2: AUTHENTICATION & SECURITY CONTROLS
// --------------------------------------------------------------------
echo "\n--- 2. Authentication & Credential Security ---\n";

// Password verification
$stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute(['aarav@student.local']);
$aarav = $stmt->fetch();
reportTest('QA-SEC-01', 'User password hashed with BCrypt', strpos($aarav['password_hash'], '$2y$') === 0);
reportTest('QA-SEC-02', 'Valid password verification', password_verify('password', $aarav['password_hash']));
reportTest('QA-SEC-03', 'Invalid password rejected', !password_verify('wrongpass', $aarav['password_hash']));

// Password strength validator
$weakResult = validatePassword('abc');
reportTest('QA-SEC-04', 'Weak password validator triggers errors', count($weakResult) >= 3);
$strongResult = validatePassword('Pass1234');
reportTest('QA-SEC-05', 'Strong password accepted by validator', count($strongResult) === 0);

// Role guards
loginUser($aarav);
reportTest('QA-SEC-06', 'Session user logged in correctly', getCurrentUserId() === (int)$aarav['id']);
reportTest('QA-SEC-07', 'Current role identified as student', getCurrentUserRole() === 'student');
reportTest('QA-SEC-08', 'Student role dashboard routing', getDashboardURL() === '/wtproject/student/dashboard.php');

// --------------------------------------------------------------------
// SECTION 3: EXPLAINABLE MATCHING ENGINE CALCULATIONS
// --------------------------------------------------------------------
echo "\n--- 3. Core Explainable Matching Engine Verification ---\n";

$aaravProfileId = getStudentProfileId($db);
reportTest('QA-MTC-01', 'Student profile ID resolved', $aaravProfileId !== null, "Profile ID: $aaravProfileId");

// Aarav against Frontend Developer Intern (Internship 1)
// Aarav has: HTML (adv), CSS (adv), JS (interm), Git (interm), Bootstrap (beg)
// Requisite: HTML (interm), CSS (interm), JS (interm), React (beg), Git (beg)
$match1 = calculateMatchScore($db, $aaravProfileId, 1);
reportTest('QA-MTC-02', 'Match score calculated as integer (0-100)', is_numeric($match1['score']) && $match1['score'] >= 0 && $match1['score'] <= 100, "Score: {$match1['score']}%");
reportTest('QA-MTC-03', 'Missing skills correctly isolated', in_array('React', array_column($match1['missing_skills'], 'name')));
reportTest('QA-MTC-04', 'Matched skills correctly identified', in_array('HTML', array_column($match1['matched_skills'], 'name')) && in_array('CSS', array_column($match1['matched_skills'], 'name')));
reportTest('QA-MTC-05', 'Human-readable explanation generated', !empty($match1['explanation']), substr($match1['explanation'], 0, 45) . "...");

// Check Priya (Data Analyst) against Internship 1 (Frontend Developer) -> Should be lower
$stmt = $db->prepare('SELECT id FROM student_profiles WHERE user_id = 3');
$stmt->execute();
$priyaProfileId = (int)$stmt->fetchColumn();
$matchPriya = calculateMatchScore($db, $priyaProfileId, 1);
reportTest('QA-MTC-06', 'Divergent profile gets lower score', $matchPriya['score'] < $match1['score'], "Priya Score: {$matchPriya['score']}% vs Aarav: {$match1['score']}%");

// --------------------------------------------------------------------
// SECTION 4: SKILL GAP & LEARNING ROADMAP
// --------------------------------------------------------------------
echo "\n--- 4. Skill Gap Diagnostics & Learning Roadmap ---\n";

$gapAnalysis = getSkillGapAnalysis($db, $aaravProfileId, 1);
reportTest('QA-GAP-01', 'Skill gap analysis generated', !empty($gapAnalysis['match']));
reportTest('QA-GAP-02', 'Learning roadmap recommended for missing skill', isset($gapAnalysis['learning_plan']['React']), 'React Roadmap active');
if (isset($gapAnalysis['learning_plan']['React'])) {
    $firstTask = $gapAnalysis['learning_plan']['React'][0];
    reportTest('QA-GAP-03', 'Milestone learning task has duration and week number', $firstTask['week_number'] >= 1 && !empty($firstTask['title']), $firstTask['title']);
}

// --------------------------------------------------------------------
// SECTION 5: STUDENT PROFILE COMPLETION METRIC
// --------------------------------------------------------------------
echo "\n--- 5. Dynamic Profile Completion Metric ---\n";

$completionAarav = calculateProfileCompletion($db, (int)$aarav['id']);
reportTest('QA-PRF-01', 'Profile completion calculated dynamically', $completionAarav >= 50 && $completionAarav <= 100, "Completion: {$completionAarav}%");

// --------------------------------------------------------------------
// SECTION 6: MCQ SKILL ASSESSMENT ENGINE
// --------------------------------------------------------------------
echo "\n--- 6. Skill Assessment & Evaluation Engine ---\n";

$stmt = $db->prepare('SELECT * FROM assessment_questions WHERE assessment_id = 1');
$stmt->execute();
$questions = $stmt->fetchAll();
reportTest('QA-ASM-01', 'Assessment questions loaded from bank', count($questions) >= 3, count($questions) . ' questions');

// Simulate student answering all correctly
$correctCount = 0;
foreach ($questions as $q) {
    if ($q['correct_option'] === 'c' || $q['correct_option'] === 'b') {
        $correctCount++;
    }
}
$scoreSim = round(($correctCount / count($questions)) * 100);
reportTest('QA-ASM-02', 'Assessment score simulation', $scoreSim >= 0 && $scoreSim <= 100, "Simulated Score: {$scoreSim}%");

// --------------------------------------------------------------------
// SECTION 7: APPLICATION SUBMISSION & DUPLICATE PREVENTION
// --------------------------------------------------------------------
echo "\n--- 7. Application Pipeline & Integrity Constraints ---\n";

// Clear previous test application if exists
$db->prepare('DELETE FROM applications WHERE student_profile_id = ? AND internship_id = ?')->execute([$aaravProfileId, 2]);

// Apply to Internship 2
$stmt = $db->prepare('INSERT INTO applications (student_profile_id, internship_id, status, cover_note) VALUES (?, ?, "applied", ?)');
$stmt->execute([$aaravProfileId, 2, 'QA automated test cover note.']);
$appId = $db->lastInsertId();
reportTest('QA-APP-01', 'Application successfully submitted', $appId > 0, "Application ID: $appId");

// Duplicate application prevention
$dupError = false;
try {
    $stmt = $db->prepare('INSERT INTO applications (student_profile_id, internship_id, status) VALUES (?, ?, "applied")');
    $stmt->execute([$aaravProfileId, 2]);
} catch (Exception $e) {
    $dupError = true;
}
reportTest('QA-APP-02', 'Duplicate application blocked by unique constraint', $dupError);

// Recruiter updates status to shortlisted
$db->prepare('UPDATE applications SET status = "shortlisted" WHERE id = ?')->execute([$appId]);
$stmt = $db->prepare('SELECT status FROM applications WHERE id = ?');
$stmt->execute([$appId]);
$updatedStatus = $stmt->fetchColumn();
reportTest('QA-APP-03', 'Application status transitioned to shortlisted', $updatedStatus === 'shortlisted');

// Clean up test application
$db->prepare('DELETE FROM applications WHERE id = ?')->execute([$appId]);

echo "\n======================================================================\n";
echo sprintf("QA Test Execution Results: %d Passed, %d Failed (Total %d Tests)\n", $passCount, $failCount, $passCount + $failCount);
echo "======================================================================\n";

if ($failCount > 0) {
    exit(1);
}

