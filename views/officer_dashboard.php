<?php
session_start();
if (!isset($_SESSION['user'], $_SESSION['role']) || $_SESSION['role'] !== 'engineer') {
    // Only engineers can access the dashboard.
    header("Location: /index.php");
    exit;
}

require_once '../db/db.php';

// Count total complaints.
$stmtTotal = $pdo->query("SELECT COUNT(*) AS total FROM ccdrc");
$totalComplaints = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

// Count unsigned complaints.
$stmtUnsigned = $pdo->query("SELECT COUNT(*) AS unsignedCount FROM cwceo WHERE officer_sign = '' OR officer_sign IS NULL");
$unsignedComplaints = $stmtUnsigned->fetch(PDO::FETCH_ASSOC)['unsignedCount'];

// Group complaints by department.
$stmtDeptList = $pdo->query("
    SELECT department, COUNT(*) as deptCount 
    FROM ccdrc 
    GROUP BY department 
    ORDER BY deptCount DESC
");
$departmentData = $stmtDeptList->fetchAll(PDO::FETCH_ASSOC);

$availableColors = ['#e0f7fa', '#f1f8e9', '#ede7f6', '#fff8e1', '#fce4ec'];
$deptColors = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Engineer Dashboard</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .dashboard-container { display: flex; gap: 20px; margin: 20px; flex-wrap: wrap; }
        .dashboard-box { border: 1px solid #ccc; padding: 20px; width: 250px; text-align: center; box-shadow: 1px 1px 5px rgba(0,0,0,0.1); cursor: pointer; transition: transform 0.3s; }
        .dashboard-box:hover { transform: scale(1.05); }
        footer { background-color: rgb(29,112,184); color: #f2f6fa; padding: 15px; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <h1>Engineer Dashboard</h1>
    <p>You are logged in as: <?php echo htmlspecialchars($_SESSION['user']['username']); ?></p>

    <div class="dashboard-container">
        <a href="/index.php" style="text-decoration: none;">
            <div class="dashboard-box" style="background: #f9f9f9;">
                <h2>Total Complaints</h2>
                <p><?php echo $totalComplaints; ?></p>
            </div>
        </a>
        <a href="/index.php?search=unsigned" style="text-decoration: none;">
            <div class="dashboard-box" style="background: #f9f9f9;">
                <h2>Unsigned Complaints</h2>
                <p><?php echo $unsignedComplaints; ?></p>
            </div>
        </a>
    </div>

    <h1>Complaints by Department</h1>
    <div class="dashboard-container">
        <?php foreach ($departmentData as $dept): ?>
            <?php 
                $departmentName = $dept['department'];
                if (!isset($deptColors[$departmentName])) {
                    $deptColors[$departmentName] = $availableColors[array_rand($availableColors)];
                }
                $bgColor = $deptColors[$departmentName];
            ?>
            <a href="/index.php?search=<?php echo urlencode($departmentName); ?>" style="text-decoration: none;">
                <div class="dashboard-box" style="background: <?php echo $bgColor; ?>;">
                    <h3><?php echo htmlspecialchars($departmentName); ?></h3>
                    <p><?php echo $dept['deptCount']; ?> Complaints</p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>