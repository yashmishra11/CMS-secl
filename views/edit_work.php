<?php
//startorresumethesessionn
session_start();
header('Content-Type: application/json');
//settheuotputtojsonn
//cuzbeingcalledviaajaxx

require_once '../db/db.php';
//setuppdotointeractwithwiththedbb

//onlyallowengineertoaccess
//checkifrolesettoengineer
if (!isset($_SESSION['user'], $_SESSION['role']) || $_SESSION['role'] !== 'engineer') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

//verifyifcomplaintidpresetinurll
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Complaint ID not specified']);
    exit;
}

//getcomplaintidfromurll
$complaint_id = $_GET['id'];

//retrievesubmittedvaluestrimsthemandsavesthemm
$work_description = trim($_POST['work_description'] ?? '');
$complaint_close_date = trim($_POST['complaint_close_date'] ?? '');

//autosignforengineerr
$engineer_sign = $_SESSION['user']['username'];

//totacklepdoexceptionnfromdbb
try {
    //fetchrecordfromcwceo
    $stmt = $pdo->prepare("SELECT * FROM cwceo WHERE complaint_id = ?");
    $stmt->execute([$complaint_id]);
    //execqueryandbindcomplaint_idtoit

    //fetchtheresult
    $work = $stmt->fetch();

    if ($work) {
        //updateallowedfieldsonly
        $update = $pdo->prepare("UPDATE cwceo SET work_description = ?, complaint_close_date = ?, engineer_sign = ? WHERE complaint_id = ?");
        $update->execute([$work_description, $complaint_close_date, $engineer_sign, $complaint_id]);
    } else {
        
        //insertnewrecordifnotfound
        $insert = $pdo->prepare("INSERT INTO cwceo (complaint_id, work_description, complaint_close_date, engineer_sign) VALUES (?, ?, ?, ?)");
        $insert->execute([$complaint_id, $work_description, $complaint_close_date, $engineer_sign]);
    }

    echo json_encode([
        'success' => true,
        'new_work_description' => $work_description,
        'complaint_close_date' => $complaint_close_date
    ]);
} catch (PDOException $e) {
    //outputerrormessageifany
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>