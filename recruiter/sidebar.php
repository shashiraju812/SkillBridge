<?php
/**
 * Recruiter Sidebar — SkillBridge
 */
$recSidebarLinks = [
    ['dashboard.php','Dashboard'],['company-profile.php','Company Profile'],['create-internship.php','Create Internship'],['manage-internships.php','Manage Internships'],
];
?>
<div class="sidebar"><ul class="sidebar-nav">
<?php foreach($recSidebarLinks as $l): ?>
<li><a href="/wtproject/recruiter/<?php echo $l[0]; ?>" class="<?php echo (isset($currentPage)&&$currentPage===$l[0])?'active':''; ?>"><?php echo e($l[1]); ?></a></li>
<?php endforeach; ?>
</ul></div>

