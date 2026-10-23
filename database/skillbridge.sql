-- ============================================================
-- SkillBridge — Student Skills & Internship Matching Platform
-- Complete MySQL Database Schema + Sample Data
-- ============================================================
-- Run this file in phpMyAdmin or MySQL CLI:
--   mysql -u root -p < skillbridge.sql
-- ============================================================

DROP DATABASE IF EXISTS skillbridge;
CREATE DATABASE skillbridge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE skillbridge;

-- ============================================================
-- 1. USERS — All roles share one login table
-- ============================================================
-- Stores login credentials and role for students, recruiters, admins.
-- The 'role' column controls access. Passwords are stored as bcrypt hashes.
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student', 'recruiter', 'admin') NOT NULL DEFAULT 'student',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_email (email)
) ENGINE=InnoDB;

-- ============================================================
-- 2. STUDENT_PROFILES — Extended info for students
-- ============================================================
-- One-to-one with users. Stores academic, career, and contact info.
CREATE TABLE student_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    college VARCHAR(200) DEFAULT NULL,
    degree VARCHAR(100) DEFAULT NULL,
    branch VARCHAR(100) DEFAULT NULL,
    graduation_year INT DEFAULT NULL,
    preferred_role VARCHAR(100) DEFAULT NULL,
    preferred_location VARCHAR(100) DEFAULT NULL,
    preferred_mode ENUM('remote', 'hybrid', 'onsite') DEFAULT NULL,
    github_url VARCHAR(255) DEFAULT NULL,
    portfolio_url VARCHAR(255) DEFAULT NULL,
    resume_path VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 3. RECRUITER_PROFILES — Company info for recruiters
-- ============================================================
-- One-to-one with users. Stores company details.
CREATE TABLE recruiter_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company_name VARCHAR(200) DEFAULT NULL,
    company_website VARCHAR(255) DEFAULT NULL,
    company_description TEXT DEFAULT NULL,
    company_logo_path VARCHAR(255) DEFAULT NULL,
    industry VARCHAR(100) DEFAULT NULL,
    company_size VARCHAR(50) DEFAULT NULL,
    location VARCHAR(150) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 4. SKILL_CATEGORIES — Groups of skills
-- ============================================================
-- e.g., "Programming", "Web Development", "Database"
CREATE TABLE skill_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    display_order INT DEFAULT 0,
    UNIQUE KEY uk_name (name)
) ENGINE=InnoDB;

-- ============================================================
-- 5. SKILLS — Master skill catalogue
-- ============================================================
-- Each skill belongs to a category. Used for both student skills
-- and internship requirements.
CREATE TABLE skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    UNIQUE KEY uk_name (name),
    FOREIGN KEY (category_id) REFERENCES skill_categories(id) ON DELETE CASCADE,
    INDEX idx_category (category_id)
) ENGINE=InnoDB;

-- ============================================================
-- 6. STUDENT_SKILLS — Skills a student has claimed
-- ============================================================
-- Links students to skills with a self-reported proficiency level.
CREATE TABLE student_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_profile_id INT NOT NULL,
    skill_id INT NOT NULL,
    proficiency ENUM('beginner', 'intermediate', 'advanced') NOT NULL DEFAULT 'beginner',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_student_skill (student_profile_id, skill_id),
    FOREIGN KEY (student_profile_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 7. PROJECTS — Student portfolio projects
-- ============================================================
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_profile_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    technologies VARCHAR(255) DEFAULT NULL,
    project_url VARCHAR(255) DEFAULT NULL,
    github_url VARCHAR(255) DEFAULT NULL,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_profile_id) REFERENCES student_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 8. CERTIFICATES — Student certifications
-- ============================================================
CREATE TABLE certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_profile_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    issuing_org VARCHAR(200) DEFAULT NULL,
    issue_date DATE DEFAULT NULL,
    expiry_date DATE DEFAULT NULL,
    credential_url VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_profile_id) REFERENCES student_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 9. ASSESSMENTS — Skill assessment definitions
-- ============================================================
-- Each assessment tests one skill and has a set of questions.
CREATE TABLE assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    skill_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    time_limit_minutes INT DEFAULT 30,
    pass_percentage INT DEFAULT 60,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 10. ASSESSMENT_QUESTIONS — MCQ questions
