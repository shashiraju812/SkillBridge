<?php
/**
 * Student Registration — SkillBridge
 */
$pageTitle = 'Student Registration';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . getDashboardURL());
    exit;
}

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCSRF();
    
    $old = $_POST;
    $full_name   = cleanInput($_POST['full_name'] ?? '');
    $email       = cleanInput($_POST['email'] ?? '');
    $password    = $_POST['password'] ?? '';
    $confirm     = $_POST['confirm_password'] ?? '';
    $college     = cleanInput($_POST['college'] ?? '');
    $degree      = cleanInput($_POST['degree'] ?? '');
    $branch      = cleanInput($_POST['branch'] ?? '');
    $grad_year   = (int)($_POST['graduation_year'] ?? 0);
    $pref_role   = cleanInput($_POST['preferred_role'] ?? '');
    $pref_loc    = cleanInput($_POST['preferred_location'] ?? '');
    $pref_mode   = cleanInput($_POST['preferred_mode'] ?? '');

    // Validate
    if (empty($full_name)) $errors[] = 'Full name is required.';
    if (empty($email) || !isValidEmail($email)) $errors[] = 'Valid email is required.';
    if (empty($password)) $errors[] = 'Password is required.';
    else {
        $pwdErrors = validatePassword($password);
        $errors = array_merge($errors, $pwdErrors);
    }
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    if (empty($college)) $errors[] = 'College is required.';
    if (empty($degree)) $errors[] = 'Degree is required.';
    if ($grad_year < 2020 || $grad_year > 2035) $errors[] = 'Enter a valid graduation year.';

    // Check database availability
    $db = getDBOrNull();
    if ($db === null && empty($errors)) {
        setFlash('info', 'Stage 2 Frontend Demo: Validation passed for ' . e($full_name) . '! (Database connection will be activated in Stage 3).');
        header('Location: /wtproject/login.php');
        exit;
    }

    // Check duplicate email
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
            $stmt->execute([$full_name, $email, $hash, 'student']);
            $userId = $db->lastInsertId();
            
            $stmt = $db->prepare('INSERT INTO student_profiles (user_id, college, degree, branch, graduation_year, preferred_role, preferred_location, preferred_mode) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$userId, $college, $degree, $branch, $grad_year ?: null, $pref_role, $pref_loc, $pref_mode ?: null]);
            
            $db->commit();
            setFlash('success', 'Registration successful! Please log in.');
            header('Location: /wtproject/login.php');
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            error_log('Registration error: ' . $e->getMessage());
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="main-content">
<div class="container" style="max-width: 720px;">
    <div class="card mt-3">
        <h2 style="text-align:center; margin-bottom:0.25rem;">Create Student Account</h2>
        <p class="text-center text-muted mb-3">Join SkillBridge to discover internships matched to your skills</p>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul style="margin:0; padding-left:1.25rem;">
                <?php foreach ($errors as $err): ?>
                <li><?php echo e($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" data-validate>
            <?php echo csrfField(); ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="full_name">Full Name <span class="required">*</span></label>
                    <input type="text" id="full_name" name="full_name" class="form-control" required value="<?php echo e($old['full_name'] ?? ''); ?>" placeholder="e.g. Aarav Sharma">
                </div>
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" required value="<?php echo e($old['email'] ?? ''); ?>" placeholder="you@example.com">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="Min 8 chars, upper, lower, digit">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="college">College / Institution <span class="required">*</span></label>
                    <input type="text" id="college" name="college" class="form-control" required value="<?php echo e($old['college'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="degree">Degree <span class="required">*</span></label>
                    <select id="degree" name="degree" class="form-control" required>
                        <option value="">Select Degree</option>
                        <?php foreach(['B.Tech','B.E.','BCA','B.Sc','MCA','M.Tech','M.Sc'] as $d): ?>
                        <option value="<?php echo $d; ?>" <?php echo ($old['degree'] ?? '') === $d ? 'selected' : ''; ?>><?php echo $d; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="branch">Branch / Specialization</label>
                    <select id="branch" name="branch" class="form-control">
                        <option value="">Select Branch</option>
                        <?php foreach(['Computer Science','Information Technology','Electronics','Electrical','Mechanical','Civil','Other'] as $b): ?>
                        <option value="<?php echo $b; ?>" <?php echo ($old['branch'] ?? '') === $b ? 'selected' : ''; ?>><?php echo $b; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="graduation_year">Graduation Year <span class="required">*</span></label>
                    <input type="number" id="graduation_year" name="graduation_year" class="form-control" required min="2020" max="2035" value="<?php echo e($old['graduation_year'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="preferred_role">Preferred Career Role</label>
                    <input type="text" id="preferred_role" name="preferred_role" class="form-control" value="<?php echo e($old['preferred_role'] ?? ''); ?>" placeholder="e.g. Frontend Developer">
                </div>
                <div class="form-group">
                    <label for="preferred_location">Preferred Location</label>
                    <input type="text" id="preferred_location" name="preferred_location" class="form-control" value="<?php echo e($old['preferred_location'] ?? ''); ?>" placeholder="e.g. Bangalore">
                </div>
            </div>

            <div class="form-group">
                <label for="preferred_mode">Preferred Work Mode</label>
                <select id="preferred_mode" name="preferred_mode" class="form-control">
                    <option value="">No Preference</option>
                    <option value="remote" <?php echo ($old['preferred_mode'] ?? '') === 'remote' ? 'selected' : ''; ?>>Remote</option>
                    <option value="hybrid" <?php echo ($old['preferred_mode'] ?? '') === 'hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                    <option value="onsite" <?php echo ($old['preferred_mode'] ?? '') === 'onsite' ? 'selected' : ''; ?>>Onsite</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">Create Account</button>
        </form>

        <p class="text-center mt-3 text-muted">
            Already have an account? <a href="/wtproject/login.php">Log in</a><br>
            Are you a recruiter? <a href="/wtproject/register-recruiter.php">Register as Recruiter</a>
        </p>
    </div>
</div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
