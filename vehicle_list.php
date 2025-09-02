<?php
ob_start();
include("config/connection.php");
include("config/site_css_links.php");
include("config/data_tables_css.php");
include("config/header.php");
include("config/sidebar.php");


// ADD VEHICLE
if (isset($_POST['add_vehicle'])) {
    $plate = trim($_POST['plate_number']);
    $type = trim($_POST['type']);
    $status = trim($_POST['status']);

    $allowed_status = ["Active", "Inactive", "Maintenance"];
    if (!in_array($status, $allowed_status)) {
        $status = "Inactive";
    }

    $stmt = $con->prepare("INSERT INTO vehicles (plate_number, type, status) VALUES (?, ?, ?)");
    $stmt->execute([$plate, $type, $status]);

    header("Location: vehicle_list.php");
    exit;
}

// EDIT VEHICLE
if (isset($_POST['edit_vehicle'])) {
    $id = intval($_POST['edit_vehicle_id']);
    $plate = trim($_POST['plate_number']);
    $type = trim($_POST['type']);
    $status = trim($_POST['status']);

    $allowed_status = ["Active", "Inactive", "Maintenance"];
    if (!in_array($status, $allowed_status)) {
        $status = "Inactive";
    }

    $stmt = $con->prepare("UPDATE vehicles SET plate_number=?, type=?, status=? WHERE id=?");
    $stmt->execute([$plate, $type, $status, $id]);

    header("Location: vehicle_list.php");
    exit;
}

// DELETE VEHICLE
if (isset($_POST['delete_vehicle'])) {
    $id = intval($_POST['delete_vehicle_id']);
    $stmt = $con->prepare("DELETE FROM vehicles WHERE id=?");
    $stmt->execute([$id]);

    header("Location: vehicle_list.php");
    exit;
}

// FETCH VEHICLES
$stmt = $con->query("SELECT * FROM vehicles ORDER BY id DESC");
$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Vehicle List</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .main-content {
            padding-top: 60px;
          
        }
        thead.table-dark th {
            background-color: #001f3f !important; /* Navy blue header */
        }
        .modal-header { background-color: #0d47a1; color: white; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">
     <div class="content-wrapper">
      <section class="main-content">
           <div class="container-fluid">
            <section class="content-header">
               <h2> Vehicle List</h2>


          <!-- Add Vehicle Button -->
        <div class="mb-3">
            <a href="fleet_dashboard.php" class="btn btn-primary"> Back to Dashboard</a>
           <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">+ Add Vehicle</button>
          </div>
</section>
        <!-- Vehicle Table -->
        <div class="card">
            <div class="card-body">
              <table class="table table-bordered table-striped mb-0">
                 <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Plate No.</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($vehicles as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['plate_number']) ?></td>
                            <td><?= htmlspecialchars($row['type']) ?></td>
                            <td>
                                <?php
                                    $status_class = 'secondary';
                                    if (strtolower($row['status']) == 'active') $status_class = 'success';
                                    elseif (strtolower($row['status']) == 'inactive') $status_class = 'warning';
                                    elseif (strtolower($row['status']) == 'maintenance') $status_class = 'danger';
                                ?>
                                <span class="badge bg-<?= $status_class ?>">
                                    <?= ucfirst(htmlspecialchars($row['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-primary btn-sm editBtn"
                                    data-id="<?= $row['id'] ?>"
                                    data-plate="<?= htmlspecialchars($row['plate_number']) ?>"
                                    data-type="<?= htmlspecialchars($row['type']) ?>"
                                    data-status="<?= htmlspecialchars($row['status']) ?>">
                                    Edit
                                </button>
                                <button class="btn btn-danger btn-sm deleteBtn"
                                    data-id="<?= $row['id'] ?>">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
      </section>
    </div>
</div>

<!-- ADD VEHICLE MODAL -->
<div class="modal fade" id="addVehicleModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h5 class="modal-title">Add Vehicle</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Plate Number</label>
            <input type="text" name="plate_number" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Type</label>
            <input type="text" name="type" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-select" required>
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
              <option value="Maintenance">Maintenance</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="add_vehicle" class="btn btn-primary">Add</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- EDIT VEHICLE MODAL -->
<div class="modal fade" id="editVehicleModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <input type="hidden" name="edit_vehicle_id" id="edit_id">
        <div class="modal-header">
          <h5 class="modal-title">Edit Vehicle</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Plate Number</label>
            <input type="text" name="plate_number" id="edit_plate" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Type</label>
            <input type="text" name="type" id="edit_type" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Status</label>
            <select name="status" id="edit_status" class="form-select" required>
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
              <option value="Maintenance">Maintenance</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="edit_vehicle" class="btn btn-primary">Update</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- DELETE CONFIRMATION MODAL -->
<div class="modal fade" id="deleteVehicleModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <form method="post">
        <input type="hidden" name="delete_vehicle_id" id="delete_id">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">Confirm Delete</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center">
          <p class="mb-0">Are you sure you want to delete this vehicle?</p>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="submit" name="delete_vehicle" class="btn btn-danger">Yes, Delete</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    $('.table').DataTable({
        "lengthMenu": [5, 10, 25, 50, 100], // number of entries options
        "pageLength": 10, // default entries per page
        "order": [[0, "desc"]], // sort by ID descending
        "columnDefs": [
            { "orderable": false, "targets": 4 } // Action column not sortable
        ]
    });
});


document.querySelectorAll('.editBtn').forEach(button => {
    button.addEventListener('click', () => {
        document.getElementById('edit_id').value = button.dataset.id;
        document.getElementById('edit_plate').value = button.dataset.plate;
        document.getElementById('edit_type').value = button.dataset.type;
        document.getElementById('edit_status').value = button.dataset.status;
        new bootstrap.Modal(document.getElementById('editVehicleModal')).show();
    });
});

document.querySelectorAll('.deleteBtn').forEach(button => {
    button.addEventListener('click', () => {
        document.getElementById('delete_id').value = button.dataset.id;
        new bootstrap.Modal(document.getElementById('deleteVehicleModal')).show();
    });
});
</script>

</body>
</html>
<?php ob_end_flush(); ?>