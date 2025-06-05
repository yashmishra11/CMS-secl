<?php
session_start();
require_once '../db/db.php';

// Only officers can edit
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'officer') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    exit("Complaint ID not specified.");
}

$complaint_id = $_GET['id'];

// Fetch complaint data from ccdrc table
$stmt = $pdo->prepare("SELECT * FROM ccdrc WHERE complaint_id = ?");
$stmt->execute([$complaint_id]);
$complaint = $stmt->fetch();

if (!$complaint) {
    exit("Complaint not found.");
}

// Process update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department = trim($_POST['department']);
    $room_no = trim($_POST['room_no']);
    $complaint_description = trim($_POST['complaint_description']);

    // Add more validations as needed

    $update = $pdo->prepare("UPDATE ccdrc SET department = ?, room_no = ?, complaint_description = ? WHERE complaint_id = ?");
    $update->execute([$department, $room_no, $complaint_description, $complaint_id]);

    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Complaint</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<h2>Edit Complaint</h2>
<form action="edit_complaint.php?id=<?php echo htmlspecialchars($complaint_id); ?>" method="POST">
    <label for="department">Department:</label>
    <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($complaint['department']); ?>" required>
    <br>
    <label for="room_no">Room No:</label>
    <input type="text" id="room_no" name="room_no" value="<?php echo htmlspecialchars($complaint['room_no']); ?>" required>
    <br>
    <label for="complaint_description">Description:</label>
    <textarea id="complaint_description" name="complaint_description" required><?php echo htmlspecialchars($complaint['complaint_description']); ?></textarea>
    <br>
    <button type="submit">Update Complaint</button>
</form>
<?php include '../includes/footer.php'; ?>
</body>
</html>