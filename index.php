<?php
// Include the database connection
require_once 'Database/clearancedb.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare the SQL statement to get the user
    $statement = $pdo->prepare('SELECT * FROM user WHERE username = ? LIMIT 1');
    $statement->execute([$username]);
    $user = $statement->fetch();

    if (!$user) {
        $error = "Invalid username or password.";
    } else if (!password_verify($password, $user['password'])) {
        // Using password_verify to ensure password matches if it's hashed
        $error = "Invalid password.";
    } else {
        // Store session data
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $role = $user['role'];

        if ($role == 'admin') {
            // Check if the admin is a Signatory Admin or Super Admin
            $admin_statement = $pdo->prepare('SELECT * FROM admin WHERE user_id = ? LIMIT 1');
            $admin_statement->execute([$user['user_id']]);
            $admin = $admin_statement->fetch();

            if ($admin) {
                $admin_type = $admin['type']; // Get the type (Signatory Admin or Super Admin)

                // Store the role in session and redirect based on admin type
                $_SESSION['role'] = $admin_type;

                if ($admin_type == 'Super Admin') {
                    header("Location: Admin/Admin_dashboard.php");
                    exit();
                } elseif ($admin_type == 'Signatory Admin') {
                    header("Location: Departmentdashboard/Signatory_Dashboard.php");
                    exit();
                } else {
                    $error = "Invalid admin type.";
                }
            } else {
                $error = "No admin found for this user.";
            }
        } elseif ($role == 'student') {
            // If the user is a student, store role in session and redirect
            $_SESSION['role'] = 'student';
            header("Location: students/students_clearanceform.php");
            exit();
        } else {
            $error = "Invalid role.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Main Form</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <!-- Navbar at the top with icon -->
    <nav class="navbar">
        <img src="images/perpetualsmallicon.png" alt="Icon" class="navbar-icon">
    </nav>
    <div class="bottom-navbar"></div>

    <!-- Updated Login Form Container -->
    <div class="login-container">
        <div class="login wrap">
            <div class="h1">QR-UCS</div>

            <!-- Add the form action to POST data to this same page -->
            <form action="" method="POST">
                <input
                    placeholder="Username"
                    id="username"
                    name="username"
                    type="text"
                    required>
                <input
                    placeholder="Password"
                    id="password"
                    name="password"
                    type="password"
                    required>
                <input value="Login" class="btn" type="submit">
            </form>

            <!-- Display error message if login fails -->
            <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
        </div>
    </div>

    <!-- Bottom Navbar -->
    <nav class="navbar bottom-navbar"></nav>
</body>
</html>
