<?php
session_start();
require_once '../db/db.php';

$message = '';
$showPopup = false;
$popupText = '';

// Generate a 6-digit alphanumeric code.
function generateRandomCode($length = 6) {
    $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    $maxIndex = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $code .= $chars[random_int(0, $maxIndex)];
    }
    return $code;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $role = trim($_POST['role'] ?? '');
    
    // Basic validation.
if (empty($name) || !in_array($role, ['officer', 'engineer', 'stockmanager'])) {
        $message = "Please enter your name and select a valid role.";
    } else {
        // Generate a unique 6-character alphanumeric code.
        $code = generateRandomCode(6);
  
        // Insert new user into the cred table.
        try {
            $stmt = $pdo->prepare("INSERT INTO cred (code, name, role) VALUES (?, ?, ?)");
            $stmt->execute([$code, $name, $role]);
            // Set popup flag and text.
            $popupText = "Registration successful! Your role is: " . ucfirst($role) . " and your unique code is: " . $code;
            $showPopup = true;
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Complaint Monitoring System</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body style="margin: 0; padding: 0; height: 80vh; overflow: hidden; font-family: 'Segoe UI', sans-serif;">
    <div style="
        display: flex;
        justify-content: space-between;
        align-items: center;
        /* margin-left: px; */
        background: rgba(29,112,184,0.46);
        color: white;
        font-family: 'Segoe UI', sans-serif;
        position: relative;
        z-index: 5;
    ">
        <img src="/img/logoo.png" alt="Company Logo" style="height: 100px;"><h1>Welcome to the Complaint Monitoring System </h1>
        <div id="dateDisplay" style=" margin-right: 20px; font-size: 1.1rem; font-weight: 500; color: white;"></div>
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
    display: flex;
    flex-direction: column;
    align-items: center;
    background: none; /* Remove white box */
    box-shadow: none;  /* Remove box shadow */
    border-radius: 0;  /* Remove border radius */
">
            <h1 style="text-align: center; color: #fff; font-size: 2rem; font-weight: 700;">
   
     <a style="color: white;text-decoration: none; font-weight: 500"href="/views/register.php">REGISTER</a><hr style="width: 100%; border: 1px solid #fff;">
     <a style="color: white;text-decoration: none; font-weight: 500"href="/views/login.php">LOGIN</a></h1>
    <?php if ($message): ?>
        <p style="
            background: rgba(0,0,0,0.15);
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            color: #fff;
            text-align: center;
            margin-bottom: 10px;
            font-size: 1rem;
        "><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <form method="POST" action="register.php" style="display: flex; flex-direction: column; gap: 18px; width: 100%;">
        <input 
            type="text" 
            name="name" 
            placeholder="Enter your name" 
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
        >Register</button>
    </form>
</div>

    <?php if ($showPopup): ?>
        <script>
            alert("<?= addslashes($popupText) ?>");
            window.location.href = "/views/login.php";
        </script>
    <?php endif; ?>
</body>
</html>