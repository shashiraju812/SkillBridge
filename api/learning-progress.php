<?php
/**
 * API: Update Learning Task Progress — SkillBridge
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || getCurrentUserRole() !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Student access required']);
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
$studentProfileId = getStudentProfileId($db);
$taskId = (int)($_POST['task_id'] ?? 0);
$status = cleanInput($_POST['status'] ?? '');

$allowed = ['not_started', 'in_progress', 'completed'];
if (!in_array($status, $allowed, true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status option']);
    exit;
}

// Check existing progress
$stmt = $db->prepare('SELECT id FROM student_learning_progress WHERE student_profile_id = ? AND learning_task_id = ?');
$stmt->execute([$studentProfileId, $taskId]);
$existing = $stmt->fetch();

if ($existing) {
    $nowStr = date('Y-m-d H:i:s');
    $extra = '';
    if ($status === 'in_progress') $extra = ", started_at = COALESCE(started_at, '{$nowStr}')";
    if ($status === 'completed') $extra = ", completed_at = '{$nowStr}'";
    $update = $db->prepare("UPDATE student_learning_progress SET status = ? $extra WHERE id = ?");
    $update->execute([$status, $existing['id']]);
} else {
    $started = ($status !== 'not_started') ? date('Y-m-d H:i:s') : null;
    $completed = ($status === 'completed') ? date('Y-m-d H:i:s') : null;
    $ins = $db->prepare('INSERT INTO student_learning_progress (student_profile_id, learning_task_id, status, started_at, completed_at) VALUES (?, ?, ?, ?, ?)');
    $ins->execute([$studentProfileId, $taskId, $status, $started, $completed]);
}

echo json_encode(['success' => true, 'message' => 'Progress updated', 'status' => $status]);

