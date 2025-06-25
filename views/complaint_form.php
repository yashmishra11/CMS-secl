<?php
session_start();
include '../includes/header.php';
require_once '../db/db.php';

$message = '';

// Ensure only officers can register a complaint.
if (!isset($_SESSION['user'], $_SESSION['role']) || $_SESSION['role'] !== 'officer') {
    header("Location: /login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Fetch form values
    $complaintDate        = trim($_POST['complaint_date']);
    $complaintDepartment  = trim($_POST['department']);
    $complaintRoomNo      = trim($_POST['room_no']);
    $complaintDescription = trim($_POST['complaint_description']);
    
    if (
        empty($complaintDate) ||
        empty($complaintDepartment) ||
        empty($complaintRoomNo) ||
        empty($complaintDescription)
    ) {
        $message = 'Please fill in all required fields.';
    } else {
        try {
            // Retrieve the officer's unique code from the session.
            $officerCode = $_SESSION['user']['unique_code'];
            
            // Insert complaint into ccdrc.
            $stmt = $pdo->prepare('INSERT INTO ccdrc (complaint_date, department, room_no, complaint_description) VALUES (?, ?, ?, ?)');
            $stmt->execute([
                $complaintDate,
                $complaintDepartment,
                $complaintRoomNo,
                $complaintDescription
            ]);
            
            $complaint_id = $pdo->lastInsertId();
            
            // Create matching row in cwceo, including the officer's unique code.
            $stmt2 = $pdo->prepare('INSERT INTO cwceo (complaint_id, work_description, complaint_close_date, engineer_sign, officer_sign, code) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt2->execute([
                $complaint_id,
                '', // work_description empty on registration
                '', // complaint_close_date empty on registration
                '', // engineer_sign empty on registration
                '', // officer_sign empty on registration
                $officerCode
            ]);
            
            header("Location: complaint_success.php");
            exit;
        } catch (PDOException $e) {
            $message = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<div class="container">
    <h1 class="main-title">Register a Complaint</h1>
    <?php if($message): ?>
        <div class="message" style="margin-bottom: 20px;"><?= htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <div class="form-container" style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.08); padding: 2rem;">
        <form method="POST" action="complaint_form.php" style="display: flex; flex-direction: column; gap: 18px;">
            <label for="complaint_date" style="font-weight: 600;">Complaint date:</label>
            <input type="date" id="complaint_date" name="complaint_date" required style="padding: 10px; border-radius: 8px; border: 1px solid #e0e0e0;">

            <label for="department" style="font-weight: 600;">Department:</label>
            <select id="department" name="department" required style="height: 40px; border-radius: 8px; border: 1px solid #e0e0e0; padding: 8px;">
                <option value="">-- Select Department --</option>
                <option>ALL DEPT.</option>
                <option>AMMC</option>
                <option>CCO</option>
                <option>CMC</option>
                <option>CMC Vigilance</option>
                <option>CMD</option>
                <option>CS</option>
                <option>Cash</option>
                <option>Civil</option>
                <option>Civil IVH</option>
                <option>Clean Energy</option>
                <option>DF</option>
                <option>DP</option>
                <option>DP ALL DEPT.</option>
                <option>DTP&amp;P</option>
                <option>Department</option>
                <option>Dispensary</option>
                <option>Dispensary NE</option>
                <option>E&amp;M</option>
                <option>Finance</option>
                <option>Forest Sales</option>
                <option>GM(P&amp;A)</option>
                <option>HRD</option>
                <option>HRD IVH</option>
                <option>Hindi DEPT</option>
                <option>IR CMS</option>
                <option>IR E&amp;T</option>
                <option>IR QC</option>
                <option>IVH</option>
                <option>Indravihar Hospital</option>
                <option>Internal Audit</option>
                <option>L&amp;R</option>
                <option>MM</option>
                <option>MM siding</option>
                <option>Manpower</option>
                <option>P&amp;A</option>
                <option>P&amp;P</option>
                <option>P&amp;P CISF</option>
                <option>P&amp;P IED</option>
                <option>P/NEE</option>
                <option>PF pension</option>
                <option>PRB Cell</option>
                <option>PRB Cell Vigilance</option>
                <option>S&amp;R</option>
                <option>SECL HQ</option>
                <option>Security</option>
                <option>Sub Station</option>
                <option>TA Vasant Vihar</option>
                <option>TS to CMD</option>
                <option>Transport</option>
                <option>Vasant Vihar</option>
                <option>Vigilance</option>
                <option>Vigilance PRB Cell</option>
                <option>company section</option>
            </select>

            <label for="room_no" style="font-weight: 600;">Room no:</label>
            <input type="text" id="room_no" name="room_no" required style="padding: 10px; border-radius: 8px; border: 1px solid #e0e0e0;">

            <label for="complaint_description" style="font-weight: 600;">Complaint description:</label>
            <textarea id="complaint_description" name="complaint_description" required style="padding: 10px; border-radius: 8px; border: 1px solid #e0e0e0; min-height: 40px;"></textarea>

            <button type="submit" style="padding: 12px; background: linear-gradient(135deg, #1d70b8 0%, #005499 100%); color: #fff; border: none; border-radius: 8px; font-weight: 600; font-size: 1.1rem; cursor: pointer; transition: background 0.3s;">
                Submit Complaint
            </button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<link rel="stylesheet" href="/css/style.css">
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
    color: #333;
}
.main-title {
    text-align: center;
    margin: 2rem 0;
    font-size: 2.5rem;
    font-weight: 700;
    color: #1d70b8;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
}
.message {
    background: linear-gradient(135deg, #d1e7dd 0%, #a3d9a5 100%);
    color: #0f5132;
    padding: 1rem;
    border-radius: 10px;
    text-align: center;
    border: 1px solid #badbcc;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.form-container label {
    margin-bottom: 4px;
}
.form-container input,
.form-container select,
.form-container textarea {
    font-size: 1rem;
    margin-bottom: 10px;
}
.form-container button:hover {
    background: linear-gradient(135deg, #005499 0%, #1d70b8 100%);
}
@media (max-width: 768px) {
    .main-title {
        font-size: 2rem;
    }
    .form-container {
        padding: 1rem !important;
    }
}