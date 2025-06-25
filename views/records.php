<?php
session_start();
require_once '../db/db.php';

// Helper for safe output
function safe($v) { return htmlspecialchars((string)($v ?? '')); }

// Filtering logic
$filter = $_GET['filter'] ?? 'all';

// Fetch stock records (inventory and purchases)
$stockQuery = "SELECT id, name, department, quantity, purchase_date, vendor, ppu, expense FROM stock";
if ($filter === 'purchase') {
    $stockQuery .= " WHERE vendor != 'Inventory'";
} elseif ($filter === 'inventory') {
    $stockQuery .= " WHERE vendor = 'Inventory'";
}
$stockQuery .= " ORDER BY id DESC";
$stockRecords = $pdo->query($stockQuery)->fetchAll(PDO::FETCH_ASSOC);

// Fetch transfer records
$transferRecords = [];
if ($filter === 'all' || $filter === 'transfer') {
    $transferStmt = $pdo->query("
        SELECT t.transfer_id, t.stock_id, t.quantity, t.date_of_transfer, t.transfer_from_dept, t.transfer_to_dept, t.transfer_to_person,
               s.name as equipment_name
        FROM transfer t
        JOIN stock s ON t.stock_id = s.id
        ORDER BY t.date_of_transfer DESC
    ");
    $transferRecords = $transferStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Transactions - Records</title>
    <style>
        /* --- Begin your main CSS styling --- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, #1d70b8 0%, #005499 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .nav {
            display: flex;
            justify-content: center;
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .nav-item {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .nav-item:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            min-height: calc(100vh - 120px);
        }

        .main-title {
            text-align: center;
            margin: 2rem 0;
            font-size: 2.5rem;
            font-weight: 700;
            color: #1d70b8;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .filter-section {
            text-align: center;
            margin: 2rem 0;
        }

        .filter-select {
            padding: 0.875rem 1.5rem;
            border: 2px solid #1d70b8;
            border-radius: 8px;
            background: white;
            color: #1d70b8;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            outline: none;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .filter-select:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .filter-select:focus {
            box-shadow: 0 0 0 3px rgba(29, 112, 184, 0.2);
        }

        .table-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            overflow: hidden;
            margin: 2rem 0;
            border: 1px solid #e9ecef;
        }

        .table-scroll {
            overflow-x: auto;
            overflow-y: auto;
            max-height: 70vh;
        }

        .records-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        .records-table thead {
            background: linear-gradient(135deg, #1d70b8 0%, #005499 100%);
            color: white;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .records-table th {
            padding: 1rem 0.75rem;
            font-weight: 600;
            font-size: 0.9rem;
            text-align: center;
            border-right: 1px solid rgba(255,255,255,0.2);
            white-space: nowrap;
        }

        .records-table th:last-child {
            border-right: none;
        }

        .records-table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #f1f3f5;
        }

        .records-table tbody tr:hover {
            background: #f8f9fa !important;
            transform: scale(1.005);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .records-table td {
            padding: 0.875rem 0.75rem;
            text-align: center;
            border-right: 1px solid #f1f3f5;
            vertical-align: middle;
        }

        .records-table td:last-child {
            border-right: none;
        }

        .inventory-row {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        }

        .purchase-row {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
        }

        .transfer-row {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        }

        .type-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-inventory {
            background: #28a745;
            color: white;
        }

        .badge-purchase {
            background: #17a2b8;
            color: white;
        }

        .badge-transfer {
            background: #ffc107;
            color: #212529;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #6c757d;
        }

        .empty-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        @media (max-width: 1200px) {
            .container {
                padding: 1rem;
            }
            .records-table {
                font-size: 0.85rem;
            }
            .records-table th,
            .records-table td {
                padding: 0.5rem 0.375rem;
            }
        }

        @media (max-width: 768px) {
            .main-title {
                font-size: 2rem;
            }
            .filter-select {
                width: 100%;
                max-width: 300px;
            }
            .records-table {
                font-size: 0.75rem;
            }
            .records-table th,
            .records-table td {
                padding: 0.375rem 0.25rem;
            }
            .type-badge {
                font-size: 0.7rem;
                padding: 0.125rem 0.5rem;
            }
        }

        .footer {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 1rem;
            margin-top: 2rem;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #1d70b8;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        /* --- End main CSS styling --- */
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <nav class="nav">
            <a href="/views/stock.php" class="nav-item">HOME</a>
            <a href="/views/records.php" class="nav-item">RECORDS</a>
            <a href="/views/logout.php" class="nav-item">LOGOUT</a>
        </nav>
    </div>

    <!-- Main Container -->
    <div class="container">
        <h2 class="main-title">All Transactions</h2>

        <!-- Filter Dropdown -->
        <div class="filter-section">
            <form method="GET" style="display:inline-block;">
                <select name="filter" onchange="this.form.submit()" class="filter-select">
                    <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Transactions</option>
                    <option value="purchase" <?= $filter === 'purchase' ? 'selected' : '' ?>>Purchases Only</option>
                    <option value="transfer" <?= $filter === 'transfer' ? 'selected' : '' ?>>Transfers Only</option>
                </select>
            </form>
        </div>

        <!-- Records Table -->
        <div class="table-container">
            <div class="table-scroll">
                <table class="records-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>ID</th>
                            <th>Equipment</th>
                            <th>Department</th>
                            <th>Date</th>
                            <th>Vendor / Transfer To Person</th>
                            <th>Quantity</th>
                            <th>PPU</th>
                            <th>Expense</th>
                            <th>From Dept</th>
                            <th>To Dept</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Inventory & Purchases
                        foreach ($stockRecords as $record):
                            $isInventory = ($record['vendor'] === 'Inventory');
                            $rowClass = $isInventory ? 'inventory-row' : 'purchase-row';
                        ?>
                        <tr class="<?= $rowClass ?>">
                            <td>
                                <span class="type-badge <?= $isInventory ? 'badge-inventory' : 'badge-purchase' ?>">
                                    <?= $isInventory ? 'Inventory Added' : 'Purchase' ?>
                                </span>
                            </td>
                            <td><?= safe($record['id']) ?></td>
                            <td><?= safe($record['name']) ?></td>
                            <td><?= safe($record['department']) ?></td>
                            <td><?= safe($record['purchase_date']) ?></td>
                            <td><?= safe($record['vendor']) ?></td>
                            <td><?= safe($record['quantity']) ?></td>
                            <td><?= $record['ppu'] !== null ? '₹' . number_format($record['ppu'], 2) : '-' ?></td>
                            <td><?= $record['expense'] !== null ? '₹' . number_format($record['expense'], 2) : '-' ?></td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                        <?php endforeach; ?>

                        <?php
                        // Transfers
                        foreach ($transferRecords as $transfer): ?>
                        <tr class="transfer-row">
                            <td><span class="type-badge badge-transfer">Transfer</span></td>
                            <td><?= safe($transfer['transfer_id']) ?></td>
                            <td><?= safe($transfer['equipment_name']) ?></td>
                            <td>-</td>
                            <td><?= safe($transfer['date_of_transfer']) ?></td>
                            <td><?= safe($transfer['transfer_to_person']) ?></td>
                            <td><?= safe($transfer['quantity']) ?></td>
                            <td>-</td>
                            <td>-</td>
                            <td><?= safe($transfer['transfer_from_dept']) ?></td>
                            <td><?= safe($transfer['transfer_to_dept']) ?></td>
                        </tr>
                        <?php endforeach; ?>

                        <?php if (empty($stockRecords) && empty($transferRecords)): ?>
                        <tr>
                            <td colspan="11" class="empty-state">
                                <span class="empty-icon">&#128196;</span><br>
                                No transactions found.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2025 Stocks Management System. All rights reserved.</p>
    </div>

    <script>
        // Add smooth loading effect when filter changes
        document.querySelector('.filter-select').addEventListener('change', function() {
            const tableContainer = document.querySelector('.table-container');
            tableContainer.style.opacity = '0.7';
            
            // Create loading overlay
            const loadingDiv = document.createElement('div');
            loadingDiv.innerHTML = '<div class="loading"></div>';
            loadingDiv.style.cssText = `
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                z-index: 1000;
            `;
            tableContainer.style.position = 'relative';
            tableContainer.appendChild(loadingDiv);
        });

        // Add smooth scroll to top when page loads
        document.addEventListener('DOMContentLoaded', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
            // Add fade-in animation
            const tableContainer = document.querySelector('.table-container');
            tableContainer.style.opacity = '0';
            tableContainer.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                tableContainer.style.transition = 'all 0.6s ease';
                tableContainer.style.opacity = '1';
                tableContainer.style.transform = 'translateY(0)';
            }, 100);
        });

        // Add keyboard navigation for table
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                const rows = document.querySelectorAll('.records-table tbody tr');
                const currentRow = document.querySelector('.records-table tbody tr:hover');
                
                if (currentRow) {
                    e.preventDefault();
                    const currentIndex = Array.from(rows).indexOf(currentRow);
                    let nextIndex;
                    
                    if (e.key === 'ArrowUp') {
                        nextIndex = currentIndex > 0 ? currentIndex - 1 : rows.length - 1;
                    } else {
                        nextIndex = currentIndex < rows.length - 1 ? currentIndex + 1 : 0;
                    }
                    
                    rows[nextIndex].scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    </script>
</body>
</html>