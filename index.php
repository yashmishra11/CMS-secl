<?php
session_start();
require_once 'db/db.php';
include 'includes/header.php';


$filterClause = "";
$params = [];

// For officers, filter by the new code column in cwceo.
if (isset($_SESSION['role']) && $_SESSION['role'] === 'officer') {
    $filterClause = " AND w.code = ? ";
    $params[] = $_SESSION['user']['unique_code'];
}

if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $searchTerm = trim($_GET['search']);
    if ($searchTerm === 'unsigned') {
        $query = "
            SELECT c.*, w.work_description, w.complaint_close_date, w.engineer_sign, w.officer_sign,
                   v.days_taken_to_resolve 
            FROM ccdrc c 
            LEFT JOIN cwceo w ON c.complaint_id = w.complaint_id 
            LEFT JOIN vw_complaint_status v ON c.complaint_id = v.complaint_id 
            WHERE (w.officer_sign IS NULL OR w.officer_sign = '')" . $filterClause . "
            ORDER BY c.complaint_id DESC
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
    } else {
        $query = "
            SELECT c.*, w.work_description, w.complaint_close_date, w.engineer_sign, w.officer_sign,
                   v.days_taken_to_resolve 
            FROM ccdrc c 
            LEFT JOIN cwceo w ON c.complaint_id = w.complaint_id 
            LEFT JOIN vw_complaint_status v ON c.complaint_id = v.complaint_id 
            WHERE c.department = ? " . $filterClause . "
            ORDER BY c.complaint_id DESC
        ";
        $params = array_merge([$searchTerm], $params);
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
    }
} else {
    $query = "
        SELECT c.*, w.work_description, w.complaint_close_date, w.engineer_sign, w.officer_sign,
               v.days_taken_to_resolve 
        FROM ccdrc c
        LEFT JOIN cwceo w ON c.complaint_id = w.complaint_id
        LEFT JOIN vw_complaint_status v ON c.complaint_id = v.complaint_id
        WHERE (w.officer_sign IS NULL OR w.officer_sign = '')" . $filterClause . "
        ORDER BY c.complaint_id DESC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
}

$complaints = $stmt->fetchAll();
$display_counter = count($complaints);
?>


