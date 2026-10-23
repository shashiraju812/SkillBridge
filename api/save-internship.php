<?php
/**
 * API: Toggle Save Internship — SkillBridge
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

if (!isLoggedIn() || getCurrentUserRole() !== 'student') {
    echo json_encode(['error' => 'Unauthorized: Please log in as a student']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!validateCSRFToken()) {
    echo json_encode(['error' => 'Invalid security token']);
    exit;
}

$db = getDB();
$studentProfileId = getStudentProfileId($db);
$internshipId = (int)($_POST['internship_id'] ?? 0);

if (!$studentProfileId || !$internshipId) {
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

// Check if currently saved
$stmt = $db->prepare('SELECT id FROM saved_internships WHERE student_profile_id = ? AND internship_id = ?');
$stmt->execute([$studentProfileId, $internshipId]);
$existing = $stmt->fetch();

if ($existing) {
    // Unsave
    $del = $db->prepare('DELETE FROM saved_internships WHERE id = ?');
    $del->execute([$existing['id']]);
    echo json_encode(['saved' => false, 'message' => 'Removed from saved internships']);
} else {
    // Save
    $ins = $db->prepare('INSERT INTO saved_internships (student_profile_id, internship_id) VALUES (?, ?)');
    $ins->execute([$studentProfileId, $internshipId]);
    echo json_encode(['saved' => true, 'message' => 'Internship saved successfully']);
}

