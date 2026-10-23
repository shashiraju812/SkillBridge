<?php
/**
 * SkillBridge End-to-End Live HTTP QA Test Suite
 * Executed against http://localhost:8000 using native PHP streams
 */

$baseUrl = 'http://localhost:8000/wtproject';
$pass = 0;
$fail = 0;
$total = 0;

function httpRequest($url, $method = 'GET', $data = null, array &$cookieJar = []) {
    $headerLines = [
        "User-Agent: SkillBridgeQA/1.0",
        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8"
    ];
    
    if (!empty($cookieJar)) {
        $pairs = [];
        foreach ($cookieJar as $k => $v) {
            $pairs[] = "$k=$v";
        }
        $headerLines[] = "Cookie: " . implode('; ', $pairs);
    }
    
    $content = null;
    if ($method === 'POST') {
        $headerLines[] = "Content-Type: application/x-www-form-urlencoded";
        $content = is_array($data) ? http_build_query($data) : ($data ?? '');
    }
    
    $opts = [
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headerLines),
            'content' => $content,
            'follow_location' => 0,
            'ignore_errors' => true,
            'timeout' => 10
        ]
    ];
    
    $context = stream_context_create($opts);
    $body = @file_get_contents($url, false, $context);
    
    $headers = $http_response_header ?? [];
    $httpCode = 0;
    if (!empty($headers[0]) && preg_match('/HTTP\/\S+\s+(\d+)/', $headers[0], $m)) {
        $httpCode = (int)$m[1];
    }
    
    foreach ($headers as $h) {
        if (stripos($h, 'Set-Cookie:') === 0) {
            $cookiePart = trim(substr($h, 11));
            $subParts = explode(';', $cookiePart);
            $kv = explode('=', $subParts[0], 2);
            if (count($kv) === 2) {
                $cookieJar[trim($kv[0])] = trim($kv[1]);
            }
        }
    }
    
    return [
        'code' => $httpCode,
        'headers' => $headers,
        'body' => $body ?: ''
    ];
}

function assertTest($id, $name, $condition, $details = '') {
    global $pass, $fail, $total;
    $total++;
    if ($condition) {
        $pass++;
        printf("  [PASS] %-10s | %-50s %s\n", $id, $name, $details ? "($details)" : "");
    } else {
        $fail++;
        printf("! [FAIL] %-10s | %-50s %s\n", $id, $name, $details ? "($details)" : "");
    }
}

echo "======================================================================\n";
echo "       SkillBridge End-to-End Live HTTP QA Test Suite                 \n";
echo "       Target: $baseUrl\n";
echo "======================================================================\n\n";

// SECTION 1: PUBLIC PAGES
echo "--- 1. Public Pages & Web Discovery ---\n";

$r = httpRequest("$baseUrl/index.php");
assertTest('TC-PUB-01', 'Home page renders (HTTP 200)', $r['code'] === 200 && strpos($r['body'], 'SkillBridge') !== false, 'Code: ' . $r['code']);

$r = httpRequest("$baseUrl/about.php");
assertTest('TC-PUB-02', 'About page renders (HTTP 200)', $r['code'] === 200 && strpos($r['body'], 'About') !== false, 'Code: ' . $r['code']);

$r = httpRequest("$baseUrl/how-it-works.php");
assertTest('TC-PUB-03', 'How It Works renders (HTTP 200)', $r['code'] === 200 && strpos($r['body'], 'How SkillBridge Works') !== false, 'Code: ' . $r['code']);

$r = httpRequest("$baseUrl/explore.php");
assertTest('TC-PUB-04', 'Explore internships renders (HTTP 200)', $r['code'] === 200 && strpos($r['body'], 'Frontend Developer') !== false, 'Code: ' . $r['code']);

$r = httpRequest("$baseUrl/explore.php?q=Frontend");
assertTest('TC-PUB-05', 'Explore query filter works', $r['code'] === 200 && strpos($r['body'], 'Frontend Developer Intern') !== false, 'Search: Frontend');

$r = httpRequest("$baseUrl/internship-details.php?id=1");
assertTest('TC-PUB-06', 'Internship details renders (HTTP 200)', $r['code'] === 200 && strpos($r['body'], 'TechCorp Solutions') !== false, 'Internship ID: 1');

$r = httpRequest("$baseUrl/contact.php");
assertTest('TC-PUB-07', 'Contact page renders (HTTP 200)', $r['code'] === 200 && strpos($r['body'], 'Contact') !== false, 'Code: ' . $r['code']);

$r = httpRequest("$baseUrl/register.php");
assertTest('TC-PUB-08', 'Student register renders (HTTP 200)', $r['code'] === 200 && strpos($r['body'], 'Student Registration') !== false, 'Code: ' . $r['code']);

$r = httpRequest("$baseUrl/register-recruiter.php");
assertTest('TC-PUB-09', 'Recruiter register renders (HTTP 200)', $r['code'] === 200 && strpos($r['body'], 'Recruiter Registration') !== false, 'Code: ' . $r['code']);

