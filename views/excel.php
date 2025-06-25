<?php
session_start();
require_once '../db/db.php';

// Process export requests before any output.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clean output buffer if any
    if (ob_get_length()) {
        ob_clean();
    }
    $export_type = $_POST['export_type'] ?? '';
  
    if ($export_type === 'rows') {
        $start = intval($_POST['start'] ?? 0);
        $end   = intval($_POST['end'] ?? 0);
        if ($start <= 0 || $end < $start) {
            die("Invalid row inputs.");
        }
        $limit  = $end - $start + 1;
        $offset = $start - 1;
        $stmt = $pdo->prepare("
            SELECT c.complaint_id,
                   c.complaint_date,
                   c.complaint_description,
                   w.work_description,
                   w.complaint_close_date,
                   w.engineer_sign,
                   w.officer_sign
            FROM ccdrc c
            LEFT JOIN cwceo w ON c.complaint_id = w.complaint_id
            ORDER BY c.complaint_id ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $records = $stmt->fetchAll();
        $filename = "complaint records of rows {$start} to {$end}.csv";
    } elseif ($export_type === 'date') {
        $start_date = trim($_POST['start_date'] ?? '');
        $end_date   = trim($_POST['end_date'] ?? '');
        if (empty($start_date) || empty($end_date) || $start_date > $end_date) {
            die("Invalid date inputs.");
        }
        $stmt = $pdo->prepare("
            SELECT c.complaint_id,
                   c.complaint_date,
                   c.complaint_description,
                   w.work_description,
                   w.complaint_close_date,
                   w.engineer_sign,
                   w.officer_sign
            FROM ccdrc c
            LEFT JOIN cwceo w ON c.complaint_id = w.complaint_id
            WHERE c.complaint_date BETWEEN ? AND ?
            ORDER BY c.complaint_id ASC
        ");
        $stmt->execute([$start_date, $end_date]);
        $records = $stmt->fetchAll();
        $filename = "complaint records from {$start_date} to {$end_date}.csv";
    } elseif ($export_type === 'department') {
        $department = trim($_POST['department'] ?? '');
        if (empty($department)) {
            die("Please select a department.");
        }
        $stmt = $pdo->prepare("
            SELECT c.complaint_id,
                   c.complaint_date,
                   c.complaint_description,
                   w.work_description,
                   w.complaint_close_date,
                   w.engineer_sign,
                   w.officer_sign
            FROM ccdrc c
            LEFT JOIN cwceo w ON c.complaint_id = w.complaint_id
            WHERE c.department = ?
            ORDER BY c.complaint_id ASC
        ");
        $stmt->execute([$department]);
        $records = $stmt->fetchAll();
        $filename = "complaint records for department " . $department . ".csv";
    } else {
        die("Invalid export type.");
    }
  
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"$filename\"");
  
    $output = fopen('php://output', 'w');
    // Write CSV header row.
    fputcsv($output, [
        'Complaint ID',
        'Complaint Date',
        'Complaint Description',
        'Work Description',
        'Complaint Close Date',
        "Engineer's Sign",
        "Officer's Sign"
    ]);
  
    foreach ($records as $row) {
        $engineer = !empty($row['engineer_sign']) ? 'Signed' : '-';
        $officer  = !empty($row['officer_sign'])  ? 'Signed' : '-';
        fputcsv($output, [
            $row['complaint_id'],
            $row['complaint_date'],
            $row['complaint_description'],
            $row['work_description'],
            $row['complaint_close_date'],
            $engineer,
            $officer
        ]);
    }
    fclose($output);
    exit;
}

// Before outputting HTML, fetch the list of department names dynamically.
$stmt = $pdo->query("SELECT DISTINCT department FROM ccdrc ORDER BY department ASC");
$departments = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<?php include '../includes/header.php'; ?>

<!-- Main container styled similar to complaint_form.php -->
<div style="display: flex; justify-content: center; gap: 40px; margin: 40px auto; max-width: 1200px;">
    <!-- Excel Export by Rows Form -->
    <div class="form-container" style="
        padding: 32px 24px;
        width: 100%;
        max-width: 350px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.10);
        font-family: 'Segoe UI', Arial, sans-serif;
    ">
        <h1 style="text-align: center; margin-bottom: 24px; font-size: 2rem; color: #333; font-weight: 700;">
            Excel Export<br>By Rows
        </h1>
        <form method="POST" action="excel.php" style="display: flex; flex-direction: column; gap: 16px;">
            <input type="hidden" name="export_type" value="rows">
            <input type="number" name="start" placeholder="Enter starting row (X)" required style="
                padding: 10px 8px; border: 1px solid #ccc; border-radius: 60px; font-size: 1rem; width: 100%;
            ">
            <input type="number" name="end" placeholder="Enter ending row (Y)" required style="
                padding: 10px 8px; border: 1px solid #ccc; border-radius: 60px; font-size: 1rem; width: 100%;
            ">
            <button type="submit" style="
                padding: 12px 0; background-color: rgb(29,112,184); color: #fff; border: none;
                border-radius: 60px; font-size: 1.1rem; font-weight: 600; width: 100%;
            ">Generate Excel</button>
        </form>
    </div>
    <!-- Excel Export by Date Form -->
    <div class="form-container" style="
        padding: 32px 24px; width: 100%; max-width: 350px; background: #fff;
        border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.10);
        font-family: 'Segoe UI', Arial, sans-serif;
    ">
        <h1 style="text-align: center; margin-bottom: 24px; font-size: 2rem; color: #333; font-weight: 700;">
            Excel Export<br>By Date
        </h1>
        <form method="POST" action="excel.php" style="display: flex; flex-direction: column; gap: 16px;">
            <input type="hidden" name="export_type" value="date">
            <input type="date" name="start_date" placeholder="Enter start date" required style="
                padding: 10px 8px; border: 1px solid #ccc; border-radius: 60px; font-size: 1rem; width: 100%;
            ">
            <input type="date" name="end_date" placeholder="Enter end date" required style="
                padding: 10px 8px; border: 1px solid #ccc; border-radius: 60px; font-size: 1rem; width: 100%;
            ">
            <button type="submit" style="
                padding: 12px 0; background-color: rgb(29,112,184); color: #fff; border: none;
                border-radius: 60px; font-size: 1.1rem; font-weight: 600; width: 100%;
            ">Generate Excel</button>
        </form>
    </div>
    <!-- Excel Export by Department Form -->
    <div class="form-container" style="
        padding: 32px 24px; width: 100%; max-width: 350px; background: #fff;
        border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.10);
        font-family: 'Segoe UI', Arial, sans-serif;
    ">
        <h1 style="text-align: center; margin-bottom: 24px; font-size: 2rem; color: #333; font-weight: 700;">
            Excel Export<br>By Department
        </h1>
        <form method="POST" action="excel.php" style="display: flex; flex-direction: column; gap: 16px;">
            <input type="hidden" name="export_type" value="department">
            <select name="department" required style="
                padding: 10px; border: 1px solid #ccc; border-radius: 60px; font-size: 1rem; width: 100%;
            ">
                <option value="">-- Select Department --</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" style="
                padding: 12px 0; background-color: rgb(29,112,184); color: #fff;
                border: none; border-radius: 60px; font-size: 1.1rem; font-weight: 600; width: 100%;
            ">Generate Excel</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>