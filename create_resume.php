<?php
session_start();
include 'db.php';
include 'notification_helper.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    echo "<script>alert('login first'); window.location.href='login.php';</script>";
}




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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


    $check = mysqli_query($conn, "SELECT * FROM create_resume WHERE username='$username'");
    if (mysqli_num_rows($check) > 0) {
        $sql = "UPDATE create_resume SET name='$name', email='$email', phone='$phone', role='$role', experience='$experience', 
        address='$address', summary='$summary', degree='$degree', university='$university', passing_year='$passing_year', percentage='$percentage', job_title=' $job_title', company_name='$company_name',
         work_duration_from='$work_duration_from', work_duration_to='$work_duration_to', previous_salary='$previous_salary', expected_salary='$expected_salary', programming_languages='$programming_languages', tools_frameworks='$tools_frameworks',
          hobbies='$hobbies', ref_name='$ref_name', ref_position='$ref_position', ref_contact='$ref_contact', ref_email='$ref_email'  WHERE username='$username' ";
    } else {
        $sql = "INSERT INTO create_resume (username, name, email, phone, role, experience, address, summary, degree, university, passing_year, percentage, job_title,
        company_name, work_duration_from, work_duration_to, previous_salary, expected_salary, programming_languages, tools_frameworks, hobbies, ref_name, ref_position, ref_contact,ref_email)
         VALUES ('$username', '$name','$email', '$phone', '$role', '$experience', '$address', '$summary', '$degree', '$university', '$passing_year', '$percentage',
         '$job_title', '$company_name', '$work_duration_from', '$work_duration_to', '$previous_salary', '$expected_salary', '$programming_languages', '$tools_frameworks', '$hobbies', '$ref_name', '$ref_position', '$ref_contact', '$ref_email')";
    }

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Resume saved successfully'); window.location.href='create_resume.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];


$username = $_SESSION['username'];
$query = "SELECT fullname, email, logo FROM user_profiles WHERE username='$username'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

$username = $_SESSION['username'] ?? null;
$query = "SELECT * FROM company_profiles WHERE username='$username'";
$result = mysqli_query($conn, $query);
$raw = mysqli_fetch_assoc($result);



