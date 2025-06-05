<?php
session_start();
require_once '../db/db.php';

// Only engineers can add work details
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'engineer') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    exit("Complaint ID not specified.");
}

$complaint_id = $_GET['id'];

// Check if a work record already exists for this complaint
$stmt = $pdo->prepare("SELECT * FROM cwceo WHERE complaint_id = ?");
$stmt->execute([$complaint_id]);
$work = $stmt->fetch();

if ($work) {
    exit("Work record already exists for this complaint.");
}

// Process form submission to add work details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $work_description = trim($_POST['work_description']);
    $complaint_close_date = trim($_POST['complaint_close_date']);

    if (empty($work_description) || empty($complaint_close_date)) {
        $error = "Please fill in all fields.";
    } else {
        $insert = $pdo->prepare("INSERT INTO cwceo (complaint_id, work_description, complaint_close_date) VALUES (?, ?, ?)");
        $insert->execute([$complaint_id, $work_description, $complaint_close_date]);
        header("Location: login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Work Details</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<h2>Add Work Details</h2>
<?php if(isset($error)): ?>
    <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>
<form action="add_work.php?id=<?php echo htmlspecialchars($complaint_id); ?>" method="POST">
    <label for="work_description">Work Description:</label>
    <textarea id="work_description" name="work_description" required></textarea>
    <br>
    <label for="complaint_close_date">Complaint Close Date:</label>
    <input type="date" id="complaint_close_date" name="complaint_close_date" required>
    <br>
    <button type="submit">Submit Work Details</button>
</form>
<?php include '../includes/footer.php'; ?>
</body>
</html>