<?php ?>
<div class="container">
    <h1 class="main-title">Unsolved Complaints</h1>
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
                            <th>Complaint Description</th>
                            <th style="width: 300px;">Work Description</th>
                            <th>Close Date</th>
                            <th>Engineer's Sign</th>
                            <th>Officer's Sign</th>
                            <th>Days Taken</th>
                        </tr>
                    </thead>
                    <tbody style="color:rgb(174, 38, 38);">
                        <?php foreach ($complaints as $complaint): 
                            $stmtWork = $pdo->prepare("SELECT * FROM cwceo WHERE complaint_id = ?");
                            $stmtWork->execute([$complaint['complaint_id']]);
                            $work = $stmtWork->fetch();
                            $dept = $complaint['department'];
                            // $bgColor = ... (if you want to use department colors)
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($complaint['complaint_id']); ?></td>
                            <td><?= htmlspecialchars($complaint['complaint_date'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($complaint['department'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($complaint['room_no'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($complaint['complaint_description'] ?? ''); ?></td>
                            <td id="work-desc-<?= $complaint['complaint_id']; ?>">
                                <?php if ($work && !empty($work['work_description'])): ?>
                                    <?= htmlspecialchars($work['work_description']); ?>
                                <?php else: ?>
                                    <?php if (isset($_SESSION['user'], $_SESSION['role']) && $_SESSION['role'] === 'engineer'): ?>
                                        <button class="add-work" data-complaint-id="<?= $complaint['complaint_id']; ?>">Add</button>
                                        <div id="form-<?= $complaint['complaint_id']; ?>" style="display:none; margin-top:5px;">
                                            <form class="work-form" method="POST" action="/views/edit_work.php?id=<?= $complaint['complaint_id']; ?>">
                                                <textarea name="work_description" placeholder="Enter work description" required></textarea>
                                                <br>
                                                <input type="date" name="complaint_close_date" required>
                                                <br>
                                                <button type="submit">Save</button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td id="close-date-<?= $complaint['complaint_id']; ?>">
                                <?= $work ? htmlspecialchars($work['complaint_close_date'] ?? '-') : '-'; ?>
                            </td>
                            <td>
                                <?php if ($work && !empty($work['engineer_sign'])): ?>
                                    <input type="checkbox" checked disabled>
                                <?php else: ?>
                                    <input type="checkbox" disabled>
                                <?php endif; ?>
                            </td>
                            <td id="officer-sign-<?= $complaint['complaint_id']; ?>">
                                <?php if (isset($_SESSION['user'], $_SESSION['role']) && $_SESSION['role'] === 'officer'): ?>
                                    <?php if ($work && !empty($work['officer_sign'])): ?>
                                        <input type="checkbox" checked disabled>
                                    <?php else: ?>
                                        <input type="checkbox" class="officer-sign" data-complaint-id="<?= $complaint['complaint_id']; ?>">
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if ($work && !empty($work['officer_sign'])): ?>
                                        <input type="checkbox" checked disabled>
                                    <?php else: ?>
                                        <input type="checkbox" disabled>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($complaint['days_taken_to_resolve'] ?? '-') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <p>No unsigned complaints found.</p>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>


<script>
document.addEventListener("DOMContentLoaded", function(){
    document.querySelectorAll(".add-work").forEach(function(button){
        button.addEventListener("click", function(){
            var complaintId = this.getAttribute("data-complaint-id");
            var formDiv = document.getElementById("form-" + complaintId);
            formDiv.style.display = (formDiv.style.display === "none" || formDiv.style.display === "") ? "block" : "none";
        });
        //showorhideaddworkformwhenbuttonisclicked
    });
//selectsworkdesc,date,complaintidfromaddwork
    document.querySelectorAll(".work-form").forEach(function(form) {
        //attchadsubmitlistenerforeachworkform
        form.addEventListener("submit", function(e){
            e.preventDefault();
                        //stopstheformfromsubmittingthetraditionalway

            var formData = new FormData(form);
            //createnewdataobjectfromform
            var actionUrl = form.getAttribute('action');
            //getvalueofactionattributefromform
            
            fetch(actionUrl, {
                method: 'POST',
                body: formData
                //sendformdataasPOSTrequest
            })
            .then(response => response.json())
            //waitforresponseandparseitinjson
            .then(data => {
                if(data.success) {
                    //checkifresponseissuccessfulfromserver
                    alert('Work details updated successfully');
                    var complaintId = actionUrl.split('id=')[1];
                    document.getElementById("work-desc-" + complaintId).innerHTML = data.new_work_description;
                    document.getElementById("close-date-" + complaintId).innerHTML = data.complaint_close_date;
                    document.getElementById("form-" + complaintId).style.display = "none";
                //runcodewithparedjason
                } else {
                    alert('Error: ' + data.error);
                    //goodforerrorhandling
                }
            })
            .catch(err => {
                console.error(err);
                alert('An error occurred while saving the data.');
                 //iferrothenshowerrorpopup
            });
        });
    });

document.querySelectorAll(".officer-sign").forEach(function(checkbox) {
    //selectallelementswithclassofficer-sign
    //startloopforeachcheckbox
    checkbox.addEventListener("change", function(){
        if(this.checked) {
            //iftheboxischecked
            var complaintId = this.getAttribute("data-complaint-id");
            //data-complaint-idsavesidforcurrentcheckbox
            fetch("/views/sign_officer.php?id=" + complaintId, {
                //sendsreqtoserverwithcomplaintidtoupdateofficersign
                method: 'POST'
                //detechanychangetothecheckbox
            })
            .then(response => response.json())
            //turnserverrespintojson
            .then(data => {
                //processjsondata
                if (data.success) {
                    alert("Officer sign updated successfully.");
                    checkbox.disabled = true;
                } else {
                    alert("Error: " + data.error);
                    checkbox.checked = false;
                    //disablethecheckboxfrombeingcheckedagain
                //checkifsuccessorerrorforupdate
                }
            })
            .catch(err => {
                console.error(err);
                alert("An error occurred while updating officer sign.");
                this.checked = false;
            });
        }
    });
});

});
</script>

    <style>
    <meta charset="UTF-8">
    <title>Unsolved Complaints</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    background: transparent !important; /* Ensure no override */
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