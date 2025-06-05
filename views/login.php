<?php
session_start();
require_once '../db/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data and trim whitespace
    $id       = trim($_POST['id']);
    $password = trim($_POST['password']);

    // Try fetching as engineer first
    $stmt = $pdo->prepare('SELECT * FROM engineers WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if ($user) {
        $role = 'engineer';
    } else {
        // If not an engineer, try as officer
        $stmt = $pdo->prepare('SELECT * FROM officers WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if ($user) {
            $role = 'officer';
        }
    }

    if ($user) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            $_SESSION['role'] = $role;
        } else {
            $error = 'Invalid credentials.';
        }
    } else {
        $error = 'User not found.';
    }
}

// If user is authenticated, show the dashboard and exit.
if (isset($_SESSION['user'])) {
    // Retrieve complaints from the ccdrc table
    $stmt = $pdo->query('SELECT * FROM ccdrc ORDER BY complaint_date DESC');
    $complaints = $stmt->fetchAll();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Dashboard - Complaint Monitoring System</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/css/style.css">
    </head>
    <body>
        <header>
            <nav>
                <a href="/index.php">Home</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>
        <main>
            <h2>Dashboard</h2>
            <p>You are logged in as <strong><?php echo htmlspecialchars($_SESSION['role']); ?></strong></p>
            <?php if ($_SESSION['role'] === 'officer'): ?>
                <p><a href="add_complaint.php">Add New Complaint</a></p>
            <?php endif; ?>
            <table border="1" cellspacing="0" cellpadding="5">
                <thead>
                    <tr>
                        <th>Complaint ID</th>
                        <th>Date</th>
                        <th>Department</th>
                        <th>Room No</th>
                        <th>Description</th>
                        <th>Work Description</th>
                        <th>Close Date</th>
                        <th>Engineer Sign</th>
                        <th>Officer Sign</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($complaints as $complaint):
                        // Fetch work details, if any
                        $stmt2 = $pdo->prepare('SELECT * FROM cwceo WHERE complaint_id = ?');
                        $stmt2->execute([$complaint['complaint_id']]);
                        $work = $stmt2->fetch();
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($complaint['complaint_id']); ?></td>
                        <td><?php echo htmlspecialchars($complaint['complaint_date']); ?></td>
                        <td><?php echo htmlspecialchars($complaint['department']); ?></td>
                        <td><?php echo htmlspecialchars($complaint['room_no']); ?></td>
                        <td><?php echo htmlspecialchars($complaint['complaint_description']); ?></td>
                        <td><?php echo $work ? htmlspecialchars($work['work_description']) : ''; ?></td>
                        <td><?php echo $work ? htmlspecialchars($work['complaint_close_date']) : ''; ?></td>
                        <td><?php echo $work ? htmlspecialchars($work['engineer_sign']) : ''; ?></td>
                        <td><?php echo $work ? htmlspecialchars($work['officer_sign']) : ''; ?></td>
                        <td>
                            <?php if ($_SESSION['role'] === 'officer'): ?>
                                <a href="edit_complaint.php?id=<?php echo $complaint['complaint_id']; ?>">Edit</a> |
                                <a href="delete_complaint.php?id=<?php echo $complaint['complaint_id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                                <?php if (empty($work['officer_sign'])): ?>
                                    | <a href="sign_officer.php?id=<?php echo $complaint['complaint_id']; ?>">Sign</a>
                                <?php endif; ?>
                            <?php elseif ($_SESSION['role'] === 'engineer'): ?>
                                <?php if (!$work): ?>
                                    <a href="add_work.php?id=<?php echo $complaint['complaint_id']; ?>">Add Work</a>
                                <?php else: ?>
                                    <?php if (empty($work['engineer_sign'])): ?>
                                        <a href="sign_engineer.php?id=<?php echo $complaint['complaint_id']; ?>">Sign</a>
                                    <?php else: ?>
                                        <span>Read Only</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
        <footer>
            <p>&copy; <?php echo date("Y"); ?> Complaint Monitoring System</p>
        </footer>
        <script src="/js/main.js"></script>
    </body>
    </html>
    <?php
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Complaint Monitoring System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="/index.php">Home</a>
        </nav>
    </header>
    <main>
        <h2>Login</h2>
        <?php if (isset($error)): ?>
            <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <label for="id">ID:</label>
            <input type="text" id="id" name="id" required>
            <br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <br>
            <button type="submit">Login</button>
        </form>
    </main>
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Complaint Monitoring System</p>
    </footer>
    <script src="/js/main.js"></script>
</body>
</html>