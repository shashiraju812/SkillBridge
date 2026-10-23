<?php
$pageTitle = 'Admin Dashboard';
$currentPage = 'dashboard.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');
$db = getDB();

// Statistics calculation from DB
$totalUsers = (int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalStudents = (int)$db->query('SELECT COUNT(*) FROM users WHERE role="student"')->fetchColumn();
$totalRecruiters = (int)$db->query('SELECT COUNT(*) FROM users WHERE role="recruiter"')->fetchColumn();
$activeInternships = (int)$db->query('SELECT COUNT(*) FROM internships WHERE status="active"')->fetchColumn();
$totalApplications = (int)$db->query('SELECT COUNT(*) FROM applications')->fetchColumn();
$pendingReports = (int)$db->query('SELECT COUNT(*) FROM reports WHERE status="pending"')->fetchColumn();

// Recent users
$recentUsers = $db->query('SELECT id, full_name, email, role, is_active, created_at FROM users ORDER BY created_at DESC LIMIT 6')->fetchAll();

// Recent internships
$recentInternships = $db->query('SELECT i.id, i.title, i.status, i.created_at, rp.company_name FROM internships i JOIN recruiter_profiles rp ON rp.id = i.recruiter_profile_id ORDER BY i.created_at DESC LIMIT 6')->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="main-content">
<div class="container">
    <div class="dashboard-layout">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <div>
            <div class="page-header">
                <h1>Platform Administration</h1>
                <p>Welcome, <?php echo e(getCurrentUserName()); ?> (Administrator)</p>
            </div>

            <!-- Stat Cards -->
            <div class="card-grid-3 mb-3">
                <div class="stat-card">
                    <div class="stat-icon teal"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg></div>
                    <div class="stat-info"><h4>Total Users</h4><div class="stat-value"><?php echo $totalUsers; ?></div></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path></svg></div>
                    <div class="stat-info"><h4>Students</h4><div class="stat-value"><?php echo $totalStudents; ?></div></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"></rect></svg></div>
                    <div class="stat-info"><h4>Recruiters</h4><div class="stat-value"><?php echo $totalRecruiters; ?></div></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg></div>
                    <div class="stat-info"><h4>Active Internships</h4><div class="stat-value"><?php echo $activeInternships; ?></div></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon amber"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path></svg></div>
                    <div class="stat-info"><h4>Applications</h4><div class="stat-value"><?php echo $totalApplications; ?></div></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path></svg></div>
                    <div class="stat-info"><h4>Pending Reports</h4><div class="stat-value"><?php echo $pendingReports; ?></div></div>
                </div>
            </div>

            <!-- Recent Activity Tables -->
            <div class="card-grid-2">
                <div class="card">
                    <div class="card-header">
                        <h3>Recent Users</h3>
                        <a href="/wtproject/admin/users.php" class="btn btn-sm btn-outline">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead><tr><th>Name</th><th>Role</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach($recentUsers as $u): ?>
                                <tr>
                                    <td><strong><?php echo e($u['full_name']); ?></strong><br><span class="text-xs text-muted"><?php echo e($u['email']); ?></span></td>
                                    <td><span class="badge badge-<?php echo $u['role']==='admin'?'purple':($u['role']==='recruiter'?'warning':'info'); ?>"><?php echo ucfirst($u['role']); ?></span></td>
                                    <td><span class="badge badge-<?php echo $u['is_active']?'success':'danger'; ?>"><?php echo $u['is_active']?'Active':'Inactive'; ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>Recent Listings</h3>
                        <a href="/wtproject/admin/internships.php" class="btn btn-sm btn-outline">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead><tr><th>Title</th><th>Company</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach($recentInternships as $ri): ?>
                                <tr>
                                    <td><strong><?php echo e($ri['title']); ?></strong></td>
                                    <td class="text-sm"><?php echo e($ri['company_name']); ?></td>
                                    <td><span class="badge badge-<?php echo $ri['status']==='active'?'success':($ri['status']==='draft'?'secondary':'danger'); ?>"><?php echo ucfirst($ri['status']); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>

