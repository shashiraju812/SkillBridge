<?php
$pageTitle = 'Platform Statistics';
$currentPage = 'statistics.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');
$db = getDB();

// User breakdown
$userRoles = $db->query('SELECT role, COUNT(*) as count FROM users GROUP BY role')->fetchAll();
$activeUsers = (int)$db->query('SELECT COUNT(*) FROM users WHERE is_active=1')->fetchColumn();
$inactiveUsers = (int)$db->query('SELECT COUNT(*) FROM users WHERE is_active=0')->fetchColumn();

// Applications by status
$appStatuses = $db->query('SELECT status, COUNT(*) as count FROM applications GROUP BY status')->fetchAll();

// Internships by work mode
$workModes = $db->query('SELECT work_mode, COUNT(*) as count FROM internships GROUP BY work_mode')->fetchAll();

// Top 10 Most popular skills amongst students
$topSkills = $db->query('SELECT s.name, COUNT(ss.id) as count FROM skills s JOIN student_skills ss ON ss.skill_id = s.id GROUP BY s.id ORDER BY count DESC LIMIT 10')->fetchAll();

// Assessment averages
$assessmentStats = $db->query('SELECT a.title, COUNT(ar.id) as attempts, ROUND(AVG(ar.score)) as avg_score FROM assessments a LEFT JOIN assessment_results ar ON ar.assessment_id = a.id GROUP BY a.id')->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="main-content">
<div class="container">
    <div class="dashboard-layout">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <div>
            <div class="page-header">
                <h1>Platform Analytics & Statistics</h1>
                <p>Quantitative database insights into users, skill coverage, and matching engagement</p>
            </div>

            <div class="card-grid-2 mb-3">
                <!-- User Distribution -->
                <div class="card">
                    <h3>User Roles Distribution</h3>
                    <table>
                        <thead><tr><th>Role</th><th>Registered Count</th></tr></thead>
                        <tbody>
                            <?php foreach ($userRoles as $ur): ?>
                            <tr>
                                <td><strong><?php echo ucfirst($ur['role']); ?></strong></td>
                                <td><?php echo (int)$ur['count']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td><span class="text-success">Active Accounts</span></td>
                                <td><strong><?php echo $activeUsers; ?></strong></td>
                            </tr>
                            <tr>
                                <td><span class="text-danger">Inactive Accounts</span></td>
                                <td><strong><?php echo $inactiveUsers; ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Applications by Status -->
                <div class="card">
                    <h3>Applications By Status</h3>
                    <table>
                        <thead><tr><th>Status</th><th>Count</th></tr></thead>
                        <tbody>
                            <?php foreach ($appStatuses as $as): ?>
                            <tr>
                                <td><span class="badge <?php echo statusBadgeClass($as['status']); ?>"><?php echo formatStatus($as['status']); ?></span></td>
                                <td><strong><?php echo (int)$as['count']; ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-grid-2 mb-3">
                <!-- Top 10 Student Skills -->
                <div class="card">
                    <h3>Top 10 Claimed Skills</h3>
                    <table>
                        <thead><tr><th>Skill Name</th><th>Students</th></tr></thead>
                        <tbody>
                            <?php foreach ($topSkills as $ts): ?>
                            <tr>
                                <td><strong><?php echo e($ts['name']); ?></strong></td>
                                <td><span class="badge badge-primary"><?php echo (int)$ts['count']; ?> students</span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Work Modes -->
                <div class="card">
                    <h3>Internship Work Modes</h3>
                    <table>
                        <thead><tr><th>Mode</th><th>Count</th></tr></thead>
                        <tbody>
                            <?php foreach ($workModes as $wm): ?>
                            <tr>
                                <td><strong><?php echo ucfirst($wm['work_mode']); ?></strong></td>
                                <td><?php echo (int)$wm['count']; ?> listings</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Assessment Performance -->
            <div class="card">
                <h3>Assessment Performance Metrics</h3>
                <table>
                    <thead><tr><th>Assessment</th><th>Total Attempts</th><th>Average Score</th></tr></thead>
                    <tbody>
                        <?php foreach ($assessmentStats as $ast): ?>
                        <tr>
                            <td><strong><?php echo e($ast['title']); ?></strong></td>
                            <td><?php echo (int)$ast['attempts']; ?> attempts</td>
                            <td>
                                <strong><?php echo (int)$ast['avg_score']; ?>%</strong>
                                <span class="badge <?php echo $ast['avg_score']>=60?'badge-success':'badge-warning'; ?> ml-1">
                                    <?php echo $ast['avg_score']>=60?'Passing Avg':'Needs Work'; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>

