<?php
ob_start();
include("config/connection.php");
include("config/site_css_links.php");
include("config/data_tables_css.php");
include("config/header.php");
include("config/sidebar.php");


// Handle Add Maintenance
if (isset($_POST['add_maintenance'])) {
    $vehicle_id = $_POST['vehicle_id'];
    $maintenance_date = $_POST['maintenance_date'];
    $description = $_POST['description'];
    $cost = $_POST['cost'];
    $status = $_POST['status'];

    $stmt = $con->prepare("INSERT INTO maintenance (vehicle_id, maintenance_date, description, cost, status)
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$vehicle_id, $maintenance_date, $description, $cost, $status]);

    header("Location: maintenance.php");
    exit;
}

// Handle Edit Maintenance
if (isset($_POST['edit_maintenance'])) {
    $id = $_POST['edit_id'];
    $vehicle_id = $_POST['vehicle_id'];
    $maintenance_date = $_POST['maintenance_date'];
    $description = $_POST['description'];
    $cost = $_POST['cost'];
    $status = $_POST['status'];

    $stmt = $con->prepare("UPDATE maintenance 
                           SET vehicle_id=?, maintenance_date=?, description=?, cost=?, status=?
                           WHERE id=?");
    $stmt->execute([$vehicle_id, $maintenance_date, $description, $cost, $status, $id]);

    header("Location: maintenance.php");
    exit;
}

// Handle Delete Maintenance
if (isset($_POST['delete_maintenance'])) {
    $id = $_POST['delete_id'];
    $stmt = $con->prepare("DELETE FROM maintenance WHERE id=?");
    $stmt->execute([$id]);

    header("Location: maintenance.php");
    exit;
}

// Fetch Data
$stmt = $con->query("SELECT m.*, v.plate_number 
                     FROM maintenance m 
                     JOIN vehicles v ON m.vehicle_id = v.id
                     ORDER BY m.maintenance_date DESC");
$maintenance_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $con->query("SELECT * FROM vehicles ORDER BY plate_number ASC");
$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Maintenance Records</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .main-content {
            padding-top: 60px;
        }
        thead.table-dark th {
            background-color: #001f3f !important; /* navy blue */
            color: white;
        }
        .modal-header { background-color: #0d47a1; color: white; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">
<div class="content-wrapper">
  <div class="main-content">
     <div class="container-fluid">
      <section class="content-header">
    <h2 class="mb-4"> Maintenance Records</h2>

    <!-- Add Maintenance Button -->
    <div class="mb-3">
        <a href="fleet_dashboard.php" class="btn btn-primary"> Back to Dashboard</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMaintenanceModal">
            + Add Maintenance
        </button>
    </div>
</section>
    <!-- Maintenance Table -->
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped my-darkblue-table">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Vehicle</th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Cost</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($maintenance_logs as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['plate_number']) ?></td>
                            <td><?= htmlspecialchars($row['maintenance_date']) ?></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td>₱<?= number_format($row['cost'], 2) ?></td>
                            <td>
                                <span class="badge bg-<?= strtolower($row['status']) == 'completed' ? 'success' : 'warning'; ?>">
                                    <?= htmlspecialchars($row['status']) ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-primary btn-sm editBtn"
                                    data-id="<?= $row['id'] ?>"
                                    data-vehicle="<?= $row['vehicle_id'] ?>"
                                    data-date="<?= $row['maintenance_date'] ?>"
                                    data-description="<?= htmlspecialchars($row['description']) ?>"
                                    data-cost="<?= $row['cost'] ?>"
                                    data-status="<?= $row['status'] ?>">
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
    </div>
</div>

<!-- Add Maintenance Modal -->
<div class="modal fade" id="addMaintenanceModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Add Maintenance</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label>Vehicle</label>
          <select name="vehicle_id" class="form-control" required>
            <option value="">Select Vehicle</option>
            <?php foreach($vehicles as $v): ?>
              <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['plate_number']) ?></option>
            <?php endforeach; ?>
          </select>
          <label class="mt-2">Date</label>
          <input type="date" name="maintenance_date" class="form-control" required>
          <label class="mt-2">Description</label>
          <textarea name="description" class="form-control" required></textarea>
          <label class="mt-2">Cost</label>
          <input type="number" step="0.01" name="cost" class="form-control" required>
          <label class="mt-2">Status</label>
          <select name="status" class="form-control" required>
            <option value="Pending">Pending</option>
            <option value="Completed">Completed</option>
          </select>
        </div>
        <div class="modal-footer">
          <button type="submit" name="add_maintenance" class="btn btn-primary">Add</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Maintenance Modal -->
<div class="modal fade" id="editMaintenanceModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <input type="hidden" name="edit_id" id="edit_id">
        <div class="modal-header">
          <h5 class="modal-title">Edit Maintenance</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label>Vehicle</label>
          <select name="vehicle_id" id="edit_vehicle" class="form-control" required>
            <?php foreach($vehicles as $v): ?>
              <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['plate_number']) ?></option>
            <?php endforeach; ?>
          </select>
          <label class="mt-2">Date</label>
          <input type="date" name="maintenance_date" id="edit_date" class="form-control" required>
          <label class="mt-2">Description</label>
          <textarea name="description" id="edit_description" class="form-control" required></textarea>
          <label class="mt-2">Cost</label>
          <input type="number" step="0.01" name="cost" id="edit_cost" class="form-control" required>
          <label class="mt-2">Status</label>
          <select name="status" id="edit_status" class="form-control" required>
            <option value="Pending">Pending</option>
            <option value="Completed">Completed</option>
          </select>
        </div>
        <div class="modal-footer">
          <button type="submit" name="edit_maintenance" class="btn btn-primary">Save Changes</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteMaintenanceModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <form method="post">
        <input type="hidden" name="delete_id" id="delete_id">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">Confirm Delete</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center">
          <p>Are you sure you want to delete this maintenance record?</p>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="submit" name="delete_maintenance" class="btn btn-danger">Yes, Delete</button>
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
        document.getElementById('edit_vehicle').value = button.dataset.vehicle;
        document.getElementById('edit_date').value = button.dataset.date;
        document.getElementById('edit_description').value = button.dataset.description;
        document.getElementById('edit_cost').value = button.dataset.cost;
        document.getElementById('edit_status').value = button.dataset.status;
        new bootstrap.Modal(document.getElementById('editMaintenanceModal')).show();
    });
});

document.querySelectorAll('.deleteBtn').forEach(button => {
    button.addEventListener('click', () => {
        document.getElementById('delete_id').value = button.dataset.id;
        new bootstrap.Modal(document.getElementById('deleteMaintenanceModal')).show();
    });
});
</script>

</body>
</html>
<?php ob_end_flush(); ?>