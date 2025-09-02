<?php
ob_start();
include("config/connection.php"); // <-- make sure this sets $con as PDO
include("config/site_css_links.php");
include("config/data_tables_css.php");
include("config/header.php");
include("config/sidebar.php");
$message = "";

// --- ADD EQUIPMENT ---
if (isset($_POST['add_equipment'])) {
    $stmt = $con->prepare("INSERT INTO equipment (equipment_name,type,capacity,status) VALUES (?,?,?,?)");
    $stmt->execute([$_POST['equipment_name'], $_POST['type'], $_POST['capacity'], $_POST['status']]);
    header("Location: equipment.php");
    exit;
}

// --- EDIT EQUIPMENT ---
if (isset($_POST['edit_equipment'])) {
    $stmt = $con->prepare("UPDATE equipment SET equipment_name=?, type=?, capacity=?, status=? WHERE id=?");
    $stmt->execute([$_POST['equipment_name'], $_POST['type'], $_POST['capacity'], $_POST['status'], $_POST['id']]);
    header("Location: equipment.php");
    exit;
}

// --- DELETE EQUIPMENT ---
if (isset($_POST['delete_equipment'])) {
    $stmt = $con->prepare("DELETE FROM equipment WHERE id=?");
    $stmt->execute([$_POST['id']]);
    header("Location: equipment.php");
    exit;
}

// --- FETCH EQUIPMENT LIST ---
$stmt = $con->query("SELECT * FROM equipment ORDER BY id DESC");
$equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Equipment Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .main-content { padding-top: 80px; }
        .table thead th { background-color: #001f3f !important; color: white; }
        .modal-header { background-color: #0d47a1; color: white; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <div class="content-wrapper">
        <div class="main-content">
<div class="container-fluid">
    <h3 class="mb-4">Equipment Management</h3>

    <!-- Add Button -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">+ Add Equipment</button>

    <!-- Equipment Table -->
     <div class="card">
        <div class="card-body">
    <table class="table table-bordered table-striped" id="equipmentTable">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Equipment Name</th>
                <th>Type</th>
                <th>Capacity</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($equipment as $row): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['equipment_name'] ?></td>
                <td><?= $row['type'] ?></td>
                <td><?= $row['capacity'] ?></td>
                <td><?= $row['status'] ?></td>
                <td>
                    <button class="btn btn-sm btn-warning editBtn"
                        data-id="<?= $row['id'] ?>"
                        data-name="<?= $row['equipment_name'] ?>"
                        data-type="<?= $row['type'] ?>"
                        data-capacity="<?= $row['capacity'] ?>"
                        data-status="<?= $row['status'] ?>"
                        data-bs-toggle="modal" data-bs-target="#editModal">Edit</button>

                    <button class="btn btn-sm btn-danger deleteBtn"
                        data-id="<?= $row['id'] ?>"
                        data-name="<?= $row['equipment_name'] ?>"
                        data-bs-toggle="modal" data-bs-target="#deleteModal">Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Add Equipment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
            <div class="mb-2">
                <label>Equipment Name</label>
                <input type="text" class="form-control" name="equipment_name" required>
            </div>
            <div class="mb-2">
                <label>Type</label>
                <input type="text" class="form-control" name="type" required>
            </div>
            <div class="mb-2">
                <label>Capacity</label>
                <input type="text" class="form-control" name="capacity">
            </div>
            <div class="mb-2">
                <label>Status</label>
                <select class="form-select" name="status">
                    <option>Available</option>
                    <option>In Use</option>
                    <option>Under Maintenance</option>
                    <option>Retired</option>
                </select>
            </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" name="add_equipment">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title">Edit Equipment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
          <input type="hidden" name="id" id="edit_id">
          <div class="mb-2">
              <label>Equipment Name</label>
              <input type="text" class="form-control" name="equipment_name" id="edit_name" required>
          </div>
          <div class="mb-2">
              <label>Type</label>
              <input type="text" class="form-control" name="type" id="edit_type" required>
          </div>
          <div class="mb-2">
              <label>Capacity</label>
              <input type="text" class="form-control" name="capacity" id="edit_capacity">
          </div>
          <div class="mb-2">
              <label>Status</label>
              <select class="form-select" name="status" id="edit_status">
                  <option>Available</option>
                  <option>In Use</option>
                  <option>Under Maintenance</option>
                  <option>Retired</option>
              </select>
          </div>
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning" name="edit_equipment">Update</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Confirm Delete</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
          Are you sure you want to delete <b id="delete_name"></b>?
          <input type="hidden" name="id" id="delete_id">
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger" name="delete_equipment">Delete</button>
      </div>
    </form>
  </div>
</div>

<?php
include("config/footer.php");
include("config/site_js_links.php");
include("config/data_tables_js.php");
?>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function(){
    $('#equipmentTable').DataTable();

    // Populate Edit Modal
    $('.editBtn').click(function(){
        $('#edit_id').val($(this).data('id'));
        $('#edit_name').val($(this).data('name'));
        $('#edit_type').val($(this).data('type'));
        $('#edit_capacity').val($(this).data('capacity'));
        $('#edit_status').val($(this).data('status'));
    });

    // Populate Delete Modal
    $('.deleteBtn').click(function(){
        $('#delete_id').val($(this).data('id'));
        $('#delete_name').text($(this).data('name'));
    });
});
</script>
</body>
</html>
<?php ob_end_flush(); ?>