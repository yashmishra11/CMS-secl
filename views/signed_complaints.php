<?php
session_start();
require_once '../db/db.php';
include '../includes/header.php';

// Get complaints that have an officer signature.
$stmt = $pdo->prepare("
    SELECT c.*, w.work_description, w.complaint_close_date, w.engineer_sign, w.officer_sign, 
           v.days_taken_to_resolve 
    FROM ccdrc c 
    LEFT JOIN cwceo w ON c.complaint_id = w.complaint_id 
    LEFT JOIN vw_complaint_status v ON c.complaint_id = v.complaint_id 
    WHERE w.officer_sign IS NOT NULL AND w.officer_sign <> ''
    ORDER BY c.complaint_id DESC
");
$stmt->execute();
$complaints = $stmt->fetchAll();
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
    color: #333;
}


        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            min-height: calc(100vh - 120px);
        }

        .main-title {
            text-align: center;
            margin: 0rem 0;
            font-size: 2.5rem;
            font-weight: 700;
            color: #1d70b8;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
.complaints-table-container {
    background: white;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    overflow: hidden;
    margin: 2rem 0;
    border: 1px solid #e9ecef;
}
.complaints-table-scroll {
    overflow-x: auto;
    overflow-y: auto;
    max-height: 70vh;
}
.complaints-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.95rem;
}
.complaints-table thead {
    background: linear-gradient(135deg, #1d70b8 0%, #005499 100%);
    color: white;
    position: sticky;
    top: 0;
    z-index: 10;
}
.complaints-table th {
    background: transparent !important;
    padding: 1rem 0.75rem;
    font-weight: 600;
    font-size: 0.9rem;
    text-align: center;
    border-right: 1px solid rgba(255,255,255,0.2);
    white-space: nowrap;
}
.complaints-table th:last-child {
    border-right: none;
}
.complaints-table tbody tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid #f1f3f5;
}
.complaints-table tbody tr:hover {
    background: #f8f9fa !important;
    transform: scale(1.005);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.complaints-table td {
    padding: 0.875rem 0.75rem;
    text-align: center;
    border-right: 1px solid #f1f3f5;
    vertical-align: middle;
}
.complaints-table td:last-child {
    border-right: none;
}
.status-signed {
    background: #27ae60;
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.status-pending {
    background: #e74c3c;
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.nav-container {
    display: flex;
    align-items: center;
    justify-content: space-between; /* keeps left, center, right spaced */
    gap: 2rem; /* space between nav links and search bar */
}

.nav-right {
    margin-left: 2rem; /* adds space between nav links and search bar */
    display: flex;
    align-items: center;
}

.footer {
    background: #2c3e50;
    color: white;
    text-align: center;
    padding: 1rem;
    margin-top: 2rem;
}
@media (max-width: 1200px) {
    .container {
        padding: 1rem;
    }
    .complaints-table {
        font-size: 0.85rem;
    }
    .complaints-table th,
    .complaints-table td {
        padding: 0.5rem 0.375rem;
    }
}
@media (max-width: 768px) {
    .main-title {
        font-size: 2rem;
    }
    .complaints-table {
        font-size: 0.75rem;
    }
    .complaints-table th,
    .complaints-table td {
        padding: 0.375rem 0.25rem;
    }
}
</style>

<div class="container">
    <h1 class="main-title" style="color:rgb(38, 174, 38);">Signed Complaints</h1>
    <?php if (count($complaints) > 0): ?>
        <div class="complaints-table-container">
            <div class="complaints-table-scroll">
                <table class="complaints-table">
                    <thead>
                        <tr>
                            <th>Complaint ID</th>
                            <th>Date</th>
                            <th>Department</th>
                            <th>Room No</th>
                            <th>Description</th>
                            <th>Work Description</th>
                            <th>Close Date</th>
                            <th>Engineer's Sign</th>
                            <th>Officer's Sign</th>
                            <th>Days Taken</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($complaints as $complaint): ?>
                            <tr>
                                <td><?= htmlspecialchars($complaint['complaint_id']); ?></td>
                                <td><?= htmlspecialchars($complaint['complaint_date'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($complaint['department'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($complaint['room_no'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($complaint['complaint_description'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($complaint['work_description'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($complaint['complaint_close_date'] ?? '-'); ?></td>
                                <td>
                                    <?php if (!empty($complaint['engineer_sign'])): ?>
                                        <span class="status-signed">Signed</span>
                                    <?php else: ?>
                                        <span class="status-pending">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($complaint['officer_sign'])): ?>
                                        <span class="status-signed">Signed</span>
                                    <?php else: ?>
                                        <span class="status-pending">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($complaint['days_taken_to_resolve'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <p>No signed complaints found.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>