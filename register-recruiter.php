<?php
/**
 * Recruiter Registration — SkillBridge
 */
$pageTitle = 'Recruiter Registration';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) { header('Location: ' . getDashboardURL()); exit; }

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCSRF();
    $old = $_POST;
    $full_name = cleanInput($_POST['full_name'] ?? '');
    $email     = cleanInput($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';
    $company   = cleanInput($_POST['company_name'] ?? '');
    $website   = cleanInput($_POST['company_website'] ?? '');
    $industry  = cleanInput($_POST['industry'] ?? '');
    $size      = cleanInput($_POST['company_size'] ?? '');
    $location  = cleanInput($_POST['location'] ?? '');
    $phone     = cleanInput($_POST['phone'] ?? '');

    if (empty($full_name)) $errors[] = 'Full name is required.';
    if (empty($email) || !isValidEmail($email)) $errors[] = 'Valid email is required.';
    $errors = array_merge($errors, validatePassword($password));
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    if (empty($company)) $errors[] = 'Company name is required.';

    $db = getDBOrNull();
    if ($db === null && empty($errors)) {
        setFlash('info', 'Stage 2 Frontend Demo: Recruiter details validated successfully for ' . e($company) . '! (Database connection will be activated in Stage 3).');
        header('Location: /wtproject/login.php');
        exit;
    }

    if (empty($errors) && $db !== null) {
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors[] = 'An account with this email already exists.';
    }

    if (empty($errors) && $db !== null) {
        try {
            $db->beginTransaction();
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare('INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$full_name, $email, $hash, 'recruiter']);
            $userId = $db->lastInsertId();
            $stmt = $db->prepare('INSERT INTO recruiter_profiles (user_id, company_name, company_website, industry, company_size, location, phone) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$userId, $company, $website, $industry, $size, $location, $phone]);
            $db->commit();
            setFlash('success', 'Recruiter account created! Please log in.');
            header('Location: /wtproject/login.php');
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            error_log('Recruiter registration error: ' . $e->getMessage());
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}
include __DIR__ . '/includes/header.php';
?>
<div class="main-content">
<div class="container" style="max-width: 720px;">
    <div class="card mt-3">
        <h2 style="text-align:center; margin-bottom:0.25rem;">Register as Recruiter</h2>
        <p class="text-center text-muted mb-3">Post internships and find talented students</p>
        <?php if (!empty($errors)): ?>
        <div class="alert alert-error"><ul style="margin:0;padding-left:1.25rem;"><?php foreach($errors as $err): ?><li><?php echo e($err); ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
        <form method="POST" data-validate>
            <?php echo csrfField(); ?>
            <div class="form-row">
                <div class="form-group"><label for="full_name">Full Name <span class="required">*</span></label><input type="text" id="full_name" name="full_name" class="form-control" required value="<?php echo e($old['full_name'] ?? ''); ?>"></div>
                <div class="form-group"><label for="email">Email <span class="required">*</span></label><input type="email" id="email" name="email" class="form-control" required value="<?php echo e($old['email'] ?? ''); ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label for="password">Password <span class="required">*</span></label><input type="password" id="password" name="password" class="form-control" required></div>
                <div class="form-group"><label for="confirm_password">Confirm Password <span class="required">*</span></label><input type="password" id="confirm_password" name="confirm_password" class="form-control" required></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label for="company_name">Company Name <span class="required">*</span></label><input type="text" id="company_name" name="company_name" class="form-control" required value="<?php echo e($old['company_name'] ?? ''); ?>"></div>
                <div class="form-group"><label for="company_website">Company Website</label><input type="url" id="company_website" name="company_website" class="form-control" value="<?php echo e($old['company_website'] ?? ''); ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label for="industry">Industry</label><input type="text" id="industry" name="industry" class="form-control" value="<?php echo e($old['industry'] ?? ''); ?>" placeholder="e.g. Information Technology"></div>
                <div class="form-group"><label for="company_size">Company Size</label>
                    <select id="company_size" name="company_size" class="form-control">
                        <option value="">Select</option>
                        <?php foreach(['1-10','11-50','50-100','100-500','500+'] as $s): ?>
                        <option value="<?php echo $s; ?>" <?php echo ($old['company_size']??'')===$s?'selected':''; ?>><?php echo $s; ?> employees</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group"><label for="location">Location</label><input type="text" id="location" name="location" class="form-control" value="<?php echo e($old['location'] ?? ''); ?>"></div>
                <div class="form-group"><label for="phone">Phone</label><input type="text" id="phone" name="phone" class="form-control" value="<?php echo e($old['phone'] ?? ''); ?>"></div>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">Create Recruiter Account</button>
        </form>
        <p class="text-center mt-3 text-muted">Already have an account? <a href="/wtproject/login.php">Log in</a> | <a href="/wtproject/register.php">Register as Student</a></p>
    </div>
</div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
