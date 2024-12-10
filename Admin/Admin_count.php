<?php
require_once '../Database/clearancedb.php';
session_start(); // Start the session to use session variables

$cpid = 1;

$statement = $pdo->prepare('SELECT DISTINCT student_id, deptstatus FROM clearance_Details WHERE Cpid = ?');
$statement->execute([$cpid]);
$clearances = $statement->fetchAll();

$students = [];

foreach($clearances as $clearance) {
    $id = $clearance['student_id'];
    $deptstatus = $clearance['deptstatus'];
    if (empty($students[$id])) {
        $students[$id] = $deptstatus == 'Signed';
    } else if ($students[$id] == true && $deptstatus != 'Signed') {
        $students[$id] =  $deptstatus == 'Signed';
    }
}
$signedCount = 0;
$notSignedCount = 0;

foreach ($students as $signed) {
    if ($signed) {
        $signedCount++;
    } else {
        $notSignedCount += 1;
    }
}

header('Content-Type: application/json');
echo json_encode([
    'signed' => (int)$signedCount,
    'notSigned' => (int)$notSignedCount,
]);
