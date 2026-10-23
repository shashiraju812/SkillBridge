<?php
$pageTitle = 'Skill Catalogue';
$currentPage = 'skills.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    requireValidCSRF();
    
    if ($_POST['action'] === 'add_category') {
        $name = cleanInput($_POST['cat_name'] ?? '');
        $desc = cleanInput($_POST['cat_desc'] ?? '');
        $order = (int)($_POST['display_order'] ?? 0);
        if ($name) {
            $stmt = $db->prepare('INSERT INTO skill_categories (name, description, display_order) VALUES (?, ?, ?)');
            $stmt->execute([$name, $desc, $order]);
            setFlash('success', 'Category added.');
        }
    } elseif ($_POST['action'] === 'add_skill') {
        $catId = (int)($_POST['category_id'] ?? 0);
        $name = cleanInput($_POST['skill_name'] ?? '');
        $desc = cleanInput($_POST['skill_desc'] ?? '');
        if ($catId && $name) {
            $stmt = $db->prepare('INSERT INTO skills (category_id, name, description) VALUES (?, ?, ?)');
            $stmt->execute([$catId, $name, $desc]);
            setFlash('success', 'Skill added.');
        }
    } elseif ($_POST['action'] === 'delete_skill') {
        $skillId = (int)($_POST['skill_id'] ?? 0);
        // Cascade or delete if allowed
        $stmt = $db->prepare('DELETE FROM skills WHERE id = ?');
        $stmt->execute([$skillId]);
        setFlash('success', 'Skill deleted.');
    }
    header('Location: /wtproject/admin/skills.php');
    exit;
}

$categories = $db->query('SELECT * FROM skill_categories ORDER BY display_order, name')->fetchAll();
$allSkills = $db->query('SELECT s.*, sc.name AS category_name FROM skills s JOIN skill_categories sc ON sc.id = s.category_id ORDER BY sc.display_order, s.name')->fetchAll();
$grouped = [];
foreach ($allSkills as $s) {
    $grouped[$s['category_name']][] = $s;
}

include __DIR__ . '/../includes/header.php';
?>
<div class="main-content">
<div class="container">
    <div class="dashboard-layout">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <div>
            <div class="page-header">
                <h1>Skill Catalogue Management</h1>
                <p>Maintain standardized skills and categories</p>
            </div>

            <div class="card-grid-2 mb-3">
                <!-- Add Category -->
                <div class="card">
                    <h3>Add Skill Category</h3>
                    <form method="POST" data-validate>
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="add_category">
                        <div class="form-group">
                            <label>Category Name <span class="required">*</span></label>
                            <input type="text" name="cat_name" class="form-control" required placeholder="e.g. Cloud Computing">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <input type="text" name="cat_desc" class="form-control" placeholder="Short description...">
                        </div>
                        <div class="form-group">
                            <label>Display Order</label>
                            <input type="number" name="display_order" class="form-control" value="0">
                        </div>
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </form>
                </div>

                <!-- Add Skill -->
                <div class="card">
                    <h3>Add New Skill</h3>
                    <form method="POST" data-validate>
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="add_skill">
                        <div class="form-group">
                            <label>Category <span class="required">*</span></label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo e($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Skill Name <span class="required">*</span></label>
                            <input type="text" name="skill_name" class="form-control" required placeholder="e.g. AWS, Kubernetes">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <input type="text" name="skill_desc" class="form-control" placeholder="Optional description...">
                        </div>
                        <button type="submit" class="btn btn-primary">Add Skill</button>
                    </form>
                </div>
            </div>

            <!-- Existing Catalogue Display -->
            <div class="card">
                <h3>Existing Skills Catalogue</h3>
                <?php foreach ($categories as $cat): ?>
                <div style="margin-top:1.5rem; padding-bottom:0.75rem; border-bottom:1px solid var(--gray-200);">
                    <h4 style="color:var(--primary);margin-bottom:0.25rem;"><?php echo e($cat['name']); ?></h4>
                    <p class="text-xs text-muted mb-2"><?php echo e($cat['description']); ?></p>

                    <div class="d-flex flex-wrap gap-1">
                        <?php 
                        $skillsInCat = $grouped[$cat['name']] ?? [];
                        if (empty($skillsInCat)): ?>
                            <span class="text-xs text-muted">No skills in this category yet.</span>
                        <?php else:
                            foreach ($skillsInCat as $sk): ?>
                            <div class="skill-chip d-flex align-center gap-1">
                                <span><?php echo e($sk['name']); ?></span>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this skill?')">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="action" value="delete_skill">
                                    <input type="hidden" name="skill_id" value="<?php echo $sk['id']; ?>">
                                    <button type="submit" style="background:none;border:none;color:var(--danger);cursor:pointer;font-size:0.9rem;padding:0 0.2rem;">&times;</button>
                                </form>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
</div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>

