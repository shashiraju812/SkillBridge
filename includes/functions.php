<?php
/**
 * Utility Functions — SkillBridge
 * 
 * Reusable helper functions used across the application:
 * - Input sanitization
 * - Match score calculation
 * - Profile completion
 * - Date formatting
 * - Skill gap analysis
 */

require_once __DIR__ . '/../config/database.php';

// ============================================================
// INPUT & OUTPUT HELPERS
// ============================================================

/**
 * Sanitize a string for safe HTML output.
 *
 * @param string|null $str
 * @return string
 */
function e(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Trim and sanitize input string.
 *
 * @param string|null $str
 * @return string
 */
function cleanInput(?string $str): string {
    if ($str === null) return '';
    return trim(strip_tags($str));
}

/**
 * Validate email format.
 *
 * @param string $email
 * @return bool
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength.
 * At least 8 chars, one uppercase, one lowercase, one digit.
 *
 * @param string $password
 * @return array List of error messages (empty if valid)
 */
function validatePassword(string $password): array {
    $errors = [];
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter.';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one digit.';
    }
    return $errors;
}

// ============================================================
// FLASH MESSAGES
// ============================================================

/**
 * Set a flash message in the session.
 *
 * @param string $type 'success', 'error', 'info', 'warning'
 * @param string $message
 */
