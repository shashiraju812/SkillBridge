<?php
/**
 * Shared Header Template — SkillBridge
 * 
 * Include this at the top of every page:
 *   $pageTitle = 'Page Name';
 *   include __DIR__ . '/includes/header.php';   (adjust path)
 */

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/functions.php';

// Default page title if not set
if (!isset($pageTitle)) $pageTitle = 'SkillBridge';

$flash = getFlash();
$isLoggedIn = isLoggedIn();
$userRole = getCurrentUserRole();
$userName = getCurrentUserName();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SkillBridge — Student Skills &amp; Internship Matching Platform">
    <title><?php echo e($pageTitle); ?> — SkillBridge</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/wtproject/assets/css/style.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <a href="/wtproject/index.php" class="navbar-brand">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
                SkillBridge
            </a>
            
            <button class="navbar-toggle" aria-label="Toggle navigation">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
            
            <ul class="navbar-nav">
                <li><a href="/wtproject/index.php" <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' && !$isLoggedIn ? 'class="active"' : ''; ?>>Home</a></li>
                <li><a href="/wtproject/explore.php" <?php echo basename($_SERVER['PHP_SELF']) === 'explore.php' ? 'class="active"' : ''; ?>>Explore Internships</a></li>
                <li><a href="/wtproject/about.php" <?php echo basename($_SERVER['PHP_SELF']) === 'about.php' ? 'class="active"' : ''; ?>>About</a></li>
                <li><a href="/wtproject/how-it-works.php" <?php echo basename($_SERVER['PHP_SELF']) === 'how-it-works.php' ? 'class="active"' : ''; ?>>How It Works</a></li>
                <li><a href="/wtproject/practicals/index.php" <?php echo strpos($_SERVER['PHP_SELF'], 'practicals') !== false ? 'class="active"' : ''; ?>>Lab Practicals</a></li>
                
                <?php if ($isLoggedIn): ?>
                    <li><a href="<?php echo getDashboardURL(); ?>" class="btn btn-sm btn-outline">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        Dashboard
                    </a></li>
                    <li>
                        <a href="/wtproject/logout.php" class="btn btn-sm btn-primary">Logout</a>
                    </li>
                <?php else: ?>
                    <li><a href="/wtproject/login.php" class="btn btn-sm btn-outline">Login</a></li>
                    <li><a href="/wtproject/register.php" class="btn btn-sm btn-primary">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if ($flash): ?>
    <div class="container" style="margin-top: 1rem;">
        <div class="alert alert-<?php echo e($flash['type']); ?>">
            <?php echo e($flash['message']); ?>
            <button class="alert-close">&times;</button>
        </div>
    </div>
    <?php endif; ?>