-- ============================================================
CREATE TABLE assessment_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT NOT NULL,
    question_text TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) DEFAULT NULL,
    option_d VARCHAR(255) DEFAULT NULL,
    correct_option CHAR(1) NOT NULL,  -- 'a', 'b', 'c', or 'd'
    explanation TEXT DEFAULT NULL,
    difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
    topic VARCHAR(100) DEFAULT NULL,   -- sub-topic for weakness analysis
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assessment_id) REFERENCES assessments(id) ON DELETE CASCADE,
    INDEX idx_assessment (assessment_id)
) ENGINE=InnoDB;

-- ============================================================
-- 11. ASSESSMENT_RESULTS — Student assessment attempts
-- ============================================================
CREATE TABLE assessment_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_profile_id INT NOT NULL,
    assessment_id INT NOT NULL,
    score INT NOT NULL DEFAULT 0,          -- percentage 0-100
    total_questions INT NOT NULL DEFAULT 0,
    correct_answers INT NOT NULL DEFAULT 0,
    answers_json TEXT DEFAULT NULL,         -- JSON of question_id: selected_option
    weak_topics TEXT DEFAULT NULL,          -- comma-separated weak topics
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_profile_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (assessment_id) REFERENCES assessments(id) ON DELETE CASCADE,
    INDEX idx_student (student_profile_id)
) ENGINE=InnoDB;

-- ============================================================
-- 12. INTERNSHIPS — Internship listings posted by recruiters
-- ============================================================
CREATE TABLE internships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recruiter_profile_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    responsibilities TEXT DEFAULT NULL,
    eligibility TEXT DEFAULT NULL,
    eligible_degrees VARCHAR(255) DEFAULT NULL,    -- e.g. "B.Tech, BCA, MCA"
    eligible_branches VARCHAR(255) DEFAULT NULL,   -- e.g. "CSE, IT, ECE"
    min_graduation_year INT DEFAULT NULL,
    max_graduation_year INT DEFAULT NULL,
    location VARCHAR(150) DEFAULT NULL,
    work_mode ENUM('remote', 'hybrid', 'onsite') DEFAULT 'remote',
    stipend_min INT DEFAULT 0,
    stipend_max INT DEFAULT 0,
    is_paid TINYINT(1) DEFAULT 1,
    duration VARCHAR(100) DEFAULT NULL,
    openings INT DEFAULT 1,
    application_instructions TEXT DEFAULT NULL,
    start_date DATE DEFAULT NULL,
    application_deadline DATE DEFAULT NULL,
    status ENUM('draft', 'active', 'closed', 'expired') DEFAULT 'active',
    career_category VARCHAR(100) DEFAULT NULL,
    is_sample TINYINT(1) DEFAULT 0,   -- 1 = sample/demo data
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (recruiter_profile_id) REFERENCES recruiter_profiles(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_deadline (application_deadline),
    INDEX idx_career (career_category)
) ENGINE=InnoDB;

