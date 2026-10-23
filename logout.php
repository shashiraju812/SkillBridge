<?php
/**
 * Logout Handler — SkillBridge
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

logoutUser();
setFlash('success', 'You have been logged out successfully.');
header('Location: /wtproject/login.php');
exit;
