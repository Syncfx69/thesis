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

// Prepare the filter query
$filterQuery = "
    SELECT s.username, s.StudNo, s.fname, s.lname, s.course, s.year_level 
    FROM clearance c 
    JOIN students s ON c.student_id = s.student_id 
    WHERE c.Cpid = ?
";

$conditions = [];
$parameters = [$cpid];

if (!empty($_GET['year_level'])) {
    $conditions[] = "s.year_level LIKE ?";
    $parameters[] = "%" . $_GET['year_level'] . "%";
}
if (!empty($_GET['course'])) {
    $conditions[] = "s.course LIKE ?";
    $parameters[] = "%" . $_GET['course'] . "%";
}
if (!empty($_GET['student_code'])) {
    $conditions[] = "s.StudNo LIKE ?";
    $parameters[] = "%" . $_GET['student_code'] . "%";
}
if (!empty($_GET['fname'])) {
    $conditions[] = "s.fname LIKE ?";
    $parameters[] = "%" . $_GET['fname'] . "%";
}
if (!empty($_GET['lname'])) {
    $conditions[] = "s.lname LIKE ?";
    $parameters[] = "%" . $_GET['lname'] . "%";
}

if (!empty($conditions)) {
    $filterQuery .= " AND " . implode(" AND ", $conditions);
}

// Execute the query
$stmt = $pdo->prepare($filterQuery);
$stmt->execute($parameters);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clearance Students</title>
    <link rel="stylesheet" href="ClearanceStudents.css">
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
            width: 180px; /* Set consistent width for inputs */
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
                <h1>Clearance Students for Clearance Period (Cpid: <?php echo htmlspecialchars($cpid); ?>)</h1>
            </div>
        </header>

        <!-- Filters -->
        <form method="GET" action="ClearanceStudents.php" class="filters">
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
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['username']); ?></td>
                            <td><?php echo htmlspecialchars($student['StudNo']); ?></td>
                            <td><?php echo htmlspecialchars($student['fname']); ?></td>
                            <td><?php echo htmlspecialchars($student['lname']); ?></td>
                            <td><?php echo htmlspecialchars($student['course']); ?></td>
                            <td><?php echo htmlspecialchars($student['year_level']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
