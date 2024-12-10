<?php
require_once '../Database/clearancedb.php';
session_start(); // Start the session to use session variables

// Check if user is logged in, redirect if not
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Retrieve error message if it exists
$errorMessage = isset($_SESSION['error']) ? $_SESSION['error'] : "";
unset($_SESSION['error']); // Clear the error message after displaying it

// Fetch the logged-in user's email from the database
$user_id = $_SESSION['user_id'];
$statement = $pdo->prepare('SELECT email FROM admin WHERE user_id = ?');
$statement->execute([$user_id]);
$email = $statement->fetchColumn();

// Store the fetched email in the session
$_SESSION['user_email'] = $email; // Now you can use this throughout your application

// Get the selected directory type from the form (default to 'user')
$directoryType = isset($_POST['directoryType']) ? $_POST['directoryType'] : 'user';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="Admin_Create_Account.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>

<body>
<div class="sidebar">
    <!-- Replace the text label with the logo -->
    <img src="/MasterThesis/images/perpetualsmallicon.png" alt="Perpetual Logo" class="logo-image">


    <ul>
        <li>
            <a href="Admin_dashboard.php" class="button">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="Admin_Create_Account.php" class="button">
                <i class="fas fa-user-plus"></i> Create Account
            </a>
        </li>
        <li>
            <a href="Create_Clearanceform.php" class="button">
                <i class="fas fa-file-alt"></i> Create ClearanceForm
            </a>
        </li>
        <li>
            <a href="#" class="button" onclick="openQRModal()">
                <i class="fas fa-qrcode"></i> Scan QR
            </a>
        </li>
    </ul>
    <div class="sidebar-bottom">
        <a href="../logout.php" class="button">
            <i class="fas fa-sign-out-alt"></i> Log Out
        </a>
        <p>
            <i class="fas fa-user"></i> Logged in as: cc@gmail.com
        </p>
    </div>
</div>




    <!-- Registration Form -->
    <div class="container" id="signup">
        <h1 class="form-title">Create Account</h1>
        <form method="post" action="register.php">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="username" id="username" placeholder="Username" required>
                <label for="username">Username</label>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="Password" required>
                <label for="password">Password</label>
            </div>
            <!-- Role Dropdown -->
            <div class="input-group">
                <i class="fas fa-users"></i>
                <select name="role" id="role" required>
                    <option value="">Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="student">Student</option>
                </select>
            </div>
            <input type="submit" class="btn" value="Sign Up" name="signUp">
        </form>

        <!-- Display error message if it exists -->
        <?php if ($errorMessage): ?>
            <div class="error-message" style="color: red; margin-top: 10px;"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
