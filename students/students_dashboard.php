<?php
// Include your database connection
require_once '../Database/clearancedb.php';
session_start();

// Check if the student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user and student data using a JOIN query
$statement = $pdo->prepare('
    SELECT u.username, s.StudNo, s.fname, s.mname, s.lname, s.course, s.year_level
    FROM user u
    JOIN students s ON u.user_id = s.user_id
    WHERE u.user_id = ?
');
$statement->execute([$user_id]);
$student = $statement->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch updated data from POST request
    $updated_username = $_POST['username'];
    $updated_fname = $_POST['fname'];
    $updated_mname = $_POST['mname'];
    $updated_lname = $_POST['lname'];
    $updated_course = $_POST['course'];
    $updated_year_level = $_POST['year_level'];

    // Prepare an SQL statement to update the student record
    $update_statement = $pdo->prepare('
        UPDATE students
        SET fname = ?, mname = ?, lname = ?, course = ?, year_level = ?
        WHERE user_id = ?
    ');
    $update_statement->execute([$updated_fname, $updated_mname, $updated_lname, $updated_course, $updated_year_level, $user_id]);

    // Optionally update the username in the user table
    $update_username_statement = $pdo->prepare('
        UPDATE user
        SET username = ?
        WHERE user_id = ?
    ');
    $update_username_statement->execute([$updated_username, $user_id]);

    // Redirect to avoid resubmitting the form on page refresh
    header('Location: students_dashboard.php');
    exit();
}
?>

<script>
// JavaScript to enable and disable editing
function enableEditing() {
    document.querySelectorAll('.editable').forEach(input => input.removeAttribute('readonly'));
    document.getElementById('edit-button').style.display = 'none';
    document.getElementById('save-button').style.display = 'inline-block';
}

function openModal(event) {
    event.preventDefault();
    document.getElementById('confirmation-modal').style.display = 'block';
}

function closeModal() {
    document.getElementById('confirmation-modal').style.display = 'none';
}

function confirmSave() {
    document.querySelector('form').submit();
}
</script>

<style>
/* Modal styles */
#confirmation-modal {
    display: none; /* Hidden by default */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
    z-index: 1000;
}

.modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #fff;
    padding: 20px;
    width: 400px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    text-align: center;
}

.modal-buttons {
    margin-top: 20px;
    display: flex;
    justify-content: space-around;
}

.modal-buttons button {
    padding: 10px 20px;
    font-size: 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

#confirm-btn {
    background-color: #8b0000;
    color: #fff;
}

#cancel-btn {
    background-color: #ccc;
    color: #333;
}
</style>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Account</title>
    <link rel="stylesheet" href="students_dashboard copy.css">
</head>
<body>
    <div class="sidebar">
        <h2 class="logo">Student Dashboard</h2>
        <ul>
            <li><a href="students_dashboard.php">Account Profile</a></li>
            <li><a href="students_clearanceform.php">Clearance Form</a></li>
            <li><a href="../logout.php">Log Out</a></li>
        </ul>
    </div>
    <div class="main-content">
        <h1>Student Account</h1>
        <form method="POST" action="">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($student['username']) ?>" class="editable" readonly>

            <label for="StudNo">Student Number:</label>
            <input type="text" id="StudNo" name="StudNo" value="<?= htmlspecialchars($student['StudNo']) ?>" readonly>

            <label for="fname">First Name:</label>
            <input type="text" id="fname" name="fname" value="<?= htmlspecialchars($student['fname']) ?>" class="editable" readonly>

            <label for="mname">Middle Name:</label>
            <input type="text" id="mname" name="mname" value="<?= htmlspecialchars($student['mname']) ?>" class="editable" readonly>

            <label for="lname">Last Name:</label>
            <input type="text" id="lname" name="lname" value="<?= htmlspecialchars($student['lname']) ?>" class="editable" readonly>

            <label for="course">Course:</label>
            <input type="text" id="course" name="course" value="<?= htmlspecialchars($student['course']) ?>" class="editable" readonly>

            <label for="year_level">Year Level:</label>
            <input type="text" id="year_level" name="year_level" value="<?= htmlspecialchars($student['year_level']) ?>" class="editable" readonly>

            <button type="button" id="edit-button" onclick="enableEditing()">Edit</button>
            <button type="submit" id="save-button" style="display: none;" onclick="openModal(event)">Save</button>
        </form>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmation-modal">
        <div class="modal-content">
            <p>Are you sure you want to save the changes?</p>
            <div class="modal-buttons">
                <button id="confirm-btn" onclick="confirmSave()">Yes</button>
                <button id="cancel-btn" onclick="closeModal()">No</button>
            </div>
        </div>
    </div>
</body>
</html>