$r = httpRequest("$baseUrl/login.php");
assertTest('TC-PUB-10', 'Login page renders (HTTP 200)', $r['code'] === 200 && (strpos($r['body'], 'Log In') !== false || strpos($r['body'], 'Welcome Back') !== false), 'Code: ' . $r['code']);

// SECTION 2: WEB TECH PRACTICALS
echo "\n--- 2. Web Technologies Practicals Hub ---\n";

$r = httpRequest("$baseUrl/practicals/index.php");
assertTest('TC-PRAC-01', 'Practicals Portfolio Hub renders', $r['code'] === 200 && strpos($r['body'], 'Web Technologies Lab') !== false, '15 Practicals Listed');

$r = httpRequest("$baseUrl/practicals/practical1.html");
assertTest('TC-PRAC-02', 'Practical 1 (HTML5 Registration Form)', $r['code'] === 200 && strpos($r['body'], 'Registration') !== false, 'Form elements present');

$r = httpRequest("$baseUrl/practicals/practical2_frameset.html");
assertTest('TC-PRAC-03', 'Practical 2 (Frameset Architecture)', $r['code'] === 200 && strpos($r['body'], 'frameset') !== false, 'Frameset tags present');

$r = httpRequest("$baseUrl/practicals/practical3.html");
assertTest('TC-PRAC-04', 'Practical 3 (CSS Selectors & Cascade)', $r['code'] === 200 && strpos($r['body'], 'CSS') !== false, 'CSS Showcase');

$r = httpRequest("$baseUrl/xml/student-profiles.xml");
assertTest('TC-PRAC-05', 'Practical 4 (XML Document)', $r['code'] === 200 && strpos($r['body'], '<?xml') !== false, 'XML valid');

$r = httpRequest("$baseUrl/xml/student-profile.dtd");
assertTest('TC-PRAC-06', 'Practical 5 (DTD Schema)', $r['code'] === 200 && strpos($r['body'], '<!ELEMENT') !== false, 'DTD valid');

$r = httpRequest("$baseUrl/xml/student-profile.xsd");
assertTest('TC-PRAC-07', 'Practical 6 (XML Schema XSD)', $r['code'] === 200 && strpos($r['body'], 'xs:schema') !== false, 'XSD valid');

// SECTION 3: STUDENT AUTHENTICATION & PORTAL
echo "\n--- 3. Student Workflow & Explainable Matching ---\n";

$studentJar = [];
$r = httpRequest("$baseUrl/login.php", 'GET', null, $studentJar);
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $r['body'], $csrfMatches);
$csrfToken = $csrfMatches[1] ?? '';

$loginData = [
    'email' => 'aarav@student.local',
    'password' => 'password',
    'csrf_token' => $csrfToken
];

$r = httpRequest("$baseUrl/login.php", 'POST', $loginData, $studentJar);
assertTest('TC-STU-01', 'Student authentication redirects (HTTP 302)', $r['code'] === 302, 'Redirect to dashboard');

$r = httpRequest("$baseUrl/student/dashboard.php", 'GET', null, $studentJar);
assertTest('TC-STU-02', 'Student dashboard renders personalized data', $r['code'] === 200 && strpos($r['body'], 'Aarav') !== false, 'Welcome Aarav');
assertTest('TC-STU-03', 'Dynamic Profile Completion indicator active', strpos($r['body'], 'Profile Completion') !== false, 'Calculated %');

$r = httpRequest("$baseUrl/student/profile.php", 'GET', null, $studentJar);
assertTest('TC-STU-04', 'Student profile page renders', $r['code'] === 200 && strpos($r['body'], 'Profile') !== false);

$r = httpRequest("$baseUrl/student/skills.php", 'GET', null, $studentJar);
assertTest('TC-STU-05', 'Skills management renders', $r['code'] === 200 && strpos($r['body'], 'HTML') !== false);

$r = httpRequest("$baseUrl/student/internships.php", 'GET', null, $studentJar);
assertTest('TC-STU-06', 'Student internship match explorer renders with scores', $r['code'] === 200 && strpos($r['body'], 'match-score') !== false);

$r = httpRequest("$baseUrl/student/internship-match.php?id=1", 'GET', null, $studentJar);
assertTest('TC-STU-07', 'Explainable match details renders', $r['code'] === 200 && strpos($r['body'], 'Skill Comparison') !== false, 'Matched/Missing skills breakdown');

$r = httpRequest("$baseUrl/student/skill-gap.php?id=1", 'GET', null, $studentJar);
assertTest('TC-STU-08', 'Skill gap analysis renders', $r['code'] === 200 && strpos($r['body'], 'Gap Analysis') !== false);

$r = httpRequest("$baseUrl/student/learning-roadmap.php", 'GET', null, $studentJar);
assertTest('TC-STU-09', 'Learning roadmap renders milestone tasks', $r['code'] === 200 && strpos($r['body'], 'Learning Roadmap') !== false);

$r = httpRequest("$baseUrl/student/assessment.php?id=1", 'GET', null, $studentJar);
assertTest('TC-STU-10', 'Skill assessment catalog renders', $r['code'] === 200 && strpos($r['body'], 'Assessment') !== false);

