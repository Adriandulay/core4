<?php
include("config/connection.php");
include("config/site_css_links.php");
include("config/data_tables_css.php");
include("config/header.php");
include("config/sidebar.php");

// --- DELETE FUEL RECORD ---
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $stmt = $con->prepare("DELETE FROM fuel_records WHERE id = ?");
    $stmt->execute([$delete_id]);
}

// --- CREATE FUEL RECORD ---
if (isset($_POST['submit'])) {
    $vehicle_id  = $_POST['vehicle_id'];
    $fuel_date   = $_POST['fuel_date'];
    $fuel_type   = $_POST['fuel_type'];
    $fuel_liters = $_POST['fuel_liters'];
    $fuel_cost   = $_POST['fuel_cost'];

    $stmt = $con->prepare("INSERT INTO fuel_records (vehicle_id, fuel_date, fuel_type, fuel_liters, fuel_cost) 
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$vehicle_id, $fuel_date, $fuel_type, $fuel_liters, $fuel_cost]);
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Fuel Records</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style> 
    .table-darkblue th {
            background-color: #1b263b !important; 
            color: white;
            
    }
    </style>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
<div class="content-wrapper">
<div class="content">
<div class="container-fluid">
  <h2 class="mb-3">Add Fuel Record</h2>
  <form method="POST" class="row g-3 mb-4">
    <div class="col-md-4">
      <label class="form-label">Vehicle ID</label>
      <input type="text" name="vehicle_id" class="form-control" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Fuel Date</label>
      <input type="date" name="fuel_date" class="form-control" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Fuel Type</label>
      <input type="text" name="fuel_type" class="form-control" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Liters</label>
      <input type="number" step="0.01" name="fuel_liters" class="form-control" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Cost</label>
      <input type="number" step="0.01" name="fuel_cost" class="form-control" required>
    </div>
    <div class="col-md-12">
      <button type="submit" name="submit" class="btn btn-primary">Save</button>
    </div>
  </form>

  <h2>Fuel Records</h2>
  <div class="card">
    <div class="card-body">
  <table class="table table-bordered table-striped">
    <thead class="table-darkblue">
      <tr>
        <th>ID</th><th>Vehicle</th><th>Date</th><th>Type</th><th>Liters</th><th>Cost</th><th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $stmt = $con->query("SELECT * FROM fuel_records ORDER BY id DESC");
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['vehicle_id'] ?></td>
            <td><?= $row['fuel_date'] ?></td>
            <td><?= $row['fuel_type'] ?></td>
            <td><?= $row['fuel_liters'] ?></td>
            <td><?= $row['fuel_cost'] ?></td>
            <td>
              <!-- Delete Button -->
              <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['id'] ?>">Delete</button>
            </td>
          </tr>

          <!-- Delete Modal -->
          <div class="modal fade" id="deleteModal<?= $row['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Confirm Delete</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  Are you sure you want to delete this fuel record (ID: <?= $row['id'] ?>)?
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <form method="POST">
                    <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                    <button type="submit" class="btn btn-danger">Delete</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
          <?php
      }
      ?>
        <?php
include("config/footer.php");
include("config/site_js_links.php");
include("config/data_tables_js.php");
?>
      </tbody>
    </table>
  </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>