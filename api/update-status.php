<?php
/**
 * API: Update Application Status — SkillBridge
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || getCurrentUserRole() !== 'recruiter') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Recruiter access required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!validateCSRFToken()) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$db = getDB();
$rpId = getRecruiterProfileId($db);
$appId = (int)($_POST['application_id'] ?? 0);
$newStatus = cleanInput($_POST['status'] ?? '');

$allowed = ['applied', 'under_review', 'shortlisted', 'interview_scheduled', 'selected', 'rejected'];
if (!in_array($newStatus, $allowed, true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status option']);
    exit;
}

// Verify recruiter ownership of this application
$stmt = $db->prepare('SELECT a.id FROM applications a JOIN internships i ON i.id = a.internship_id WHERE a.id = ? AND i.recruiter_profile_id = ?');
$stmt->execute([$appId, $rpId]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Application not found or unauthorized']);
    exit;
}

$update = $db->prepare('UPDATE applications SET status = ? WHERE id = ?');
$update->execute([$newStatus, $appId]);

echo json_encode(['success' => true, 'message' => 'Status updated successfully', 'status' => $newStatus]);

