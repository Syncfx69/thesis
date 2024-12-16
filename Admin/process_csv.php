<?php
require_once '../Database/clearancedb.php';
session_start();

$file = $_FILES['csv']['tmp_name'];
if (is_null($file)) {
    die('No file uploaded.');
}

function processUserAndStudent($pdo, $username, $rawPassword, $role, $studNo) {
    $password = password_hash($rawPassword, PASSWORD_BCRYPT);

    // Check if the username already exists using a prepared statement
    $statement = $pdo->prepare('SELECT * FROM user WHERE username = ?');
    $statement->execute([$username]);
    $user = $statement->fetch();

    if ($user) {
        // If the user exists, we can update or ignore this entry
        return;
    }

    // Insert into the `user` table
    $userInsertStmt = $pdo->prepare('INSERT INTO user (username, password, role) VALUES (?, ?, ?)');
    $userInsertStmt->execute([$username, $password, $role]);

    // Get the last inserted user_id
    $userId = $pdo->lastInsertId();

    // If role is 'Student', insert into the `students` table
    if ($role === 'Student') {
        $studentInsertStmt = $pdo->prepare('INSERT INTO students (user_id, StudNo) VALUES (?, ?)');
        $studentInsertStmt->execute([$userId, $studNo]);
    }
}

if (($handle = fopen($file, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        // Process each row of the CSV
        $username = $data[0];
        $rawPassword = $data[1];
        $role = $data[2];
        $studNo = $data[3]; // The 4th cell is the StudNo

        processUserAndStudent($pdo, $username, $rawPassword, $role, $studNo);
    }
    fclose($handle);
}

unlink($file);
echo "CSV processed successfully.";
?>
