<?php
session_start();
include("config/connection.php"); // Source DB
include("config/site_css_links.php");
include("config/data_tables_css.php");
include("config/header.php");
include("config/sidebar.php");

$message = "";

// --- Share Project (mark as shared) ---
if(isset($_POST['share_project'])) {
    $project_id = $_POST['project_id'];
    $update = $con->prepare("UPDATE projects SET shared = 1 WHERE id = :id");
    $update->execute([':id' => $project_id]);
    $_SESSION['message'] = "✅ Project marked as Already Shared!";
}

// --- Edit Project ---
if(isset($_POST['edit_project'])) {
    $id = $_POST['project_id'];
    $name = $_POST['project_name'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $status = $_POST['status'];

    $update = $con->prepare("UPDATE projects SET project_name=:name, start_date=:start, end_date=:end, status=:status WHERE id=:id");
    $update->execute([
        ':name' => $name,
        ':start' => $start,
        ':end' => $end,
        ':status' => $status,
        ':id' => $id
    ]);
    $_SESSION['message'] = "✅ Project '{$name}' updated successfully!";
}

// --- Delete Project ---
if(isset($_POST['delete_project'])) {
    $id = $_POST['project_id'];
    $delete = $con->prepare("DELETE FROM projects WHERE id=:id");
    $delete->execute([':id' => $id]);
    $_SESSION['message'] = "✅ Project deleted successfully!";
}

// Fetch all projects
$projects = $con->query("SELECT * FROM projects")->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
    .main-content {
        padding-top: 50px;
    }
    .table thead th {
        background-color: #001f3f !important; /* navy blue */
            color: white;
    }
    .modal-header { background-color: #0d47a1; color: white;
    
    }
    </style>
<div class="content-wrapper">
  <div class="main-content container-fluid">
    <section class="content-header">
    <h2>Project Data</h2>
    
</section>
<?php if($message): ?>
        <div id="alertMessage" class="alert alert-info"><?= $_SESSION['message'] ?>
</div>
    <?php endif; ?>
    <div class="card">
      <div class="card-body">

        <table id="projectsTable" class="table table-bordered">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Project Name</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($projects as $p): ?>
            <tr>
              <td><?= $p['id'] ?></td>
              <td><?= $p['project_name'] ?></td>
              <td><?= $p['start_date'] ?></td>
              <td><?= $p['end_date'] ?></td>
              <td><?= $p['status'] ?></td>
              <td>
                <!-- Share -->
                <?php if(isset($p['shared']) && $p['shared'] == 1): ?>
                    <button class="btn btn-secondary btn-sm" disabled>Already Shared</button>
                <?php else: ?>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="project_id" value="<?= $p['id'] ?>">
                        <button type="submit" name="share_project" class="btn btn-success btn-sm" onclick="return confirm('Mark this project as shared?')">Share</button>
                    </form>
                <?php endif; ?>

                <!-- Edit -->
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $p['id'] ?>">Edit</button>

                <!-- Delete -->
                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $p['id'] ?>">Delete</button>
              </td>
            </tr>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal<?= $p['id'] ?>" tabindex="-1">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="post">
                    <div class="modal-header">
                      <h5 class="modal-title">Edit Project</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <input type="hidden" name="project_id" value="<?= $p['id'] ?>">
                      <div class="mb-3">
                        <label>Project Name</label>
                        <input type="text" name="project_name" class="form-control" value="<?= $p['project_name'] ?>" required>
                      </div>
                      <div class="mb-3">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?= $p['start_date'] ?>" required>
                      </div>
                      <div class="mb-3">
                        <label>End Date</label>
                        <input type="date" name="end_date" class="form-control" value="<?= $p['end_date'] ?>" required>
                      </div>
                      <div class="mb-3">
                        <label>Status</label>
                        <select name="status" class="form-control">
                          <option value="Planned" <?= $p['status']=='Planned'?'selected':'' ?>>Planned</option>
                          <option value="Ongoing" <?= $p['status']=='Ongoing'?'selected':'' ?>>Ongoing</option>
                          <option value="Completed" <?= $p['status']=='Completed'?'selected':'' ?>>Completed</option>
                        </select>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" name="edit_project" class="btn btn-primary">Save Changes</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- Delete Modal -->
            <div class="modal fade" id="deleteModal<?= $p['id'] ?>" tabindex="-1">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="post">
                    <div class="modal-header">
                      <h5 class="modal-title">Delete Project</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <p>Are you sure you want to delete project <strong><?= $p['project_name'] ?></strong>?</p>
                      <input type="hidden" name="project_id" value="<?= $p['id'] ?>">
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" name="delete_project" class="btn btn-danger">Delete</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php
include("config/footer.php");
include("config/site_js_links.php");
include("config/data_tables_js.php");
?>

<script>
$(document).ready(function() {
    $('#projectsTable').DataTable({
        "lengthMenu": [5, 10, 25, 50, 100],
        "pageLength": 10,
        "order": [[0, "desc"]]
    });

setTimeout(function() {
    ('#alertMessage').fadeOut('slow', 
    function() {
        $(this).remove();
    });
    }, 3000);
});
</script>