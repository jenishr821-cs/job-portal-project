<?php
session_start();
include 'db.php';
include 'notification_helper.php';

$username = $_SESSION['username'] ?? '';
$role = $_SESSION['role'] ?? '';
$notifications = [];
$noti_count = 0;

// Fetch notifications only if user is logged in
if ($username !== '') {
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE receiver_username  = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = $result->fetch_all(MYSQLI_ASSOC);

    // Count unread
    $count_stmt = $conn->prepare("SELECT COUNT(*) as unread FROM notifications WHERE receiver_username  = ? AND is_read = 0");
    $count_stmt->bind_param("s", $username);
    $count_stmt->execute();
    $noti_count = $count_stmt->get_result()->fetch_assoc()['unread'] ?? 0;

    // Fetch logo (user or company)
    if ($role === 'company') {
        $logo_sql = $conn->prepare("SELECT logo FROM company WHERE username = ?");
    } else {
        $logo_sql = $conn->prepare("SELECT logo FROM user_profiles WHERE username = ?");
    }
    $logo_sql->bind_param("s", $username);
    $logo_sql->execute();
    $logo_res = $logo_sql->get_result()->fetch_assoc();
    $raw['logo'] = $logo_res['logo'] ?? '';
}



// If admin clicked Resume -> show target user, else show own resume


// --- Whose resume to show? ---
$role = $_SESSION['role'] ?? '';
$login_username = $_SESSION['username'] ?? '';

if ($role === 'admin' && isset($_GET['username'])) {
    // Admin views selected user's resume
    $view_username = $_GET['username'];
    $stmt = $conn->prepare("SELECT * FROM create_resume WHERE username = ?");
    $stmt->bind_param("s", $view_username);
    $stmt->execute();
    $resume = $stmt->get_result()->fetch_assoc();
} elseif ($role === 'user') {
    // User views their own resume
    $stmt = $conn->prepare("SELECT * FROM create_resume WHERE username = ?");
    $stmt->bind_param("s", $login_username);
    $stmt->execute();
    $resume = $stmt->get_result()->fetch_assoc();
} else {
    die("Unauthorized access.");
}




if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $role = $_POST['role'] ?? '';
    $experience = $_POST['experience'] ?? '';
    $address = $_POST['address'] ?? '';
    $summary = $_POST['summary'] ?? '';
    $degree = $_POST['degree'] ?? '';
    $university = $_POST['university'] ?? '';
    $passing_year = $_POST['passing_year'] ?? '';
    $percentage = $_POST['percentage'] ?? '';
    $job_title = $_POST['job_title'] ?? '';
    $company_name = $_POST['company_name'] ?? '';
    $work_duration_from = $_POST['work_duration_from'] ?? '';
    $work_duration_to = $_POST['work_duration_to'] ?? '';
    $previous_salary = $_POST['previous_salary'] ?? '';
    $expected_salary = $_POST['expected_salary'] ?? '';
    $programming_languages = $_POST['programming_languages'] ?? '';
    $tools_frameworks = $_POST['tools_frameworks'] ?? '';
    $hobbies = $_POST['hobbies'] ?? '';
    $ref_name = $_POST['ref_name'] ?? '';
    $ref_position = $_POST['ref_position'] ?? '';
    $ref_contact = $_POST['ref_contact'] ?? '';
    $ref_email = $_POST['ref_email'] ?? '';


    $query = "UPDATE create_resume SET 
        name = ?, email = ?, phone = ?, role = ?, experience = ?, address = ?, summary = ?, 
        degree = ?, university = ?, passing_year = ?, percentage = ?, 
        job_title = ?, company_name = ?, work_duration_from = ?, work_duration_to = ?, previous_salary = ?, expected_salary = ?,
        programming_languages = ?, tools_frameworks = ?, hobbies = ?, 
        ref_name = ?, ref_position = ?, ref_contact = ?, ref_email = ?
        WHERE username = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "ssssissssssssssssssssssss",
        $name,
        $email,
        $phone,
        $role,
        $experience,
        $address,
        $summary,
        $degree,
        $university,
        $passing_year,
        $percentage,
        $job_title,
        $company_name,
        $work_duration_from,
        $work_duration_to,
        $previous_salary,
        $expected_salary,
        $programming_languages,
        $tools_frameworks,
        $hobbies,
        $ref_name,
        $ref_position,
        $ref_contact,
        $ref_email,
        $username
    );
    if ($stmt->execute()) {
        echo "<script>alert('Resume updated successfully!'); window.location.href='myresume.php';</script>";
        exit;
    } else {
        echo "<script>alert('Error updating resume.');</script>";
    }
}

