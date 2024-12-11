<?php
require_once '../Database/clearancedb.php';
session_start();

$file = $_FILES['csv']['tmp_name'];
if (is_null($file)) {
    die('test');
}

function processUser($pdo, $username, $rawPassword, $role) {
    $password = password_hash($rawPassword, PASSWORD_BCRYPT); // ALWAYS FUCKING HASH THE PASSWORD!!!!!!!!
    // $admin_type = isset($_POST['admin_type']) ? $_POST['admin_type'] : ''; // Capture the admin type (if applicable)

    // Check if the username already exists using a prepared statement
    $statement = $pdo->prepare('SELECT * FROM user WHERE username = ?');
    $statement->execute([$username]);
    $user = $statement->fetch();
    if ($user) {
        return;
    }

    // Directly insert the plain text password (without hashing) THIS IS FUCKING STUPID

    $statement = $pdo->prepare('INSERT INTO user (username, password, role) VALUES (?, ?, ?)');
    $statement->execute([$username, $password, $role]);
}

if (($handle = fopen($file, "r")) !== FALSE) {
  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    processUser($pdo, $data[0], $data[1], $data[2]);
  }
  fclose($handle);
}

unlink($file);
