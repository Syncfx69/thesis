<?php
require_once '../Database/clearancedb.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Signatory Admin') {
    header('Location: ../index.php');
    exit();
}

// Get the Cpid from the query parameter
$cpid = $_GET['cpid'] ?? null;
if (!$cpid) {
    die("Error: No Cpid specified.");
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'clearancedb');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare the filter query
$filterQuery = "
    SELECT student_id, username, StudNo, fname, lname, course, year_level 
    FROM students
";

$conditions = [];
if (!empty($_GET['year_level'])) {
    $conditions[] = "year_level LIKE '%" . $conn->real_escape_string($_GET['year_level']) . "%'";
}
if (!empty($_GET['course'])) {
    $conditions[] = "course LIKE '%" . $conn->real_escape_string($_GET['course']) . "%'";
}
if (!empty($_GET['student_code'])) {
    $conditions[] = "StudNo LIKE '%" . $conn->real_escape_string($_GET['student_code']) . "%'";
}
if (!empty($_GET['fname'])) {
    $conditions[] = "fname LIKE '%" . $conn->real_escape_string($_GET['fname']) . "%'";
}
if (!empty($_GET['lname'])) {
    $conditions[] = "lname LIKE '%" . $conn->real_escape_string($_GET['lname']) . "%'";
}

if (!empty($conditions)) {
    $filterQuery .= " WHERE " . implode(" AND ", $conditions);
}

// Execute the query
$students = $conn->query($filterQuery);
if (!$students) {
    die("Query failed: " . $conn->error);
}

// Handle adding students
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];

    // Check if the student is already added to the clearance period in the `clearance` table
    $check_stmt = $conn->prepare('SELECT COUNT(*) FROM clearance WHERE student_id = ? AND Cpid = ?');
    $check_stmt->bind_param('ii', $student_id, $cpid);
    $check_stmt->execute();
    $check_stmt->bind_result($exists);
    $check_stmt->fetch();
    $check_stmt->close();

    if (!$exists) {
        // Add the student to the `clearance` table
        $stmt = $conn->prepare('INSERT INTO clearance (student_id, Cpid, status) VALUES (?, ?, "Pending")');
        $stmt->bind_param('ii', $student_id, $cpid);
        $stmt->execute();
        $clearance_id = $conn->insert_id;
        $stmt->close();

        // Fetch all signatory IDs from the `signatory` table
        $signatories = $conn->query('SELECT signatory_id FROM signatory')->fetch_all(MYSQLI_ASSOC);

        // Insert entries into `clearance_details` for each signatory with default values
        $clearance_detail_stmt = $conn->prepare('
            INSERT INTO clearance_details (clearance_id, student_id, signatory_id, deptstatus, Cpid)
            VALUES (?, ?, ?, "Clear", ?)
        ');
        foreach ($signatories as $signatory) {
            $clearance_detail_stmt->bind_param('iiii', $clearance_id, $student_id, $signatory['signatory_id'], $cpid);
            $clearance_detail_stmt->execute();
        }
        $clearance_detail_stmt->close();

        // Redirect back to the students list for the same clearance period
        header("Location: Studentslist.php?cpid=$cpid");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students List</title>
    <link rel="stylesheet" href="Studentslist.css">
    <style>
        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            align-items: center;
        }

        .filters input[type="text"], .filters button {
            padding: 10px;
            font-size: 14px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .filters button {
            background-color: #f00;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .filters button:hover {
            background-color: #d00;
        }

        .filters input[type="text"] {
            width: 180px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2 class="logo">Signatory Dashboard</h2>
        <ul>
            <li><a href="Signatory_Dashboard.php" class="button">Signatory Account</a></li>
            <li><a href="Clearancedashboard.php" class="button">Clearance Dashboard</a></li>
            <li><a href="Signatory_clearanceforms.php" class="button active">Clearance Forms</a></li>
            <li><a href="../logout.php" class="button">Log Out</a></li>
        </ul>
    </div>

    <div class="main-content">
        <header>
            <div class="header-container">
                <h1>Students List for Clearance Period (Cpid: <?php echo htmlspecialchars($cpid); ?>)</h1>
            </div>
        </header>

        <!-- Filters -->
        <form method="GET" action="Studentslist.php" class="filters">
            <input type="hidden" name="cpid" value="<?= htmlspecialchars($cpid) ?>">
            <input type="text" name="year_level" placeholder="Year Level" value="<?php echo htmlspecialchars($_GET['year_level'] ?? ''); ?>">
            <input type="text" name="course" placeholder="Course" value="<?php echo htmlspecialchars($_GET['course'] ?? ''); ?>">
            <input type="text" name="student_code" placeholder="Student Code" value="<?php echo htmlspecialchars($_GET['student_code'] ?? ''); ?>">
            <input type="text" name="fname" placeholder="First Name" value="<?php echo htmlspecialchars($_GET['fname'] ?? ''); ?>">
            <input type="text" name="lname" placeholder="Last Name" value="<?php echo htmlspecialchars($_GET['lname'] ?? ''); ?>">
            <button type="submit">Search</button>
        </form>

        <!-- Students Table -->
        <div class="students-table">
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Student No</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Course</th>
                        <th>Year Level</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($student = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['username']); ?></td>
                            <td><?php echo htmlspecialchars($student['StudNo']); ?></td>
                            <td><?php echo htmlspecialchars($student['fname']); ?></td>
                            <td><?php echo htmlspecialchars($student['lname']); ?></td>
                            <td><?php echo htmlspecialchars($student['course']); ?></td>
                            <td><?php echo htmlspecialchars($student['year_level']); ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>">
                                    <button type="submit" class="btn-add">Add</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Back Button -->
        <a href="Signatory_clearanceforms.php" class="back-button">Back</a>
    </div>
</body>
</html>
