<?php
session_start();
require_once '../db/db.php';

// Only officers can sign
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'officer') {
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
    $officer_sign = $_SESSION['user']['id'];  // using the officer's ID as a signature
    $update = $pdo->prepare("UPDATE cwceo SET officer_sign = ? WHERE complaint_id = ?");
    $update->execute([$officer_sign, $complaint_id]);
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Officer Sign</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<h2>Officer Sign</h2>
<p>Are you sure you want to sign off on this complaint?</p>
<form action="sign_officer.php?id=<?php echo htmlspecialchars($complaint_id); ?>" method="POST">
    <button type="submit">Sign as Officer</button>
</form>
<?php include '../includes/footer.php'; ?>
</body>
</html>