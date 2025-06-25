<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: 0");
header("Pragma: no-cache");
session_start();

$error = '';

// Connect to the database
require_once '../db/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Use "code" as the login credential
    $code = trim($_POST['code'] ?? '');
    $role = trim($_POST['role'] ?? '');
    
    // Fetch user data from "cred" table based on code.
    $stmt = $pdo->prepare("SELECT code, name, role FROM cred WHERE code = ?");
    $stmt->execute([$code]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if user exists and the role matches.
    if ($userData && $userData['role'] === $role) {
        $_SESSION['user'] = [
            'username'    => $userData['name'],
            'unique_code' => $userData['code']
        ];
        $_SESSION['role'] = $userData['role'];
        
        // Redirect based on role. Stock users go to stock.php.
        if ($role === 'stockmanager') {
            header("Location: /views/stock.php");
        } else {
            header("Location: /index.php");
        }
        exit;
    } else {
        $error = "Invalid credentials. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="/css/style.css">
    <title>Login - Equipment Management</title>
</head>
<body style="margin: 0; padding: 0; height: 80vh; overflow: hidden; font-family: 'Segoe UI', sans-serif;">
 
<div style="
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(29, 112, 184, 0.46);
    color: white;
    font-family: 'Segoe UI', sans-serif;
    position: relative;
    z-index: 5;
">
    <img src="/img/logoo.png" alt="Company Logo" style="height: 100px;">
    <h1>Welcome to the Complaint Monitoring System </h1>
    <div id="dateDisplay" style="margin-right: 20px; font-size: 1.1rem; font-weight: 500; color: rgb(255, 255, 255);"></div>
</div>

<script>
    const dateDisplay = document.getElementById('dateDisplay');
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    dateDisplay.textContent = now.toLocaleDateString('en-IN', options);
</script>

<div style="
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('/img/secl_bg.webp') no-repeat center center;
    background-size: cover;
    filter: blur(8px);
    z-index: -1;
"></div>

<div style="
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 100%;
    max-width: 350px;
    padding: 20px;
">
    <h1 style="text-align: center; color: #fff; font-size: 2rem; font-weight: 700;">
        <a style="color: white; text-decoration: none; font-weight: 500" href="/views/login.php">LOGIN</a>
        <hr style="width: 50%; border: 1px solid #fff;">
        <a style="color: white; text-decoration: none; font-weight: 500" href="/views/register.php">REGISTER</a>
    </h1>

    <?php if ($error): ?>
        <p style="
            background: rgba(255, 0, 0, 0.1);
            border: 1px solid #e0b4b4;
            padding: 10px;
            border-radius: 6px;
            color: #fff;
            text-align: center;
        "><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php" style="display: flex; flex-direction: column; gap: 16px;">
        <input 
            type="password" 
            name="code" 
            placeholder="Enter Code" 
            required
            style="
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 60px;
                font-size: 1rem;
                width: 100%;
                height: 22px;
            "
        >
        <select
            name="role" 
            required
            style="
                padding: 12px;
                border: none;
                border-radius: 60px;
                font-size: 1rem;
                width: 100%;
                background: rgba(255,255,255,0.85);
                margin-bottom: 10px;
                outline: none;
                margin-left: 10px;
            "
        >
            <option value="">-- Select Role --</option>
            <option value="officer">Officer</option>
            <option value="engineer">Engineer</option>
            <option value="stockmanager">Stock Manager</option>
        </select>
        <button 
            type="submit"
            style="
                padding: 12px;
                background-color: rgba(29, 112, 184, 0.85);
                color: white;
                border: none;
                border-radius: 80px;
                font-size: 1.1rem;
                font-weight: bold;
                margin-left: 20px;
            "
        >Login</button>
    </form>
</div>
</body>
</html>