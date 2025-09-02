<?php
ob_start();
session_start();
include("config/connection.php"); 
include("config/site_css_links.php");
include("config/data_tables_css.php");
include("config/header.php");
include("config/sidebar.php");

// Handle Delete
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $stmt = $con->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([$delete_id]);
    header("Location: active_projects.php");
    exit;
}

// Handle Edit / Update
if (isset($_POST['update'])) {
    $id = $_POST['project_id'];
    $name = $_POST['project_name'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $progress = $_POST['progress'];
    $status = $_POST['status'];

    $stmt = $con->prepare("UPDATE projects 
                           SET project_name=?, start_date=?, end_date=?, progress=?, status=? 
                           WHERE id=?");
    $stmt->execute([$name, $start, $end, $progress, $status, $id]);

    header("Location: active_projects.php");
    exit;
}

// Fetch Active Projects
$stmt = $con->prepare("SELECT id, project_name, start_date, end_date, status, progress 
                       FROM projects 
                       WHERE status = 'Active'
                       ORDER BY start_date DESC");
$stmt->execute();
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="content-wrapper">
    <section class="content-header">
        <h1 class="text-dark">Active Projects</h1>
    </section>
<style>
.modal-header { background-color: #0d47a1; color: white; }
</style>
    <section class="content">
        <div class="card">
            <div class="card-body">
                <table id="activeProjectsTable" class="table table-bordered table-striped">
                    <thead style="background-color:#001f3f; color:white;">
                        <tr>
                            <th>ID</th>
                            <th>Project Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $row) { ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td><?= htmlspecialchars($row['project_name']) ?></td>
                                <td><?= htmlspecialchars($row['start_date']) ?></td>
                                <td><?= htmlspecialchars($row['end_date']) ?></td>
                                <td><span class="badge bg-success"><?= htmlspecialchars($row['status']) ?></span></td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" role="progressbar" 
                                             style="width: <?= $row['progress'] ?>%" 
                                             aria-valuenow="<?= $row['progress'] ?>" 
                                             aria-valuemin="0" aria-valuemax="100">
                                            <?= $row['progress'] ?>%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <!-- Edit Button -->
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>

                                    <!-- Delete Form -->
                                    <form method="POST" action="" style="display:inline;">
                                        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this project?')">Delete</button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
                              <div class="modal-dialog">
                                <div class="modal-content">
                                  <form method="POST">
                                    <div class="modal-header">
                                      <h5 class="modal-title">Edit Project</h5>
                                      <button type="button" class="close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                      <input type="hidden" name="project_id" value="<?= $row['id'] ?>">
                                      <div class="form-group">
                                        <label>Project Name</label>
                                        <input type="text" name="project_name" class="form-control" value="<?= htmlspecialchars($row['project_name']) ?>" required>
                                      </div>
                                      <div class="form-group">
                                        <label>Start Date</label>
                                        <input type="date" name="start_date" class="form-control" value="<?= $row['start_date'] ?>" required>
                                      </div>
                                      <div class="form-group">
                                        <label>End Date</label>
                                        <input type="date" name="end_date" class="form-control" value="<?= $row['end_date'] ?>" required>
                                      </div>
                                      <div class="form-group">
                                        <label>Progress (%)</label>
                                        <input type="number" name="progress" class="form-control" min="0" max="100" value="<?= $row['progress'] ?>" required>
                                      </div>
                                      <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" class="form-control">
                                          <option value="Active" <?= $row['status']=="Active"?"selected":"" ?>>Active</option>
                                          <option value="Completed" <?= $row['status']=="Completed"?"selected":"" ?>>Completed</option>
                                          <option value="On-Hold" <?= $row['status']=="On-Hold"?"selected":"" ?>>On-Hold</option>
                                        </select>
                                      </div>
                                    </div>
                                    <div class="modal-footer">
                                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                      <button type="submit" name="update" class="btn btn-primary">Save Changes</button>
                                    </div>
                                  </form>
                                </div>
                              </div>
                            </div>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<?php
include("config/footer.php");
include("config/site_js_links.php");
include("config/data_tables_js.php");
?>

<script>
$(function () {
    $("#activeProjectsTable").DataTable({
        "responsive": true,
        "autoWidth": false
    });
});
</script>
<?php ob_end_flush(); ?>