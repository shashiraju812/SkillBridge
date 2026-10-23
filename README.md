# SkillBridge — Student Skills & Internship Matching Platform
## Web Technologies Lab (CS351) Major Project & Practical Portfolio

[![PHP 8](https://img.shields.io/badge/PHP-8.x-777bb4.svg)](https://www.php.net/)
[![MySQL 8](https://img.shields.io/badge/MySQL-8.0-4479a1.svg)](https://www.mysql.com/)
[![Java 17](https://img.shields.io/badge/Java-17-orange.svg)](https://www.oracle.com/java/)
[![Apache Tomcat](https://img.shields.io/badge/Apache-Tomcat-yellow.svg)](https://tomcat.apache.org/)

SkillBridge is a web application designed to connect student qualifications with internship listings using an explainable, transparent skill-matching engine, automated skill-gap analysis, personal learning roadmaps, and end-to-end application tracking.

---

## 🚀 Quick Setup Instructions (XAMPP Local Development)

### 1. Place Project in XAMPP
Copy the `wtproject` folder into your XAMPP web root directory:
```
C:\xampp\htdocs\wtproject
```

### 2. Start Services
Open the **XAMPP Control Panel** and start:
- **Apache** (Web Server)
- **MySQL** (Database Server)

### 3. Import MySQL Database Schema
1. Open your browser and navigate to `http://localhost/phpmyadmin`
2. Click **New** and create a database named `skillbridge` (or click **Import** directly)
3. Select and import `database/skillbridge.sql` located inside the project:
   ```
   C:\xampp\htdocs\wtproject\database\skillbridge.sql
   ```
4. All 19 normalized tables and educational seed sample data will be created automatically.

### 4. Verify Configuration
Open `config/database.php` and verify your local MySQL credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'skillbridge');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP password is empty
```

### 5. Access the Platform
Open your browser and visit:
```
http://localhost/wtproject/
```

---

## 👥 Demo Logins (Pre-seeded Accounts)

All demo accounts use the standard password: `password`

| Role | Email Address | Password | Features Accessible |
|---|---|---|---|
| **Student** | `aarav@student.local` | `password` | Profile, Skills, Assessments, Matching, Gap Analysis, Learning Roadmap, Applications |
| **Student (Alternative)** | `rahul@student.local` | `password` | Advanced student with full-stack skills |
| **Recruiter** | `hr@techcorp.local` | `password` | Post internships, define required skills, review applicants with match breakdown |
| **Administrator** | `admin@skillbridge.local` | `password` | Manage all users, moderate listings, skill catalogue, mock assessments, analytics |

---

## 🔬 Web Technologies Lab Practicals Index (Practicals 1–15)

Open `http://localhost/wtproject/practicals/index.php` in your browser for the full lab checklist and live interactive demos.

| Practical # | Topic / Lab Aim | Implementation File(s) | Execution Mode |
|---|---|---|---|
| **Practical 1** | HTML Registration Form with Controls | `practicals/practical1.html` | Open directly in browser |
| **Practical 2** | HTML Frameset & Menu Layout | `practicals/practical2_frameset.html` | Open directly in browser |
| **Practical 3** | CSS & Interactive Selection Controls | `practicals/practical3.html` | Open directly in browser |
| **Practical 4** | JavaScript Number to Words (0–999) | `assets/js/app.js` (`numberToWords`) | Live on `practicals/index.php` |
| **Practical 5** | JavaScript Regular Expressions | `assets/js/app.js` (`validateField`) | Live on `practicals/index.php` |
| **Practical 6** | XML, DTD and XSD Schema Validation | `xml/student-profiles.xml`, `xml/student-profile.xsd` | Run `java-practicals/xml/XMLValidator.java` |
| **Practical 7A** | Java DOM Parser (Retrieve student by ID) | `java-practicals/dom/DOMParserDemo.java` | `java -cp java-practicals dom.DOMParserDemo 101` |
| **Practical 7B** | Java SAX Parser (Event-driven XML reader) | `java-practicals/sax/SAXParserDemo.java` | `java -cp java-practicals sax.SAXParserDemo 103` |
| **Practical 8** | PHP Registration with MySQL | `register.php`, `config/database.php` | In web app: `/wtproject/register.php` |
| **Practical 9** | PHP Authentication & Sessions | `login.php`, `includes/auth.php` | In web app: `/wtproject/login.php` |
| **Practical 10**| Tomcat Hello World Servlet | `java-practicals/servlet/HelloServlet.java` | Deploy to Tomcat: `/hello` |
| **Practical 11**| Servlet Registration with MySQL | `java-practicals/servlet/RegisterServlet.java`| Deploy to Tomcat: `/register-servlet` |
| **Practical 12**| Servlet Authentication | `java-practicals/servlet/LoginServlet.java` | Deploy to Tomcat: `/login-servlet` |
| **Practical 13**| Servlet Session Techniques (Cookies, Hidden, HttpSession, URL Rewriting) | `java-practicals/servlet/SessionTechniquesServlet.java` | Deploy to Tomcat: `/session-demo` |
| **Practical 14**| JSP Registration with JavaBean & DB | `java-practicals/jsp/register.jsp`, `beans/UserBean.java` | Deploy to Tomcat: `register.jsp` |
| **Practical 15**| JSP Authentication & Protected Pages | `java-practicals/jsp/login.jsp`, `welcome.jsp` | Deploy to Tomcat: `login.jsp` |

---

## 📁 Project Directory Structure

```
wtproject/
├── index.php                      # Public Landing Page
├── about.php                      # About Platform
├── how-it-works.php               # Workflow & Algorithm Guide
├── explore.php                    # Public Internship Explorer
├── internship-details.php         # Full Details & Match View
├── register.php                   # Student Registration (PHP + PDO)
├── register-recruiter.php         # Recruiter Registration
├── login.php                      # Unified Authentication (BCrypt + Session)
├── logout.php                     # Secure Session Termination
├── contact.php                    # Contact & Feedback Form
│
├── config/
│   └── database.php               # PDO MySQL Connection Singleton
├── includes/
│   ├── auth.php                   # Role-Based Access Control Guards
│   ├── csrf.php                   # CSRF Token Generation & Validation
│   ├── functions.php              # Matching Algorithm, Gap Analysis & Helpers
│   ├── header.php                 # Global Header & Navigation
│   └── footer.php                 # Global Footer
├── assets/
│   ├── css/style.css              # Custom Responsive CSS3 Design System
│   └── js/app.js                  # Client Validation, AJAX & Practicals 4 & 5
│
├── student/                       # Student Portal (15 Pages)
│   ├── dashboard.php              # Profile Completion & Stat Summary
│   ├── profile.php                # Personal Information Editor
│   ├── education.php              # Degree & Graduation Year Details
│   ├── skills.php                 # Skill Catalogue & Proficiency Manager
│   ├── projects.php               # Projects & Certification Portfolio
│   ├── resume.php                 # Secure PDF Resume Upload & Download
│   ├── assessment.php             # MCQ Skill Assessment Test
│   ├── assessment-results.php     # Evaluation & Weak-Topic Analysis
│   ├── internships.php            # Ranked Internship Recommendations
│   ├── internship-match.php       # Detailed Requirement vs Profile Comparison
│   ├── skill-gap.php              # Skill Gap Breakdown (Matched / Weak / Missing)
│   ├── learning-roadmap.php       # Week-by-Week Milestone Learning Tasks
│   ├── saved-internships.php      # Bookmarked Listings
│   ├── applications.php           # Status Tracker (Under Review, Selected, etc.)
│   ├── settings.php               # Password & Email Settings
│   └── sidebar.php                # Navigation Component
│
├── recruiter/                     # Recruiter Portal (7 Pages)
│   ├── dashboard.php              # Listings & Application Pipeline Stats
│   ├── company-profile.php        # Company Profile Editor
│   ├── create-internship.php      # Listing Generator with Skill Weights
│   ├── edit-internship.php        # Listing Modification
│   ├── manage-internships.php     # Activate / Close Listings
│   ├── applicants.php             # Candidate List with Match Scores
│   ├── candidate-details.php      # Candidate Profile & Status Updater
│   └── sidebar.php                # Navigation Component
│
├── admin/                         # Administrator Portal (7 Pages)
│   ├── dashboard.php              # High-Level Metric Cards
│   ├── users.php                  # User Status & Role Governance
│   ├── internships.php            # Listing Moderation & Removal
│   ├── skills.php                 # Skill Category & Item CRUD
│   ├── assessments.php            # Question Bank & Assessment Creator
│   ├── reports.php                # Flagged Listing Review
│   ├── statistics.php             # Quantitative Charts & Metrics
│   └── sidebar.php                # Navigation Component
│
├── api/                           # Secure RESTful AJAX Endpoints
│   ├── save-internship.php        # Toggle Bookmark
│   ├── apply.php                  # Submit Application
│   ├── update-status.php          # Recruiter Decision Update
│   └── learning-progress.php     # Learning Milestone Checkpoint
│
├── database/
│   └── skillbridge.sql            # Normalized DDL (19 Tables) + Seed Data
├── xml/
│   ├── student-profiles.xml       # 10 Sample Student Profiles
│   ├── student-profile.dtd        # DTD Validation Schema
│   └── student-profile.xsd        # XML Schema Definition
├── java-practicals/
│   ├── dom/DOMParserDemo.java     # DOM Parser Implementation
│   ├── sax/SAXParserDemo.java     # SAX Parser Implementation
│   ├── xml/XMLValidator.java      # XSD Validator Engine
│   ├── servlet/                   # Servlets (Hello, Register, Login, Session)
│   ├── jsp/                       # JSP Pages & UserBean.java
│   └── WEB-INF/web.xml            # Deployment Descriptor
└── practicals/
    ├── index.php                  # Interactive Practicals Index
    ├── practical1.html            # Standalone HTML Form
    ├── practical2_frameset.html   # Frameset Demonstration
    └── practical3.html            # CSS Controls Demo
```

---

## 🔒 Security Best Practices Implemented

- **Prepared SQL Statements (PDO)**: Zero string concatenation in database queries prevents SQL Injection.
- **BCrypt Password Hashing**: Strong cryptographic hashing with salt via `password_hash()`.
- **Session Fixation Defense**: `session_regenerate_id(true)` called upon login.
- **CSRF Tokens**: Cryptographic per-session tokens required on all POST form submissions and API calls.
- **XSS Prevention**: Strict HTML entity escaping via `htmlspecialchars()` helper `e()`.
- **Server-Side Access Control**: Every dashboard page enforces role authorization on the server, not just in UI links.
- **File Upload Validation**: Type restriction (`application/pdf`), size restriction (2MB max), and randomized filename renaming.

---

## 📜 Academic Integrity Note
SkillBridge was developed for educational evaluation in the **B.Tech Web Technologies Laboratory**. Sample internship listings, student records, and company profiles are fictional representations created to demonstrate full-stack software architecture principles.