?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
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
                        <img alt="User profile" class="w-10 h-10 rounded-full"
                            <?php if ($role === 'user'): ?>
                            src="uploads/<?php echo $row['logo']; ?>" />
                    <?php elseif ($role === 'company'): ?>
                        src="uploads/<?php echo $raw['logo']; ?>" />
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

                <?php if ($role === 'user'): ?>
                    <!-- Navigation -->
                    <nav class="flex-1 px-2 py-4 space-y-1">
                        <a class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 rounded-md sidebar-link active" href="index.php">
                            <i class="fas fa-chart-pie mr-3 text-slate-400"></i>
                            Dashboard
                        </a>
                        <a class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 rounded-md sidebar-link" href="profile.php">
                            <i class="fas fa-user mr-3 text-slate-400"></i>
                            Profile
                        </a>
                        <a class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 rounded-md sidebar-link" href="myresume.php">
                            <i class="fas fa-file-alt mr-3 text-slate-400"></i>
                            My Resumes
                        </a>
                        <a class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 rounded-md sidebar-link" href="job matches.php">
                            <i class="fas fa-briefcase mr-3 text-slate-400"></i>
                            Job Matches
                        </a>
                        <?php if (isset($_SESSION['username'])): ?>
                            <a class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 rounded-md sidebar-link"
                                href="logout.php">
                                <i class="fas fa-sign-out-alt mr-3 text-slate-400"></i>
                                Logout
                            </a>
                        <?php else: ?>
                            <a class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 rounded-md sidebar-link"
                                href="login.php">
                                <i class="fas fa-users mr-3 text-slate-400"></i>
                                Login
                            </a>
                        <?php endif; ?>
                        <a class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 rounded-md sidebar-link" href="setting.php">
                            <i class="fas fa-cog mr-3 text-slate-400"></i>
                            Settings
                        </a>
                    </nav>
                    <!-- Upgrade Banner -->

            </div>
        </div>
        <!-- Main Content -->
        <div class="flex flex-col flex-1 overflow-auto custom-scrollbar">

            <!-- Top Navigation -->
            <div class="flex items-center justify-between h-16 px-4 bg-slate-800 border-b border-slate-700">
                <div class="flex items-center">
                    <button class="p-1 text-slate-400 rounded-md md:hidden hover:text-slate-300 hover:bg-slate-700">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="relative ml-4">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-search text-slate-500"></i>
                        </div>
                        <input class="w-full py-2 pl-10 pr-4 text-sm bg-slate-700 border-0 rounded-lg focus:ring-2 focus:ring-primary-600 text-slate-200 placeholder-slate-400" placeholder="Search jobs, profiles..." type="text" />
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Dark Mode Toggle -->
                    <!-- Theme Toggle Switch -->



                    <button class="p-2 text-slate-400 rounded-full hover:text-slate-300 hover:bg-slate-700">
                        <i class="fas fa-bell"></i>
                    </button>
                    <a href="profile.php">
                        <div class="relative">
                            <button class="flex items-center space-x-2">
                                <img alt="User profile" class="w-8 h-8 rounded-full" src="uploads/<?php echo $row['logo']; ?>" />
                            </button>
                    </a>
                </div>
            </div>
        </div>

        <div class="p-6 rounded-lg shadow bg-slate-800">
            <h3 class="mb-4 text-lg font-semibold text-white">Personal Information</h3>
            <form action="create_resume.php" method="post">


                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-300 mb-1">Full Name</label>
                        <input type="text" placeholder="Alex Johnson" id="name" name="name" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-300 mb-1">Email Address</label>
                        <input type="email" placeholder="alex.johnson@example.com" id="email" name="email" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-slate-300 mb-1">Phone Number</label>
                        <input type="tel" placeholder="+1 (555) 123-4567" pattern="^\+91\s?[6-9]\d{9}$" id="phone" name="phone" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                    </div>
                    <div>
                        <label for="role" class="block text-sm font-medium text-slate-300 mb-1">Current Role</label>
                        <input type="text" placeholder="senior developer" id="role" name="role" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                    </div>
                    <div>
                        <label for="experience" class="block text-sm font-medium text-slate-300 mb-1">Years of Experience</label>
                        <input type="number" placeholder="8" id="experience" name="experience" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                    </div>
                    <div>
                        <label for="username" class="block text-sm font-medium text-slate-300 mb-1">User Name</label>
                        <input type="text" placeholder="john@123" id="username" name="username" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                    </div>
                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-medium text-slate-300 mb-1">Address</label>
                        <textarea placeholder="Address" id="address" name="address" rows="3" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600 " required></textarea>
                    </div>
                </div>

        </div>

        <div class="p-6 rounded-lg shadow bg-slate-800">
            <h3 class="mb-4 text-lg font-semibold text-white">Career Objective</h3>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <div class="md:col-span-2">
                    <label for="summary" class="block text-sm font-medium text-slate-300 mb-1">Summary</label>
                    <textarea placeholder="About Your Career" id="summary" name="summary" rows="3" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600 " required></textarea>
                </div>
            </div>
        </div>

        <div class="p-6 rounded-lg shadow bg-slate-800">
            <h3 class="mb-4 text-lg font-semibold text-white">Qualification</h3>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <div>
                    <label for="degree" class="block text-sm font-medium text-slate-300 mb-1">Degree</label>
                    <input type="text" placeholder="Mca,Msc&IT,PGDCA" id="degree" name="degree" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                </div>

                <div>
                    <label for="university" class="block text-sm font-medium text-slate-300 mb-1">University</label>
                    <input type="text" placeholder="Harvard University" id="university" name="university" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                </div>
                <div>
                    <label for="passing_year" class="block text-sm font-medium text-slate-300 mb-1">Passing Year</label>
                    <input type="number" placeholder="2006" id="passing_year" name="passing_year" min="2000" max="2020" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                </div>
                <div>
                    <label for="percentage" class="block text-sm font-medium text-slate-300 mb-1">Percentage</label>
                    <input type="number" placeholder="75.5" id="percentage" name="percentage" min="0%" max="100%" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                </div>
            </div>
        </div>

        <div class="p-6 rounded-lg shadow bg-slate-800">
            <h3 class="mb-4 text-lg font-semibold text-white">Work Experience</h3>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <div>
                    <label for="jobtitle" class="block text-sm font-medium text-slate-300 mb-1">Job Title</label>
                    <input type="text" placeholder="Python Developer" id="job_title" name="job_title" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                </div>
                <div>
                    <label for="company name" class="block text-sm font-medium text-slate-300 mb-1">Company Name</label>
                    <input type="text" placeholder="Google,Microsoft,Nvdia" id="company_name" name="company_name" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                </div>
                <div>
                    <label for="Duration(From)" class="block text-sm font-medium text-slate-300 mb-1">Duration(From)</label>
                    <input type="date" placeholder="" id="work_duration_from" name="work_duration_from" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                </div>
                <div>
                    <label for="Duration(To)" class="block text-sm font-medium text-slate-300 mb-1">Duration(To)</label>
                    <input type="date" placeholder="" id="work_duration_to" name="work_duration_to" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                </div>
            </div>
        </div>

        <div class="p-6 rounded-lg shadow bg-slate-800">
            <h3 class="mb-4 text-lg font-semibold text-white">Salary</h3>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <div>
                    <label for="previous_salary" class="block text-sm font-medium text-slate-300 mb-1">Previous Salary</label>
                    <input type="number" placeholder="25000" id="previous_salary" name="previous_salary" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                </div>

                <div>
                    <label for="expected_salary" class="block text-sm font-medium text-slate-300 mb-1">Expected Salary</label>
                    <input type="number" placeholder="50000" id="expected_salary" name="expected_salary" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                </div>
            </div>
        </div>


        <div class="p-6 rounded-lg shadow bg-slate-800">
            <h3 class="mb-4 text-lg font-semibold text-white">Skills</h3>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <div>
                    <label for="Programming Languages" class="block text-sm font-medium text-slate-300 mb-1">Programming Languages</label>
                    <input type="text" placeholder="Python,Java,Aws,php" id="programming_languages" name="programming_languages" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                </div>
                <div>
                    <label for="Tools_Frameworks" class="block text-sm font-medium text-slate-300 mb-1">Tools/Frameworks</label>
                    <input type="text" placeholder="React,Node js,Laravel,Django" id="tools_frameworks" name="tools_frameworks" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                </div>
            </div>
        </div>

        <div class="p-6 rounded-lg shadow bg-slate-800">
            <h3 class="mb-4 text-lg font-semibold text-white">Hobbies</h3>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <div class="md:col-span-2">
                    <label for="hobbies" class="block text-sm font-medium text-slate-300 mb-1">Hobbies</label>
                    <textarea placeholder="Intresting Thing About Yourself " id="hobbies" name="hobbies" rows="3" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600 " required></textarea>
                </div>
            </div>
        </div>

        <div class="p-6 rounded-lg shadow bg-slate-800">
            <h3 class="mb-4 text-lg font-semibold text-white">References</h3>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <div>
                    <label for="Company Name" class="block text-sm font-medium text-slate-300 mb-1">Company Name</label>
                    <input type="text" placeholder="Google,Microsoft,Nvidia" id="ref_name" name="ref_name" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                </div>

                <div>
                    <label for="position" class="block text-sm font-medium text-slate-300 mb-1">Company Position</label>
                    <input type="text" placeholder="Senior Engineer" id="ref_position" name="ref_position" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                </div>

                <div>
                    <label for="contact info" class="block text-sm font-medium text-slate-300 mb-1">Company Contact No</label>
                    <input type="tel" placeholder="+91 98765 43210" pattern="^\+91\s?[6-9]\d{9}$" id="ref_contact" name="ref_contact" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                </div>

                <div>
                    <label for="contact info" class="block text-sm font-medium text-slate-300 mb-1">Company Email</label>
                    <input type="email" placeholder="Nvdia2025@gmail.com" id="ref_email" name="ref_email" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                </div>
            </div>
            <div class="mt-8 flex justify-center">
                <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg gradient-bg hover:opacity-90">
                    <i class="fas fa-save mr-2"></i>Save
                </button>

            </div>
        </div>
        </form>

    </div>


