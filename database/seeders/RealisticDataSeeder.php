<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Message;
use App\Models\Review;
use App\Models\SkillRequest;
use App\Models\User;
use App\Models\UserMatch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RealisticDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->clearOldData();

        $password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // "password"

        // Create admin user
        $admin = User::firstOrCreate(
            ['Email' => 'admin@isatu.edu.ph'],
            [
                'Full_Name' => 'System Admin',
                'Password_Hash' => $password,
                'Role' => 'Admin',
                'Council' => 'Administration',
                'Bio' => 'System administrator for ISAT-U Skill Matching Platform.',
                'Avg_Rating' => 0.00,
                'Total_Completed' => 0,
                'Is_Verified' => true,
                'Created_At' => now()->subDays(120),
                'Account_Status' => 'Active',
                'Warning_Count' => 0,
                'settings' => ['dark_mode' => false],
                'Service_Modes' => ['Face-to-Face', 'Remote'],
            ]
        );

        // Realistic student users from ISAT-U
        $students = [
            // College of Engineering & Architecture (CEA)
            [
                'Full_Name' => 'Alexandria M. Santos',
                'Email' => 'alexandria.santos@isatu.edu.ph',
                'Role' => 'Student',
                'Council' => 'CEA',
                'Bio' => '3rd year BS Computer Engineering student. Strong in embedded systems, IoT, and PCB design. Can help with Arduino, Raspberry Pi, and microcontroller projects.',
                'Avg_Rating' => 4.85,
                'Total_Completed' => 14,
                'Is_Verified' => true,
            ],
            [
                'Full_Name' => 'Christian P. Dela Cruz',
                'Email' => 'christian.delacruz@isatu.edu.ph',
                'Role' => 'Student',
                'Council' => 'CEA',
                'Bio' => '4th year BS Electrical Engineering. Specializes in power systems, circuit analysis, and MATLAB/Simulink. Also does PCB layout in KiCad.',
                'Avg_Rating' => 4.90,
                'Total_Completed' => 18,
                'Is_Verified' => true,
            ],
            [
                'Full_Name' => 'Maria Teresa R. Gonzales',
                'Email' => 'maria.gonzales@isatu.edu.ph',
                'Role' => 'Student',
                'Council' => 'CEA',
                'Bio' => '2nd year BS Civil Engineering. Good at AutoCAD, structural analysis basics, and construction materials. Can tutor Statics and Mechanics of Materials.',
                'Avg_Rating' => 4.70,
                'Total_Completed' => 9,
                'Is_Verified' => true,
            ],
            [
                'Full_Name' => 'Joshua L. Reyes',
                'Email' => 'joshua.reyes@isatu.edu.ph',
                'Role' => 'Student',
                'Council' => 'CEA',
                'Bio' => '3rd year BS Mechanical Engineering. Proficient in SolidWorks, Fusion 360, and 3D printing. Can help with CAD modeling and mechanical design.',
                'Avg_Rating' => 4.65,
                'Total_Completed' => 11,
                'Is_Verified' => true,
            ],
            [
                'Full_Name' => 'Samantha A. Bautista',
                'Email' => 'samantha.bautista@isatu.edu.ph',
                'Role' => 'Student',
                'Council' => 'CEA',
                'Bio' => '4th year BS Architecture. Skilled in Revit, SketchUp, Lumion, and architectural visualization. Also does hand drafting and model making.',
                'Avg_Rating' => 4.88,
                'Total_Completed' => 16,
                'Is_Verified' => true,
            ],

            // College of Industrial Technology (CIT)
            [
                'Full_Name' => 'Rafael M. Torres',
                'Email' => 'rafael.torres@isatu.edu.ph',
                'Role' => 'Student',
                'Council' => 'CIT',
                'Bio' => 'BS Information Technology major in Network Administration. CCNA certified. Can help with network configuration, Cisco Packet Tracer, and server setup.',
                'Avg_Rating' => 4.80,
                'Total_Completed' => 13,
                'Is_Verified' => true,
            ],
            [
                'Full_Name' => 'Jennifer P. Lim',
                'Email' => 'jennifer.lim@isatu.edu.ph',
                'Role' => 'Student',
                'Council' => 'CIT',
                'Bio' => 'BSIT major in Web Development. Full-stack developer (React, Node.js, Laravel, PostgreSQL). Built 5+ capstone projects. Can mentor on system design.',
                'Avg_Rating' => 4.95,
                'Total_Completed' => 22,
                'Is_Verified' => true,
            ],
            [
                'Full_Name' => 'Mark Anthony V. Chen',
                'Email' => 'mark.chen@isatu.edu.ph',
                'Role' => 'Student',
                'Council' => 'CIT',
                'Bio' => 'BSIT major in Mobile Development. Flutter and React Native experience. Published 2 apps on Play Store. Can help with UI/UX and app architecture.',
                'Avg_Rating' => 4.75,
                'Total_Completed' => 10,
                'Is_Verified' => true,
            ],
            [
                'Full_Name' => 'Angela D. Pascual',
                'Email' => 'angela.pascual@isatu.edu.ph',
                'Role' => 'Student',
                'Council' => 'CIT',
                'Bio' => 'BSIT major in Data Analytics. Python (pandas, NumPy, scikit-learn), Tableau, Power BI. Can help with data cleaning, visualization, and ML basics.',
                'Avg_Rating' => 4.82,
                'Total_Completed' => 12,
                'Is_Verified' => true,
            ],
            [
                'Full_Name' => 'Kevin S. Mendoza',
                'Email' => 'kevin.mendoza@isatu.edu.ph',
                'Role' => 'Student',
                'Council' => 'CIT',
                'Bio' => 'BSIT major in Cybersecurity. Knows network security, penetration testing basics, Wireshark, and Linux hardening. Can help with CTF challenges.',
                'Avg_Rating' => 4.60,
                'Total_Completed' => 7,
                'Is_Verified' => true,
            ],

            // College of Business and Accountancy (CBA)
            [
                'Full_Name' => 'Patricia M. Ramos',
                'Email' => 'patricia.ramos@isatu.edu.ph',
                'Role' => 'Student',
                'Council' => 'CBA',
                'Bio' => 'BS Accountancy 3rd year. Strong in Financial Accounting, Cost Accounting, and Taxation. Can tutor accounting principles and Excel for finance.',
                'Avg_Rating' => 4.88,
                'Total_Completed' => 15,
                'Is_Verified' => true,
            ],
            [
                'Full_Name' => 'Daniel R. Aquino',
                'Email' => 'daniel.aquino@isatu.edu.ph',
                'Role' => 'Student',
                'Council' => 'CBA',
                'Bio' => 'BS Business Administration major in Marketing Management. Experienced in social media marketing, content creation, Canva, and market research.',
                'Avg_Rating' => 4.55,
                'Total_Completed' => 8,
                'Is_Verified' => true,
            ],
            [
                'Full_Name' => 'Michelle T. Villanueva',
                'Email' => 'michelle.villanueva@isatu.edu.ph',
                'Role' => 'Student',
                'Council' => 'CBA',
                'Bio' => 'BS Entrepreneurship. Can help with business plan writing, feasibility studies, financial projections, and startup basics. Won 2 business competitions.',
                'Avg_Rating' => 4.72,
                'Total_Completed' => 11,
                'Is_Verified' => true,
            ],
            [
                'Full_Name' => 'Ethan G. Cruz',
                'Email' => 'ethan.cruz@isatu.edu.ph',
                'Role' => 'Student',
                'Council' => 'CBA',
                'Bio' => 'BS Financial Management. Good at financial analysis, investment basics, budgeting, and personal finance. Can teach Excel financial modeling.',
                'Avg_Rating' => 4.68,
                'Total_Completed' => 9,
                'Is_Verified' => true,
            ],

            // College of Education (CED)
            [
                'Full_Name' => 'Rose Ann B. Flores',
                'Email' => 'rose.flores@isatu.edu.ph',
                'Role' => 'Student',
                'Council' => 'CED',
                'Bio' => 'BEEd major in General Education. Experienced in lesson planning, classroom management, and teaching strategies. Can tutor Education subjects and LET review.',
                'Avg_Rating' => 4.92,
                'Total_Completed' => 19,
                'Is_Verified' => true,
            ],
            [
                'Full_Name' => 'Gabriel P. Herrera',
                'Email' => 'gabriel.herrera@isatu.edu.ph',
                'Role' => 'Student',
                'Council' => 'CED',
                'Bio' => 'BSEd major in Mathematics. Strong in Algebra, Calculus, Statistics, and Math pedagogy. Can tutor high school and college Math.',
                'Avg_Rating' => 4.85,
                'Total_Completed' => 17,
                'Is_Verified' => true,
            ],
            [
                'Full_Name' => 'Kathleen M. Soriano',
                'Email' => 'kathleen.soriano@isatu.edu.ph',
                'Role' => 'Student',
                'Council' => 'CED',
                'Bio' => 'BSEd major in English. Expert in grammar, academic writing, literature analysis, and ESL teaching. Can help with essays, research papers, and IELTS prep.',
                'Avg_Rating' => 4.90,
                'Total_Completed' => 14,
                'Is_Verified' => true,
            ],

            // College of Arts and Sciences (CAS)
            [
                'Full_Name' => 'Nicole A. Reyes',
                'Email' => 'nicole.reyes@isatu.edu.ph',
                'Role' => 'Student',
                'Council' => 'CAS',
                'Bio' => 'BS Psychology. Can help with research methods, SPSS/JAMOVI, statistical analysis, academic writing, and basic counseling skills.',
                'Avg_Rating' => 4.78,
                'Total_Completed' => 10,
                'Is_Verified' => true,
            ],
            [
                'Full_Name' => 'Adrian L. Bautista',
                'Email' => 'adrian.bautista@isatu.edu.ph',
                'Role' => 'Student',
                'Council' => 'CAS',
                'Bio' => 'BS Biology. Good at laboratory techniques, microscopy, research writing, and biological concepts. Can tutor General Biology, Genetics, Microbiology.',
                'Avg_Rating' => 4.65,
                'Total_Completed' => 8,
                'Is_Verified' => true,
            ],
            [
                'Full_Name' => 'Stephanie M. Navarro',
                'Email' => 'stephanie.navarro@isatu.edu.ph',
                'Role' => 'Student',
                'Council' => 'CAS',
                'Bio' => 'BS Chemistry. Experienced in analytical chemistry, lab reports, titration, spectroscopy basics, and chemical calculations. Can tutor Chem 1-3.',
                'Avg_Rating' => 4.82,
                'Total_Completed' => 12,
                'Is_Verified' => true,
            ],

            // College of Nursing and Allied Health Sciences (CNAHS)
            [
                'Full_Name' => 'Francis D. Aquino',
                'Email' => 'francis.aquino@isatu.edu.ph',
                'Role' => 'Student',
                'Council' => 'CNAHS',
                'Bio' => 'BS Nursing 3rd year. BLS/ACLS certified. Can teach first aid, CPR, vital signs taking, nursing care plans, and pharmacology basics.',
                'Avg_Rating' => 4.95,
                'Total_Completed' => 20,
                'Is_Verified' => true,
            ],
            [
                'Full_Name' => 'Camille R. Ortega',
                'Email' => 'camille.ortega@isatu.edu.ph',
                'Role' => 'Student',
                'Council' => 'CNAHS',
                'Bio' => 'BS Medical Technology. Strong in clinical chemistry, hematology, microbiology lab procedures, and diagnostic testing principles.',
                'Avg_Rating' => 4.88,
                'Total_Completed' => 13,
                'Is_Verified' => true,
            ],

            // New students (unverified)
            [
                'Full_Name' => 'Lorenzo M. Dela Rosa',
                'Email' => 'lorenzo.delarosa@isatu.edu.ph',
                'Role' => 'Student',
                'Council' => 'CEA',
                'Bio' => 'Freshman BS Computer Engineering. Eager to learn programming and electronics. Can help with basic Python and logic building.',
                'Avg_Rating' => 0.00,
                'Total_Completed' => 0,
                'Is_Verified' => false,
            ],
            [
                'Full_Name' => 'Isabela P. Mendoza',
                'Email' => 'isabela.mendoza@isatu.edu.ph',
                'Role' => 'Student',
                'Council' => 'CIT',
                'Bio' => '1st year BSIT. Learning web development basics. Can help with HTML, CSS, and JavaScript fundamentals.',
                'Avg_Rating' => 0.00,
                'Total_Completed' => 0,
                'Is_Verified' => false,
            ],
        ];

        $createdUsers = [];
        foreach ($students as $s) {
            $user = User::firstOrCreate(
                ['Email' => $s['Email']],
                array_merge($s, [
                    'Password_Hash' => $password,
                    'Created_At' => now()->subDays(rand(30, 180)),
                    'Account_Status' => 'Active',
                    'Warning_Count' => 0,
                    'settings' => ['dark_mode' => false],
                    'Service_Modes' => ['Face-to-Face', 'Remote', 'Hybrid'][array_rand(['Face-to-Face', 'Remote', 'Hybrid'])],
                ])
            );
            $createdUsers[] = $user;
        }

        // Add admin to created users for potential matches
        $createdUsers[] = $admin;

        // Get all skills
        $skills = DB::table('skills')->pluck('Skill_ID', 'Skill_Title')->toArray();
        $skillIds = array_values($skills);
        $skillTitles = array_keys($skills);

        // Assign skills to users based on their bios/councils (realistic mapping)
        $userSkillMapping = [
            // CEA - Engineering
            'alexandria.santos@isatu.edu.ph' => ['Programming', 'Embedded Systems', 'IoT', 'PCB Design', 'Arduino', 'Raspberry Pi', 'Microcontroller', 'Circuit Analysis', 'Electronics', 'CAD'],
            'christian.delacruz@isatu.edu.ph' => ['Circuit Analysis', 'Power Systems', 'MATLAB', 'Simulink', 'PCB Layout', 'KiCad', 'Electrical Engineering', 'Electronics', 'Programming', 'Microcontroller'],
            'maria.gonzales@isatu.edu.ph' => ['AutoCAD', 'Structural Analysis', 'Construction Materials', 'Statics', 'Mechanics of Materials', 'Civil Engineering', 'Engineering', 'Technical Drawing', 'CAD', 'Mathematics'],
            'joshua.reyes@isatu.edu.ph' => ['SolidWorks', 'Fusion 360', '3D Printing', 'CAD', 'Mechanical Design', 'Mechanical Engineering', 'Engineering', '3D Modeling', 'Technical Drawing', 'Mathematics'],
            'samantha.bautista@isatu.edu.ph' => ['Revit', 'SketchUp', 'Lumion', 'Architectural Visualization', 'Hand Drafting', 'Model Making', 'Architecture', 'Design', 'CAD', 'Interior Design'],

            // CIT - IT
            'rafael.torres@isatu.edu.ph' => ['Network Administration', 'CCNA', 'Cisco Packet Tracer', 'Server Setup', 'Networking', 'Linux', 'System Administration', 'IT Support', 'Cloud Computing', 'Infrastructure'],
            'jennifer.lim@isatu.edu.ph' => ['Web Development', 'React', 'Node.js', 'Laravel', 'PostgreSQL', 'Full-Stack Development', 'Programming', 'Software Development', 'System Design', 'Database Management'],
            'mark.chen@isatu.edu.ph' => ['Mobile Development', 'Flutter', 'React Native', 'UI/UX', 'App Architecture', 'Dart', 'Programming', 'Software Development', 'Design', 'User Experience'],
            'angela.pascual@isatu.edu.ph' => ['Data Analytics', 'Python', 'pandas', 'NumPy', 'scikit-learn', 'Tableau', 'Power BI', 'Data Science', 'Machine Learning', 'Data Visualization', 'Statistical Analysis'],
            'kevin.mendoza@isatu.edu.ph' => ['Cybersecurity', 'Network Security', 'Penetration Testing', 'Wireshark', 'Linux Hardening', 'CTF', 'Security', 'IT Security', 'Information Security', 'Risk Assessment'],

            // CBA - Business
            'patricia.ramos@isatu.edu.ph' => ['Financial Accounting', 'Cost Accounting', 'Taxation', 'Accounting', 'Excel', 'Finance', 'Bookkeeping', 'Business Finance', 'Auditing', 'Financial Analysis'],
            'daniel.aquino@isatu.edu.ph' => ['Marketing Management', 'Social Media Marketing', 'Content Creation', 'Canva', 'Market Research', 'Marketing', 'Sales', 'Digital Marketing', 'Business Analysis', 'Advertising'],
            'michelle.villanueva@isatu.edu.ph' => ['Entrepreneurship', 'Business Plan', 'Feasibility Study', 'Financial Projections', 'Startup', 'Business Management', 'Project Management', 'Business Analysis', 'Finance', 'Strategic Planning'],
            'ethan.cruz@isatu.edu.ph' => ['Financial Management', 'Financial Analysis', 'Investment', 'Budgeting', 'Personal Finance', 'Excel', 'Finance', 'Financial Planning', 'Cost Estimation', 'Business Finance'],

            // CED - Education
            'rose.flores@isatu.edu.ph' => ['Teaching', 'Lesson Planning', 'Classroom Management', 'Pedagogy', 'Education', 'LET Review', 'Instructional Design', 'Curriculum Development', 'Assessment', 'Mentoring'],
            'gabriel.herrera@isatu.edu.ph' => ['Mathematics', 'Algebra', 'Calculus', 'Statistics', 'Math Pedagogy', 'Teaching', 'Education', 'Tutoring', 'Problem Solving', 'Mathematical Reasoning'],
            'kathleen.soriano@isatu.edu.ph' => ['English', 'Grammar', 'Academic Writing', 'Literature', 'ESL', 'Teaching', 'Education', 'IELTS', 'Essay Writing', 'Communication'],

            // CAS - Arts & Sciences
            'nicole.reyes@isatu.edu.ph' => ['Psychology', 'Research Methods', 'SPSS', 'JAMOVI', 'Statistical Analysis', 'Academic Writing', 'Counseling', 'Research', 'Data Collection', 'Experimentation'],
            'adrian.bautista@isatu.edu.ph' => ['Biology', 'Laboratory Techniques', 'Microscopy', 'Genetics', 'Microbiology', 'Research Writing', 'Scientific Research', 'Experimentation', 'Data Collection', 'Biology Techniques'],
            'stephanie.navarro@isatu.edu.ph' => ['Chemistry', 'Analytical Chemistry', 'Lab Reports', 'Titration', 'Spectroscopy', 'Chemical Calculations', 'Laboratory Techniques', 'Scientific Writing', 'Chemistry Techniques', 'Research'],

            // CNAHS - Nursing
            'francis.aquino@isatu.edu.ph' => ['Nursing', 'First Aid', 'CPR', 'BLS', 'ACLS', 'Vital Signs', 'Care Plans', 'Pharmacology', 'Patient Care', 'Emergency Response', 'Basic Life Support'],
            'camille.ortega@isatu.edu.ph' => ['Medical Technology', 'Clinical Chemistry', 'Hematology', 'Microbiology', 'Laboratory Techniques', 'Diagnostic Testing', 'Scientific Research', 'Lab', 'Biology Techniques', 'Chemistry Techniques'],
        ];

        foreach ($createdUsers as $user) {
            $email = $user->Email;
            if (!isset($userSkillMapping[$email])) continue;

            $targetSkills = $userSkillMapping[$email];
            $count = 0;
            foreach ($targetSkills as $skillTitle) {
                if ($count >= 8) break; // Max 8 skills per user
                $skillId = $skills[$skillTitle] ?? null;
                if ($skillId) {
                    DB::table('user_skills')->insertOrIgnore([
                        'User_ID' => $user->User_ID,
                        'Skill_ID' => $skillId,
                        'Proficiency' => rand(3, 5),
                        'Status' => 'verified',
                        'Created_At' => now()->subDays(rand(1, 90)),
                    ]);
                    $count++;
                }
            }
        }

        // Create realistic skill requests
        $requestData = [
            // Engineering requests
            ['title' => 'Arduino-based Automated Plant Watering System', 'desc' => 'Need help designing and coding an Arduino project that monitors soil moisture and automatically waters plants. Should include sensor calibration, relay control, and optional Bluetooth monitoring.', 'skills' => ['Arduino', 'Embedded Systems', 'IoT', 'Electronics', 'Programming'], 'owner' => 'maria.gonzales@isatu.edu.ph', 'mode' => 'Face-to-Face'],
            ['title' => 'PCB Design for Motor Driver Circuit', 'desc' => 'Need assistance creating a 2-layer PCB layout in KiCad for a H-bridge motor driver (L298N). Includes schematic review, footprint assignment, routing, and Gerber generation.', 'skills' => ['PCB Design', 'KiCad', 'Electronics', 'Circuit Analysis', 'CAD'], 'owner' => 'lorenzo.delarosa@isatu.edu.ph', 'mode' => 'Remote'],
            ['title' => 'Structural Analysis of Simple Beam - Statics Homework', 'desc' => 'Struggling with calculating reactions, shear force, and bending moment diagrams for a simply supported beam with UDL and point loads. Need step-by-step explanation.', 'skills' => ['Statics', 'Structural Analysis', 'Mechanics of Materials', 'Mathematics', 'Engineering'], 'owner' => 'joshua.reyes@isatu.edu.ph', 'mode' => 'Hybrid'],
            ['title' => '3D Model of Gear Assembly in SolidWorks', 'desc' => 'Need a parametric 3D model of a gear train assembly (sun, planet, ring gears) with motion study. For MECHENG 302 final project. Must be manufacturable.', 'skills' => ['SolidWorks', '3D Modeling', 'Mechanical Design', 'CAD', 'Engineering'], 'owner' => 'alexandria.santos@isatu.edu.ph', 'mode' => 'Remote'],
            ['title' => 'Revit Architectural Model - 2-Storey Residential', 'desc' => 'Need help completing a Revit model for a 2-storey house: walls, floors, roofs, doors/windows, stairs, and basic furniture. Include 3D views and sheets for printing.', 'skills' => ['Revit', 'Architecture', 'CAD', 'BIM', 'Architectural Visualization'], 'owner' => 'samantha.bautista@isatu.edu.ph', 'mode' => 'Face-to-Face'],

            // IT/CS requests
            ['title' => 'Laravel + Vue.js Inventory Management System', 'desc' => 'Capstone project: Build a full-stack inventory system with role-based access (Admin, Staff), product CRUD, stock movements, low-stock alerts, and PDF reports. Using Laravel 10, Vue 3, PostgreSQL.', 'skills' => ['Laravel', 'Vue.js', 'PostgreSQL', 'Full-Stack Development', 'Web Development', 'Software Development'], 'owner' => 'isabela.mendoza@isatu.edu.ph', 'mode' => 'Remote'],
            ['title' => 'React Native E-Commerce App - Cart & Checkout', 'desc' => 'Stuck on implementing shopping cart state management (Context API/Redux) and Stripe checkout integration in React Native Expo. Need debugging help and best practices.', 'skills' => ['React Native', 'Flutter', 'Mobile Development', 'JavaScript', 'Payment Integration', 'State Management'], 'owner' => 'mark.chen@isatu.edu.ph', 'mode' => 'Remote'],
            ['title' => 'Network Topology Design - Cisco Packet Tracer', 'desc' => 'Design a network for a 3-building campus: VLANs, inter-VLAN routing, DHCP, OSPF, ACLs, and NAT. Must simulate in Packet Tracer and provide configuration scripts.', 'skills' => ['Network Administration', 'Cisco Packet Tracer', 'Networking', 'VLAN', 'Routing', 'CCNA'], 'owner' => 'rafael.torres@isatu.edu.ph', 'mode' => 'Hybrid'],
            ['title' => 'Python Data Analysis - Student Performance Dataset', 'desc' => 'Analyze a CSV dataset (500 rows) of student grades, attendance, demographics. Need: cleaning, EDA, correlation analysis, visualization (matplotlib/seaborn), and basic ML model (regression) to predict final grade.', 'skills' => ['Python', 'pandas', 'NumPy', 'matplotlib', 'Data Analytics', 'Machine Learning', 'Data Visualization'], 'owner' => 'angela.pascual@isatu.edu.ph', 'mode' => 'Remote'],
            ['title' => 'Linux Server Hardening Guide & Script', 'desc' => 'Need a comprehensive guide and Bash script to harden Ubuntu 22.04 server: SSH config, firewall (UFW), fail2ban, automatic updates, auditd, Lynis audit. For cybersecurity elective.', 'skills' => ['Linux', 'Cybersecurity', 'System Administration', 'Bash Scripting', 'Security', 'Server Setup'], 'owner' => 'kevin.mendoza@isatu.edu.ph', 'mode' => 'Remote'],

            // Business requests
            ['title' => 'Financial Statement Analysis - Jollibee Foods Corp', 'desc' => 'Analyze JFC 2023 Annual Report: ratio analysis (liquidity, profitability, leverage, efficiency), trend analysis (3 years), DuPont analysis, and 500-word investment recommendation.', 'skills' => ['Financial Analysis', 'Accounting', 'Finance', 'Excel', 'Financial Statement Analysis', 'Investment'], 'owner' => 'patricia.ramos@isatu.edu.ph', 'mode' => 'Remote'],
            ['title' => 'Social Media Content Calendar for Student Org', 'desc' => 'Create a 30-day content calendar for ISAT-U Computer Studies Council FB/IG: 12 posts (graphics + captions), 8 stories, 4 reels ideas. Theme: Tech tips, student life, events. Provide Canva templates.', 'skills' => ['Social Media Marketing', 'Content Creation', 'Canva', 'Marketing', 'Digital Marketing', 'Graphic Design'], 'owner' => 'daniel.aquino@isatu.edu.ph', 'mode' => 'Hybrid'],
            ['title' => 'Business Plan for Campus Coffee Cart', 'desc' => 'Feasibility study for a mobile coffee cart inside ISAT-U main campus: market analysis, startup costs, revenue projections (3 years), break-even, marketing strategy, risk assessment. 15-page document.', 'skills' => ['Entrepreneurship', 'Business Plan', 'Feasibility Study', 'Financial Projections', 'Market Research', 'Business Management'], 'owner' => 'michelle.villanueva@isatu.edu.ph', 'mode' => 'Face-to-Face'],
            ['title' => 'Personal Budgeting Excel Template with Dashboard', 'desc' => 'Create an Excel template for student budgeting: income/expense tracking by category, monthly/yearly summaries, charts (spending trends, savings rate), conditional formatting alerts, and a dashboard sheet.', 'skills' => ['Excel', 'Budgeting', 'Personal Finance', 'Finance', 'Data Visualization', 'Financial Planning'], 'owner' => 'ethan.cruz@isatu.edu.ph', 'mode' => 'Remote'],

            // Education requests
            ['title' => 'Demo Teaching Lesson Plan - Grade 7 Algebra', 'desc' => 'Need a detailed lesson plan for "Solving Linear Equations in One Variable" (Grade 7 Math): objectives, motivation, lesson proper (5E model), assessment, assignment. Include PowerPoint and activity sheets.', 'skills' => ['Teaching', 'Lesson Planning', 'Mathematics', 'Pedagogy', 'Education', 'Curriculum Development'], 'owner' => 'gabriel.herrera@isatu.edu.ph', 'mode' => 'Face-to-Face'],
            ['title' => 'Research Paper Editing - English Education Thesis', 'desc' => 'Help edit/proofread 40-page thesis: "Effectiveness of Task-Based Language Teaching in Improving Speaking Skills." Check grammar, APA 7th citation, flow, academic tone. Native-level English preferred.', 'skills' => ['Academic Writing', 'English', 'Grammar', 'Research Writing', 'Editing', 'APA Citation'], 'owner' => 'kathleen.soriano@isatu.edu.ph', 'mode' => 'Remote'],
            ['title' => 'Classroom Management Strategies Workshop', 'desc' => 'Preparing for practice teaching. Need practical strategies for: handling disruptive behavior, positive reinforcement, routines/procedures, attention signals, and building rapport. Role-play practice preferred.', 'skills' => ['Classroom Management', 'Teaching', 'Pedagogy', 'Education', 'Mentoring', 'Behavior Management'], 'owner' => 'rose.flores@isatu.edu.ph', 'mode' => 'Face-to-Face'],

            // Arts & Sciences requests
            ['title' => 'SPSS Analysis for Psychology Thesis', 'desc' => 'Run and interpret: descriptive stats, reliability test (Cronbach alpha), independent t-test, one-way ANOVA, Pearson correlation, and simple regression. Dataset: 120 respondents, 50 variables. Provide APA-formatted output.', 'skills' => ['SPSS', 'Statistical Analysis', 'Psychology', 'Research Methods', 'Data Analysis', 'Academic Writing'], 'owner' => 'nicole.reyes@isatu.edu.ph', 'mode' => 'Remote'],
            ['title' => 'Microbiology Lab Report - Gram Staining & Culture', 'desc' => 'Write a formal lab report for Gram staining procedure and bacterial culture identification. Include: intro, methods, results (with microscope photos), discussion, conclusion. Follow scientific format.', 'skills' => ['Biology', 'Laboratory Techniques', 'Microbiology', 'Scientific Writing', 'Lab Reports', 'Research'], 'owner' => 'adrian.bautista@isatu.edu.ph', 'mode' => 'Face-to-Face'],
            ['title' => 'Organic Chemistry - Reaction Mechanism Tutorial', 'desc' => 'Need step-by-step explanation of: SN1/SN2, E1/E2, electrophilic aromatic substitution, and carbonyl nucleophilic addition. With energy diagrams and stereochemistry. For Chem 222 midterm prep.', 'skills' => ['Chemistry', 'Organic Chemistry', 'Chemistry Techniques', 'Tutoring', 'Scientific Explanation', 'Teaching'], 'owner' => 'stephanie.navarro@isatu.edu.ph', 'mode' => 'Hybrid'],

            // Nursing/Health requests
            ['title' => 'Nursing Care Plan for Pneumonia Patient', 'desc' => 'Create a comprehensive NCP for community-acquired pneumonia: 3 nursing diagnoses (priority), NANDA labels, goals/outcomes, interventions with rationale, evaluation. Use NANDA-I, NIC, NOC taxonomy.', 'skills' => ['Nursing', 'Care Plans', 'Patient Care', 'Pharmacology', 'Nursing Diagnosis', 'Clinical Reasoning'], 'owner' => 'camille.ortega@isatu.edu.ph', 'mode' => 'Face-to-Face'],
            ['title' => 'BLS/CPR Skills Practice & Certification Prep', 'desc' => 'Need hands-on practice for BLS renewal: adult/child/infant CPR, AED use, choking relief, bag-mask ventilation. Must be AHA-aligned. Schedule 2-hour session this week.', 'skills' => ['CPR', 'BLS', 'First Aid', 'Emergency Response', 'Basic Life Support', 'Patient Care'], 'owner' => 'francis.aquino@isatu.edu.ph', 'mode' => 'Face-to-Face'],
        ];

        $createdRequests = [];
        foreach ($requestData as $rd) {
            $owner = User::where('Email', $rd['owner'])->first();
            if (!$owner) continue;

            // Find primary skill
            $primarySkillId = null;
            foreach ($rd['skills'] as $skillTitle) {
                if (isset($skills[$skillTitle])) {
                    $primarySkillId = $skills[$skillTitle];
                    break;
                }
            }
            if (!$primarySkillId) {
                $primarySkillId = $skillIds[array_rand($skillIds)];
            }

            $statuses = ['Open', 'Open', 'Open', 'Open', 'Open', 'Completed', 'Cancelled', 'Failed'];
            $status = $statuses[array_rand($statuses)];

            $request = SkillRequest::create([
                'User_ID' => $owner->User_ID,
                'Skill_ID' => $primarySkillId,
                'Title' => $rd['title'],
                'Description' => $rd['desc'],
                'Status' => $status,
                'Service_Mode' => $rd['mode'],
                'Created_At' => now()->subDays(rand(1, 60)),
            ]);
            $createdRequests[] = $request;

            // Add secondary skills
            foreach ($rd['skills'] as $skillTitle) {
                if (isset($skills[$skillTitle]) && $skills[$skillTitle] != $primarySkillId) {
                    DB::table('request_skills')->insertOrIgnore([
                        'Request_ID' => $request->Request_ID,
                        'Skill_ID' => $skills[$skillTitle],
                    ]);
                }
            }
        }

        // Create assignments (applications)
        $assignmentStatuses = ['Pending', 'Accepted', 'Completed', 'Failed', 'Cancelled'];
        foreach ($createdRequests as $request) {
            $applicants = $createdUsers;
            shuffle($applicants);
            $numApplicants = rand(0, 4);
            $appCount = 0;
            for ($i = 0; $i < count($applicants) && $appCount < $numApplicants; $i++) {
                $applicant = $applicants[$i];
                if ($applicant->User_ID === $request->User_ID) continue;
                if ($applicant->Role === 'Admin') continue;

                $status = $assignmentStatuses[array_rand($assignmentStatuses)];
                $createdAt = now()->subDays(rand(1, 30));
                $respondedAt = in_array($status, ['Pending']) ? null : $createdAt->copy()->addHours(rand(1, 72));
                $completedAt = $status === 'Completed' ? $respondedAt->copy()->addDays(rand(1, 14)) : null;

                Assignment::create([
                    'Request_ID' => $request->Request_ID,
                    'User_ID' => $applicant->User_ID,
                    'Status' => $status,
                    'Created_At' => $createdAt,
                    'Responded_At' => $respondedAt,
                    'Status_Updated_At' => $respondedAt ?? $createdAt,
                    'Completed_At' => $completedAt,
                ]);
                $appCount++;
            }
        }

        // Create user matches (algorithmic matches)
        foreach ($createdRequests as $request) {
            $potentialMatches = $createdUsers;
            shuffle($potentialMatches);
            $numMatches = rand(1, 4);
            $matchCount = 0;
            for ($i = 0; $i < count($potentialMatches) && $matchCount < $numMatches; $i++) {
                $matchedUser = $potentialMatches[$i];
                if ($matchedUser->User_ID === $request->User_ID) continue;
                if ($matchedUser->Role === 'Admin') continue;

                // Check if already assigned
                $alreadyAssigned = Assignment::where('Request_ID', $request->Request_ID)
                    ->where('User_ID', $matchedUser->User_ID)
                    ->whereIn('Status', ['Pending', 'Accepted', 'Completed'])
                    ->exists();
                if ($alreadyAssigned) continue;

                UserMatch::create([
                    'Matched_User_ID' => $matchedUser->User_ID,
                    'Request_ID' => $request->Request_ID,
                    'Match_Score' => rand(65, 98),
                    'Matched_At' => now()->subDays(rand(1, 25)),
                ]);
                $matchCount++;
            }
        }

        // Create messages for accepted/completed assignments
        $assignments = Assignment::whereIn('Status', ['Accepted', 'Completed'])->get();
        $messageTemplates = [
            'sender' => [
                'Hi! Thanks for accepting my request. When are you available to start?',
                'Great! Let me share the details and requirements.',
                'Here\'s the file/guide I mentioned. Let me know if you have questions.',
                'Perfect! See you at the agreed time. Thanks again!',
                'Just checking in - how\'s the progress going?',
                'That looks good! A few minor adjustments needed.',
                'Excellent work! Thank you so much for your help.',
            ],
            'receiver' => [
                'Hi! I\'m available weekday evenings after 5pm or weekends. What works for you?',
                'Got it! I\'ll review the materials and get back to you by tomorrow.',
                'Thanks for the details. I have a few clarifying questions...',
                'Confirmed! I\'ll be there. See you then.',
                'Progress is good! About halfway done. Will update you soon.',
                'Noted the changes. I\'ll revise and send the updated version.',
                'You\'re welcome! Glad I could help. Good luck with your project!',
            ],
        ];

        foreach ($assignments as $assignment) {
            $request = $assignment->request;
            $sender = $request->user;
            $receiver = User::find($assignment->User_ID);

            $numMessages = rand(3, 8);
            for ($j = 0; $j < $numMessages; $j++) {
                $isSender = rand(0, 1);
                $msgSender = $isSender ? $sender : $receiver;
                $msgReceiver = $isSender ? $receiver : $sender;

                $templateKey = $isSender ? 'sender' : 'receiver';
                $msgText = $messageTemplates[$templateKey][array_rand($messageTemplates[$templateKey])];

                Message::create([
                    'Sender_ID' => $msgSender->User_ID,
                    'Receiver_ID' => $msgReceiver->User_ID,
                    'Message_Text' => $msgText,
                    'Sent_At' => now()->subHours(rand(1, 120)),
                    'Is_Read' => rand(0, 1),
                    'Read_At' => rand(0, 1) ? now()->subMinutes(rand(1, 60)) : null,
                ]);
            }
        }

        // Create reviews for completed assignments
        $completedAssignments = Assignment::where('Status', 'Completed')->get();
        $reviewComments = [
            'Excellent work! Very knowledgeable and patient. Explained everything clearly.',
            'Great help! Completed the task ahead of schedule with high quality.',
            'Highly recommended! Professional, responsive, and skilled.',
            'Amazing tutoring session. Made complex topics easy to understand.',
            'Quick response time and very thorough. Would definitely work with again.',
            'Outstanding assistance. Went above and beyond to ensure I understood.',
            'Perfect match for what I needed. Reliable and communicative throughout.',
            'Fantastic help! Patient and encouraging. Boosted my confidence.',
        ];

        foreach ($completedAssignments as $assignment) {
            $request = $assignment->request;
            $reviewer = $request->user;
            $reviewed = User::find($assignment->User_ID);

            // Requester reviews provider
            if (!Review::where('Request_ID', $request->Request_ID)->where('Reviewer_ID', $reviewer->User_ID)->exists()) {
                Review::create([
                    'Reviewed_User_ID' => $reviewed->User_ID,
                    'Reviewer_ID' => $reviewer->User_ID,
                    'Request_ID' => $request->Request_ID,
                    'Rating' => rand(4, 5),
                    'Comment' => $reviewComments[array_rand($reviewComments)],
                    'Created_At' => $assignment->Completed_At ?? now(),
                ]);
            }

            // Provider reviews requester (occasionally)
            if (rand(0, 1) && !Review::where('Request_ID', $request->Request_ID)->where('Reviewer_ID', $reviewed->User_ID)->exists()) {
                Review::create([
                    'Reviewed_User_ID' => $reviewer->User_ID,
                    'Reviewer_ID' => $reviewed->User_ID,
                    'Request_ID' => $request->Request_ID,
                    'Rating' => rand(4, 5),
                    'Comment' => $reviewComments[array_rand($reviewComments)],
                    'Created_At' => $assignment->Completed_At ?? now(),
                ]);
            }
        }

        // Update user ratings and completed counts based on reviews
        foreach ($createdUsers as $user) {
            $reviewsReceived = Review::where('Reviewed_User_ID', $user->User_ID)->get();
            if ($reviewsReceived->count() > 0) {
                $avgRating = $reviewsReceived->avg('Rating');
                $totalCompleted = Assignment::where('User_ID', $user->User_ID)
                    ->where('Status', 'Completed')
                    ->count();
                $user->update([
                    'Avg_Rating' => round($avgRating, 2),
                    'Total_Completed' => $totalCompleted,
                ]);
            }
        }

        $this->command->info("Realistic data created: " . count($createdUsers) . " users, " . count($createdRequests) . " requests.");
    }

    private function clearOldData(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate tables in correct order (children first)
        $tables = [
            'reviews',
            'messages',
            'user_matches',
            'assignments',
            'request_skills',
            'skill_requests',
            'skill_endorsements',
            'user_skills',
            'notifications',
            'reports',
            'admin_action_logs',
            'blocks',
        ];

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        // Delete non-admin users (keep admin)
        User::where('Role', '!=', 'Admin')->delete();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Old dummy data cleared.');
    }
}