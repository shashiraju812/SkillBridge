<?php
/**
 * Login Page — SkillBridge (All Roles)
 */
$pageTitle = 'Login';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) { header('Location: ' . getDashboardURL()); exit; }

$error = '';
$oldEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCSRF();
    $email    = cleanInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $oldEmail = $email;

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $db = getDBOrNull();
        if ($db !== null) {
            $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                if (!$user['is_active']) {
                    $error = 'Your account has been deactivated. Please contact support.';
                } else {
                    loginUser($user);
                    $redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? '';
                    if (!empty($redirect) && strpos($redirect, '/wtproject/') === 0) {
                        header('Location: ' . $redirect);
                    } else {
                        header('Location: ' . getDashboardURL());
                    }
                    exit;
                }
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            // Stage 2 Frontend Demo Login Fallback
            $mockUsers = [
                'aarav@student.local'    => ['id' => 1, 'full_name' => 'Aarav Sharma (Student)', 'email' => 'aarav@student.local', 'role' => 'student', 'is_active' => 1],
                'hr@techcorp.local'      => ['id' => 2, 'full_name' => 'TechCorp HR (Recruiter)', 'email' => 'hr@techcorp.local', 'role' => 'recruiter', 'is_active' => 1],
                'admin@skillbridge.local' => ['id' => 3, 'full_name' => 'Platform Admin', 'email' => 'admin@skillbridge.local', 'role' => 'admin', 'is_active' => 1],
            ];
            if (isset($mockUsers[$email]) && $password === 'password') {
                loginUser($mockUsers[$email]);
                setFlash('info', 'Stage 2 Frontend Mode: Logged in via mock frontend profile. Database authentication will activate in Stage 3.');
                header('Location: ' . getDashboardURL());
                exit;
            } else {
                $error = 'Stage 2 Frontend Mode: Use demo credentials below or start MySQL for Stage 3 DB authentication.';
            }
        }
    }
}
include __DIR__ . '/includes/header.php';
?>
<div class="main-content">
<div class="container" style="max-width: 460px;">
    <div class="card mt-4">
        <h2 style="text-align:center; margin-bottom:0.25rem;">Welcome Back</h2>
        <p class="text-center text-muted mb-3">Log in to your SkillBridge account</p>

        <?php if ($error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
        <?php endif; ?>

        <form method="POST" data-validate>
            <?php echo csrfField(); ?>
            <?php if (!empty($_GET['redirect'])): ?>
            <input type="hidden" name="redirect" value="<?php echo e($_GET['redirect']); ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required value="<?php echo e($oldEmail); ?>" placeholder="you@example.com">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">Log In</button>
        </form>

        <p class="text-center mt-3 text-muted" style="font-size:0.85rem;">
            Don't have an account?<br>
            <a href="/wtproject/register.php">Register as Student</a> &middot; <a href="/wtproject/register-recruiter.php">Register as Recruiter</a>
        </p>

        <div class="alert alert-info mt-3" style="font-size:0.8rem;">
            <strong>Demo Accounts:</strong><br>
            Admin: admin@skillbridge.local<br>
            Student: aarav@student.local<br>
            Recruiter: hr@techcorp.local<br>
            Password for all: <code>password</code>
        </div>
    </div>
</div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
