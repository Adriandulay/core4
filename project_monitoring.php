<?php
ob_start();
session_start();
include("config/connection.php"); // <-- make sure this gives $con as PDO
include("config/site_css_links.php");
include("config/data_tables_css.php");
include("config/header.php");
include("config/sidebar.php");

// ---------------- Handle Add Project ----------------
if (isset($_POST['add'])) {
    $name = $_POST['project_name'] ?? '';
    $start = $_POST['start_date'] ?? '';
    $end = $_POST['end_date'] ?? '';
    $status = $_POST['status'] ?? 'Active';
    $progress = $_POST['progress'] ?? 0;

    if (!empty($name)) {
        $stmt = $con->prepare("INSERT INTO projects (project_name, start_date, end_date, status, progress) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $start, $end, $status, $progress]);
    }
    header("Location: project_monitoring.php");
    exit();
}

// ---------------- Handle Edit Project ----------------
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

    header("Location: project_monitoring.php");
    exit();
}

// ---------------- Handle Delete Project ----------------
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $stmt = $con->prepare("DELETE FROM projects WHERE id=?");
    $stmt->execute([$id]);

    header("Location: project_monitoring.php");
    exit();
}

// ---------------- Fetch All Projects (ALWAYS visible) ----------------
$stmt = $con->prepare("SELECT * FROM projects ORDER BY start_date DESC");
$stmt->execute();
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="content-wrapper">
    <section class="content-header">
        <h1 class="text-dark">Project Monitoring</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProjectModal">Add Project</button>
        <a href="active_projects.php" class="btn btn-primary">View Active Projects</a>
    </section>
<style>
    .modal-header { background-color: #0d47a1; color: white; }
</style>
    <section class="content">
        <div class="card">
            <div class="card-body">
                <table id="projectsTable" class="table table-bordered table-striped">
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
                        <?php foreach ($projects as $p) { ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <td><?= htmlspecialchars($p['project_name']) ?></td>
                                <td><?= $p['start_date'] ?></td>
                                <td><?= $p['end_date'] ?></td>
                                <td><?= $p['status'] ?></td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" role="progressbar"
                                             style="width: <?= $p['progress'] ?>%">
                                             <?= $p['progress'] ?>%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <!-- Edit Button -->
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" 
                                            data-bs-target="#editProjectModal<?= $p['id'] ?>">Edit</button>
                                    <!-- Delete Button -->
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" 
                                            data-bs-target="#deleteProjectModal<?= $p['id'] ?>">Delete</button>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editProjectModal<?= $p['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <div class="modal-header">
                                                <h5>Edit Project</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                                <div class="form-group">
                                                    <label>Project Name</label>
                                                    <input type="text" name="project_name" class="form-control" value="<?= htmlspecialchars($p['project_name']) ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Start Date</label>
                                                    <input type="date" name="start_date" class="form-control" value="<?= $p['start_date'] ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>End Date</label>
                                                    <input type="date" name="end_date" class="form-control" value="<?= $p['end_date'] ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Status</label>
                                                    <select name="status" class="form-control">
                                                        <option <?= $p['status']=="Active"?"selected":"" ?>>Active</option>
                                                        <option <?= $p['status']=="Completed"?"selected":"" ?>>Completed</option>
                                                        <option <?= $p['status']=="On-Hold"?"selected":"" ?>>On-Hold</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Progress (%)</label>
                                                    <input type="number" name="progress" class="form-control" min="0" max="100" value="<?= $p['progress'] ?>">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" name="update" class="btn btn-warning">Update</button>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Modal -->
                            <div class="modal fade" id="deleteProjectModal<?= $p['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <div class="modal-header bg-danger text-white">
                                                <h5>Delete Project</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to delete <b><?= htmlspecialchars($p['project_name']) ?></b>?
                                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" name="delete" class="btn btn-danger">Delete</button>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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

<!-- Add Project Modal -->
<div class="modal fade" id="addProjectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Add Project</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Project Name</label>
                <input type="text" name="project_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_date" class="form-control">
            </div>
            <div class="form-group">
                <label>End Date</label>
                <input type="date" name="end_date" class="form-control">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="Active">Active</option>
                    <option value="Completed">Completed</option>
                    <option value="On-Hold">On-Hold</option>
                </select>
            </div>
            <div class="form-group">
                <label>Progress (%)</label>
                <input type="number" name="progress" class="form-control" min="0" max="100" value="0">
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="add" class="btn btn-primary">Save</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
include("config/footer.php");
include("config/site_js_links.php");
include("config/data_tables_js.php");
?>

<script>
$(function () {
    $("#projectsTable").DataTable({
        "responsive": true,
        "autoWidth": false
    });
});
</script>
<?php ob_end_flush(); ?>