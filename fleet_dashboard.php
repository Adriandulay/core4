<?php
ob_start();
include("config/connection.php");
include("config/site_css_links.php");
include("config/data_tables_css.php");
include("config/header.php");
include("config/sidebar.php");

// Count vehicles function
function countVehicles($con, $status = null) {
    if ($status === null) {
        $stmt = $con->query("SELECT COUNT(*) AS count FROM vehicles");
    } else {
        $stmt = $con->prepare("SELECT COUNT(*) AS count FROM vehicles WHERE LOWER(status) = LOWER(?)");
        $stmt->execute([$status]);
    }
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['count'] : 0;
}

// Handle update from modal
if (isset($_POST['edit_id'])) {
    $id = intval($_POST['edit_id']);
    $plate_number = trim($_POST['plate_number']);
    $type = trim($_POST['type']);
    $status = trim($_POST['status']);

    $stmt = $con->prepare("UPDATE vehicles SET plate_number = ?, type = ?, status = ? WHERE id = ?");
    $stmt->execute([$plate_number, $type, $status, $id]);

    header("Location: fleet_dashboard.php");
    exit;
}

// Handle delete from modal (vehicles)
if (isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $stmt = $con->prepare("DELETE FROM vehicles WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: fleet_dashboard.php");
    exit;
}

// ✅ Handle delete from modal (fuel logs) — ADDED
if (isset($_POST['fuel_delete_id'])) {
    $vehicle_id = intval($_POST['fuel_delete_id']);
    $stmt = $con->prepare("DELETE FROM fuel_logs WHERE vehicle_id = ?");
    $stmt->execute([$vehicle_id]);

    header("Location: fleet_dashboard.php");
    exit;
}

// Get counts
$total_vehicles = countVehicles($con);
$available = countVehicles($con, "active");
$in_use = countVehicles($con, "inactive");
$under_maintenance = countVehicles($con, "maintenance");

// Get list
$stmt = $con->query("SELECT * FROM vehicles ORDER BY id DESC");
$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fleet + Fuel Integration
$query = "
    SELECT 
        v.id, 
        v.plate_number, 
        v.type, 
        COALESCE(SUM(f.fuel_liters),0) AS total_liters, 
        COALESCE(SUM(f.cost),0) AS total_cost
    FROM vehicles v
    LEFT JOIN fuel_logs f ON v.id = f.vehicle_id
    GROUP BY v.id, v.plate_number, v.type
    ORDER BY v.id DESC
