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
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
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
        $logo_sql = $conn->prepare("SELECT logo FROM company_profiles WHERE username = ?");
    } else {
        $logo_sql = $conn->prepare("SELECT logo FROM user_profiles WHERE username = ?");
    }
    $logo_sql->bind_param("s", $username);
    $logo_sql->execute();
    $logo_res = $logo_sql->get_result()->fetch_assoc();
    $raw['logo'] = $logo_res['logo'] ?? '';
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Fetch the latest user profile
if ($role === 'user') {
    $stmt = $conn->prepare("SELECT * FROM user_profiles WHERE username=?");
} else if ($role === 'company') {
    $stmt = $conn->prepare("SELECT * FROM company_profiles WHERE username=?");
}
$stmt->bind_param("s", $username);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc(); // For users or companies

$username = $_SESSION['username'];
$result = mysqli_query($conn, "SELECT logo FROM user_profiles WHERE username = '$username'");
$row = mysqli_fetch_assoc($result);
$logoPath = $row['logo'] ?? 'default-logo.png';

if (!isset($_SESSION['username'])  || !isset($_SESSION['role'])) {
    echo "<script>alert('login first'); window.location.href='login.php';</script>";
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];

$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $birthdate = $_POST['birthdate'];
    $language = $_POST['language'];
    $aboutme = $_POST['aboutme'];
    $skills = $_POST['skills'];
    $logo = $_POST['logo'];

    $check = mysqli_query($conn, "SELECT * FROM user_profiles WHERE username='$username'");
    if (mysqli_num_rows($check) > 0) {
        $update = "UPDATE user_profiles SET fullname='$fullname', phone='$phone', address='$address', username='$username', email='$email', birthdate='$birthdate',
        language='$language', aboutme='$aboutme', skills='$skills', logo='$logo' WHERE username='$username'";
        mysqli_query($conn, $update);
    } else {
        $insert = "INSERT INTO user_profiles (username, fullname, phone, address, email, birthdate, aboutme, skills, language, logo)
                   VALUES ('$username', '$fullname', '$phone', '$address', '$email', '$birthdate', '$aboutme', '$skills', '$language', '$logo')";
        mysqli_query($conn, $insert);
    }

    echo "<script>alert('Profile saved successfully.'); window.location.href='index.php';</script>";
}
$username = $_SESSION['username'];
$query = "SELECT fullname, phone, address, email, birthdate, aboutme, skills, language, logo, is_verified, username FROM user_profiles WHERE username='$username'";
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
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>NexusCareer - Resume Analysis Platform</title>
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

    <link href="styles.css" rel="stylesheet" />


</head>

