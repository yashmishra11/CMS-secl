<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Complaint Monitoring System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/css/style.css">

</head>

<body>
    <header>
        <nav>
            <?php if (isset($_SESSION['user'], $_SESSION['role']) && 
         ($_SESSION['role'] === 'engineer' || $_SESSION['role'] === 'officer')): ?>
            <a href="/index.php">HOME</a>
            <?php endif; ?>

            <?php if (isset($_SESSION['user'], $_SESSION['role']) && $_SESSION['role'] === 'engineer'): ?>
                <a href="/views/officer_dashboard.php">DASHBOARD</a>
                      <?php endif; ?>
                 <?php if (isset($_SESSION['user'], $_SESSION['role']) && 
         ($_SESSION['role'] === 'engineer' || $_SESSION['role'] === 'officer')): ?>
            <a href="/views/signed_complaints.php">SIGNED</a>
              <?php endif; ?>

         <?php if (isset($_SESSION['user'], $_SESSION['role']) && 
         ($_SESSION['role'] === 'engineer' || $_SESSION['role'] === 'officer')): ?>
            <a href="/views/excel.php">EXCEL</a>
            <?php endif; ?>
                
            <?php if (isset($_SESSION['user'], $_SESSION['role']) && $_SESSION['role'] === 'officer'): ?>
                <a href="/views/complaint_form.php">REGISTER A COMPLAINT</a>
            <?php endif; ?>

            <?php if (isset($_SESSION['user'], $_SESSION['role']) && $_SESSION['role'] === 'stockmanager'): ?>
           <a href="/views/stock.php">HOME</a>
             <?php endif; ?>

        <?php if (isset($_SESSION['user'], $_SESSION['role']) && $_SESSION['role'] === 'stockmanager'): ?>
           <a href="/views/records.php">RECORDS</a>
             <?php endif; ?>

          <a href="/views/logout.php">LOGOUT</a>


            <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['officer', 'engineer'])): ?>
    <div style="margin-top: -22px;">
        <form class="nav-search" method="GET" action="/index.php">
            <input type="text" name="search" placeholder="Search complaints..." />
            <button type="submit" style="height:20%;">Search</button>
        </form>
    </div>
<?php endif; ?>

        </nav>
    </header>
    <main>
<style>.header,
.nav,
.nav-item {
    font-size: 0.75rem !important;
}</style>