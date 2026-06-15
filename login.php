<?php
session_start();
include 'db.php'; // DB connection

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Lookup user with matching username
    $userQuery = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");

    if (mysqli_num_rows($userQuery) === 0) {
        echo "<script>alert('User not found. Please sign up first.'); window.location.href='login.php';</script>";
        exit();
    }

    $user = mysqli_fetch_assoc($userQuery);

    // Verify password
    if (password_verify($password, $user['password'])) {
        // Store user session
        $_SESSION['username'] = $user['username'];

        $_SESSION['role'] = $user['role'];

        // --- Notify all admins on login ---
        $who  = $_SESSION['username'];
        $role = $_SESSION['role'];

        if ($role === 'company') {
            $msg  = "Company <strong>{$who}</strong> logged in.";
            $link = "manage_companies.php"; // change if different
        } else {
            $msg  = "User <strong>{$who}</strong> logged in.";
            $link = "manage_user.php";
        }
        $type = 'login';

        $admins = $conn->query("SELECT username FROM users WHERE role='admin'");
        $ins = $conn->prepare("INSERT INTO notifications (receiver_username, message, link, type) VALUES (?,?,?,?)");
        while ($a = $admins->fetch_assoc()) {
            $adminUser = $a['username'];
            $ins->bind_param("ssss", $adminUser, $msg, $link, $type);
            $ins->execute();
        }
        $ins->close();

        $statusCheck = $conn->prepare("SELECT status FROM user_status WHERE username = ?");
        $statusCheck->bind_param("s", $username);
        $statusCheck->execute();
        $statusRow = $statusCheck->get_result()->fetch_assoc();
        $statusCheck->close();

        if ($statusRow && $statusRow['status'] === 'banned') {
            echo "<script>
        alert('Your account has been permanently banned by Admin. You cannot login.');
        window.location.href='login.php';
    </script>";
            exit();
        }

        // ✅ Only allow login if NOT banned
        $_SESSION['username'] = $username;


        // Redirect based on role
        if ($user['role'] === 'admin') {
            echo "<script>alert('Welcome Admin!'); window.location.href='admin_dashboard.php';</script>";
        } elseif ($user['role'] === 'company') {
            echo "<script>alert('Welcome  $username !'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Welcome  $username '); window.location.href='index.php';</script>";
        }
        exit();
    } else {
        echo "<script>alert('Incorrect password.'); window.location.href='login.php';</script>";
        exit();
    }
}

//for admin notificaion who login



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Zentrua - Sign up / Login Form</title>
    <link rel="stylesheet" href="login.css">

</head>

<body>
    <!DOCTYPE html>
    <html>

    <head>
        <title>Slide Navbar</title>
        <link rel="stylesheet" type="text/css" href="assets/css/login.css">
        <link href="https://fonts.googleapis.com/css2?family=Jost:wght@500&display=swap" rel="stylesheet">


    </head>

    <body>
        <div class="main">
            <input type="checkbox" id="chk" aria-hidden="true">

            <div class="signup">

                <form action="signup.php" method="post">

                    <label for="chk" aria-hidden="true">Sign up</label>
                    <input type="text" name="username" placeholder="User name" required>
                    <input type="email" name="email" pattern="^[a-zA-Z0-9._%+-]+@gmail\.com$" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Sign up</button>

                </form>
            </div>

            <div class="login">
                <form action="login.php" method="post">
                    <label for="chk" aria-hidden="true">Login</label>
                    <input type="text" name="username" placeholder="User name" required>

                    <input type="password" name="password" placeholder="Password" required>

                    <button>Login</button>



                </form>


            </div>
        </div>
    </body>

    </html>
    <!-- partial -->

</body>

</html>