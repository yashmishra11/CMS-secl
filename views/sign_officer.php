<?php
session_start();
require_once '../db/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    //ifreqmethpostsetresponsetojson
}
//checkswhetheruserisloggedinaaofficer
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'officer') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        //ifnotthenshowerror
        //andstopfurtherexecution
        exit;
    } else {
        header("Location: ../login.php");
        //redirecttologin
        exit;
    }
}

if (!isset($_GET['id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo json_encode(['success' => false, 'error' => 'Complaint ID missing']);
        exit;
        //idgivenintheURL
    } else {
        exit("Complaint ID not specified.");
        //ifnotthenexitwitherrormessage
    }
}

$complaint_id = $_GET['id'];
//retrievingcomplaintidfromURL

$stmt = $pdo->prepare("SELECT * FROM cwceo WHERE complaint_id = ?");
$stmt->execute([$complaint_id]);
//execthequerybysubstitutingcomplaintid
$work = $stmt->fetch();
//getfirstrowfromresultset
//fetchingworkrecordbasedoncomplaintidfromcwceowheresigncolumnis

if (!$work) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo json_encode(['success' => false, 'error' => 'Work record not found']);
        exit;
    } else {
        exit("Work record not found for this complaint.");
    }
    //checkifworkrecordexists
    //ifnotthenexitwitherrormessage
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $update = $pdo->prepare("UPDATE cwceo SET officer_sign = 1 WHERE complaint_id = ?");
        //updatequerytoupadteofficersignfromnullto1
        $update->execute([$complaint_id]);
        echo json_encode(['success' => true]);
        //ifsuccessfulsensasuccessresmessage
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}
?>