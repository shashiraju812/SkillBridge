<?php
/**
 * API / Form Handler: Submit Application — SkillBridge
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!isLoggedIn() || getCurrentUserRole() !== 'student') {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    setFlash('error', 'Please log in as a student to apply.');
    header('Location: /wtproject/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }
    header('Location: /wtproject/student/internships.php');
    exit;
}

requireValidCSRF();

$db = getDB();
$studentProfileId = getStudentProfileId($db);
$internshipId = (int)($_POST['internship_id'] ?? 0);
$coverNote = cleanInput($_POST['cover_note'] ?? '');

if (!$studentProfileId || !$internshipId) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit;
    }
    setFlash('error', 'Invalid internship selected.');
    header('Location: /wtproject/student/internships.php');
    exit;
}

// Verify internship is active and deadline not passed
$stmt = $db->prepare('SELECT status, application_deadline FROM internships WHERE id = ?');
$stmt->execute([$internshipId]);
$internship = $stmt->fetch();

if (!$internship || $internship['status'] !== 'active') {
    $msg = 'This internship is not accepting applications.';
    if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => $msg]); exit; }
    setFlash('error', $msg);
    header("Location: /wtproject/internship-details.php?id=$internshipId");
    exit;
}

if ($internship['application_deadline'] && strtotime($internship['application_deadline']) < time()) {
    $msg = 'The deadline for this internship has passed.';
    if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => $msg]); exit; }
    setFlash('error', $msg);
    header("Location: /wtproject/internship-details.php?id=$internshipId");
    exit;
}

// Check if already applied
$check = $db->prepare('SELECT id FROM applications WHERE student_profile_id = ? AND internship_id = ?');
$check->execute([$studentProfileId, $internshipId]);
if ($check->fetch()) {
    $msg = 'You have already applied for this internship.';
    if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => $msg]); exit; }
    setFlash('info', $msg);
    header("Location: /wtproject/student/internship-match.php?id=$internshipId");
    exit;
}

// Insert application
$insert = $db->prepare('INSERT INTO applications (student_profile_id, internship_id, status, cover_note) VALUES (?, ?, "applied", ?)');
$insert->execute([$studentProfileId, $internshipId, $coverNote]);

if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Application submitted successfully!']);
    exit;
}

setFlash('success', 'Your application has been submitted successfully!');
header("Location: /wtproject/student/applications.php");
exit;

