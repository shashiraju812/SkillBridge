<?php
/**
 * SQLite Database Initializer for Local Execution — SkillBridge
 * Generates an embedded, zero-configuration relational database with all 19 tables and seed data.
 */

$dbPath = __DIR__ . '/skillbridge.db';
if (file_exists($dbPath)) {
    unlink($dbPath);
}

$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys = ON;');

// 1. users
$pdo->exec("
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'student',
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
");

// 2. student_profiles
$pdo->exec("
CREATE TABLE student_profiles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    college TEXT,
    degree TEXT,
    branch TEXT,
    graduation_year INTEGER,
    preferred_role TEXT,
    preferred_location TEXT,
    preferred_mode TEXT,
    github_url TEXT,
    portfolio_url TEXT,
    resume_path TEXT,
    bio TEXT,
    phone TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
");

// 3. recruiter_profiles
$pdo->exec("
CREATE TABLE recruiter_profiles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    company_name TEXT,
    company_website TEXT,
    company_description TEXT,
    company_logo_path TEXT,
    industry TEXT,
    company_size TEXT,
    location TEXT,
    phone TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
");

// 4. skill_categories
$pdo->exec("
CREATE TABLE skill_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    description TEXT,
    display_order INTEGER DEFAULT 0
);
");

// 5. skills
$pdo->exec("
CREATE TABLE skills (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL,
    name TEXT NOT NULL UNIQUE,
    description TEXT,
    FOREIGN KEY (category_id) REFERENCES skill_categories(id) ON DELETE CASCADE
);
");

// 6. student_skills
$pdo->exec("
CREATE TABLE student_skills (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_profile_id INTEGER NOT NULL,
    skill_id INTEGER NOT NULL,
    proficiency TEXT NOT NULL DEFAULT 'beginner',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(student_profile_id, skill_id),
    FOREIGN KEY (student_profile_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
);
");

// 7. projects
$pdo->exec("
CREATE TABLE projects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_profile_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    description TEXT,
    technologies TEXT,
    project_url TEXT,
    github_url TEXT,
    start_date DATE,
    end_date DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_profile_id) REFERENCES student_profiles(id) ON DELETE CASCADE
);
");

// 8. certificates
$pdo->exec("
CREATE TABLE certificates (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_profile_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    issuing_org TEXT,
    issue_date DATE,
    expiry_date DATE,
    credential_url TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_profile_id) REFERENCES student_profiles(id) ON DELETE CASCADE
);
");

// 9. assessments
$pdo->exec("
CREATE TABLE assessments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    skill_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    description TEXT,
    time_limit_minutes INTEGER DEFAULT 20,
    pass_percentage INTEGER DEFAULT 60,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
);
");

// 10. assessment_questions
$pdo->exec("
CREATE TABLE assessment_questions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    assessment_id INTEGER NOT NULL,
    question_text TEXT NOT NULL,
    option_a TEXT NOT NULL,
    option_b TEXT NOT NULL,
    option_c TEXT,
    option_d TEXT,
    correct_option TEXT NOT NULL,
    explanation TEXT,
    difficulty TEXT DEFAULT 'medium',
    topic TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assessment_id) REFERENCES assessments(id) ON DELETE CASCADE
);
");

// 11. assessment_results
$pdo->exec("
CREATE TABLE assessment_results (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_profile_id INTEGER NOT NULL,
    assessment_id INTEGER NOT NULL,
    score INTEGER NOT NULL DEFAULT 0,
    total_questions INTEGER NOT NULL DEFAULT 0,
    correct_answers INTEGER NOT NULL DEFAULT 0,
    answers_json TEXT,
    weak_topics TEXT,
    attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_profile_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (assessment_id) REFERENCES assessments(id) ON DELETE CASCADE
);
");

// 12. internships
$pdo->exec("
CREATE TABLE internships (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    recruiter_profile_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    responsibilities TEXT,
    eligibility TEXT,
    eligible_degrees TEXT,
    eligible_branches TEXT,
    min_graduation_year INTEGER,
    max_graduation_year INTEGER,
    location TEXT,
    work_mode TEXT DEFAULT 'remote',
    stipend_min INTEGER DEFAULT 0,
    stipend_max INTEGER DEFAULT 0,
    is_paid INTEGER DEFAULT 1,
    duration TEXT,
    openings INTEGER DEFAULT 1,
    application_instructions TEXT,
    start_date DATE,
    application_deadline DATE,
    status TEXT DEFAULT 'active',
    career_category TEXT,
    is_sample INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recruiter_profile_id) REFERENCES recruiter_profiles(id) ON DELETE CASCADE
);
");

// 13. internship_skills
$pdo->exec("
CREATE TABLE internship_skills (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    internship_id INTEGER NOT NULL,
    skill_id INTEGER NOT NULL,
    importance TEXT DEFAULT 'required',
    min_proficiency TEXT DEFAULT 'beginner',
    UNIQUE(internship_id, skill_id),
    FOREIGN KEY (internship_id) REFERENCES internships(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
);
");

// 14. saved_internships
$pdo->exec("
CREATE TABLE saved_internships (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_profile_id INTEGER NOT NULL,
    internship_id INTEGER NOT NULL,
    saved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(student_profile_id, internship_id),
    FOREIGN KEY (student_profile_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (internship_id) REFERENCES internships(id) ON DELETE CASCADE
);
");

// 15. applications
$pdo->exec("
CREATE TABLE applications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_profile_id INTEGER NOT NULL,
    internship_id INTEGER NOT NULL,
    status TEXT NOT NULL DEFAULT 'applied',
    cover_note TEXT,
    applied_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    recruiter_notes TEXT,
    UNIQUE(student_profile_id, internship_id),
    FOREIGN KEY (student_profile_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (internship_id) REFERENCES internships(id) ON DELETE CASCADE
);
");

// 16. learning_tasks
$pdo->exec("
CREATE TABLE learning_tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    skill_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    description TEXT,
    week_number INTEGER DEFAULT 1,
    task_order INTEGER DEFAULT 1,
    resource_url TEXT,
    estimated_hours REAL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
);
");

// 17. student_learning_progress
$pdo->exec("
CREATE TABLE student_learning_progress (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_profile_id INTEGER NOT NULL,
    learning_task_id INTEGER NOT NULL,
    status TEXT DEFAULT 'not_started',
    started_at DATETIME,
    completed_at DATETIME,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(student_profile_id, learning_task_id),
    FOREIGN KEY (student_profile_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (learning_task_id) REFERENCES learning_tasks(id) ON DELETE CASCADE
);
");

// 18. contact_messages
$pdo->exec("
CREATE TABLE contact_messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    subject TEXT,
    message TEXT NOT NULL,
    is_read INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
");

// 19. reports
$pdo->exec("
CREATE TABLE reports (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reporter_user_id INTEGER,
    reported_type TEXT NOT NULL,
    reported_id INTEGER,
    reason TEXT NOT NULL,
    status TEXT DEFAULT 'pending',
    admin_notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_user_id) REFERENCES users(id) ON DELETE SET NULL
);
");

// ==========================================
// SEED INITIAL DEMO DATA (password: "password")
// ==========================================
$hash = password_hash('password', PASSWORD_DEFAULT);

// Users
$pdo->exec("
INSERT INTO users (id, full_name, email, password_hash, role) VALUES
(1, 'Admin User', 'admin@skillbridge.local', '$hash', 'admin'),
(2, 'Aarav Sharma', 'aarav@student.local', '$hash', 'student'),
(3, 'Priya Patel', 'priya@student.local', '$hash', 'student'),
(4, 'Rahul Kumar', 'rahul@student.local', '$hash', 'student'),
(5, 'TechCorp HR', 'hr@techcorp.local', '$hash', 'recruiter'),
(6, 'InnoSoft HR', 'hr@innosoft.local', '$hash', 'recruiter');
");

// Student Profiles
$pdo->exec("
INSERT INTO student_profiles (user_id, college, degree, branch, graduation_year, preferred_role, preferred_location, preferred_mode, github_url, bio) VALUES
(2, 'ABC Institute of Technology', 'B.Tech', 'Computer Science', 2027, 'Frontend Developer', 'Bangalore', 'hybrid', 'https://github.com/aarav-demo', 'Passionate about web development and UI design.'),
(3, 'XYZ Engineering College', 'B.Tech', 'Information Technology', 2027, 'Data Analyst', 'Remote', 'remote', 'https://github.com/priya-demo', 'Interested in data science and analytics.'),
(4, 'DEF University', 'B.Tech', 'Computer Science', 2026, 'Full Stack Developer', 'Hyderabad', 'onsite', 'https://github.com/rahul-demo', 'Building full-stack applications with PHP and JavaScript.');
");

// Recruiter Profiles
$pdo->exec("
INSERT INTO recruiter_profiles (user_id, company_name, company_website, company_description, industry, company_size, location) VALUES
(5, 'TechCorp Solutions', 'https://techcorp-demo.example', 'A sample technology company for demonstration purposes.', 'Information Technology', '100-500', 'Bangalore'),
(6, 'InnoSoft Labs', 'https://innosoft-demo.example', 'A sample software development company for demonstration purposes.', 'Software Development', '50-100', 'Hyderabad');
");

// Skill Categories
$pdo->exec("
INSERT INTO skill_categories (id, name, description, display_order) VALUES
(1, 'Programming Languages', 'General-purpose programming languages', 1),
(2, 'Web Development', 'Frontend and backend web technologies', 2),
(3, 'Database', 'Database management and query languages', 3),
(4, 'Computer Science Fundamentals', 'Core CS theory and concepts', 4),
(5, 'Development Tools', 'Tools for version control, testing, and deployment', 5),
(6, 'Data Science', 'Data analysis, visualization, and machine learning basics', 6),
(7, 'Soft Skills', 'Communication, teamwork, and professional skills', 7);
");

// Skills
$pdo->exec("
INSERT INTO skills (id, category_id, name, description) VALUES
(1, 1, 'Java', 'Object-oriented programming language'),
(2, 1, 'Python', 'General-purpose high-level language'),
(3, 1, 'C', 'Low-level procedural language'),
(4, 1, 'C++', 'Object-oriented extension of C'),
(5, 1, 'JavaScript', 'Web scripting and full-stack language'),
(6, 2, 'HTML', 'HyperText Markup Language for web pages'),
(7, 2, 'CSS', 'Cascading Style Sheets for styling'),
(8, 2, 'PHP', 'Server-side scripting for web applications'),
(9, 2, 'React', 'JavaScript library for building UIs'),
(10, 2, 'Node.js', 'Server-side JavaScript runtime'),
(11, 2, 'Bootstrap', 'CSS framework for responsive design'),
(12, 2, 'REST APIs', 'Designing and consuming RESTful services'),
(13, 3, 'MySQL', 'Relational database management system'),
(14, 3, 'SQL', 'Structured Query Language'),
(15, 3, 'MongoDB', 'NoSQL document-oriented database'),
(16, 3, 'Database Design', 'Normalization, ER modeling, schema design'),
(17, 4, 'Data Structures', 'Arrays, lists, trees, graphs, hash tables'),
(18, 4, 'Algorithms', 'Sorting, searching, dynamic programming'),
(19, 4, 'Operating Systems', 'Process management, memory, file systems'),
(20, 4, 'Computer Networks', 'Protocols, TCP/IP, HTTP, networking'),
(21, 5, 'Git', 'Distributed version control system'),
(22, 5, 'GitHub', 'Cloud-based Git repository hosting'),
(23, 5, 'Docker', 'Containerization platform'),
(24, 5, 'Linux', 'Linux command line and administration'),
(25, 5, 'VS Code', 'Code editor with extensions'),
(26, 6, 'Pandas', 'Python data manipulation library'),
(27, 6, 'NumPy', 'Numerical computing in Python'),
(28, 6, 'Machine Learning Basics', 'Supervised and unsupervised learning concepts'),
(29, 6, 'Data Visualization', 'Charts, graphs, dashboards using tools like matplotlib'),
(30, 7, 'Communication', 'Written and verbal communication'),
(31, 7, 'Teamwork', 'Collaborating effectively in teams'),
(32, 7, 'Problem Solving', 'Analytical and creative problem solving');
");

// Student Skills (Aarav - profile 1)
$pdo->exec("
INSERT INTO student_skills (student_profile_id, skill_id, proficiency) VALUES
(1, 6, 'advanced'),
(1, 7, 'advanced'),
(1, 5, 'intermediate'),
(1, 8, 'intermediate'),
(1, 13, 'intermediate'),
(1, 21, 'intermediate'),
(1, 22, 'beginner'),
(1, 1, 'intermediate'),
(1, 17, 'beginner'),
(1, 11, 'beginner');
");

// Internships
$pdo->exec("
INSERT INTO internships (id, recruiter_profile_id, title, description, responsibilities, eligibility, eligible_degrees, eligible_branches, min_graduation_year, max_graduation_year, location, work_mode, stipend_min, stipend_max, is_paid, duration, openings, application_deadline, status, career_category, is_sample) VALUES
(1, 1, 'Frontend Developer Intern', 'Join our frontend team to build responsive user interfaces. This is SAMPLE data for demonstration purposes only.', 'Build responsive UI components\nCollaborate with designers\nWrite clean HTML/CSS/JS', 'Currently pursuing B.Tech/BCA/MCA in CS/IT.', 'B.Tech, BCA, MCA', 'CSE, IT', 2026, 2028, 'Bangalore', 'hybrid', 15000, 20000, 1, '3 months', 3, '2027-03-31', 'active', 'Web Development', 1),
(2, 1, 'Backend Developer Intern (PHP)', 'Work on server-side applications using PHP and MySQL. This is SAMPLE data for demonstration purposes only.', 'Develop RESTful APIs\nWrite database queries\nPerform code testing', 'B.Tech/BCA/MCA students with knowledge of PHP and databases.', 'B.Tech, BCA, MCA', 'CSE, IT', 2026, 2028, 'Bangalore', 'onsite', 18000, 25000, 1, '6 months', 2, '2027-04-15', 'active', 'Web Development', 1),
(3, 2, 'Data Analyst Intern', 'Analyze datasets and create visualizations for business insights. This is SAMPLE data for demonstration purposes only.', 'Clean and preprocess data\nCreate dashboards and reports\nPerform statistical analysis', 'B.Tech/B.Sc students with knowledge of Python.', 'B.Tech, B.Sc', 'CSE, IT, Statistics', 2026, 2028, 'Hyderabad', 'remote', 12000, 18000, 1, '3 months', 2, '2027-02-28', 'active', 'Data Science', 1);
");

// Internship Skills
$pdo->exec("
INSERT INTO internship_skills (internship_id, skill_id, importance, min_proficiency) VALUES
(1, 6, 'required', 'intermediate'),
(1, 7, 'required', 'intermediate'),
(1, 5, 'required', 'intermediate'),
(1, 9, 'preferred', 'beginner'),
(1, 21, 'required', 'beginner'),
(1, 11, 'nice_to_have', 'beginner'),

(2, 8, 'required', 'intermediate'),
(2, 13, 'required', 'intermediate'),
(2, 14, 'required', 'beginner'),
(2, 21, 'required', 'beginner'),

(3, 2, 'required', 'intermediate'),
(3, 14, 'required', 'intermediate'),
(3, 26, 'required', 'beginner'),
(3, 29, 'required', 'beginner');
");

// Assessments & Questions
$pdo->exec("
INSERT INTO assessments (id, skill_id, title, description, time_limit_minutes, pass_percentage) VALUES
(1, 6, 'HTML Fundamentals Assessment', 'Test your knowledge of HTML5 elements, attributes, and semantics.', 20, 60),
(2, 5, 'JavaScript Basics Assessment', 'Test your understanding of JavaScript fundamentals.', 25, 60);

INSERT INTO assessment_questions (assessment_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, topic) VALUES
(1, 'Which HTML element defines the largest heading?', '<h6>', '<heading>', '<h1>', '<head>', 'c', 'The <h1> element defines the largest heading.', 'easy', 'HTML Elements'),
(1, 'Which attribute specifies an alternate text for an image?', 'title', 'alt', 'src', 'href', 'b', 'The alt attribute provides alternative text for images.', 'easy', 'HTML Attributes'),
(1, 'What is the correct HTML element for inserting a line break?', '<break>', '<lb>', '<br>', '<newline>', 'c', 'The <br> element inserts a single line break.', 'easy', 'HTML Elements'),
(2, 'Which keyword declares a block-scoped variable in JavaScript?', 'var', 'let', 'define', 'dim', 'b', 'let declares a block-scoped variable.', 'easy', 'Variables'),
(2, 'What does === check in JavaScript?', 'Value only', 'Type only', 'Value and type', 'Reference', 'c', 'Strict equality checks both value and type.', 'easy', 'Operators');
");

// Learning Tasks
$pdo->exec("
INSERT INTO learning_tasks (skill_id, title, description, week_number, task_order, estimated_hours) VALUES
(9, 'Introduction to React', 'Learn what React is, why it is used, and set up a basic React project.', 1, 1, 3),
(9, 'Components and Props', 'Understand functional components, JSX syntax, and how to pass data via props.', 1, 2, 4),
(9, 'State and Event Handling', 'Learn useState hook, event handlers, and interactive components.', 2, 1, 5),
(9, 'Build a Portfolio Project', 'Create and document a portfolio-quality React project for your profile.', 3, 1, 8);
");

echo "✅ SkillBridge Embedded Database initialized successfully at: " . $dbPath . "\n";