function setFlash(string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear the flash message.
 *
 * @return array|null ['type' => ..., 'message' => ...]
 */
function getFlash(): ?array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// ============================================================
// MATCH SCORE CALCULATION
// ============================================================

/**
 * Calculate match score between a student and an internship.
 *
 * Returns an associative array with:
 * - score: Overall percentage (0-100)
 * - matched_skills: Skills the student has
 * - missing_skills: Skills the student lacks
 * - weak_skills: Skills below required proficiency
 * - total_required: Count of required skills
 * - explanation: Human-readable explanation
 *
 * @param PDO $db
 * @param int $studentProfileId
 * @param int $internshipId
 * @return array
 */
function calculateMatchScore(PDO $db, int $studentProfileId, int $internshipId): array {
    // Get internship required skills
    $stmt = $db->prepare('
        SELECT isr.skill_id, isr.importance, isr.min_proficiency, s.name AS skill_name
        FROM internship_skills isr
        JOIN skills s ON s.id = isr.skill_id
        WHERE isr.internship_id = ?
    ');
    $stmt->execute([$internshipId]);
    $requiredSkills = $stmt->fetchAll();
    
    // Get student skills
    $stmt = $db->prepare('
        SELECT ss.skill_id, ss.proficiency, s.name AS skill_name
        FROM student_skills ss
        JOIN skills s ON s.id = ss.skill_id
        WHERE ss.student_profile_id = ?
    ');
    $stmt->execute([$studentProfileId]);
    $studentSkillsRaw = $stmt->fetchAll();
    
    // Build student skill lookup: skill_id => proficiency
    $studentSkills = [];
    foreach ($studentSkillsRaw as $ss) {
        $studentSkills[(int)$ss['skill_id']] = $ss['proficiency'];
    }
    
    // Handle edge case: no required skills
    if (empty($requiredSkills)) {
        return [
            'score'           => 100,
            'matched_skills'  => [],
            'missing_skills'  => [],
            'weak_skills'     => [],
            'total_required'  => 0,
            'explanation'     => 'This internship has no specific skill requirements listed. All students are potentially eligible.',
        ];
    }
    
    $proficiencyLevels = ['beginner' => 1, 'intermediate' => 2, 'advanced' => 3];
    
    $matched  = [];
    $missing  = [];
    $weak     = [];
    
    // Weights for importance levels
    $importanceWeights = ['required' => 1.0, 'preferred' => 0.6, 'nice_to_have' => 0.3];
    
    $totalWeight = 0;
    $earnedWeight = 0;
    
    foreach ($requiredSkills as $rs) {
        $skillId   = (int) $rs['skill_id'];
        $skillName = $rs['skill_name'];
        $importance = $rs['importance'];
        $minProf   = $rs['min_proficiency'];
        $weight    = $importanceWeights[$importance] ?? 1.0;
        
        $totalWeight += $weight;
        
        if (isset($studentSkills[$skillId])) {
            // Student has this skill
            $studentLevel = $proficiencyLevels[$studentSkills[$skillId]] ?? 1;
            $requiredLevel = $proficiencyLevels[$minProf] ?? 1;
            
            if ($studentLevel >= $requiredLevel) {
                // Fully matched
                $matched[] = [
                    'name'       => $skillName,
                    'importance' => $importance,
                    'student_level' => $studentSkills[$skillId],
                    'required_level' => $minProf,
                ];
                $earnedWeight += $weight;
            } else {
                // Has skill but below required level
                $weak[] = [
                    'name'       => $skillName,
                    'importance' => $importance,
                    'student_level' => $studentSkills[$skillId],
                    'required_level' => $minProf,
                ];
                // Partial credit for having the skill at a lower level
                $earnedWeight += $weight * ($studentLevel / $requiredLevel);
            }
        } else {
            // Missing skill
            $missing[] = [
                'name'       => $skillName,
                'importance' => $importance,
                'required_level' => $minProf,
            ];
        }
    }
    
    // Calculate score
    $score = ($totalWeight > 0) ? round(($earnedWeight / $totalWeight) * 100) : 0;
    $score = min(100, max(0, $score));
    
    // Generate explanation
    $explanation = "You match {$score}% of the requirements. ";
    $matchedCount = count($matched);
    $totalCount   = count($requiredSkills);
    $explanation  .= "You have {$matchedCount} of {$totalCount} required skills at the needed level. ";
    
    if (!empty($missing)) {
        $missingNames = array_column($missing, 'name');
        $explanation .= "Missing: " . implode(', ', $missingNames) . ". ";
    }
    if (!empty($weak)) {
        $weakNames = array_column($weak, 'name');
        $explanation .= "Needs improvement: " . implode(', ', $weakNames) . ". ";
    }
    
    return [
        'score'           => $score,
        'matched_skills'  => $matched,
        'missing_skills'  => $missing,
        'weak_skills'     => $weak,
        'total_required'  => $totalCount,
        'explanation'     => trim($explanation),
    ];
}

/**
 * Check if a student meets basic eligibility for an internship.
 *
 * @param PDO $db
 * @param int $studentProfileId
 * @param int $internshipId
 * @return array ['eligible' => bool, 'reasons' => string[]]
 */
function checkEligibility(PDO $db, int $studentProfileId, int $internshipId): array {
    $stmt = $db->prepare('SELECT * FROM student_profiles WHERE id = ?');
    $stmt->execute([$studentProfileId]);
    $profile = $stmt->fetch();
    
    $stmt = $db->prepare('SELECT * FROM internships WHERE id = ?');
    $stmt->execute([$internshipId]);
    $internship = $stmt->fetch();
    
    if (!$profile || !$internship) {
        return ['eligible' => false, 'reasons' => ['Profile or internship not found.']];
    }
    
    $reasons  = [];
    $eligible = true;
    
    // Check graduation year
    if ($internship['min_graduation_year'] && $profile['graduation_year']) {
        if ($profile['graduation_year'] < $internship['min_graduation_year']) {
            $eligible = false;
            $reasons[] = 'Graduation year is before the minimum required (' . $internship['min_graduation_year'] . ').';
        }
    }
    if ($internship['max_graduation_year'] && $profile['graduation_year']) {
        if ($profile['graduation_year'] > $internship['max_graduation_year']) {
            $eligible = false;
            $reasons[] = 'Graduation year is after the maximum allowed (' . $internship['max_graduation_year'] . ').';
        }
    }
    
    // Check if deadline has passed
    if ($internship['application_deadline']) {
        if (strtotime($internship['application_deadline']) < time()) {
            $eligible = false;
            $reasons[] = 'Application deadline has passed.';
        }
    }
    
    // Check internship status
    if ($internship['status'] !== 'active') {
        $eligible = false;
        $reasons[] = 'This internship is no longer accepting applications.';
    }
    
    // If no graduation year on profile
    if (!$profile['graduation_year'] && ($internship['min_graduation_year'] || $internship['max_graduation_year'])) {
        $reasons[] = 'Graduation year not verified — please update your profile.';
    }
    
    if (empty($reasons) && $eligible) {
        $reasons[] = 'You appear to meet the basic eligibility criteria.';
    }
    
    return ['eligible' => $eligible, 'reasons' => $reasons];
}

// ============================================================
// PROFILE COMPLETION
// ============================================================

/**
 * Calculate profile completion percentage from actual data.
 *
 * @param PDO $db
 * @param int $userId
 * @return int Percentage 0-100
 */
function calculateProfileCompletion(PDO $db, int $userId): int {
    $stmt = $db->prepare('SELECT * FROM student_profiles WHERE user_id = ?');
    $stmt->execute([$userId]);
    $profile = $stmt->fetch();
    
    if (!$profile) return 0;
    
    $fields = [
        'college'            => 10,
        'degree'             => 10,
        'branch'             => 10,
        'graduation_year'    => 10,
        'preferred_role'     => 5,
        'preferred_location' => 5,
        'preferred_mode'     => 5,
        'bio'                => 5,
        'phone'              => 5,
        'github_url'         => 5,
    ];
    
    $earned = 0;
    foreach ($fields as $field => $weight) {
        if (!empty($profile[$field])) {
            $earned += $weight;
        }
    }
    
    // Check if student has added skills (15 points)
    $stmt = $db->prepare('SELECT COUNT(*) as cnt FROM student_skills WHERE student_profile_id = ?');
    $stmt->execute([$profile['id']]);
    $skillCount = (int) $stmt->fetch()['cnt'];
    if ($skillCount >= 3) $earned += 15;
    elseif ($skillCount >= 1) $earned += 8;
    
    // Check if student has at least one project (10 points)
    $stmt = $db->prepare('SELECT COUNT(*) as cnt FROM projects WHERE student_profile_id = ?');
    $stmt->execute([$profile['id']]);
    if ((int) $stmt->fetch()['cnt'] > 0) $earned += 10;
    
    // Check if resume is uploaded (5 points)
    if (!empty($profile['resume_path'])) $earned += 5;
    
    return min(100, $earned);
}

// ============================================================
// FORMATTING HELPERS
// ============================================================

/**
 * Format a date string for display.
 *
 * @param string|null $date
 * @param string $format
 * @return string
 */
function formatDate(?string $date, string $format = 'M d, Y'): string {
    if (empty($date)) return 'N/A';
    $timestamp = strtotime($date);
    return $timestamp ? date($format, $timestamp) : 'N/A';
}

/**
 * Format stipend for display.
 *
 * @param int $min
 * @param int $max
 * @param bool $isPaid
 * @return string
 */
function formatStipend(int $min, int $max, bool $isPaid): string {
    if (!$isPaid) return 'Unpaid';
    if ($min === 0 && $max === 0) return 'Not specified';
    if ($min === $max) return '₹' . number_format($min) . '/month';
    return '₹' . number_format($min) . ' – ₹' . number_format($max) . '/month';
}

/**
 * Get days remaining until a deadline.
 *
 * @param string|null $deadline
 * @return int|null Null if no deadline, negative if passed
 */
function daysRemaining(?string $deadline): ?int {
    if (empty($deadline)) return null;
    $deadlineTime = strtotime($deadline);
    if (!$deadlineTime) return null;
    $diff = $deadlineTime - time();
    return (int) ceil($diff / 86400);
}

/**
 * Get a CSS class for application status badges.
 *
 * @param string $status
 * @return string
 */
function statusBadgeClass(string $status): string {
    $map = [
        'applied'             => 'badge-info',
        'under_review'        => 'badge-warning',
        'shortlisted'         => 'badge-primary',
        'interview_scheduled' => 'badge-purple',
        'selected'            => 'badge-success',
        'rejected'            => 'badge-danger',
        'withdrawn'           => 'badge-secondary',
    ];
    return $map[$status] ?? 'badge-secondary';
}

/**
 * Format status for display.
 *
 * @param string $status
 * @return string
 */
function formatStatus(string $status): string {
    return ucwords(str_replace('_', ' ', $status));
}

/**
 * Get all internships with match scores for a student.
 *
 * @param PDO $db
 * @param int $studentProfileId
 * @param array $filters Optional filters
 * @return array
 */
function getMatchedInternships(PDO $db, int $studentProfileId, array $filters = []): array {
    $where = ['i.status = ?'];
    $params = ['active'];
    
    if (!empty($filters['skill'])) {
        $where[] = 'EXISTS (SELECT 1 FROM internship_skills isr JOIN skills s ON s.id = isr.skill_id WHERE isr.internship_id = i.id AND s.name LIKE ?)';
        $params[] = '%' . $filters['skill'] . '%';
    }
    if (!empty($filters['location'])) {
        $where[] = 'i.location LIKE ?';
        $params[] = '%' . $filters['location'] . '%';
    }
    if (!empty($filters['work_mode'])) {
        $where[] = 'i.work_mode = ?';
        $params[] = $filters['work_mode'];
    }
    if (!empty($filters['career_category'])) {
        $where[] = 'i.career_category = ?';
        $params[] = $filters['career_category'];
    }
    if (!empty($filters['search'])) {
        $where[] = '(i.title LIKE ? OR rp.company_name LIKE ?)';
        $params[] = '%' . $filters['search'] . '%';
        $params[] = '%' . $filters['search'] . '%';
    }
    
    $whereSQL = implode(' AND ', $where);
    
    $stmt = $db->prepare("
        SELECT i.*, rp.company_name
        FROM internships i
        JOIN recruiter_profiles rp ON rp.id = i.recruiter_profile_id
        WHERE {$whereSQL}
        ORDER BY i.created_at DESC
    ");
    $stmt->execute($params);
    $internships = $stmt->fetchAll();
    
    // Calculate match score for each
    foreach ($internships as &$internship) {
        $match = calculateMatchScore($db, $studentProfileId, (int)$internship['id']);
        $internship['match_score'] = $match['score'];
        $internship['matched_skills'] = $match['matched_skills'];
        $internship['missing_skills'] = $match['missing_skills'];
        $internship['weak_skills'] = $match['weak_skills'];
        $internship['explanation'] = $match['explanation'];
    }
    
    // Sort by match score descending
    usort($internships, fn($a, $b) => $b['match_score'] <=> $a['match_score']);
    
    return $internships;
}

/**
 * Get skill-gap analysis for a student targeting a specific internship.
 *
 * @param PDO $db
 * @param int $studentProfileId
 * @param int $internshipId
 * @return array
 */
function getSkillGapAnalysis(PDO $db, int $studentProfileId, int $internshipId): array {
    $match = calculateMatchScore($db, $studentProfileId, $internshipId);
    
    // Get learning tasks for missing and weak skills
    $learningPlan = [];
    
    $allGapSkills = array_merge(
        array_map(fn($s) => $s['name'], $match['missing_skills']),
        array_map(fn($s) => $s['name'], $match['weak_skills'])
    );
    
    if (!empty($allGapSkills)) {
        $placeholders = implode(',', array_fill(0, count($allGapSkills), '?'));
        $stmt = $db->prepare("
            SELECT lt.*, s.name AS skill_name
            FROM learning_tasks lt
            JOIN skills s ON s.id = lt.skill_id
            WHERE s.name IN ({$placeholders})
            ORDER BY lt.week_number, lt.task_order
        ");
        $stmt->execute($allGapSkills);
        $tasks = $stmt->fetchAll();
        
        // Group by skill
        foreach ($tasks as $task) {
            $learningPlan[$task['skill_name']][] = $task;
        }
    }
    
    return [
        'match'         => $match,
        'learning_plan' => $learningPlan,
    ];
}

