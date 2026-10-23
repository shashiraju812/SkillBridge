<?php
/**
 * Authentication & Authorization — SkillBridge
 * 
 * Handles session management, login state checks,
 * and role-based access control.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a user is currently logged in.
 *
 * @return bool
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get the current logged-in user's ID.
 *
 * @return int|null
 */
function getCurrentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get the current user's role.
 *
 * @return string|null 'student', 'recruiter', 'admin', or null
 */
function getCurrentUserRole(): ?string {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Get the current user's full name.
 *
 * @return string|null
 */
function getCurrentUserName(): ?string {
    return $_SESSION['user_name'] ?? null;
}

/**
 * Set session variables after successful login.
 *
 * @param array $user User row from database
 */
function loginUser(array $user): void {
    // Regenerate session ID to prevent session fixation (when headers not already sent)
    if (!headers_sent()) {
        session_regenerate_id(true);
    }
    
    $_SESSION['user_id']   = (int) $user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['login_time'] = time();
}

/**
 * Destroy the session and log out the user.
 */
function logoutUser(): void {
    $_SESSION = [];
    
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    
    session_destroy();
}

/**
 * Require the user to be logged in.
 * Redirects to login page if not authenticated.
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /wtproject/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

/**
 * Require a specific role. Redirects or shows 403 if unauthorized.
 *
 * @param string|array $roles Allowed role(s)
 */
function requireRole($roles): void {
    requireLogin();
    
    if (is_string($roles)) {
        $roles = [$roles];
    }
    
    if (!in_array(getCurrentUserRole(), $roles, true)) {
        http_response_code(403);
        die('Access denied. You do not have permission to view this page.');
    }
}

/**
 * Get the dashboard URL for the current user's role.
 *
 * @return string
 */
function getDashboardURL(): string {
    $role = getCurrentUserRole();
    switch ($role) {
        case 'student':   return '/wtproject/student/dashboard.php';
        case 'recruiter': return '/wtproject/recruiter/dashboard.php';
        case 'admin':     return '/wtproject/admin/dashboard.php';
        default:          return '/wtproject/login.php';
    }
}

/**
 * Get the student profile ID for the current user.
 * Returns null if the user is not a student or profile doesn't exist.
 *
 * @param PDO|null $db Database connection
 * @return int|null
 */
function getStudentProfileId(?PDO $db): ?int {
    if (getCurrentUserRole() !== 'student') return null;
    if ($db === null) return 1; // Stage 2 fallback mock profile id
    
    $stmt = $db->prepare('SELECT id FROM student_profiles WHERE user_id = ?');
    $stmt->execute([getCurrentUserId()]);
    $result = $stmt->fetch();
    
    return $result ? (int) $result['id'] : null;
}

/**
 * Get the recruiter profile ID for the current user.
 *
 * @param PDO|null $db Database connection
 * @return int|null
 */
function getRecruiterProfileId(?PDO $db): ?int {
    if (getCurrentUserRole() !== 'recruiter') return null;
    if ($db === null) return 1; // Stage 2 fallback mock profile id
    
    $stmt = $db->prepare('SELECT id FROM recruiter_profiles WHERE user_id = ?');
    $stmt->execute([getCurrentUserId()]);
    $result = $stmt->fetch();
    
    return $result ? (int) $result['id'] : null;
}

