<?php
session_start();
require_once '../db/db.php';

// Only engineers can sign
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'engineer') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    exit("Complaint ID not specified.");
}

$complaint_id = $_GET['id'];

// Check if work record exists
$stmt = $pdo->prepare("SELECT * FROM cwceo WHERE complaint_id = ?");
$stmt->execute([$complaint_id]);
$work = $stmt->fetch();

if (!$work) {
    exit("Work record not found for this complaint.");
}

// Process the signing action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $engineer_sign = $_SESSION['user']['id'];  // using the engineer's ID as a signature
    $update = $pdo->prepare("UPDATE cwceo SET engineer_sign = ? WHERE complaint_id = ?");
    $update->execute([$engineer_sign, $complaint_id]);
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Engineer Sign</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<h2>Engineer Sign</h2>
<p>Do you want to sign this complaint as the engineer?</p>
<form action="sign_engineer.php?id=<?php echo htmlspecialchars($complaint_id); ?>" method="POST">
    <button type="submit">Sign as Engineer</button>
</form>
<?php include '../includes/footer.php'; ?>
</body>
</html>