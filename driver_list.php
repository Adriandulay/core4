<?php
ob_start();
include("config/connection.php"); // <-- make sure this gives $con as PDO
include("config/site_css_links.php");
include("config/data_tables_css.php");
include("config/header.php");
include("config/sidebar.php");

// Fetch drivers
$stmt = $con->prepare("SELECT * FROM drivers ORDER BY id DESC");
$stmt->execute();
$drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Add Driver
if (isset($_POST['addDriver'])) {
    $driver_name = $_POST['driver_name'];
    $license_no  = $_POST['license_no'];
    $contact     = $_POST['contact'];
    $status      = $_POST['status'];
    $plate_no    = $_POST['plate_no'];

    $stmt = $con->prepare("INSERT INTO drivers (driver_name, license_no, contact, status, plate_no, date_added) 
                           VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$driver_name, $license_no, $contact, $status, $plate_no]);
    header("Location: driver_list.php");
    exit();
}

// Handle Update Driver
if (isset($_POST['updateDriver'])) {
    $id          = $_POST['id'];
    $driver_name = $_POST['driver_name'];
    $license_no  = $_POST['license_no'];
    $contact     = $_POST['contact'];
    $status      = $_POST['status'];
    $plate_no    = $_POST['plate_no'];

    $stmt = $con->prepare("UPDATE drivers 
                           SET driver_name=?, license_no=?, contact=?, status=?, plate_no=? 
                           WHERE id=?");
    $stmt->execute([$driver_name, $license_no, $contact, $status, $plate_no, $id]);
    header("Location: driver_list.php");
    exit();
}

// Handle Delete Driver  ✅ FIXED
if (isset($_POST['deleteDriver'])) {
    $id = $_POST['id'];
    $stmt = $con->prepare("DELETE FROM drivers WHERE id=?");
    $stmt->execute([$id]);
    header("Location: driver_list.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Driver List</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <style>
    .table thead th {
        background-color: #001f3f;
        color: white;
        cursor: pointer;
    }
    .main-content {
        padding-top: 60px;
    }        
    .modal-header { background-color: #0d47a1; color: white; }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="content-wrapper">
<div class="main-content">
<div class="container-fluid">
  <section class="content-header">
    <h2>Driver List</h2>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addDriverModal">Add Driver</button>
  </section>

  <div class="card">
    <div class="card-body">
      <!-- ✅ Added id="driversTable" -->
      <table id="driversTable" class="table table-bordered table-stripe mb-0 align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Driver Name</th>
                <th>License No</th>
                <th>Contact</th>
                <th>Status</th>
                <th>Plate No</th>
                <th>Date Added</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($drivers as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['id']) ?></td>
                <td><?= htmlspecialchars($d['driver_name']) ?></td>
                <td><?= htmlspecialchars($d['license_no']) ?></td>
                <td><?= htmlspecialchars($d['contact']) ?></td>
                <td><?= htmlspecialchars($d['status']) ?></td>
                <td><?= htmlspecialchars($d['plate_no']) ?></td>
                <td><?= htmlspecialchars($d['date_added']) ?></td>
                <td>
                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editDriverModal<?= $d['id'] ?>">Edit</button>
                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteDriverModal<?= $d['id'] ?>">Delete</button>
                </td>
            </tr>

            <!-- Edit Modal -->
            <div class="modal fade" id="editDriverModal<?= $d['id'] ?>" tabindex="-1">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="post">
                    <div class="modal-header">
                      <h5 class="modal-title">Edit Driver</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <input type="hidden" name="id" value="<?= $d['id'] ?>">
                      <div class="mb-2">
                        <label>Driver Name</label>
                        <input type="text" name="driver_name" class="form-control" value="<?= $d['driver_name'] ?>" required>
                      </div>
                      <div class="mb-2">
                        <label>License No</label>
                        <input type="text" name="license_no" class="form-control" value="<?= $d['license_no'] ?>" required>
                      </div>
                      <div class="mb-2">
                        <label>Contact</label>
                        <input type="text" name="contact" class="form-control" value="<?= $d['contact'] ?>" required>
                      </div>
                      <div class="mb-2">
                        <label>Status</label>
                        <select name="status" class="form-control" required>
                          <option <?= $d['status']=="Active"?"selected":"" ?>>Active</option>
                          <option <?= $d['status']=="Inactive"?"selected":"" ?>>Inactive</option>
                        </select>
                      </div>
                      <div class="mb-2">
                        <label>Plate No</label>
                        <input type="text" name="plate_no" class="form-control" value="<?= $d['plate_no'] ?>" required>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="submit" name="updateDriver" class="btn btn-success">Update</button>
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- Delete Modal ✅ FIXED -->
            <div class="modal fade" id="deleteDriverModal<?= $d['id'] ?>" tabindex="-1">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="post">
                    <div class="modal-header bg-danger text-white">
                      <h5 class="modal-title">Delete Driver</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <p>Are you sure you want to delete <strong><?= htmlspecialchars($d['id']) ?></strong>?</p>
                      <input type="hidden" name="id" value="<?= $d['id'] ?>">
                    </div>
                    <div class="modal-footer">
                      <button type="submit" name="deleteDriver" class="btn btn-danger">Delete</button>
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
</div>

<!-- Add Modal -->
<div class="modal fade" id="addDriverModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h5 class="modal-title">Add Driver</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label>Driver Name</label>
            <input type="text" name="driver_name" class="form-control" required>
          </div>
          <div class="mb-2">
            <label>License No</label>
            <input type="text" name="license_no" class="form-control" required>
          </div>
          <div class="mb-2">
            <label>Contact</label>
            <input type="text" name="contact" class="form-control" required>
          </div>
          <div class="mb-2">
            <label>Status</label>
            <select name="status" class="form-control" required>
              <option>Active</option>
              <option>Inactive</option>
            </select>
          </div>
          <div class="mb-2">
            <label>Plate No</label>
            <input type="text" name="plate_no" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="addDriver" class="btn btn-primary">Add</button>
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
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
  $(document).ready(function(){
      // ✅ Fixed DataTable to target driversTable
      $('#driversTable').DataTable({
          "lengthMenu":[5,10,25,50,100],
          "pageLength":10,
          "order":[[0,"desc"]],
          "columnDefs":[{"orderable":false,"targets":7}]
      });
  });
</script>
</body>
</html>