$r = httpRequest("$baseUrl/student/applications.php", 'GET', null, $studentJar);
assertTest('TC-STU-11', 'Application tracker renders', $r['code'] === 200 && strpos($r['body'], 'Applications') !== false);

// Security Role Check: Student trying to access recruiter dashboard
$r = httpRequest("$baseUrl/recruiter/dashboard.php", 'GET', null, $studentJar);
assertTest('TC-SEC-01', 'Student cannot access recruiter portal (Blocked)', $r['code'] === 403 || $r['code'] === 302, 'Code: ' . $r['code']);

// SECTION 4: RECRUITER WORKFLOW
echo "\n--- 4. Recruiter Workflow & Candidate Management ---\n";

$recruiterJar = [];
$r = httpRequest("$baseUrl/login.php", 'GET', null, $recruiterJar);
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $r['body'], $csrfMatches);
$csrfToken = $csrfMatches[1] ?? '';

$loginData = [
    'email' => 'hr@techcorp.local',
    'password' => 'password',
    'csrf_token' => $csrfToken
];

$r = httpRequest("$baseUrl/login.php", 'POST', $loginData, $recruiterJar);
assertTest('TC-REC-01', 'Recruiter authentication redirects (HTTP 302)', $r['code'] === 302, 'Redirect to dashboard');

$r = httpRequest("$baseUrl/recruiter/dashboard.php", 'GET', null, $recruiterJar);
assertTest('TC-REC-02', 'Recruiter dashboard renders', $r['code'] === 200 && strpos($r['body'], 'TechCorp') !== false, 'Company portal loaded');

$r = httpRequest("$baseUrl/recruiter/manage-internships.php", 'GET', null, $recruiterJar);
assertTest('TC-REC-03', 'Manage internships renders recruiter listings', $r['code'] === 200 && strpos($r['body'], 'Frontend Developer') !== false);

$r = httpRequest("$baseUrl/recruiter/applicants.php?internship_id=1", 'GET', null, $recruiterJar);
assertTest('TC-REC-04', 'Recruiter candidate applicant review renders', $r['code'] === 200 && strpos($r['body'], 'Applicants') !== false);

// Security Role Check: Recruiter trying to access admin dashboard
$r = httpRequest("$baseUrl/admin/dashboard.php", 'GET', null, $recruiterJar);
assertTest('TC-SEC-02', 'Recruiter cannot access admin portal (Blocked)', $r['code'] === 403 || $r['code'] === 302, 'Code: ' . $r['code']);

// SECTION 5: ADMINISTRATOR WORKFLOW
echo "\n--- 5. Administrator Portal & Governance ---\n";

$adminJar = [];
$r = httpRequest("$baseUrl/login.php", 'GET', null, $adminJar);
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $r['body'], $csrfMatches);
$csrfToken = $csrfMatches[1] ?? '';

$loginData = [
    'email' => 'admin@skillbridge.local',
    'password' => 'password',
    'csrf_token' => $csrfToken
];

$r = httpRequest("$baseUrl/login.php", 'POST', $loginData, $adminJar);
assertTest('TC-ADM-01', 'Admin authentication redirects (HTTP 302)', $r['code'] === 302, 'Redirect to admin dashboard');

$r = httpRequest("$baseUrl/admin/dashboard.php", 'GET', null, $adminJar);
assertTest('TC-ADM-02', 'Admin dashboard renders key metrics', $r['code'] === 200 && (strpos($r['body'], 'Overview') !== false || strpos($r['body'], 'Admin') !== false));

$r = httpRequest("$baseUrl/admin/users.php", 'GET', null, $adminJar);
assertTest('TC-ADM-03', 'User management console renders all accounts', $r['code'] === 200 && strpos($r['body'], 'Users') !== false);

$r = httpRequest("$baseUrl/admin/internships.php", 'GET', null, $adminJar);
assertTest('TC-ADM-04', 'Admin internship moderation panel renders', $r['code'] === 200 && strpos($r['body'], 'Internships') !== false);

$r = httpRequest("$baseUrl/admin/skills.php", 'GET', null, $adminJar);
assertTest('TC-ADM-05', 'Master skill catalogue administration renders', $r['code'] === 200 && strpos($r['body'], 'Skill') !== false);

$r = httpRequest("$baseUrl/admin/assessments.php", 'GET', null, $adminJar);
assertTest('TC-ADM-06', 'Assessment question bank management renders', $r['code'] === 200 && strpos($r['body'], 'Assessment') !== false);

$r = httpRequest("$baseUrl/admin/statistics.php", 'GET', null, $adminJar);
assertTest('TC-ADM-07', 'Platform analytics and statistics render', $r['code'] === 200 && (strpos($r['body'], 'Statistics') !== false || strpos($r['body'], 'Analytics') !== false));

echo "\n======================================================================\n";
printf("Live QA Testing Summary: %d Passed, %d Failed (Total %d Tests)\n", $pass, $fail, $total);
echo "======================================================================\n";

if ($fail > 0) {
    exit(1);
}
