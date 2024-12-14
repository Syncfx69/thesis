<?php
require_once '../Database/clearancedb.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If the user is not logged in, redirect them to the login page
    header("Location: ../index.php");
    exit();
}

// Fetch the logged-in user_id from session
$user_id = $_SESSION['user_id'];

$statement = $pdo->prepare('SELECT email FROM admin WHERE user_id = ?');
$statement->execute([$user_id]);
$email = $statement->fetchColumn();

// Fetch counts for the pie chart
$studentCountQuery = $pdo->query("SELECT COUNT(*) AS total_students FROM students");
$adminCountQuery = $pdo->query("SELECT COUNT(*) AS total_admins FROM admin");
$studentCount = $studentCountQuery->fetch(PDO::FETCH_ASSOC)['total_students'];
$adminCount = $adminCountQuery->fetch(PDO::FETCH_ASSOC)['total_admins'];

// Get the selected directory type from the form (default to 'user')
$directoryType = isset($_POST['directoryType']) ? $_POST['directoryType'] : 'user';

// Set the SQL query based on the selected directory type
if ($directoryType == 'admin') {
    $sql = "SELECT admin_id, user_id, first_name, last_name, email, type, signatory_id FROM admin";
} elseif ($directoryType == 'student') {
    $sql = "SELECT student_id, user_id, StudNo, fname, mname, lname, course, year_level, email FROM students";
} else {
    $sql = "SELECT user_id, username, role FROM user";
}

$statement = $pdo->prepare($sql);
$statement->execute();
$result = $statement->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="Admin_dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100vh;
            width: 250px;
            background-color: #800000;
        }

        .logo {
            text-align: center;
            padding: 10px;
            color: #fff;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            margin: 10px 0;
        }

        a.button, p {
            display: block;
            padding: 10px;
            color: white;
            text-decoration: none;
            text-align: center;
        }

        a.button:hover {
            background-color: #34495e;
        }

        .sidebar-bottom {
            margin-top: auto;
            text-align: center;
        }

        .sidebar-bottom a.button {
            margin-bottom: 0;
        }

        .sidebar-bottom p {
            margin-top: 0;
        }

        .content-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 20px;
        }

        .directory-section {
            flex: 2;
            margin-right: 20px;
        }

        .chart-section {
            flex: 1;
            text-align: center;
        }

        .chart-section canvas {
            max-width: 300px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <img src="images/perpetualsmallicon.png" alt="icon" class="navbar-icon">
    <ul>
        <li><a href="Admin_dashboard.php" class="button"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="Admin_Create_Account.php" class="button"><i class="fas fa-user-plus"></i> Create Account</a></li>
        <li><a href="Create_Clearanceform.php" class="button"><i class="fas fa-file-alt"></i> Create ClearanceForm</a></li>
        <li><a href="#" class="button" onclick="openQRModal()"><i class="fas fa-qrcode"></i> Scan QR</a></li>
        <li><a href="Graph.php" class="button"><i class="fas fa-chart-bar"></i> Graph</a></li>
    </ul>
    <div class="sidebar-bottom">
        <a href="../logout.php" class="button"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        <p>Logged in as: <?php echo htmlspecialchars($email); ?></p>
    </div>
</div>

<div class="main-content">
    <div class="header">
        <h1>Admin Dashboard</h1>
    </div>

    <div class="content-wrapper">
        <!-- Left Section: Directory Table -->
        <div class="directory-section">
            <form method="POST" action="Admin_dashboard.php">
                <label for="directoryType">Choose a directory:</label>
                <select name="directoryType" id="directoryType" onchange="this.form.submit()">
                    <option value="user" <?php if ($directoryType == 'user') echo 'selected'; ?>>User Directory</option>
                    <option value="admin" <?php if ($directoryType == 'admin') echo 'selected'; ?>>Admin Directory</option>
                    <option value="student" <?php if ($directoryType == 'student') echo 'selected'; ?>>Student Directory</option>
                </select>
            </form>
            <h2>
                <?php
                if ($directoryType == 'admin') {
                    echo "Admin Directory";
                } elseif ($directoryType == 'student') {
                    echo "Student Directory";
                } else {
                    echo "User Directory";
                }
                ?>
            </h2>

            <!-- User Table -->
            <table>
                <thead>
                    <tr>
                        <?php
                        if ($directoryType == 'admin') {
                            echo "<th>Admin ID</th><th>User ID</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Type</th><th>Signatory ID</th>";
                        } elseif ($directoryType == 'student') {
                            echo "<th>Student ID</th><th>User ID</th><th>Student Number</th><th>First Name</th><th>Middle Name</th><th>Last Name</th><th>Course</th><th>Year Level</th><th>Email</th>";
                        } else {
                            echo "<th>User ID</th><th>Username</th><th>Role</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (count($result) > 0) {
                        foreach ($result as $row) {
                            echo "<tr>";
                            if ($directoryType == 'admin') {
                                echo "<td>" . $row['admin_id'] . "</td>";
                                echo "<td>" . $row['user_id'] . "</td>";
                                echo "<td>" . $row['first_name'] . "</td>";
                                echo "<td>" . $row['last_name'] . "</td>";
                                echo "<td>" . $row['email'] . "</td>";
                                echo "<td>" . $row['type'] . "</td>";
                                echo "<td>" . $row['signatory_id'] . "</td>";
                            } elseif ($directoryType == 'student') {
                                echo "<td>" . $row['student_id'] . "</td>";
                                echo "<td>" . $row['user_id'] . "</td>";
                                echo "<td>" . $row['StudNo'] . "</td>";
                                echo "<td>" . $row['fname'] . "</td>";
                                echo "<td>" . $row['mname'] . "</td>";
                                echo "<td>" . $row['lname'] . "</td>";
                                echo "<td>" . $row['course'] . "</td>";
                                echo "<td>" . $row['year_level'] . "</td>";
                                echo "<td>" . $row['email'] . "</td>";
                            } else {
                                echo "<td>" . $row['user_id'] . "</td>";
                                echo "<td>" . $row['username'] . "</td>";
                                echo "<td>" . $row['role'] . "</td>";
                            }
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8'>No records found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Right Section: Pie Chart -->
        <div class="chart-section">
            <h3>Overview: Students & Admins</h3>
            <canvas id="overviewChart"></canvas>
        </div>
    </div>
</div>

<script>
    const adminCount = <?php echo $adminCount; ?>;
    const studentCount = <?php echo $studentCount; ?>;

    const ctx = document.getElementById('overviewChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Admins', 'Students'],
            datasets: [{
                label: 'Count',
                data: [adminCount, studentCount],
                backgroundColor: ['#800000', '#FFD700'],

            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
</script>
</body>
</html>