-- ============================================================
-- 13. INTERNSHIP_SKILLS — Skills required for an internship
-- ============================================================
-- Links internships to required skills with optional importance level.
CREATE TABLE internship_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    internship_id INT NOT NULL,
    skill_id INT NOT NULL,
    importance ENUM('required', 'preferred', 'nice_to_have') DEFAULT 'required',
    min_proficiency ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    UNIQUE KEY uk_internship_skill (internship_id, skill_id),
    FOREIGN KEY (internship_id) REFERENCES internships(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 14. SAVED_INTERNSHIPS — Student bookmarks
-- ============================================================
CREATE TABLE saved_internships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_profile_id INT NOT NULL,
    internship_id INT NOT NULL,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_saved (student_profile_id, internship_id),
    FOREIGN KEY (student_profile_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (internship_id) REFERENCES internships(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 15. APPLICATIONS — Internship applications
-- ============================================================
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_profile_id INT NOT NULL,
    internship_id INT NOT NULL,
    status ENUM('applied', 'under_review', 'shortlisted', 'interview_scheduled', 'selected', 'rejected', 'withdrawn') NOT NULL DEFAULT 'applied',
    cover_note TEXT DEFAULT NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    recruiter_notes TEXT DEFAULT NULL,
    UNIQUE KEY uk_application (student_profile_id, internship_id),
    FOREIGN KEY (student_profile_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (internship_id) REFERENCES internships(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_internship (internship_id)
) ENGINE=InnoDB;

-- ============================================================
-- 16. LEARNING_TASKS — Recommended learning items for a skill
-- ============================================================
CREATE TABLE learning_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    skill_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    week_number INT DEFAULT 1,
    task_order INT DEFAULT 1,
    resource_url VARCHAR(255) DEFAULT NULL,
    estimated_hours DECIMAL(4,1) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE,
    INDEX idx_skill (skill_id)
) ENGINE=InnoDB;

-- ============================================================
-- 17. STUDENT_LEARNING_PROGRESS — Task tracking per student
-- ============================================================
CREATE TABLE student_learning_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_profile_id INT NOT NULL,
    learning_task_id INT NOT NULL,
    status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    started_at TIMESTAMP NULL DEFAULT NULL,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_progress (student_profile_id, learning_task_id),
    FOREIGN KEY (student_profile_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (learning_task_id) REFERENCES learning_tasks(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 18. CONTACT_MESSAGES — Feedback / contact form submissions
-- ============================================================
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200) DEFAULT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 19. REPORTS — Flagged content
-- ============================================================
CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reporter_user_id INT DEFAULT NULL,
    reported_type ENUM('internship', 'user', 'other') NOT NULL,
    reported_id INT DEFAULT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'reviewed', 'resolved', 'dismissed') DEFAULT 'pending',
    admin_notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;


-- ============================================================
-- SAMPLE DATA — Clearly labeled as educational/demo content
-- ============================================================

-- -----------------------------------------------
-- Admin user (password: Admin@123)
-- -----------------------------------------------
INSERT INTO users (full_name, email, password_hash, role) VALUES
('Admin User', 'admin@skillbridge.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- -----------------------------------------------
-- Sample Students (password for all: Student@123)
-- -----------------------------------------------
INSERT INTO users (full_name, email, password_hash, role) VALUES
('Aarav Sharma', 'aarav@student.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Priya Patel', 'priya@student.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Rahul Kumar', 'rahul@student.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

-- -----------------------------------------------
-- Sample Recruiters (password for all: Recruiter@123)
-- -----------------------------------------------
INSERT INTO users (full_name, email, password_hash, role) VALUES
('TechCorp HR', 'hr@techcorp.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'recruiter'),
('InnoSoft HR', 'hr@innosoft.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'recruiter');

-- -----------------------------------------------
-- Student Profiles
-- -----------------------------------------------
INSERT INTO student_profiles (user_id, college, degree, branch, graduation_year, preferred_role, preferred_location, preferred_mode, github_url, bio) VALUES
(2, 'ABC Institute of Technology', 'B.Tech', 'Computer Science', 2027, 'Frontend Developer', 'Bangalore', 'hybrid', 'https://github.com/aarav-demo', 'Passionate about web development and UI design.'),
(3, 'XYZ Engineering College', 'B.Tech', 'Information Technology', 2027, 'Data Analyst', 'Remote', 'remote', 'https://github.com/priya-demo', 'Interested in data science and analytics.'),
(4, 'DEF University', 'B.Tech', 'Computer Science', 2026, 'Full Stack Developer', 'Hyderabad', 'onsite', 'https://github.com/rahul-demo', 'Building full-stack applications with PHP and JavaScript.');

-- -----------------------------------------------
-- Recruiter Profiles
-- -----------------------------------------------
INSERT INTO recruiter_profiles (user_id, company_name, company_website, company_description, industry, company_size, location) VALUES
(5, 'TechCorp Solutions', 'https://techcorp-demo.example', 'A sample technology company for demonstration purposes.', 'Information Technology', '100-500', 'Bangalore'),
(6, 'InnoSoft Labs', 'https://innosoft-demo.example', 'A sample software development company for demonstration purposes.', 'Software Development', '50-100', 'Hyderabad');

-- -----------------------------------------------
-- Skill Categories
-- -----------------------------------------------
INSERT INTO skill_categories (name, description, display_order) VALUES
('Programming Languages', 'General-purpose programming languages', 1),
('Web Development', 'Frontend and backend web technologies', 2),
('Database', 'Database management and query languages', 3),
('Computer Science Fundamentals', 'Core CS theory and concepts', 4),
('Development Tools', 'Tools for version control, testing, and deployment', 5),
('Data Science', 'Data analysis, visualization, and machine learning basics', 6),
('Soft Skills', 'Communication, teamwork, and professional skills', 7);

-- -----------------------------------------------
-- Skills
-- -----------------------------------------------
INSERT INTO skills (category_id, name, description) VALUES
-- Programming Languages (cat 1)
(1, 'Java', 'Object-oriented programming language'),
(1, 'Python', 'General-purpose high-level language'),
(1, 'C', 'Low-level procedural language'),
(1, 'C++', 'Object-oriented extension of C'),
(1, 'JavaScript', 'Web scripting and full-stack language'),
-- Web Development (cat 2)
(2, 'HTML', 'HyperText Markup Language for web pages'),
(2, 'CSS', 'Cascading Style Sheets for styling'),
(2, 'PHP', 'Server-side scripting for web applications'),
(2, 'React', 'JavaScript library for building UIs'),
(2, 'Node.js', 'Server-side JavaScript runtime'),
(2, 'Bootstrap', 'CSS framework for responsive design'),
(2, 'REST APIs', 'Designing and consuming RESTful services'),
-- Database (cat 3)
(3, 'MySQL', 'Relational database management system'),
(3, 'SQL', 'Structured Query Language'),
(3, 'MongoDB', 'NoSQL document-oriented database'),
(3, 'Database Design', 'Normalization, ER modeling, schema design'),
-- CS Fundamentals (cat 4)
(4, 'Data Structures', 'Arrays, lists, trees, graphs, hash tables'),
(4, 'Algorithms', 'Sorting, searching, dynamic programming'),
(4, 'Operating Systems', 'Process management, memory, file systems'),
(4, 'Computer Networks', 'Protocols, TCP/IP, HTTP, networking'),
-- Dev Tools (cat 5)
(5, 'Git', 'Distributed version control system'),
(5, 'GitHub', 'Cloud-based Git repository hosting'),
(5, 'Docker', 'Containerization platform'),
(5, 'Linux', 'Linux command line and administration'),
(5, 'VS Code', 'Code editor with extensions'),
-- Data Science (cat 6)
(6, 'Pandas', 'Python data manipulation library'),
(6, 'NumPy', 'Numerical computing in Python'),
(6, 'Machine Learning Basics', 'Supervised and unsupervised learning concepts'),
(6, 'Data Visualization', 'Charts, graphs, dashboards using tools like matplotlib'),
-- Soft Skills (cat 7)
(7, 'Communication', 'Written and verbal communication'),
(7, 'Teamwork', 'Collaborating effectively in teams'),
(7, 'Problem Solving', 'Analytical and creative problem solving'),
(7, 'Time Management', 'Prioritizing tasks and meeting deadlines');

-- -----------------------------------------------
-- Student Skills (sample data for Aarav - user 2, profile 1)
-- -----------------------------------------------
INSERT INTO student_skills (student_profile_id, skill_id, proficiency) VALUES
(1, 6, 'advanced'),      -- HTML
(1, 7, 'advanced'),      -- CSS
(1, 5, 'intermediate'),  -- JavaScript
(1, 8, 'intermediate'),  -- PHP
(1, 14, 'intermediate'), -- MySQL
(1, 21, 'intermediate'), -- Git
(1, 22, 'beginner'),     -- GitHub
(1, 1, 'intermediate'),  -- Java
(1, 17, 'beginner'),     -- Data Structures
(1, 11, 'beginner');     -- Bootstrap

-- Student Skills for Priya (profile 2)
INSERT INTO student_skills (student_profile_id, skill_id, proficiency) VALUES
(2, 2, 'advanced'),      -- Python
(2, 14, 'intermediate'), -- MySQL
(2, 15, 'intermediate'), -- SQL
(2, 26, 'intermediate'), -- Pandas
(2, 27, 'beginner'),     -- NumPy
(2, 29, 'beginner'),     -- Data Visualization
(2, 17, 'intermediate'), -- Data Structures
(2, 18, 'beginner');     -- Algorithms

-- Student Skills for Rahul (profile 3)
INSERT INTO student_skills (student_profile_id, skill_id, proficiency) VALUES
(3, 6, 'advanced'),      -- HTML
(3, 7, 'advanced'),      -- CSS
(3, 5, 'advanced'),      -- JavaScript
(3, 8, 'advanced'),      -- PHP
(3, 14, 'advanced'),     -- MySQL
(3, 9, 'intermediate'),  -- React
(3, 10, 'intermediate'), -- Node.js
(3, 21, 'advanced'),     -- Git
(3, 22, 'advanced'),     -- GitHub
(3, 1, 'intermediate'),  -- Java
(3, 2, 'intermediate'),  -- Python
(3, 13, 'intermediate'), -- REST APIs
(3, 17, 'advanced'),     -- Data Structures
(3, 18, 'intermediate'); -- Algorithms

-- -----------------------------------------------
-- Sample Projects
-- -----------------------------------------------
INSERT INTO projects (student_profile_id, title, description, technologies, project_url, start_date, end_date) VALUES
(1, 'Personal Portfolio Website', 'A responsive personal portfolio showcasing projects and skills.', 'HTML, CSS, JavaScript', 'https://aarav-portfolio.example', '2026-01-15', '2026-03-01'),
(1, 'Student Grade Calculator', 'A PHP web app to calculate and display student grades.', 'PHP, MySQL, HTML, CSS', NULL, '2026-04-01', '2026-05-15'),
(3, 'E-Commerce Dashboard', 'A full-stack admin dashboard with charts, product management, and order tracking.', 'React, Node.js, MySQL', 'https://rahul-ecommerce.example', '2026-02-01', '2026-06-30');

-- -----------------------------------------------
-- Sample Certificates
-- -----------------------------------------------
INSERT INTO certificates (student_profile_id, title, issuing_org, issue_date, credential_url) VALUES
(1, 'Web Development Fundamentals', 'Sample Academy (Demo)', '2026-06-01', 'https://cert.example/web-fundamentals'),
(2, 'Python for Data Science', 'Sample Academy (Demo)', '2026-05-15', 'https://cert.example/python-ds'),
(3, 'Full Stack Development', 'Sample Academy (Demo)', '2026-04-01', 'https://cert.example/full-stack');

-- -----------------------------------------------
-- Sample Internships (clearly labeled as sample/demo data)
-- -----------------------------------------------
INSERT INTO internships (recruiter_profile_id, title, description, responsibilities, eligibility, eligible_degrees, eligible_branches, min_graduation_year, max_graduation_year, location, work_mode, stipend_min, stipend_max, is_paid, duration, openings, application_deadline, status, career_category, is_sample) VALUES
(1, 'Frontend Developer Intern',
 'Join our frontend team to build responsive user interfaces. This is SAMPLE data for demonstration purposes only.',
 'Build responsive UI components\nCollaborate with designers\nWrite clean HTML/CSS/JS\nParticipate in code reviews',
 'Currently pursuing B.Tech/BCA/MCA in CS/IT. Good understanding of web fundamentals.',
 'B.Tech, BCA, MCA', 'CSE, IT', 2026, 2028,
 'Bangalore', 'hybrid', 15000, 20000, 1, '3 months', 3,
 '2027-03-31', 'active', 'Web Development', 1),

(1, 'Backend Developer Intern (PHP)',
 'Work on server-side applications using PHP and MySQL. This is SAMPLE data for demonstration purposes only.',
 'Develop RESTful APIs\nWrite database queries\nPerform code testing\nDocument features',
 'B.Tech/BCA/MCA students with knowledge of PHP and databases.',
 'B.Tech, BCA, MCA', 'CSE, IT', 2026, 2028,
 'Bangalore', 'onsite', 18000, 25000, 1, '6 months', 2,
 '2027-04-15', 'active', 'Web Development', 1),

(2, 'Data Analyst Intern',
 'Analyze datasets and create visualizations for business insights. This is SAMPLE data for demonstration purposes only.',
 'Clean and preprocess data\nCreate dashboards and reports\nPerform statistical analysis\nPresent findings to team',
 'B.Tech/B.Sc students with knowledge of Python and basic statistics.',
 'B.Tech, B.Sc', 'CSE, IT, Statistics', 2026, 2028,
 'Hyderabad', 'remote', 12000, 18000, 1, '3 months', 2,
 '2027-02-28', 'active', 'Data Science', 1),

(2, 'Full Stack Developer Intern',
 'Build complete web applications from frontend to backend. This is SAMPLE data for demonstration purposes only.',
 'Design database schemas\nBuild frontend interfaces\nDevelop backend APIs\nDeploy and test applications',
 'B.Tech students with strong web development fundamentals.',
 'B.Tech', 'CSE, IT', 2025, 2027,
 'Hyderabad', 'hybrid', 20000, 30000, 1, '6 months', 1,
 '2027-05-30', 'active', 'Web Development', 1),

(1, 'Java Developer Intern',
 'Develop enterprise applications using Java. This is SAMPLE data for demonstration purposes only.',
 'Write Java applications\nWork with Spring Boot basics\nCreate unit tests\nCollaborate with senior developers',
 'B.Tech/MCA students with good Java and OOP knowledge.',
 'B.Tech, MCA', 'CSE, IT', 2026, 2028,
 'Bangalore', 'onsite', 20000, 25000, 1, '4 months', 2,
 '2027-04-30', 'active', 'Software Development', 1);

-- -----------------------------------------------
-- Internship Skills (required skills for each internship)
-- -----------------------------------------------
-- Frontend Developer Intern (internship 1)
INSERT INTO internship_skills (internship_id, skill_id, importance, min_proficiency) VALUES
(1, 6, 'required', 'intermediate'),    -- HTML
(1, 7, 'required', 'intermediate'),    -- CSS
(1, 5, 'required', 'intermediate'),    -- JavaScript
(1, 9, 'preferred', 'beginner'),       -- React
(1, 21, 'required', 'beginner'),       -- Git
(1, 11, 'nice_to_have', 'beginner');   -- Bootstrap

-- Backend Developer Intern (internship 2)
INSERT INTO internship_skills (internship_id, skill_id, importance, min_proficiency) VALUES
(2, 8, 'required', 'intermediate'),    -- PHP
(2, 14, 'required', 'intermediate'),   -- MySQL
(2, 15, 'required', 'beginner'),       -- SQL
(2, 13, 'preferred', 'beginner'),      -- REST APIs
(2, 21, 'required', 'beginner'),       -- Git
(2, 5, 'nice_to_have', 'beginner');    -- JavaScript

-- Data Analyst Intern (internship 3)
INSERT INTO internship_skills (internship_id, skill_id, importance, min_proficiency) VALUES
(3, 2, 'required', 'intermediate'),    -- Python
(3, 15, 'required', 'intermediate'),   -- SQL
(3, 26, 'required', 'beginner'),       -- Pandas
(3, 27, 'preferred', 'beginner'),      -- NumPy
(3, 29, 'required', 'beginner'),       -- Data Visualization
(3, 28, 'nice_to_have', 'beginner');   -- ML Basics

-- Full Stack Developer Intern (internship 4)
INSERT INTO internship_skills (internship_id, skill_id, importance, min_proficiency) VALUES
(4, 6, 'required', 'advanced'),        -- HTML
(4, 7, 'required', 'advanced'),        -- CSS
(4, 5, 'required', 'advanced'),        -- JavaScript
(4, 8, 'required', 'intermediate'),    -- PHP
(4, 14, 'required', 'intermediate'),   -- MySQL
(4, 9, 'preferred', 'intermediate'),   -- React
(4, 13, 'preferred', 'beginner'),      -- REST APIs
(4, 21, 'required', 'intermediate');   -- Git

-- Java Developer Intern (internship 5)
INSERT INTO internship_skills (internship_id, skill_id, importance, min_proficiency) VALUES
(5, 1, 'required', 'intermediate'),    -- Java
(5, 17, 'required', 'intermediate'),   -- Data Structures
(5, 18, 'preferred', 'beginner'),      -- Algorithms
(5, 21, 'required', 'beginner'),       -- Git
(5, 14, 'nice_to_have', 'beginner');   -- MySQL

-- -----------------------------------------------
-- Sample Assessments
-- -----------------------------------------------
INSERT INTO assessments (skill_id, title, description, time_limit_minutes, pass_percentage) VALUES
(6, 'HTML Fundamentals Assessment', 'Test your knowledge of HTML5 elements, attributes, and semantics. Educational content only.', 20, 60),
(5, 'JavaScript Basics Assessment', 'Test your understanding of JavaScript fundamentals. Educational content only.', 25, 60),
(8, 'PHP Basics Assessment', 'Test your knowledge of PHP syntax, functions, and database interaction. Educational content only.', 25, 60);

-- -----------------------------------------------
-- Sample Assessment Questions (educational content)
-- -----------------------------------------------
-- HTML Assessment (assessment 1)
INSERT INTO assessment_questions (assessment_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, topic) VALUES
(1, 'Which HTML element is used to define the largest heading?', '<h6>', '<heading>', '<h1>', '<head>', 'c', 'The <h1> element defines the largest (most important) heading. Headings range from h1 (largest) to h6 (smallest).', 'easy', 'HTML Elements'),
(1, 'Which attribute specifies an alternate text for an image?', 'title', 'alt', 'src', 'href', 'b', 'The alt attribute provides alternative text for an image if it cannot be displayed.', 'easy', 'HTML Attributes'),
(1, 'What is the correct HTML element for inserting a line break?', '<break>', '<lb>', '<br>', '<newline>', 'c', 'The <br> element inserts a single line break. It is an empty element.', 'easy', 'HTML Elements'),
(1, 'Which HTML element is used to define an unordered list?', '<ol>', '<ul>', '<list>', '<dl>', 'b', 'The <ul> element defines an unordered (bulleted) list. <ol> is for ordered lists.', 'easy', 'HTML Lists'),
(1, 'What does the <DOCTYPE html> declaration do?', 'Defines the document title', 'Links to a CSS file', 'Tells the browser to use HTML5', 'Creates a paragraph', 'c', 'The DOCTYPE declaration tells the browser which version of HTML the page is written in.', 'medium', 'HTML Basics');

-- JavaScript Assessment (assessment 2)
INSERT INTO assessment_questions (assessment_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, topic) VALUES
(2, 'Which keyword declares a block-scoped variable in JavaScript?', 'var', 'let', 'define', 'dim', 'b', 'let declares a block-scoped variable. var is function-scoped and define/dim are not JS keywords.', 'easy', 'Variables'),
(2, 'What does === check in JavaScript?', 'Value only', 'Type only', 'Value and type', 'Reference', 'c', 'The strict equality operator (===) checks both value and type without type coercion.', 'easy', 'Operators'),
(2, 'Which method adds an element to the end of an array?', 'push()', 'append()', 'add()', 'insert()', 'a', 'The push() method adds one or more elements to the end of an array.', 'easy', 'Arrays'),
(2, 'What is the output of typeof null?', 'null', 'undefined', 'object', 'boolean', 'c', 'typeof null returns "object" due to a historical bug in JavaScript.', 'medium', 'Data Types'),
(2, 'Which function converts a JSON string to a JavaScript object?', 'JSON.stringify()', 'JSON.parse()', 'JSON.convert()', 'JSON.toObject()', 'b', 'JSON.parse() parses a JSON string and returns the corresponding JavaScript object.', 'medium', 'JSON');

-- PHP Assessment (assessment 3)
INSERT INTO assessment_questions (assessment_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, topic) VALUES
(3, 'How do you start a PHP code block?', '<?php', '<script php>', '<? php', '<php>', 'a', 'PHP code starts with <?php and ends with ?>. The opening tag <?php is required.', 'easy', 'PHP Basics'),
(3, 'Which superglobal contains form data sent via POST?', '$_GET', '$_POST', '$_REQUEST', '$_FORM', 'b', '$_POST is a superglobal array that contains data sent via the HTTP POST method.', 'easy', 'Forms'),
(3, 'Which function hashes a password securely in PHP?', 'md5()', 'sha1()', 'password_hash()', 'crypt()', 'c', 'password_hash() uses bcrypt by default and is the recommended way to hash passwords in PHP.', 'medium', 'Security'),
(3, 'What does PDO stand for?', 'PHP Database Objects', 'PHP Data Objects', 'PHP Data Operations', 'PHP Database Operations', 'b', 'PDO (PHP Data Objects) is a database access abstraction layer for PHP.', 'medium', 'Database'),
(3, 'Which function prevents SQL injection when using PDO?', 'mysql_real_escape_string()', 'htmlspecialchars()', 'Prepared statements', 'strip_tags()', 'c', 'Prepared statements with bound parameters prevent SQL injection by separating SQL logic from data.', 'medium', 'Security');

-- -----------------------------------------------
-- Sample Learning Tasks
-- -----------------------------------------------
-- Learning tasks for React (skill 9)
INSERT INTO learning_tasks (skill_id, title, description, week_number, task_order, estimated_hours) VALUES
(9, 'Introduction to React', 'Learn what React is, why it is used, and set up a basic React project.', 1, 1, 3),
(9, 'Components and Props', 'Understand functional components, JSX syntax, and how to pass data via props.', 1, 2, 4),
(9, 'State and Event Handling', 'Learn useState hook, event handlers, and interactive components.', 2, 1, 5),
(9, 'Build a To-Do App', 'Create a small to-do list application to practice state management.', 2, 2, 6),
(9, 'React Router Basics', 'Learn client-side routing with React Router.', 3, 1, 3),
(9, 'Build a Portfolio Project', 'Create and document a portfolio-quality React project for your profile.', 3, 2, 8);

-- Learning tasks for Python (skill 2)
INSERT INTO learning_tasks (skill_id, title, description, week_number, task_order, estimated_hours) VALUES
(2, 'Python Setup and Basics', 'Install Python, learn variables, data types, and basic I/O.', 1, 1, 3),
(2, 'Control Flow and Functions', 'Learn if/else, loops, and function definitions.', 1, 2, 4),
(2, 'Data Structures in Python', 'Learn lists, dictionaries, tuples, and sets.', 2, 1, 5),
(2, 'File Handling and Modules', 'Read/write files, import modules, handle exceptions.', 2, 2, 4),
(2, 'Build a Python Project', 'Create a small utility project demonstrating Python skills.', 3, 1, 6);

-- Learning tasks for Data Structures (skill 17)
INSERT INTO learning_tasks (skill_id, title, description, week_number, task_order, estimated_hours) VALUES
(17, 'Arrays and Linked Lists', 'Understand array operations and linked list implementations.', 1, 1, 5),
(17, 'Stacks and Queues', 'Implement stack and queue using arrays and linked lists.', 1, 2, 4),
(17, 'Trees and Binary Search Trees', 'Learn tree traversals and BST operations.', 2, 1, 6),
(17, 'Sorting Algorithms', 'Implement bubble, selection, merge, and quick sort.', 2, 2, 5),
(17, 'Practice Problems', 'Solve 10 data structure problems on practice platforms.', 3, 1, 8);

-- -----------------------------------------------
-- Sample Applications
-- -----------------------------------------------
INSERT INTO applications (student_profile_id, internship_id, status, cover_note) VALUES
(1, 1, 'under_review', 'I am passionate about frontend development and have built several projects using HTML, CSS, and JavaScript.'),
(3, 4, 'shortlisted', 'I have strong full-stack experience with PHP, MySQL, React, and Node.js.');

-- -----------------------------------------------
-- Sample Saved Internships
-- -----------------------------------------------
INSERT INTO saved_internships (student_profile_id, internship_id) VALUES
(1, 2),
(1, 4),
(2, 3);

-- ============================================================
-- NOTE: All sample data above is fictional and created for
-- educational demonstration purposes only. Company names,
-- internship listings, and user profiles are not real.
-- The password hash used above corresponds to 'password' and
-- should be changed in any non-demo environment.
-- ============================================================