// --- Fetch Profile Info (logo, fullname, email) ---
if ($role === 'user') {
    $stmt = $conn->prepare("SELECT fullname, email, logo FROM user_profiles WHERE username=?");
    $stmt->bind_param("s", $login_username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} elseif ($role === 'company') {
    $stmt = $conn->prepare("SELECT company_name, email, logo FROM company WHERE username=?");
    $stmt->bind_param("s", $login_username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} elseif ($role === 'admin' && isset($_GET['username'])) {
    // when admin views user resume → fetch that user's profile logo
    $stmt = $conn->prepare("SELECT fullname, email, logo FROM user_profiles WHERE username=?");
    $stmt->bind_param("s", $_GET['username']);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>


    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ($role === 'admin' ? "View User Resume" : "My Resume") ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            600: '#7c3aed',
                            700: '#6d28d9',
                        },
                        dark: {
                            800: '#1e1e2d',
                            900: '#12141d',
                        },
                        slate: {
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
    <script>
        function toggleEdit() {
            const form = document.getElementById("resume-form");
            const btn = document.getElementById("edit-btn");
            const inputs = form.querySelectorAll("input, textarea");

            if (btn.innerText === "Edit") {
                inputs.forEach(i => i.removeAttribute("readonly")); // 🔄 Make editable
                btn.innerText = "Save Changes"; // ✏️ Change button text to Save
            } else {
                form.submit(); // 💾 Submit form to save
            }
        }
    </script>
    <style>
        input[type="number"] {
            appearance: textfield;
            -moz-appearance: textfield;
            /* Firefox */
            -webkit-appearance: none;
            /* Chrome/Safari */
        }

        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            display: none;
        }
    </style>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="styles.css">

</head>

<body class="dark">
    <!-- Main Layout -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div class="hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64 bg-slate-900 border-r border-slate-800">
                <div class="flex items-center h-16 px-4">
                    <h1 class="text-2xl font-bold text-white"><a href="index.php">Nexus<span class="text-primary-600">Career</span></a></h1>
                </div>
                <!-- User Profile -->
                 
                <a href="profile.php">
                    <div class="flex items-center px-4 py-3 space-x-3 border-b border-slate-800">
                        <img alt="User Logo" class="w-10 h-10 rounded-full"
                            <?php if ($role === 'user'): ?>
                            src="uploads/<?= htmlspecialchars($row['logo'] ?? 'default.png'); ?>" />
                    <?php elseif ($role === 'company'): ?>
                        src="uploads/<?= htmlspecialchars($row['logo'] ?? 'default.png'); ?>" />
                    <?php endif; ?>



                    <div>
                        <?php if ($role === 'user'): ?>
                            <p class="font-medium text-white"><?php echo $row['fullname'] ?? null; ?></p>
                            <p class="text-xs text-slate-400"><?php echo $row['email'] ?? null; ?></p>
                        <?php elseif ($role === 'company'): ?>
                            <p class="font-medium text-white"><?php echo $raw['company_name'] ?? null; ?></p>
                            <p class="text-xs text-slate-400"><?php echo $raw['email'] ?? null; ?></p>

                        <?php endif; ?>
                    </div>
                    </div>
                </a>
                <!-- Navigation -->
                <?php if ($role === 'user'): ?>
                    <!-- Navigation -->
                    <!-- Sidebar -->
                    <aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col">
                        <!-- Logo / Branding -->

                        <!-- Profile Info -->


                        <!-- Navigation -->
                        <nav class="flex-1 px-3 py-4 space-y-1">
                            <a href="index.php" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            text-slate-300 hover:bg-slate-800 hover:text-white transition
            <?php if (basename($_SERVER['PHP_SELF']) == 'index.php') echo 'bg-purple-700 text-white'; ?>">
                                <i class="fas fa-chart-pie mr-3"></i> Dashboard
                            </a>

                            <a href="profile.php" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            text-slate-300 hover:bg-slate-800 hover:text-white transition
            <?php if (basename($_SERVER['PHP_SELF']) == 'profile.php') echo 'bg-purple-700 text-white'; ?>">
                                <i class="fas fa-user mr-3"></i> Profile
                            </a>

                            <a href="myresume.php" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            text-slate-300 hover:bg-slate-800 hover:text-white transition
            <?php if (basename($_SERVER['PHP_SELF']) == 'myresume.php') echo 'bg-purple-700 text-white'; ?>">
                                <i class="fas fa-file-alt mr-3"></i> My Resume
                            </a>

                            <a href="job_matches.php" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            text-slate-300 hover:bg-slate-800 hover:text-white transition
            <?php if (basename($_SERVER['PHP_SELF']) == 'jobmatches.php') echo 'bg-purple-700 text-white'; ?>">
                                <i class="fas fa-briefcase mr-3"></i> Job Matches
                            </a>

                            <a href="setting.php" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            text-slate-300 hover:bg-slate-800 hover:text-white transition
            <?php if (basename($_SERVER['PHP_SELF']) == 'setting.php') echo 'bg-purple-700 text-white'; ?>">
                                <i class="fas fa-cog mr-3"></i> Settings
                            </a>

                            <a href="logout.php" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            text-red-400 hover:bg-red-600 hover:text-white transition">
                                <i class="fas fa-sign-out-alt mr-3"></i> Logout
                            </a>
                        </nav>
                    </aside>

                    <!-- Upgrade Banner -->

            </div>
        </div>
        <!-- Main Content -->
        <div class="flex flex-col flex-1 overflow-auto custom-scrollbar">

            <!-- Top Navigation -->
            <div class="flex items-center justify-between h-16 px-4 bg-slate-800 border-b border-slate-700">
                
                <div class="flex items-center space-x-4">
                    <!-- Dark Mode Toggle -->
                    <!-- Theme Toggle Switch -->



                    

                    <a href="profile.php">
                        <div class="relative">
                            <button class="flex items-center space-x-2">
                                <img alt="User profile" class="w-8 h-8 rounded-full" src="uploads/<?php echo $row['logo']; ?>" />
                            </button>
                    </a>
                </div>
            </div>
        </div>
        <form action="myresume.php" method="post" id="resume-form">
            <div class="p-6 rounded-lg shadow bg-slate-800">
                <h3 class="mb-4 text-lg font-semibold text-white">Personal Information</h3>



                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-300 mb-1">Full Name</label>
                        <input type="text" placeholder="Alex Johnson" id="name" name="name" value="<?php echo $resume['name'] ?? ''; ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" readonly class="input" required/>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-300 mb-1">Email Address</label>
                        <input type="email" placeholder="alex.johnson@example.com" id="email" name="email" value="<?php echo $resume['email'] ?? ''; ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" readonly class="input" required />
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-slate-300 mb-1">Phone Number</label>
                        <input type="tel" placeholder="+1 (555) 123-4567" pattern="^\+91\s?[6-9]\d{9}$" id="phone" name="phone" value="<?php echo $resume['phone'] ?? ''; ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" readonly class="input" required />
                    </div>
                    <div>
                        <label for="role" class="block text-sm font-medium text-slate-300 mb-1">Current Role</label>
                        <input type="text" placeholder="senior developer" id="role" name="role" value="<?php echo $resume['role'] ?? ''; ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" readonly class="input" required />
                    </div>
                    <div>
                        <label for="experience" class="block text-sm font-medium text-slate-300 mb-1">Years of Experience</label>
                        <input type="number" placeholder="8" id="experience" name="experience" value="<?php echo $resume['experience'] ?? ''; ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" readonly class="input" required />
                    </div>
                    <div>
                        <label for="username" class="block text-sm font-medium text-slate-300 mb-1">User Name</label>
                        <input type="text" placeholder="john@123" id="username" name="username" value="<?php echo $resume['username'] ?? ''; ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" readonly class="input" required />
                    </div>
                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-medium text-slate-300 mb-1">Address</label>
                        <textarea placeholder="Address" id="address" name="address" rows="3" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600 " readonly class="input" required><?php echo $resume['address'] ?? ''; ?></textarea>
                    </div>
                </div>

            </div>

            <div class="p-6 rounded-lg shadow bg-slate-800">
                <h3 class="mb-4 text-lg font-semibold text-white">Career Objective</h3>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    <div class="md:col-span-2">
                        <label for="summary" class="block text-sm font-medium text-slate-300 mb-1">Summary</label>
                        <textarea placeholder="About Your Career" id="summary" name="summary" rows="3" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600 " readonly class="input" required><?php echo $resume['summary'] ?? ''; ?></textarea>
                    </div>
                </div>
            </div>

            <div class="p-6 rounded-lg shadow bg-slate-800">
                <h3 class="mb-4 text-lg font-semibold text-white">Qualification</h3>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    <div>
                        <label for="degree" class="block text-sm font-medium text-slate-300 mb-1">Degree</label>
                        <input type="text" placeholder="Mca,Msc&IT,PGDCA" id="degree" name="degree" value="<?php echo $resume['degree'] ?? ''; ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" readonly class="input" required />
                    </div>

                    <div>
                        <label for="university" class="block text-sm font-medium text-slate-300 mb-1">University</label>
                        <input type="text" placeholder="Harvard University" id="university" name="university" value="<?php echo $resume['university'] ?? ''; ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" readonly class="input" required />
                    </div>
                    <div>
                        <label for="passing_year" class="block text-sm font-medium text-slate-300 mb-1">Passing Year</label>
                        <input type="number" placeholder="2006" id="passing_year" value="<?php echo $resume['passing_year'] ?? ''; ?>" name="passing_year" min="2000" max="2020" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" readonly class="input" required />
                    </div>
                    <div>
                        <label for="percentage" class="block text-sm font-medium text-slate-300 mb-1">Percentage</label>
                        <input type="number" placeholder="75.5" id="percentage" value="<?php echo $resume['percentage'] ?? ''; ?>" name="percentage" min="0%" max="100%" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" readonly class="input" required />
                    </div>
                </div>
            </div>

            <div class="p-6 rounded-lg shadow bg-slate-800">
                <h3 class="mb-4 text-lg font-semibold text-white">Work Experience</h3>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    <div>
                        <label for="jobtitle" class="block text-sm font-medium text-slate-300 mb-1">Job Title</label>
                        <input type="text" placeholder="Python Developer" id="job_title" name="job_title" value="<?php echo $resume['job_title'] ?? ''; ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" readonly class="input" required />
                    </div>
                    <div>
                        <label for="company name" class="block text-sm font-medium text-slate-300 mb-1">Company Name</label>
                        <input type="text" placeholder="Google,Microsoft,Nvdia" id="company_name" name="company_name" value="<?php echo $resume['company_name'] ?? ''; ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" readonly class="input" required />
                    </div>
                    <div>
                        <label for="Duration(From)" class="block text-sm font-medium text-slate-300 mb-1">Duration(From)</label>
                        <input type="date" placeholder="" id="work_duration_from" name="work_duration_from" value="<?php echo $resume['work_duration_from'] ?? ''; ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" readonly class="input" required />
                    </div>
                    <div>
                        <label for="Duration(To)" class="block text-sm font-medium text-slate-300 mb-1">Duration(To)</label>
                        <input type="date" placeholder="" id="work_duration_to" name="work_duration_to" value="<?php echo $resume['work_duration_to'] ?? ''; ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" readonly class="input" required />
                    </div>
                </div>
            </div>

            <div class="p-6 rounded-lg shadow bg-slate-800">
                <h3 class="mb-4 text-lg font-semibold text-white">Salary</h3>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    <div>
                        <label for="previous_salary" class="block text-sm font-medium text-slate-300 mb-1">Previous Salary</label>
                        <input type="number" placeholder="25000" id="previous_salary" name="previous_salary" value="<?php echo $resume['previous_salary'] ?? ''; ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" readonly class="input" required />
                    </div>

                    <div>
                        <label for="expected_salary" class="block text-sm font-medium text-slate-300 mb-1">Expected Salary</label>
                        <input type="number" placeholder="50000" id="expected_salary" name="expected_salary" value="<?php echo $resume['expected_salary'] ?? ''; ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" readonly class="input" required />
                    </div>
                </div>
            </div>


            <div class="p-6 rounded-lg shadow bg-slate-800">
                <h3 class="mb-4 text-lg font-semibold text-white">Skills</h3>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    <div>
                        <label for="Programming Languages" class="block text-sm font-medium text-slate-300 mb-1">Programming Languages</label>
                        <input type="text" placeholder="Python,Java,Aws,php" id="programming_languages" name="programming_languages" value="<?php echo $resume['programming_languages'] ?? ''; ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" readonly class="input" required />
                    </div>
                    <div>
                        <label for="Tools_Frameworks" class="block text-sm font-medium text-slate-300 mb-1">Tools/Frameworks</label>
                        <input type="text" placeholder="React,Node js,Laravel,Django" id="tools_frameworks" name="tools_frameworks" value="<?php echo $resume['tools_frameworks'] ?? ''; ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" readonly class="input" required />
                    </div>
                </div>
            </div>

            <div class="p-6 rounded-lg shadow bg-slate-800">
                <h3 class="mb-4 text-lg font-semibold text-white">Hobbies</h3>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    <div class="md:col-span-2">
                        <label for="hobbies" class="block text-sm font-medium text-slate-300 mb-1">Hobbies</label>
                        <textarea placeholder="Intresting Thing About Yourself " id="hobbies" name="hobbies" rows="3" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600 " readonly class="input" required><?php echo $resume['hobbies'] ?? ''; ?></textarea>
                    </div>
                </div>
            </div>

            <div class="p-6 rounded-lg shadow bg-slate-800">
                <h3 class="mb-4 text-lg font-semibold text-white">References</h3>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    <div>
                        <label for="Company Name" class="block text-sm font-medium text-slate-300 mb-1">Company Name</label>
                        <input type="text" placeholder="Google,Microsoft,Nvidia" id="ref_name" name="ref_name" value="<?php echo $resume['ref_name'] ?? ''; ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" readonly class="input" required />
                    </div>

                    <div>
                        <label for="position" class="block text-sm font-medium text-slate-300 mb-1">Company Position</label>
                        <input type="text" placeholder="Senior Engineer" id="ref_position" name="ref_position" value="<?php echo $resume['ref_position'] ?? ''; ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" readonly class="input" required />
                    </div>

                    <div>
                        <label for="contact info" class="block text-sm font-medium text-slate-300 mb-1">Company Contact No</label>
                        <input type="tel" placeholder="+91 98765 43210" pattern="^\+91\s?[6-9]\d{9}$" id="ref_contact" name="ref_contact" value="<?php echo $resume['ref_contact'] ?? ''; ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" readonly class="input" required />
                    </div>

                    <div>
                        <label for="contact info" class="block text-sm font-medium text-slate-300 mb-1">Company Email</label>
                        <input type="email" placeholder="Nvdia2025@gmail.com" id="ref_email" name="ref_email" value="<?php echo $resume['ref_email'] ?? ''; ?>" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" readonly class="input" required />
                    </div>
                </div>

                
                <div class="mt-8 flex justify-center">
                    <button type="button" id="edit-btn" onclick="toggleEdit()" class="px-6 py-2 text-sm font-medium text-white rounded-lg gradient-bg hover:opacity-90">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </button>
                    


                    
        </form>
    </div>

    </div>
    </div>
    </form>

    </div>
<?php endif ?>

<script>
    function toggleDropdown() {
        const dropdown = document.getElementById('dropdown');
        dropdown.classList.toggle('hidden');
    }

    window.addEventListener('click', function(e) {
        const dropdown = document.getElementById('dropdown');
        const button = document.querySelector('button[onclick="toggleDropdown()"]');
        if (!dropdown.contains(e.target) && !button.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
</script>

<script src="script.js">


</script>






</body>

</html>