<?php
session_start();
require_once '../db/db.php';

// Only officers can delete complaints
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'officer') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    exit("Complaint ID not specified.");
}

$complaint_id = $_GET['id'];

// Delete work details first (if any) due to foreign key constraints
$stmt = $pdo->prepare("DELETE FROM cwceo WHERE complaint_id = ?");
$stmt->execute([$complaint_id]);

// Delete complaint record from ccdrc table
$stmt = $pdo->prepare("DELETE FROM ccdrc WHERE complaint_id = ?");
$stmt->execute([$complaint_id]);

header("Location: login.php");
exit;
?>