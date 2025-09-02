<?php
ob_start();
include("config/connection.php");
include("config/site_css_links.php");
include("config/data_tables_css.php");
include("config/header.php");
include("config/sidebar.php");

// --- ADD REQUEST ---
if (isset($_POST['add_request'])) {
    $vehicle_id = $_POST['vehicle_id'];
    $amount = $_POST['amount'];
    $purpose = $_POST['purpose'];
    $requested_by = $_SESSION['user_id'];

    $stmt = $con->prepare("INSERT INTO vehicle_budget_requests (vehicle_id, requested_by, amount, purpose) VALUES (?, ?, ?, ?)");
    $stmt->execute([$vehicle_id, $requested_by, $amount, $purpose]);
    header("Location: request_budget.php");
}

// --- EDIT REQUEST ---
if (isset($_POST['edit_request'])) {
    $id = $_POST['request_id'];
    $vehicle_id = $_POST['vehicle_id'];
    $amount = $_POST['amount'];
    $purpose = $_POST['purpose'];

    $stmt = $con->prepare("UPDATE vehicle_budget_requests SET vehicle_id=?, amount=?, purpose=? WHERE id=?");
    $stmt->execute([$vehicle_id, $amount, $purpose, $id]);
    header("Location: request_budget.php");
}

// --- DELETE REQUEST ---
if (isset($_POST['delete_request'])) {
    $id = $_POST['request_id'];
    $stmt = $con->prepare("DELETE FROM vehicle_budget_requests WHERE id=?");
    $stmt->execute([$id]);
    header("Location: request_budget.php");
}

// Fetch vehicles
$vehicles = $con->query("SELECT id, plate_number FROM vehicles")->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's requests
$requests = $con->query("SELECT r.*, v.plate_number FROM vehicle_budget_requests r JOIN vehicles v ON r.vehicle_id = v.id WHERE r.requested_by = ".$_SESSION['user_id'])->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
    .main-content {
        padding-top: 75px;
    }
    .table thead th {
        background-color: #001f3f !important; /* navy blue */
            color: white;
    }
    .modal-header { background-color: #0d47a1; color: white;
    
    }
    </style>

<body clas="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <div class="content-wrapper">
            <div class="main-content">
                <div class="container-fluid">
                    <section class="content-header">
<h2>Request Vehicle Operation Budget</h2>
<!-- ADD REQUEST FORM -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Add Request</button>
</section>
<!-- REQUEST TABLE -->
 <div class="card">
    <div class="card-body">
<table class="table table-bordered ">
    <thead>
        <tr>
            <th>ID</th>
            <th>Vehicle</th>
            <th>Amount</th>
            <th>Purpose</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($requests as $r): ?>
        <tr>
            <td><?= $r['id'] ?></td>
            <td><?= $r['plate_number'] ?></td>
            <td><?= $r['amount'] ?></td>
            <td><?= $r['purpose'] ?></td>
            <td><?= $r['status'] ?></td>
            <td>
                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $r['id'] ?>">Edit</button>
                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $r['id'] ?>">Delete</button>
            </td>
        </tr>

        <!-- EDIT MODAL -->
        <div class="modal fade" id="editModal<?= $r['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="POST">
                <div class="modal-header bg-warning">
                  <h5 class="modal-title">Edit Request</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                    <label>Vehicle</label>
                    <select name="vehicle_id" class="form-control" required>
                        <?php foreach($vehicles as $v): ?>
                            <option value="<?= $v['id'] ?>" <?= $v['id']==$r['vehicle_id'] ? 'selected':'' ?>><?= $v['plate_number'] ?></option>
                        <?php endforeach; ?>
                    </select><br>
                    <label>Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control" value="<?= $r['amount'] ?>" required><br>
                    <label>Purpose</label>
                    <input type="text" name="purpose" class="form-control" value="<?= $r['purpose'] ?>" required>
                </div>
                <div class="modal-footer">
                  <button type="submit" name="edit_request" class="btn btn-primary">Save Changes</button>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- DELETE MODAL -->
        <div class="modal fade" id="deleteModal<?= $r['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="POST">
                <div class="modal-header bg-danger">
                  <h5 class="modal-title">Confirm Delete</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this request for vehicle <?= $r['plate_number'] ?>?
                    <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                </div>
                <div class="modal-footer">
                  <button type="submit" name="delete_request" class="btn btn-danger">Delete</button>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <?php endforeach; ?>
    </tbody>
</table>

<!-- ADD MODAL -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Add Budget Request</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <label>Vehicle</label>
            <select name="vehicle_id" class="form-control" required>
                <?php foreach($vehicles as $v): ?>
                    <option value="<?= $v['id'] ?>"><?= $v['plate_number'] ?></option>
                <?php endforeach; ?>
            </select><br>
            <label>Amount</label>
            <input type="number" step="0.01" name="amount" class="form-control" required><br>
            <label>Purpose</label>
            <input type="text" name="purpose" class="form-control" required>
        </div>
        <div class="modal-footer">
          <button type="submit" name="add_request" class="btn btn-success">Submit Request</button>
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
<?php ob_end_flush(); ?>