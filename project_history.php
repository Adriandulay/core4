<?php
ob_start();
session_start();
include("config/connection.php"); // <-- make sure this gives $con as PDO
include("config/site_css_links.php");
include("config/data_tables_css.php");
include("config/header.php");
include("config/sidebar.php");

// ---------------- Handle Edit ----------------
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $name = $_POST['project_name'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $status = $_POST['status'];
    $progress = $_POST['progress'];

    $stmt = $con->prepare("UPDATE projects 
                           SET project_name=?, start_date=?, end_date=?, status=?, progress=? 
                           WHERE id=?");
    $stmt->execute([$name, $start, $end, $status, $progress, $id]);

    header("Location: project_history.php");
    exit();
}

// ---------------- Handle Delete ----------------
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $stmt = $con->prepare("DELETE FROM projects WHERE id=?");
    $stmt->execute([$id]);

    header("Location: project_history.php");
    exit();
}

// ---------------- Fetch Completed/Cancelled Projects ----------------
$stmt = $con->prepare("SELECT * FROM projects 
                       WHERE status IN ('Completed','Cancelled') 
                       ORDER BY end_date DESC");
$stmt->execute();
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Project History</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<style>
  .main-content {    
        padding-top: 70px;
        
    }
  table.dataTable thead th {
    background-color: #001f3f;
    color: white;
  }
</style>
  </head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="content-wrapper">
  <div class="main-content">
<div class="container-fluid">
    <section class="content-header">
    <h1 class="mb-3">📂 Project History</h1>
    <a href="project_monitoring.php" class="btn btn-primary mb-3">Project Monitoring</a>
    <a href="active_projects.php" class="btn btn-success mb-3">Active Projects</a>
    </section>
    <div class="card">
        <div class="card-body">
    <table id="historyTable" class="display nowrap table table-bordered table-striped" style="width:100%">
        <thead style="background-color:#001f3f; color:white;">
            <tr>
                <th>ID</th>
                <th>Project Name</th>
                <th>Start</th>
                <th>End</th>
                <th>Status</th>
                <th>Progress</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($projects as $p) { ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['project_name']) ?></td>
                <td><?= $p['start_date'] ?></td>
                <td><?= $p['end_date'] ?></td>
                <td><?= $p['status'] ?></td>
                <td><?= $p['progress'] ?>%</td>
                <td>
                    <!-- Edit Button -->
                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $p['id'] ?>">Edit</button>
                    <!-- Delete Button -->
                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $p['id'] ?>">Delete</button>
                </td>
            </tr>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal<?= $p['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <form method="post">
                        <div class="modal-content">
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title">Edit Project</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <div class="mb-2">
                                    <label>Project Name</label>
                                    <input type="text" name="project_name" value="<?= htmlspecialchars($p['project_name']) ?>" class="form-control" required>
                                </div>
                                <div class="mb-2">
                                    <label>Start Date</label>
                                    <input type="date" name="start_date" value="<?= $p['start_date'] ?>" class="form-control" required>
                                </div>
                                <div class="mb-2">
                                    <label>End Date</label>
                                    <input type="date" name="end_date" value="<?= $p['end_date'] ?>" class="form-control" required>
                                </div>
                                <div class="mb-2">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option <?= $p['status']=="Completed"?"selected":"" ?>>Completed</option>
                                        <option <?= $p['status']=="Cancelled"?"selected":"" ?>>Cancelled</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label>Progress (%)</label>
                                    <input type="number" name="progress" value="<?= $p['progress'] ?>" class="form-control" min="0" max="100">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="edit" class="btn btn-warning">Update</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delete Modal -->
            <div class="modal fade" id="deleteModal<?= $p['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <form method="post">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title">Delete Project</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                Are you sure you want to delete project <b><?= htmlspecialchars($p['project_name']) ?></b>?
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="delete" class="btn btn-danger">Delete</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php } ?>
        </tbody>
    </table>
            </section>
</div>

<?php
include("config/footer.php");
include("config/site_js_links.php");
include("config/data_tables_js.php");
?>
<script>
$(document).ready(function () {
    $('#historyTable').DataTable({
        responsive: true,
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
    });
});
</script>
</body>
</html>
<?php ob_end_flush(); ?>