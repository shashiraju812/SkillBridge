<?php
/**
 * Contact / Feedback — SkillBridge
 */
$pageTitle = 'Contact Us';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCSRF();
    $name    = cleanInput($_POST['name'] ?? '');
    $email   = cleanInput($_POST['email'] ?? '');
    $subject = cleanInput($_POST['subject'] ?? '');
    $message = cleanInput($_POST['message'] ?? '');
    if (empty($name)) $errors[] = 'Name is required.';
    if (empty($email) || !isValidEmail($email)) $errors[] = 'Valid email is required.';
    if (empty($message)) $errors[] = 'Message is required.';
    if (empty($errors)) {
        $db = getDB();
        $stmt = $db->prepare('INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $email, $subject, $message]);
        setFlash('success', 'Thank you for your message! We will get back to you soon.');
        header('Location: /wtproject/contact.php');
        exit;
    }
}
include __DIR__ . '/includes/header.php';
?>
<div class="main-content">
<div class="container" style="max-width:600px;">
    <div class="page-header text-center"><h1>Contact Us</h1><p>Have questions or feedback? Send us a message.</p></div>
    <?php if(!empty($errors)): ?><div class="alert alert-error"><ul style="margin:0;padding-left:1.25rem;"><?php foreach($errors as $e): ?><li><?php echo e($e); ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <div class="card">
        <form method="POST" data-validate>
            <?php echo csrfField(); ?>
            <div class="form-group"><label for="name">Name <span class="required">*</span></label><input type="text" id="name" name="name" class="form-control" required></div>
            <div class="form-group"><label for="email">Email <span class="required">*</span></label><input type="email" id="email" name="email" class="form-control" required></div>
            <div class="form-group"><label for="subject">Subject</label><input type="text" id="subject" name="subject" class="form-control"></div>
            <div class="form-group"><label for="message">Message <span class="required">*</span></label><textarea id="message" name="message" class="form-control" rows="5" required></textarea></div>
            <button type="submit" class="btn btn-primary btn-block">Send Message</button>
        </form>
        <p class="text-center text-muted text-sm mt-3">This is an educational project. Responses may not be monitored.</p>
    </div>
</div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
