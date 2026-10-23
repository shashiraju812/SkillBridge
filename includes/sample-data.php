<?php
/**
 * Sample Internship & Educational Dataset — SkillBridge
 * 
 * IMPORTANT ACADEMIC NOTICE:
 * All entries in this file are sample educational mockups designed
 * strictly for laboratory demonstration and UI/UX testing in Stage 2.
 * They do NOT represent active company vacancies or real employment offers.
 */

function getSampleStats(): array {
    return [
        'student_count'    => 120,
        'internship_count' => 6,
        'skills_count'     => 32,
    ];
}

function getSampleSkills(): array {
    return [
        'Programming Languages' => ['Java', 'Python', 'C', 'C++', 'JavaScript'],
        'Web Development'       => ['HTML', 'CSS', 'PHP', 'React', 'Node.js', 'Bootstrap', 'REST APIs'],
        'Database'              => ['MySQL', 'SQL', 'MongoDB', 'Database Design'],
        'CS Fundamentals'       => ['Data Structures', 'Algorithms', 'Operating Systems', 'Computer Networks'],
        'Development Tools'     => ['Git', 'GitHub', 'Docker', 'Linux', 'VS Code'],
        'Data Science'          => ['Pandas', 'NumPy', 'Machine Learning Basics', 'Data Visualization'],
        'Soft Skills'           => ['Communication', 'Teamwork', 'Problem Solving', 'Time Management'],
    ];
}

