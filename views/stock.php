<?php
session_start();
require_once '../db/db.php'; // Adjust path if needed

$showSuccess = false;
$successMessage = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_inventory') {
        $name = trim($_POST['equipment'] ?? '');
        $department = 'in stock'; // Always 'in stock' for inventory
        $purchaseDate = trim($_POST['purchase_date'] ?? '');
        $vendor = trim($_POST['vendor'] ?? '');
        $quantity = trim($_POST['quantity'] ?? '');
        $ppu = trim($_POST['ppu'] ?? '');
        $expense = trim($_POST['expense'] ?? '');

        $stmt = $pdo->prepare("INSERT INTO stock (name, department, purchase_date, vendor, quantity, ppu, expense)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $department, $purchaseDate, $vendor, $quantity, $ppu, $expense]);
        $showSuccess = true;
        $successMessage = 'Successfully added item!';
    }

    if ($action === 'add_purchase') {
        $name = trim($_POST['equipment'] ?? '');
        $department = trim($_POST['department'] ?? 'General');
        $purchaseDate = trim($_POST['purchase_date'] ?? '');
        $vendor = trim($_POST['vendor'] ?? '');
        $quantity = trim($_POST['quantity'] ?? '');
        $ppu = trim($_POST['ppu'] ?? '');
        $expense = trim($_POST['expense'] ?? '');

        $stmt = $pdo->prepare("INSERT INTO stock (name, department, purchase_date, vendor, quantity, ppu, expense)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $department, $purchaseDate, $vendor, $quantity, $ppu, $expense]);
        $showSuccess = true;
    }
      }  ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stocks Management</title>
    <style>
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

        /* Header styles */
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

        /* Main container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            min-height: calc(100vh - 120px);
        }

        .main-title {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        /* Message styles */
        .message {
            background: linear-gradient(135deg, #d1e7dd 0%, #a3d9a5 100%);
            color: #0f5132;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 2rem;
            border: 1px solid #badbcc;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Tab styles */
        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            justify-content: center;
        }

        .tab-button {
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            background: linear-gradient(135deg, #1d70b8 0%, #005499 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .tab-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .tab-button.active {
            background: linear-gradient(135deg, #005499 0%, #003d73 100%);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        /* Tab content */
        .tab-content {
            display: none;
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }

        .tab-content.active {
            display: block;
        }

        .tab-content h3 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            font-weight: 600;
            border-bottom: 3px solid #1d70b8;
            padding-bottom: 0.5rem;
        }

        /* Form styles */
        .form {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .form-input {
            padding: 0.875rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-input:focus {
            outline: none;
            border-color: #1d70b8;
            background: white;
            box-shadow: 0 0 0 3px rgba(29, 112, 184, 0.1);
        }

        .form-input[readonly] {
            background: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
        }

        .form-button {
            padding: 0.875rem 2rem;
            background: linear-gradient(135deg, #1d70b8 0%, #005499 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            align-self: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .form-button:active {
            transform: translateY(0);
        }

        /* Special layout for purchase form */
        .purchase-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 1rem;
        }

        .expense-field {
            max-width: 300px;
            margin: 0 auto;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .main-title {
                font-size: 2rem;
            }

            .tabs {
                flex-direction: column;
                align-items: center;
            }

            .form-grid,
            .purchase-grid {
                grid-template-columns: 1fr;
            }

            .tab-content {
                padding: 1.5rem;
            }
        }

        /* Footer */
        .footer {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 1rem;
            margin-top: 2rem;
        }
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

     <?php if ($showSuccess): ?>
    <div id="successPopup" style="
        position: fixed;
        top: 30px;
        left: 50%;
        transform: translateX(-50%);
        background: #27ae60;
        color: #fff;
        padding: 1.2rem 2.5rem;
        border-radius: 12px;
        font-size: 1.2rem;
        font-weight: 600;
        box-shadow: 0 4px 16px rgba(0,0,0,0.18);
        z-index: 9999;
        text-align: center;
        display: block;
        ">
        Successfully added item!
    </div>
    <script>
        setTimeout(function() {
            var popup = document.getElementById('successPopup');
            if (popup) popup.style.display = 'none';
        }, 2000);
    </script>
    <?php endif; ?>

    <!-- Main Container -->
    <div class="container">
        <h1 class="main-title">Stocks Management</h1>
        
        <!-- Success Message (show when needed) -->
        <div class="message" style="display: none;" id="successMessage">
            Operation completed successfully!
        </div>

        <!-- Tab Buttons -->
        <div class="tabs">
            <button class="tab-button active" data-tab="inventory">Add Inventory</button>
            <button class="tab-button" data-tab="purchases">Purchases</button>
            <button class="tab-button" data-tab="transfers">Transfers</button>
        </div>

        <!-- Tab Contents -->
        <div id="inventory" class="tab-content active">
            <h3>Add Equipment to Inventory</h3>
            <form class="form" method="post" action="stock.php">
                <input type="hidden" name="action" value="add_inventory">
                
                <div class="form-grid">
                    <input type="text" name="equipment" placeholder="Equipment Name" required class="form-input">
                    <input type="date" name="purchase_date" placeholder="Purchase Date" required class="form-input">
                    <input type="text" name="vendor" placeholder="Vendor" required class="form-input">
                    <input type="number" id="inv_quantity" name="quantity" placeholder="Quantity" required class="form-input">
                    <input type="number" step="0.01" id="inv_ppu" name="ppu" placeholder="Price Per Unit" required class="form-input">
                    <input type="number" step="0.01" id="inv_expense" name="expense" placeholder="Total Expense" readonly class="form-input">
                </div>
                
                <button type="submit" class="form-button">Add Inventory</button>
            </form>
        </div>

        <div id="purchases" class="tab-content">
            <h3>Equipment Purchases</h3>
            <form class="form" method="post" action="stock.php">
                <input type="hidden" name="action" value="add_purchase">
                
                <div class="purchase-grid">
                    <input type="text" name="equipment" placeholder="Equipment Name" required class="form-input">
                    <input type="text" name="department" placeholder="Department (optional)" class="form-input">
                    <input type="number" id="pur_quantity" name="quantity" placeholder="Quantity" required class="form-input">
                    <input type="date" name="purchase_date" required class="form-input">
                    <input type="text" name="vendor" placeholder="Vendor" required class="form-input">
                    <input type="number" step="0.01" id="pur_ppu" name="ppu" placeholder="Price Per Unit" required class="form-input">
                </div>
                
                <input type="number" step="0.01" id="pur_expense" name="expense" placeholder="Total Expense" readonly class="form-input expense-field">
                
                <button type="submit" class="form-button">Record Purchase</button>
            </form>
        </div>

        <div id="transfers" class="tab-content">
            <h3>Equipment Transfers</h3>
            <form class="form" method="post" action="stock.php">
                <input type="hidden" name="action" value="make_transfer">
                
                <div class="form-grid">
                    <input type="text" name="equipment" placeholder="Equipment Name" required class="form-input">
                    <input type="number" name="quantity" placeholder="Quantity" required class="form-input">
                    <input type="text" name="from_department" placeholder="From Department" required class="form-input">
                    <input type="text" name="to_department" placeholder="To Department" required class="form-input">
                    <input type="date" name="transfer_date" required class="form-input">
                    <input type="text" name="transfer_to_person" placeholder="Transfer To Person" required class="form-input">
                </div>
                
                <button type="submit" class="form-button">Record Transfer</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2025 Stocks Management System. All rights reserved.</p>
    </div>

    <script>
        // Tab switching functionality
        const buttons = document.querySelectorAll('.tab-button');
        const contents = document.querySelectorAll('.tab-content');

        buttons.forEach(button => {
            button.addEventListener('click', () => {
                const target = button.getAttribute('data-tab');
                buttons.forEach(btn => btn.classList.remove('active'));
                contents.forEach(content => content.classList.remove('active'));
                button.classList.add('active');
                document.getElementById(target).classList.add('active');
            });
        });

        // Auto-calculation functions
        function calculateInventoryExpense() {
            const quantity = parseFloat(document.getElementById('inv_quantity').value) || 0;
            const ppu = parseFloat(document.getElementById('inv_ppu').value) || 0;
            const totalExpense = quantity * ppu;
            document.getElementById('inv_expense').value = totalExpense.toFixed(2);
        }

        function calculatePurchaseExpense() {
            const quantity = parseFloat(document.getElementById('pur_quantity').value) || 0;
            const ppu = parseFloat(document.getElementById('pur_ppu').value) || 0;
            const totalExpense = quantity * ppu;
            document.getElementById('pur_expense').value = totalExpense.toFixed(2);
        }

        // Add event listeners for inventory form
        document.getElementById('inv_quantity').addEventListener('input', calculateInventoryExpense);
        document.getElementById('inv_ppu').addEventListener('input', calculateInventoryExpense);

        // Add event listeners for purchase form
        document.getElementById('pur_quantity').addEventListener('input', calculatePurchaseExpense);
        document.getElementById('pur_ppu').addEventListener('input', calculatePurchaseExpense);

        // Add smooth transitions and hover effects
        document.addEventListener('DOMContentLoaded', function() {
            // Add focus effects to form inputs
            const inputs = document.querySelectorAll('.form-input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'translateY(-2px)';
                });
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>
