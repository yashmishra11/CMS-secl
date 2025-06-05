<?php
include '../includes/header.php';
require_once '../db/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $complaintTitle       = trim($_POST['complaint_title']);
    $complaintDescription = trim($_POST['complaint_description']);
    
    if (empty($complaintTitle) || empty($complaintDescription)) {
        $message = 'Please fill in all required fields.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO ccdrc (complaint_date, department, room_no, complaint_description) VALUES (CURDATE(), ?, ?, ?)');
            // For demo purposes, using placeholders for department and room_no
            $stmt->execute(['General', '101', $complaintDescription]);
            $message = 'Complaint submitted successfully!';
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $message = 'An error occurred. Please try again later.';
        }
    }
}
?>

<h2>Register a Complaint</h2>
<?php if($message): ?>
    <p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>
<form method="POST" action="complaint_form.php">
    <label for="complaint_title">Title:</label>
    <input type="text" id="complaint_title" name="complaint_title" required>
    <br>
    <label for="complaint_description">Description:</label>
    <textarea id="complaint_description" name="complaint_description" required></textarea>
    <br>
    <button type="submit">Submit Complaint</button>
</form>

<?php
include '../includes/footer.php';
?>