<body class="dark">

    <body class=" overflow-auto h-screen">
        <!-- Main Layout -->
        <div class="flex h-screen overflow-hidden">


            <!-- Sidebar -->
            <div class="hidden md:flex md:flex-shrink-0">
                <div class="flex flex-col w-64 bg-slate-900 border-r border-slate-800">
                    <div class="flex items-center h-16 px-4">
                        <h1 class="text-2xl font-bold text-white"><a href="index.php">Nexus<span class="text-primary-600">Career</span></a></h1>
                    </div>
                    <!-- User Profile -->

                    <div class="flex items-center px-4 py-3 border-b border-slate-800">
                        <div class="relative">
                            <img alt="User Logo" class="w-10 h-10 rounded-full" src="uploads/<?php echo $raw['logo'] ?? 'default-logo.png'; ?>" />

                            <?php if ($role === 'user' && !empty($row['is_verified']) && $row['is_verified'] == 1): ?>
                                <span class="absolute bottom-0 right-0 bg-blue-600 text-white text-[10px] px-1 rounded-full shadow">
                                         <i class="fas fa-check-circle"></i>
                                    </span>
                            <?php endif; ?>
                        </div>

                        <div class="ml-3">
                            <p class="font-medium text-white"><?php echo $raw['fullname'] ?? $raw['company_name'] ?? ''; ?></p>
                            <p class="text-xs text-slate-400"><?php echo $raw['email'] ?? ''; ?></p>
                        </div>
                    </div>


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
            <?php if (basename($_SERVER['PHP_SELF']) == 'myjobs.php') echo 'bg-purple-700 text-white'; ?>">
                                    <i class="fas fa-file-alt mr-3"></i> My Resume
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
                        
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Dark Mode Toggle -->
                        <!-- Theme Toggle Switch -->



                        <!-- BELL ICON AND DROPDOWN -->
                       
                        



                        <a href="profile.php">
                            <div class="relative ">
                                <button class="flex items-center space-x-2">
                                <img src="uploads/<?php echo $row['logo'] ?? 'default-logo.png'; ?>"
                                    alt="User Logo" class="w-8 h-8 rounded-full">
                                <?php if (!empty($row['is_verified']) && $row['is_verified'] == 1): ?>
                                    <span class="absolute bottom-0 right-0 bg-blue-600 text-white text-[10px] px-1 rounded-full shadow">
                                       <i class="fas fa-check-circle"></i>
                                    </span>
                                <?php endif; ?>
                            </div>

                        </a>
                        

                    
                </div>
            </div>

            <div class="p-6 rounded-lg shadow bg-slate-800">
                <h3 class="mb-4 text-lg font-semibold text-white">Personal Information</h3>
                <form action="profile.php" method="POST">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">


                        <div>
                            <label for="fullname" class="block text-sm font-medium text-slate-300 mb-1">Full Name</label>
                            <input type="text" placeholder="Alex Johnson" id="fullname" name="fullname" value="<?php echo $row['fullname'] ?? null; ?>"
                                class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" />
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-300 mb-1">Email Address</label>
                            <input type="email" placeholder="alex.johnson@example.com" id="email" name="email" value="<?php echo $row['email'] ?? null; ?>"
                                class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" />
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-slate-300 mb-1">Phone Number</label>
                            <input type="tel" placeholder="+1 (555) 123-4567" pattern="^\+91\s?[6-9]\d{9}$" id="phone" name="phone" value="<?php echo $row['phone'] ?? null; ?>"
                                class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" />
                        </div>
                        <div>
                            <label for="username" class="block text-sm font-medium text-slate-300 mb-1">User Name</label>
                            <input type="text" placeholder="john@123" id="username" name="username" value="<?php echo $row['username'] ?? null; ?>"
                                class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600"
                                required />
                        </div>
                        <div>
                            <label for="birthdate" class="block text-sm font-medium text-slate-300 mb-1">Birth Date</label>
                            <input type="date" placeholder="21/04/2006" id="birthdate" name="birthdate" value="<?php echo $row['birthdate'] ?? null; ?>"
                                class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" />
                        </div>
                        <div>
                            <label for="language" class="block text-sm font-medium text-slate-300 mb-1">Languages</label>
                            <input type="text" placeholder="English,Gujarati,Hindi" id="language" name="language" value="<?php echo $row['language'] ?? null; ?>"
                                class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600" />
                        </div>
                        <div class="md:col-span-2">
                            <label for="aboutme" class="block text-sm font-medium text-slate-300 mb-1">About Me</label>
                            <textarea placeholder="Tell Us About Yourself" id="skills" name="aboutme" rows="3" value="<?php echo $row['aboutme'] ?? null; ?>"
                                class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600 "><?php echo $row['aboutme'] ?? null; ?></textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label for="address" class="block text-sm font-medium text-slate-300 mb-1">Address</label>
                            <textarea placeholder="Address" id="address" name="address" rows="3"
                                class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600 "><?php echo $row['address'] ?? null; ?></textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label for="skills" class="block text-sm font-medium text-slate-300 mb-1">Skills</label>
                            <textarea placeholder="Python,Java Script,React,Node Js,C++" id="skills" name="skills" rows="3"
                                class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600 "><?php echo $row['skills'] ?? null; ?></textarea>
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
                                <input accept="image/*" name="logo"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" id="logo"
                                    type="file" />
                            </div>
                        </div>


                        <div class="mt-8 flex justify-center">
                            <button type="submit"
                                class="px-6 py-2 text-sm font-medium text-white rounded-lg gradient-bg hover:opacity-90">
                                <i class="fas fa-save mr-2"></i>Submit
                            </button>

                        </div>
                    </div>
            </div>
        </div>
        </div>
        </div>

    <?php elseif ($role === 'company'): ?>

        <!-- Navigation -->
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
                        <input
                            class="w-full py-2 pl-10 pr-4 text-sm bg-slate-700 border-0 rounded-lg focus:ring-2 focus:ring-primary-600 text-slate-200 placeholder-slate-400"
                            placeholder="Search jobs, profiles..." type="text" />
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Dark Mode Toggle -->
                    <!-- Theme Toggle Switch -->



                    <!-- BELL ICON AND DROPDOWN -->
                    <div class="relative inline-block text-left">
                        <!-- Bell Icon -->
                        <button onclick="toggleDropdown()" class="relative p-2 text-slate-400 rounded-full hover:text-slate-300 hover:bg-slate-700 focus:outline-none transition">
                            <i class="fas fa-bell"></i>
                            <?php if ($noti_count > 0): ?>
                                <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] min-w-[18px] h-[18px] flex items-center justify-center rounded-full">
                                    <?= $noti_count ?>
                                </span>
                            <?php endif; ?>
                        </button>

                        <!-- Dropdown -->
                        <div id="dropdown" class="hidden absolute right-0 mt-2 w-96 bg-white shadow-xl ring-1 ring-black ring-opacity-10 rounded-xl z-50 overflow-hidden">

                            <!-- Header with user info -->
                            <div class="flex items-center gap-3 p-4 bg-gray-50 border-b">
                                <img src="uploads/<?= !empty($raw['logo']) ? htmlspecialchars($raw['logo']) : 'default-logo.png' ?>"
                                    alt="logo"
                                    class="w-10 h-10 rounded-full object-cover border border-gray-300">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($username) ?></div>
                                    <div class="text-xs text-gray-500"><?= ucfirst($role) ?> Panel</div>
                                </div>
                            </div>

                            <!-- Notifications -->
                            <div class="max-h-96 overflow-y-auto divide-y divide-gray-100">
                                <?php if (!empty($notifications)): ?>
                                    <?php foreach ($notifications as $noti): ?>
                                        <a href="<?= htmlspecialchars($noti['link']) ?>"
                                            class="block px-4 py-3 hover:bg-gray-50 transition text-sm text-gray-800 notification-item"
                                            data-type="<?= htmlspecialchars($noti['type'] ?? 'general') ?>">

                                            <div class="font-medium text-gray-900 leading-5">
                                                <?= htmlspecialchars(strip_tags($noti['message'])) ?>
                                            </div>

                                            <div class="text-xs text-gray-400 mt-1">
                                                <?= date("M d, Y h:i A", strtotime($noti['created_at'])) ?>
                                            </div>

                                            <?php if (!empty($noti['type'])): ?>
                                                <div class="mt-1">
                                                    <span class="inline-block text-[10px] uppercase font-medium px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
                                                        <?= strtoupper($noti['type']) ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="p-4 text-sm text-gray-500 text-center">No notifications</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>


                    <a href="profile.php">
                        <div class="relative">
                            <button class="flex items-center space-x-2">
                                <img alt="User profile" class="w-8 h-8 rounded-full"
                                    src="uploads/<?php echo $raw['logo']; ?>" />
                            </button>
                    </a>
                </div>
            </div>
        </div>

        <div class="p-6 rounded-lg shadow bg-slate-800">
            <h3 class="mb-4 text-lg font-semibold text-white">Company Information</h3>
            <form action="company_profile.php" method="POST">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">


                    <div>
                        <label for="company_name" class="block text-sm font-medium text-slate-300 mb-1">Company Name</label>
                        <input type="text" placeholder="Jio Company" id="company_name" name="company_name" value="<?php echo $raw['company_name'] ?? null; ?>"
                            class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600"
                            required />
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-300 mb-1">Email Address</label>
                        <input type="email" placeholder="alex.johnson@example.com" id="email" name="email" value="<?php echo $raw['email'] ?? null; ?>"
                            class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600"
                            required />
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-slate-300 mb-1">Phone Number</label>
                        <input type="tel" placeholder="+1 (555) 123-4567" pattern="^\+91\s?[6-9]\d{9}$" id="phone" name="phone" value="<?php echo $raw['phone'] ?? null; ?>"
                            class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600"
                            required />
                    </div>
                    <div>
                        <label for="username" class="block text-sm font-medium text-slate-300 mb-1">User Name</label>
                        <input type="text" placeholder="john@123" id="username" name="username" value="<?php echo $raw['username'] ?? null; ?>"
                            class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600"
                            required />
                    </div>

                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-medium text-slate-300 mb-1">Address</label>
                        <textarea placeholder="Address" id="address" name="address" rows="3"
                            class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600 "
                            required><?php echo $raw['address'] ?? null; ?></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-slate-300 mb-1">Description</label>
                        <textarea placeholder="Tell Us About Yourself" id="description" name="description" rows="3" value="<?php echo $raw['description'] ?? null; ?>"
                            class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-md text-white focus:ring-primary-600 focus:border-primary-600 "
                            required><?php echo $raw['description'] ?? null; ?></textarea>
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
                            <input accept="image/*" name="logo"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" id="logo"
                                type="file" />
                        </div>
                    </div>


                    <div class="mt-8 flex justify-center">
                        <button type="submit"
                            class="px-6 py-2 text-sm font-medium text-white rounded-lg gradient-bg hover:opacity-90">
                            <i class="fas fa-save mr-2"></i>Submit
                        </button>

                    </div>
                </div>
        </div>
        </div>
        </div>
        </div>
    <?php elseif ($role === 'admin'): ?>


    <?php endif; ?>
    <script src="script.js"></script>
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

    </body>

</html>