function getSampleInternships(): array {
    return [
        1 => [
            'id' => 1,
            'title' => 'Frontend Developer Intern',
            'company_name' => 'TechCorp Solutions',
            'company_website' => 'https://techcorp-demo.example',
            'industry' => 'Information Technology',
            'location' => 'Bangalore',
            'work_mode' => 'hybrid',
            'career_category' => 'Web Development',
            'duration' => '3 months',
            'openings' => 3,
            'stipend_min' => 15000,
            'stipend_max' => 20000,
            'is_paid' => 1,
            'application_deadline' => date('Y-m-d', strtotime('+45 days')),
            'status' => 'active',
            'is_sample' => true,
            'description' => "SAMPLE LISTING FOR LAB DEMONSTRATION:\nJoin our user interface team to construct responsive web components, collaborate with product designers, and refine frontend code for web applications.",
            'responsibilities' => "• Develop modular UI components using modern HTML5, CSS3, and JavaScript.\n• Coordinate with UX design team on mockups and usability guidelines.\n• Ensure cross-browser responsiveness and accessibility standards.\n• Write structured code and participate in team reviews.",
            'eligibility' => "Currently enrolled in B.Tech / BCA / MCA in Computer Science or Information Technology with graduation between 2026 and 2028.",
            'eligible_degrees' => 'B.Tech, BCA, MCA',
            'eligible_branches' => 'CSE, IT',
            'min_graduation_year' => 2026,
            'max_graduation_year' => 2028,
            'required_skills' => [
                ['name' => 'HTML', 'importance' => 'required', 'min_proficiency' => 'intermediate'],
                ['name' => 'CSS', 'importance' => 'required', 'min_proficiency' => 'intermediate'],
                ['name' => 'JavaScript', 'importance' => 'required', 'min_proficiency' => 'intermediate'],
                ['name' => 'React', 'importance' => 'preferred', 'min_proficiency' => 'beginner'],
                ['name' => 'Git', 'importance' => 'required', 'min_proficiency' => 'beginner'],
                ['name' => 'Bootstrap', 'importance' => 'nice_to_have', 'min_proficiency' => 'beginner'],
            ]
        ],
        2 => [
            'id' => 2,
            'title' => 'Backend Developer Intern (PHP / MySQL)',
            'company_name' => 'TechCorp Solutions',
            'company_website' => 'https://techcorp-demo.example',
            'industry' => 'Information Technology',
            'location' => 'Bangalore',
            'work_mode' => 'onsite',
            'career_category' => 'Web Development',
            'duration' => '6 months',
            'openings' => 2,
            'stipend_min' => 18000,
            'stipend_max' => 25000,
            'is_paid' => 1,
            'application_deadline' => date('Y-m-d', strtotime('+60 days')),
            'status' => 'active',
            'is_sample' => true,
            'description' => "SAMPLE LISTING FOR LAB DEMONSTRATION:\nWork alongside senior backend developers building server-side REST APIs, architecting relational MySQL queries, and securing web endpoints.",
            'responsibilities' => "• Implement clean server-side application logic using PHP.\n• Formulate optimized database queries using PDO prepared statements.\n• Design RESTful JSON services consumed by web and mobile clients.\n• Write automated unit test scripts and integration tests.",
            'eligibility' => "B.Tech/MCA students with academic exposure to relational databases and server-side programming.",
            'eligible_degrees' => 'B.Tech, MCA',
            'eligible_branches' => 'CSE, IT',
            'min_graduation_year' => 2026,
            'max_graduation_year' => 2028,
            'required_skills' => [
                ['name' => 'PHP', 'importance' => 'required', 'min_proficiency' => 'intermediate'],
                ['name' => 'MySQL', 'importance' => 'required', 'min_proficiency' => 'intermediate'],
                ['name' => 'SQL', 'importance' => 'required', 'min_proficiency' => 'beginner'],
                ['name' => 'REST APIs', 'importance' => 'preferred', 'min_proficiency' => 'beginner'],
                ['name' => 'Git', 'importance' => 'required', 'min_proficiency' => 'beginner'],
                ['name' => 'JavaScript', 'importance' => 'nice_to_have', 'min_proficiency' => 'beginner'],
            ]
        ],
        3 => [
            'id' => 3,
            'title' => 'Data Analyst Intern',
            'company_name' => 'InnoSoft Labs',
            'company_website' => 'https://innosoft-demo.example',
            'industry' => 'Data Science & Software',
            'location' => 'Hyderabad',
            'work_mode' => 'remote',
            'career_category' => 'Data Science',
            'duration' => '3 months',
            'openings' => 2,
            'stipend_min' => 12000,
            'stipend_max' => 18000,
            'is_paid' => 1,
            'application_deadline' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'active',
            'is_sample' => true,
            'description' => "SAMPLE LISTING FOR LAB DEMONSTRATION:\nAssist in extracting business metrics, cleaning tabular datasets, and preparing interactive charts and data visualizations.",
            'responsibilities' => "• Clean and preprocess large CSV and SQL datasets using Python.\n• Construct visual dashboards using charting libraries.\n• Perform exploratory data analysis and summarize statistical patterns.\n• Present findings to engineering and product stakeholders.",
            'eligibility' => "Undergraduate or postgraduate students pursuing B.Tech, B.Sc, or BCA with Python scripting skills.",
            'eligible_degrees' => 'B.Tech, B.Sc, BCA',
            'eligible_branches' => 'CSE, IT, Statistics, Mathematics',
            'min_graduation_year' => 2026,
            'max_graduation_year' => 2028,
            'required_skills' => [
                ['name' => 'Python', 'importance' => 'required', 'min_proficiency' => 'intermediate'],
                ['name' => 'SQL', 'importance' => 'required', 'min_proficiency' => 'intermediate'],
                ['name' => 'Pandas', 'importance' => 'required', 'min_proficiency' => 'beginner'],
                ['name' => 'NumPy', 'importance' => 'preferred', 'min_proficiency' => 'beginner'],
                ['name' => 'Data Visualization', 'importance' => 'required', 'min_proficiency' => 'beginner'],
            ]
        ],
        4 => [
            'id' => 4,
            'title' => 'Full Stack Developer Intern',
            'company_name' => 'InnoSoft Labs',
            'company_website' => 'https://innosoft-demo.example',
            'industry' => 'Software Engineering',
            'location' => 'Hyderabad',
            'work_mode' => 'hybrid',
            'career_category' => 'Web Development',
            'duration' => '6 months',
            'openings' => 1,
            'stipend_min' => 20000,
            'stipend_max' => 30000,
            'is_paid' => 1,
            'application_deadline' => date('Y-m-d', strtotime('+75 days')),
            'status' => 'active',
            'is_sample' => true,
            'description' => "SAMPLE LISTING FOR LAB DEMONSTRATION:\nContribute to end-to-end full stack web architecture including frontend interface design, server-side APIs, and relational database schema design.",
            'responsibilities' => "• Develop responsive customer-facing web screens.\n• Connect client forms to backend REST endpoints.\n• Design normalized relational database schemas.\n• Collaborate on version control workflows via Git and GitHub.",
            'eligibility' => "Pre-final or final-year B.Tech students with demonstrable projects in web technologies.",
            'eligible_degrees' => 'B.Tech',
            'eligible_branches' => 'CSE, IT',
            'min_graduation_year' => 2025,
            'max_graduation_year' => 2027,
            'required_skills' => [
                ['name' => 'HTML', 'importance' => 'required', 'min_proficiency' => 'advanced'],
                ['name' => 'CSS', 'importance' => 'required', 'min_proficiency' => 'advanced'],
                ['name' => 'JavaScript', 'importance' => 'required', 'min_proficiency' => 'advanced'],
                ['name' => 'PHP', 'importance' => 'required', 'min_proficiency' => 'intermediate'],
                ['name' => 'MySQL', 'importance' => 'required', 'min_proficiency' => 'intermediate'],
                ['name' => 'React', 'importance' => 'preferred', 'min_proficiency' => 'intermediate'],
                ['name' => 'Git', 'importance' => 'required', 'min_proficiency' => 'intermediate'],
            ]
        ],
        5 => [
            'id' => 5,
            'title' => 'Java Developer Intern',
            'company_name' => 'CloudPinnacle Systems',
            'company_website' => 'https://cloudpinnacle-demo.example',
            'industry' => 'Enterprise Cloud Solutions',
            'location' => 'Pune',
            'work_mode' => 'onsite',
            'career_category' => 'Software Development',
            'duration' => '4 months',
            'openings' => 2,
            'stipend_min' => 20000,
            'stipend_max' => 25000,
            'is_paid' => 1,
            'application_deadline' => date('Y-m-d', strtotime('+50 days')),
            'status' => 'active',
            'is_sample' => true,
            'description' => "SAMPLE LISTING FOR LAB DEMONSTRATION:\nDevelop object-oriented Java modules, implement standard algorithms, and build enterprise backend microservices.",
            'responsibilities' => "• Write robust, object-oriented Java code adhering to clean architecture standards.\n• Develop and run unit tests for core computational algorithms.\n• Interact with SQL databases using JDBC / ORM components.\n• Work with senior engineering mentors on code reviews.",
            'eligibility' => "B.Tech or MCA students with strong fundamentals in Core Java and Object-Oriented Design.",
            'eligible_degrees' => 'B.Tech, MCA',
            'eligible_branches' => 'CSE, IT',
            'min_graduation_year' => 2026,
            'max_graduation_year' => 2028,
            'required_skills' => [
                ['name' => 'Java', 'importance' => 'required', 'min_proficiency' => 'intermediate'],
                ['name' => 'Data Structures', 'importance' => 'required', 'min_proficiency' => 'intermediate'],
                ['name' => 'Algorithms', 'importance' => 'preferred', 'min_proficiency' => 'beginner'],
                ['name' => 'Git', 'importance' => 'required', 'min_proficiency' => 'beginner'],
                ['name' => 'MySQL', 'importance' => 'nice_to_have', 'min_proficiency' => 'beginner'],
            ]
        ],
        6 => [
            'id' => 6,
            'title' => 'UI/UX & Web Design Intern',
            'company_name' => 'PixelCraft Media',
            'company_website' => 'https://pixelcraft-demo.example',
            'industry' => 'Digital Design Agency',
            'location' => 'Mumbai',
            'work_mode' => 'remote',
            'career_category' => 'Design & Frontend',
            'duration' => '3 months',
            'openings' => 2,
            'stipend_min' => 14000,
            'stipend_max' => 18000,
            'is_paid' => 1,
            'application_deadline' => date('Y-m-d', strtotime('+35 days')),
            'status' => 'active',
            'is_sample' => true,
            'description' => "SAMPLE LISTING FOR LAB DEMONSTRATION:\nTurn wireframes into accessible, high-performance HTML/CSS layouts with mobile-first responsive design.",
            'responsibilities' => "• Translate design prototypes into clean, standards-compliant HTML5 and CSS3.\n• Design responsive web screens utilizing CSS Flexbox and Grid layouts.\n• Test accessibility (WCAG) and ensure cross-device consistency.\n• Assist in building component design systems and style guides.",
            'eligibility' => "Creative undergraduate students with a passion for web design, CSS styling, and user experience.",
            'eligible_degrees' => 'B.Tech, BCA, B.Sc',
            'eligible_branches' => 'Any discipline',
            'min_graduation_year' => 2026,
            'max_graduation_year' => 2029,
            'required_skills' => [
                ['name' => 'HTML', 'importance' => 'required', 'min_proficiency' => 'advanced'],
                ['name' => 'CSS', 'importance' => 'required', 'min_proficiency' => 'advanced'],
                ['name' => 'Bootstrap', 'importance' => 'preferred', 'min_proficiency' => 'intermediate'],
                ['name' => 'JavaScript', 'importance' => 'preferred', 'min_proficiency' => 'beginner'],
                ['name' => 'Communication', 'importance' => 'required', 'min_proficiency' => 'intermediate'],
            ]
        ],
    ];
}

function getSampleInternshipById(int $id): ?array {
    $all = getSampleInternships();
    return $all[$id] ?? null;
}