";
$stmt = $con->prepare($query);
$stmt->execute();
$fleetFuel = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fleet Availability Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .main-content { padding-top: 60px; padding-bottom: 60px; }
        .bg-darkblue { background-color: #1b263b !important; }
        .table-darkblue th { background-color: #1b263b !important; color: #ffffff;}
        .modal-header { background-color: #0d47a1; color: white; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="content-wrapper">
<div class="main-content">
<div class="container-fluid">
<section class="content-header">
    <h2 class="mb-4">Fleet Availability Dashboard</h2>
</section>

<div class="d-flex justify-content-end mb-3">
    <a href="vehicle_list.php" class="btn btn-primary me-2">Go to Vehicle List</a>
    <a href="maintenance.php" class="btn btn-primary me-2">Go to Maintenance</a>
</div>

<!-- Dashboard Counters -->
<div class="row text-center mb-4">
    <div class="col-md-3 mb-2">
        <div class="card shadow-sm"><div class="card-body"><h6>Total Vehicles</h6><h3><?= $total_vehicles ?></h3></div></div>
    </div>
    <div class="col-md-3 mb-2">
        <div class="card shadow-sm"><div class="card-body"><h6>Available</h6><h3 class="text-success"><?= $available ?></h3></div></div>
    </div>
    <div class="col-md-3 mb-2">
        <div class="card shadow-sm"><div class="card-body"><h6>In Use</h6><h3 class="text-warning"><?= $in_use ?></h3></div></div>
    </div>
    <div class="col-md-3 mb-2">
        <div class="card shadow-sm"><div class="card-body"><h6>Under Maintenance</h6><h3 class="text-danger"><?= $under_maintenance ?></h3></div></div>
    </div>
</div>

<!-- Vehicle Table -->
<div class="card mb-4">
    <div class="card-body">
        <h5 class="mb-3">Vehicle List</h5>
        <div class="table-responsive">
            <table id="vehicleTable" class="table table-bordered table-hover table-striped mb-0 align-middle">
                <thead class="table-darkblue">
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
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['plate_number']) ?></td>
                        <td><?= htmlspecialchars($row['type']) ?></td>
                        <td>
                            <?php
                                $status_class = 'secondary';
                                if (strtolower($row['status']) == 'active') $status_class = 'success';
                                elseif (strtolower($row['status']) == 'inactive') $status_class = 'warning';
                                elseif (strtolower($row['status']) == 'maintenance') $status_class = 'danger';
                            ?>
                            <span class="badge bg-<?= $status_class ?>"><?= ucfirst($row['status']) ?></span>
                        </td>
                        <td>
                            <button class="btn btn-primary btn-sm editBtn"
                                data-id="<?= $row['id'] ?>"
                                data-plate="<?= htmlspecialchars($row['plate_number'], ENT_QUOTES) ?>"
                                data-type="<?= htmlspecialchars($row['type'], ENT_QUOTES) ?>"
                                data-status="<?= $row['status'] ?>"
                                data-bs-toggle="modal" data-bs-target="#editModal">
                                Edit
                            </button>
                            <button class="btn btn-danger btn-sm deleteBtn"
                                data-id="<?= $row['id'] ?>"
                                data-plate="<?= htmlspecialchars($row['plate_number'], ENT_QUOTES) ?>"
                                data-bs-toggle="modal" data-bs-target="#deleteModal">
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

<!-- Fuel Usage Table -->
<div class="card">
    <div class="card-body">
        <h5 class="mb-3">Fuel Usage per Vehicle</h5>
        <div class="table-responsive">
            <table id="fuelTable" class="table table-bordered table-striped mb-0 align-middle">
                <thead class="table-darkblue">
                    <tr>
                        <th>Plate No.</th>
                        <th>Type</th>
                        <th>Total Fuel Liters</th>
                        <th>Total Fuel Cost (₱)</th>
                        <th>Action</th> <!-- ADDED -->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($fleetFuel as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['plate_number']) ?></td>
                        <td><?= htmlspecialchars($row['type']) ?></td>
                        <td><?= number_format($row['total_liters'], 2) ?></td>
                        <td>₱<?= number_format($row['total_cost'], 2) ?></td>
                        <td>
                            <!-- Fuel Usage Delete button — ADDED -->
                            <button class="btn btn-danger btn-sm fuelDeleteBtn"
                                data-id="<?= $row['id'] ?>"
                                data-plate="<?= htmlspecialchars($row['plate_number'], ENT_QUOTES) ?>"
                                data-bs-toggle="modal" data-bs-target="#fuelDeleteModal">
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
</div>
</div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Edit Vehicle</h5></div>
      <div class="modal-body">
        <input type="hidden" name="edit_id" id="edit_id">
        <div class="mb-3"><label>Plate Number</label><input type="text" name="plate_number" id="edit_plate" class="form-control" required></div>
        <div class="mb-3"><label>Type</label><input type="text" name="type" id="edit_type" class="form-control" required></div>
        <div class="mb-3"><label>Status</label>
          <select name="status" id="edit_status" class="form-control">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="maintenance">Maintenance</option>
          </select>
        </div>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
    </form>
  </div>
</div>

<!-- Delete Modal (Vehicles) -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header bg-danger text-white"><h5 class="modal-title">Delete Vehicle</h5></div>
      <div class="modal-body">
        <input type="hidden" name="delete_id" id="delete_id">
        <p>Are you sure you want to delete <strong id="delete_plate"></strong>?</p>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-danger">Delete</button></div>
    </form>
  </div>
</div>

<!-- Fuel Delete Modal — ADDED -->
<div class="modal fade" id="fuelDeleteModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header bg-danger text-white"><h5 class="modal-title">Delete Fuel Usage</h5></div>
      <div class="modal-body">
        <input type="hidden" name="fuel_delete_id" id="fuel_delete_id">
        <p>Delete <strong>all fuel logs</strong> for <strong id="fuel_delete_plate"></strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger">Delete</button>
      </div>
    </form>
  </div>
</div>

<?php
include("config/footer.php");
include("config/site_js_links.php");
include("config/data_tables_js.php");
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll(".editBtn").forEach(btn => {
    btn.addEventListener("click", function() {
        document.getElementById("edit_id").value = this.dataset.id;
        document.getElementById("edit_plate").value = this.dataset.plate;
        document.getElementById("edit_type").value = this.dataset.type;
        document.getElementById("edit_status").value = this.dataset.status;
    });
});
document.querySelectorAll(".deleteBtn").forEach(btn => {
    btn.addEventListener("click", function() {
        document.getElementById("delete_id").value = this.dataset.id;
        document.getElementById("delete_plate").innerText = this.dataset.plate;
    });
});

// ✅ Fuel delete wiring — ADDED
document.querySelectorAll(".fuelDeleteBtn").forEach(btn => {
    btn.addEventListener("click", function() {
        document.getElementById("fuel_delete_id").value = this.dataset.id; // vehicle_id
        document.getElementById("fuel_delete_plate").innerText = this.dataset.plate;
    });
});

$(document).ready(function () {
    // Enable entries + pagination on Vehicle Table
    $('#vehicleTable').DataTable({
        "pageLength": 5,
        "lengthMenu": [5, 10, 25, 50, 100]
    });

    // Enable entries + pagination on Fuel Table
    $('#fuelTable').DataTable({
        "pageLength": 5,
        "lengthMenu": [5, 10, 25, 50, 100]
    });
});
</script>
<?php ob_end_flush(); ?>
</body>
</html>