<?php elseif ($role === 'company'): ?>

    <!-- Navigation -->
    <aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col">
        <!-- Logo / Branding -->

        <!-- Profile Info -->


        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-1">
            <a href="index.php" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            text-slate-300 hover:bg-slate-800 hover:text-white transition sidebar-link active
            <?php if (basename($_SERVER['PHP_SELF']) == 'index.php') echo 'bg-purple-700 text-white'; ?>">
                <i class="fas fa-chart-pie mr-3"></i> Dashboard
            </a>

            <a href="profile.php" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            text-slate-300 hover:bg-slate-800 hover:text-white transition
            <?php if (basename($_SERVER['PHP_SELF']) == 'profile.php') echo 'bg-purple-700 text-white'; ?>">
                <i class="fas fa-user mr-3"></i> Profile
            </a>

            <a href="myjobs.php" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            text-slate-300 hover:bg-slate-800 hover:text-white transition
            <?php if (basename($_SERVER['PHP_SELF']) == 'myjobs.php') echo 'bg-purple-700 text-white'; ?>">
                <i class="fas fa-file-alt mr-3"></i> My Jobs
            </a>

            <a href="job_matches.php" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg 
            text-slate-300 hover:bg-slate-800 hover:text-white transition
            <?php if (basename($_SERVER['PHP_SELF']) == 'job_matches.php') echo 'bg-purple-700 text-white'; ?>">
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
            <div class="flex items-center">
                <button class="p-1 text-slate-400 rounded-md md:hidden hover:text-slate-300 hover:bg-slate-700">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="relative ml-4">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fas fa-search text-slate-500"></i>
                    </div>
                    <input class="w-full py-2 pl-10 pr-4 text-sm bg-slate-700 border-0 rounded-lg focus:ring-2 focus:ring-primary-600 text-slate-200 placeholder-slate-400" placeholder="Search jobs, profiles..." type="text" />
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <!-- Dark Mode Toggle -->
                <!-- Theme Toggle Switch -->



                <button class="p-2 text-slate-400 rounded-full hover:text-slate-300 hover:bg-slate-700">
                    <i class="fas fa-bell"></i>
                </button>
                <a href="profile.php">
                    <div class="relative">
                        <button class="flex items-center space-x-2">
                            <img alt="User profile" class="w-8 h-8 rounded-full" src="uploads/<?php echo $raw['logo']; ?>" />
                        </button>
                </a>
            </div>
        </div>
    </div>

    <div class="p-6 rounded-lg shadow bg-slate-800">
        <h3 class="mb-4 text-lg font-semibold text-white">Job Information</h3>
        <form action="uploadjob.php" method="post">


            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="company_name" class="block text-sm font-medium text-slate-300 mb-1">Company Name</label>
                    <input type="text" placeholder="Reliance Company" id="company_name" name="company_name" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                </div>
                <div>
                    <label for="job_title" class="block text-sm font-medium text-slate-300 mb-1">Job Title</label>
                    <input type="text" placeholder="Python Developer" id="job_title" name="job_title" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                </div>
                <div>
                    <label for="job_type" class="block text-sm font-medium text-slate-300 mb-1">Job Type</label>
                    <input type="text" placeholder="Software Developing" id="job_type" name="job_type" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                </div>
                <div>
                    <label for="job_location" class="block text-sm font-medium text-slate-300 mb-1">Job Location</label>
                    <input type="text" placeholder="Ahemdabad" id="job_location" name="job_location" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
                </div>

                <div class="md:col-span-2">
                    <label for="benefits" class="block text-sm font-medium text-slate-300 mb-1">Benefits</label>
                    <textarea placeholder="About Your Benefits For Employee" id="benefits" name="benefits" rows="4" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600 " required></textarea>
                </div>
            </div>

    </div>

    <div class="p-6 rounded-lg shadow bg-slate-800">
        <h3 class="mb-4 text-lg font-semibold text-white">Job Objective</h3>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            <div>
                <label for="responsibilities" class="block text-sm font-medium text-slate-300 mb-1">Responsibilities</label>
                <input type="text" placeholder="Data Analist" id="responsibilities" name="responsibilities" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
            </div>

            <div>
                <label for="experience_required" class="block text-sm font-medium text-slate-300 mb-1">Experience Required</label>
                <input type="number" placeholder="8" id="experience_required" name="experience_required" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
            </div>

            <div>
                <label for="qualifications" class="block text-sm font-medium text-slate-300 mb-1">Qualifications</label>
                <input type="text" placeholder="Mca,Bsc&IT" id="qualifications" name="qualifications" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
            </div>

            <div>
                <label for="salary" class="block text-sm font-medium text-slate-300 mb-1">Salary</label>
                <input type="number" placeholder="50000" id="salary" name="salary" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
            </div>

            <div>
                <label for="number_of_openings" class="block text-sm font-medium text-slate-300 mb-1">Number Of Openings</label>
                <input type="number" placeholder="20" id="number_of_openings" name="number_of_openings" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
            </div>

            <div>
                <label for="work_mode" class="block text-sm font-medium text-slate-300 mb-1">Work Mode</label>
                <input type="text" placeholder="Remote,Part Time,Full Time" id="work_mode" name="work_mode" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
            </div>

            <div class="md:col-span-2">
                <label for="job_description" class="block text-sm font-medium text-slate-300 mb-1">Description</label>
                <textarea placeholder="About Your Jobs" id="job_description" name="job_description" rows="4" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600 " required></textarea>
            </div>
        </div>
    </div>

    <div class="p-6 rounded-lg shadow bg-slate-800">
        <h3 class="mb-4 text-lg font-semibold text-white">Company Contact Info</h3>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            <div>
                <label for="contact_no" class="block text-sm font-medium text-slate-300 mb-1">Contact No</label>
                <input type="tel" placeholder="+91 98765 43210" pattern="^\+91\s?[6-9]\d{9}$" id="contact_no" name="contact_no" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-slate-300 mb-1">Email</label>
                <input type="email" placeholder="Nvdia2025@gmail.com" id="email" name="email" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
            </div>

            <div>
                <label for="username" class="block text-sm font-medium text-slate-300 mb-1">Username</label>
                <input type="text" placeholder="Jio Company" id="username" name="username" value="" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" required />
            </div>
        </div>


        <div class="p-6 mb-6 rounded-lg shadow bg-slate-800">
            <h3 class="mb-4 text-lg font-semibold text-white flex flex-col items-center justify-center">Upload Logo</h3>
            <div class="file-upload relative flex flex-col items-center justify-center p-8 mb-4 text-center border-2 border-dashed rounded-lg cursor-pointer transition-colors"
                id="dropzone">
                <div class="flex flex-col items-center justify-center">
                    <i class="mb-3 text-4xl text-slate-500 fas fa-cloud-upload-alt"></i>
                    <p class="mb-1 font-medium text-slate-300">Drag and drop your file here</p>
                    <p class="text-sm text-slate-500">or click to browse files</p>
                    <input accept="image/*" name="company_logo"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" id="company_logo"
                        type="file" />
                </div>
            </div>






            <div class="mt-8 flex justify-center">
                <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg gradient-bg hover:opacity-90">
                    <i class="fas fa-save mr-2"></i>Save
                </button>

            </div>
        </div>
        </form>

    </div>
<?php endif; ?>


<script src="script.js"></script>




</body>

</html>