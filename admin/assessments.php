<?php
$pageTitle = 'Manage Assessments';
$currentPage = 'assessments.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');
$db = getDB();

$selectedAssessmentId = (int)($_GET['assessment_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    requireValidCSRF();
    
    if ($_POST['action'] === 'create_assessment') {
        $skillId = (int)($_POST['skill_id'] ?? 0);
        $title = cleanInput($_POST['title'] ?? '');
        $desc = cleanInput($_POST['description'] ?? '');
        $timeLimit = (int)($_POST['time_limit_minutes'] ?? 20);
        $passPct = (int)($_POST['pass_percentage'] ?? 60);

        if ($skillId && $title) {
            $stmt = $db->prepare('INSERT INTO assessments (skill_id, title, description, time_limit_minutes, pass_percentage, is_active) VALUES (?, ?, ?, ?, ?, 1)');
            $stmt->execute([$skillId, $title, $desc, $timeLimit, $passPct]);
            setFlash('success', 'Assessment created successfully.');
        }
    } elseif ($_POST['action'] === 'add_question') {
        $assessId = (int)($_POST['assessment_id'] ?? 0);
        $qText = cleanInput($_POST['question_text'] ?? '');
        $optA = cleanInput($_POST['option_a'] ?? '');
        $optB = cleanInput($_POST['option_b'] ?? '');
        $optC = cleanInput($_POST['option_c'] ?? '');
        $optD = cleanInput($_POST['option_d'] ?? '');
        $correct = cleanInput($_POST['correct_option'] ?? 'a');
        $explanation = cleanInput($_POST['explanation'] ?? '');
        $difficulty = cleanInput($_POST['difficulty'] ?? 'medium');
        $topic = cleanInput($_POST['topic'] ?? '');

        if ($assessId && $qText && $optA && $optB && in_array($correct, ['a', 'b', 'c', 'd'])) {
            $stmt = $db->prepare('INSERT INTO assessment_questions (assessment_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, topic) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$assessId, $qText, $optA, $optB, $optC, $optD, $correct, $explanation, $difficulty, $topic]);
            setFlash('success', 'Question added to assessment.');
        }
        header("Location: /wtproject/admin/assessments.php?assessment_id=$assessId");
        exit;
    } elseif ($_POST['action'] === 'delete_question') {
        $qId = (int)($_POST['question_id'] ?? 0);
        $assessId = (int)($_POST['assessment_id'] ?? 0);
        $db->prepare('DELETE FROM assessment_questions WHERE id = ?')->execute([$qId]);
        setFlash('success', 'Question deleted.');
        header("Location: /wtproject/admin/assessments.php?assessment_id=$assessId");
        exit;
    } elseif ($_POST['action'] === 'toggle_active') {
        $assessId = (int)($_POST['assessment_id'] ?? 0);
        $stmt = $db->prepare('SELECT is_active FROM assessments WHERE id = ?');
        $stmt->execute([$assessId]);
        $current = $stmt->fetchColumn();
        if ($current !== false) {
            $db->prepare('UPDATE assessments SET is_active = ? WHERE id = ?')->execute([$current ? 0 : 1, $assessId]);
            setFlash('success', 'Assessment visibility toggled.');
        }
    }
    header('Location: /wtproject/admin/assessments.php');
    exit;
}

$assessments = $db->query('SELECT a.*, s.name AS skill_name, (SELECT COUNT(*) FROM assessment_questions WHERE assessment_id=a.id) AS q_count FROM assessments a JOIN skills s ON s.id = a.skill_id ORDER BY a.created_at DESC')->fetchAll();
$skills = $db->query('SELECT id, name FROM skills ORDER BY name')->fetchAll();

// If an assessment is selected for inspection
$activeQuestions = [];
$currentAssessment = null;
if ($selectedAssessmentId) {
    $stmt = $db->prepare('SELECT a.*, s.name AS skill_name FROM assessments a JOIN skills s ON s.id = a.skill_id WHERE a.id = ?');
    $stmt->execute([$selectedAssessmentId]);
    $currentAssessment = $stmt->fetch();
    
    $stmt = $db->prepare('SELECT * FROM assessment_questions WHERE assessment_id = ? ORDER BY id');
    $stmt->execute([$selectedAssessmentId]);
    $activeQuestions = $stmt->fetchAll();
}

include __DIR__ . '/../includes/header.php';
?>
<div class="main-content">
<div class="container">
    <div class="dashboard-layout">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <div>
            <div class="page-header">
                <h1>Manage Skill Assessments</h1>
                <p>Create mock assessments and question banks for student evaluation</p>
            </div>

            <?php if ($selectedAssessmentId && $currentAssessment): ?>
            <!-- Managing Specific Assessment Questions -->
            <a href="/wtproject/admin/assessments.php" class="text-sm text-muted">&larr; Back to all assessments</a>
            <div class="card mt-2 mb-3">
                <div class="d-flex justify-between align-center">
                    <div>
                        <h2><?php echo e($currentAssessment['title']); ?></h2>
                        <span class="badge badge-primary"><?php echo e($currentAssessment['skill_name']); ?></span>
                        <span class="text-sm text-muted ml-2"><?php echo count($activeQuestions); ?> Questions</span>
                    </div>
                </div>
            </div>

            <!-- Add Question Form -->
            <div class="card mb-3">
                <h3>Add New Question</h3>
                <form method="POST" data-validate>
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="add_question">
                    <input type="hidden" name="assessment_id" value="<?php echo $selectedAssessmentId; ?>">
                    
                    <div class="form-group">
                        <label>Question Prompt <span class="required">*</span></label>
                        <textarea name="question_text" class="form-control" rows="3" required placeholder="Enter the MCQ question..."></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group"><label>Option A <span class="required">*</span></label><input type="text" name="option_a" class="form-control" required></div>
                        <div class="form-group"><label>Option B <span class="required">*</span></label><input type="text" name="option_b" class="form-control" required></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Option C</label><input type="text" name="option_c" class="form-control"></div>
                        <div class="form-group"><label>Option D</label><input type="text" name="option_d" class="form-control"></div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Correct Option <span class="required">*</span></label>
                            <select name="correct_option" class="form-control" required>
                                <option value="a">A</option>
                                <option value="b">B</option>
                                <option value="c">C</option>
                                <option value="d">D</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Difficulty</label>
                            <select name="difficulty" class="form-control">
                                <option value="easy">Easy</option>
                                <option value="medium" selected>Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Topic / Sub-skill</label>
                            <input type="text" name="topic" class="form-control" placeholder="e.g. Loops, Syntax">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Explanation</label>
                        <textarea name="explanation" class="form-control" rows="2" placeholder="Educational explanation of why the correct answer is right..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Add Question</button>
                </form>
            </div>

            <!-- List Questions -->
            <div class="card">
                <h3>Existing Questions (<?php echo count($activeQuestions); ?>)</h3>
                <?php if (empty($activeQuestions)): ?>
                    <p class="text-muted">No questions in this assessment yet.</p>
                <?php else: ?>
                    <?php foreach ($activeQuestions as $idx => $q): ?>
                    <div class="question-card mt-2">
                        <div class="d-flex justify-between align-center mb-1">
                            <span class="question-number">Q<?php echo $idx + 1; ?> (<?php echo ucfirst($q['difficulty']); ?> - <?php echo e($q['topic'] ?: 'General'); ?>)</span>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete question?')">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="delete_question">
                                <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
                                <input type="hidden" name="assessment_id" value="<?php echo $selectedAssessmentId; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </div>
                        <p class="question-text"><?php echo e($q['question_text']); ?></p>
                        <div class="text-sm">
                            <p class="<?php echo $q['correct_option']==='a'?'text-success':''; ?>">A: <?php echo e($q['option_a']); ?></p>
                            <p class="<?php echo $q['correct_option']==='b'?'text-success':''; ?>">B: <?php echo e($q['option_b']); ?></p>
                            <?php if ($q['option_c']): ?><p class="<?php echo $q['correct_option']==='c'?'text-success':''; ?>">C: <?php echo e($q['option_c']); ?></p><?php endif; ?>
                            <?php if ($q['option_d']): ?><p class="<?php echo $q['correct_option']==='d'?'text-success':''; ?>">D: <?php echo e($q['option_d']); ?></p><?php endif; ?>
                        </div>
                        <?php if ($q['explanation']): ?>
                        <small class="text-muted"><strong>Explanation:</strong> <?php echo e($q['explanation']); ?></small>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php else: ?>

            <!-- Overview: Create Assessment & List Existing -->
            <div class="card mb-3">
                <h3>Create New Assessment</h3>
                <form method="POST" data-validate>
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="create_assessment">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Target Skill <span class="required">*</span></label>
                            <select name="skill_id" class="form-control" required>
                                <option value="">Select Skill</option>
                                <?php foreach ($skills as $sk): ?>
                                <option value="<?php echo $sk['id']; ?>"><?php echo e($sk['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Title <span class="required">*</span></label>
                            <input type="text" name="title" class="form-control" required placeholder="e.g. Java Fundamentals Assessment">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Educational guidance..."></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Time Limit (minutes)</label>
                            <input type="number" name="time_limit_minutes" class="form-control" value="20">
                        </div>
                        <div class="form-group">
                            <label>Passing Percentage (%)</label>
                            <input type="number" name="pass_percentage" class="form-control" value="60">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Assessment</button>
                </form>
            </div>

            <div class="card">
                <h3>Existing Assessments</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Assessment Title</th>
                                <th>Skill</th>
                                <th>Questions</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assessments as $a): ?>
                            <tr>
                                <td><strong><?php echo e($a['title']); ?></strong></td>
                                <td><span class="badge badge-primary"><?php echo e($a['skill_name']); ?></span></td>
                                <td><?php echo (int)$a['q_count']; ?> questions</td>
                                <td>
                                    <span class="badge badge-<?php echo $a['is_active']?'success':'secondary'; ?>">
                                        <?php echo $a['is_active'] ? 'Active' : 'Disabled'; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/wtproject/admin/assessments.php?assessment_id=<?php echo $a['id']; ?>" class="btn btn-sm btn-outline">Manage Questions</a>
                                    <form method="POST" style="display:inline;">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="action" value="toggle_active">
                                        <input type="hidden" name="assessment_id" value="<?php echo $a['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-secondary"><?php echo $a['is_active']?'Disable':'Enable'; ?></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php endif; ?>

        </div>
    </div>
</div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>

