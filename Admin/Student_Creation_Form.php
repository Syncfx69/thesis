<?php
require_once '../Database/clearancedb.php';
session_start();

// Check if the user ID is provided
$user_id = $_GET['user_id'];
if (!isset($user_id) || empty($user_id)) {
    header("Location: ../index.php");
    exit();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If the user is not logged in, redirect them to the login page
    header("Location: index.php");
    exit();
}

if (isset($_POST['createStudent'])) {
    $stud_no = $_POST['stud_no']; // New field for Student Number
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];

    // Insert into students table including StudNo
    $statement = $pdo->prepare('INSERT INTO students (user_id, StudNo, fname, mname, lname, email, course, year_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $statement->execute([$user_id, $stud_no, $first_name, $middle_name, $last_name, $email, $course, $year_level]);

    if ($statement->rowCount() < 1) {
        echo "Error creating student.";
        exit();
    }
    header("Location: Admin_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Creation Form</title>
    <link rel="stylesheet" href="Student_Creation_Form.css"> <!-- Ensure correct CSS file path -->
</head>
<body>

    <!-- Sidebar Section -->
    <div class="sidebar">
        <h2 class="logo">Dashboard</h2>
        <ul>
            <li><a href="Admin_dashboard.php" class="button">Dashboard</a></li>
            <li><a href="Admin_Create_Account.php">Create Account</a></li>
            
        </ul>
    </div>

    <!-- Main Content Section -->
    <div class="container" id="signup">
        <h1 class="form-title">Student Creation Form</h1>
        <form method="post" action="">
            <!-- Student Number (StudNo) Input Field -->
            <div class="input-group">
                <i class="fas fa-id-card"></i>
                <input type="text" name="stud_no" placeholder="Enter Student Number" required>
                <label for="stud_no">Student Number</label>
            </div>
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="first_name" placeholder="Enter first name" required>
                <label for="first_name">First Name</label>
            </div>
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="middle_name" placeholder="Enter middle name">
                <label for="middle_name">Middle Name</label>
            </div>
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="last_name" placeholder="Enter last name" required>
                <label for="last_name">Last Name</label>
            </div>
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Enter email" required>
                <label for="email">Email</label>
            </div>
            <div class="input-group">
                <i class="fas fa-book"></i>
                <input type="text" name="course" placeholder="Enter course" required>
                <label for="course">Course</label>
            </div>
            <div class="input-group">
    <i class="fas fa-graduation-cap"></i>
    <label for="year_level">Year Level</label>
    <select name="year_level" required>
        <option value="" disabled selected>Select Year Level</option>
        <option value="1st Year">1st Year</option>
        <option value="2nd Year">2nd Year</option>
        <option value="3rd Year">3rd Year</option>
        <option value="4th Year">4th Year</option>
    </select>
</div>

            <input type="submit" class="btn" value="Create Account" name="createStudent">
        </form>
    </div>
</body>
</html>
