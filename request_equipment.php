<?php
ob_start();
session_start();
include("config/connection.php");
include("config/site_css_links.php");
include("config/data_tables_css.php");
include("config/header.php");
include("config/sidebar.php");

$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ---------------- Handle Add Request ----------------
if (isset($_POST['send_request'])) {
    $requester = $_POST['requester'];
    $equipment = $_POST['equipment_name'];
    $purpose   = $_POST['purpose'];
    $date_needed = $_POST['date_needed'];

    $stmt = $con->prepare("INSERT INTO equipment_requests (requester, equipment_name, purpose, date_needed, status) 
                           VALUES (?, ?, ?, ?, 'Pending')");
    $stmt->execute([$requester, $equipment, $purpose, $date_needed]);
    header("Location: request_equipment.php");
    exit;
}

// ---------------- Handle Edit Request ----------------
if (isset($_POST['edit_request'])) {
    $id = $_POST['id'];
    $requester = $_POST['requester'];
    $equipment = $_POST['equipment_name'];
    $purpose   = $_POST['purpose'];
    $date_needed = $_POST['date_needed'];
    $status    = $_POST['status'];

    $stmt = $con->prepare("UPDATE equipment_requests 
                           SET requester=?, equipment_name=?, purpose=?, date_needed=?, status=? 
                           WHERE id=?");
    $stmt->execute([$requester, $equipment, $purpose, $date_needed, $status, $id]);
    header("Location: request_equipment.php");
    exit;
}

// ---------------- Handle Delete Request ----------------
if (isset($_POST['delete_request'])) {
    $id = $_POST['id'];
    $stmt = $con->prepare("DELETE FROM equipment_requests WHERE id=?");
    $stmt->execute([$id]);
    header("Location: request_equipment.php");
    exit;
}

// ---------------- Fetch Requests ----------------
$stmt = $con->query("SELECT * FROM equipment_requests ORDER BY id DESC");
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- CSS Fixes -->
<style>
/* Ensure modals are always on top */
.modal {
    z-index: 2000 !important;
}
.modal-backdrop {
    z-index: 1900 !important;
}
/* Sidebar clickable */
#layoutSidenav, .sb-sidenav {
    position: relative;
    z-index: 1;
}
/* Prevent DataTable dropdowns blocking modals */
div.dataTables_wrapper {
    z-index: auto;
}
.table thead th {
        background-color: #001f3f !important; /* navy blue */
            color: white;
    }
</style>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
       <div class="content-wrapper">
<div class="main-content">
<div class="container-fluid">
    <h3 class="mt-4">Equipment Requests</h3>
    
    <!-- Add Request Button -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
        Request Equipment
    </button>

    <!-- Requests Table -->
    <div class="card">
        <div class="card-body">
            <table id="datatablesSimple" class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Requester</th>
                        <th>Equipment</th>
                        <th>Purpose</th>
                        <th>Date Needed</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td><?= $req['id'] ?></td>
                            <td><?= htmlspecialchars($req['requester']) ?></td>
                            <td><?= htmlspecialchars($req['equipment_name']) ?></td>
                            <td><?= htmlspecialchars($req['purpose']) ?></td>
                            <td><?= htmlspecialchars($req['date_needed']) ?></td>
                            <td><?= htmlspecialchars($req['status']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $req['id'] ?>">Edit</button>
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $req['id'] ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Request Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Request Equipment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
          <div class="mb-3">
              <label class="form-label">Requester Name</label>
              <input type="text" name="requester" class="form-control" required>
          </div>
          <div class="mb-3">
              <label class="form-label">Equipment Name</label>
              <input type="text" name="equipment_name" class="form-control" required>
          </div>
          <div class="mb-3">
              <label class="form-label">Purpose</label>
              <textarea name="purpose" class="form-control"></textarea>
          </div>
          <div class="mb-3">
              <label class="form-label">Date Needed</label>
              <input type="date" name="date_needed" class="form-control" required>
          </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="send_request" class="btn btn-primary">Submit Request</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit & Delete Modals -->
<?php foreach ($requests as $req): ?>
    <!-- Edit Modal -->
    <div class="modal fade" id="editModal<?= $req['id'] ?>" tabindex="-1">
      <div class="modal-dialog">
        <form method="POST" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Request</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
              <input type="hidden" name="id" value="<?= $req['id'] ?>">
              <div class="mb-3">
                  <label class="form-label">Requester Name</label>
                  <input type="text" name="requester" class="form-control" value="<?= htmlspecialchars($req['requester']) ?>" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Equipment Name</label>
                  <input type="text" name="equipment_name" class="form-control" value="<?= htmlspecialchars($req['equipment_name']) ?>" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Purpose</label>
                  <textarea name="purpose" class="form-control"><?= htmlspecialchars($req['purpose']) ?></textarea>
              </div>
              <div class="mb-3">
                  <label class="form-label">Date Needed</label>
                  <input type="date" name="date_needed" class="form-control" value="<?= $req['date_needed'] ?>" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Status</label>
                  <select name="status" class="form-select">
                      <option <?= $req['status']=='Pending'?'selected':'' ?>>Pending</option>
                      <option <?= $req['status']=='Approved'?'selected':'' ?>>Approved</option>
                      <option <?= $req['status']=='Rejected'?'selected':'' ?>>Rejected</option>
                  </select>
              </div>
          </div>
          <div class="modal-footer">
            <button type="submit" name="edit_request" class="btn btn-warning">Update</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal<?= $req['id'] ?>" tabindex="-1">
      <div class="modal-dialog">
        <form method="POST" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Delete Request</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
              <input type="hidden" name="id" value="<?= $req['id'] ?>">
              <p>Are you sure you want to delete this request?</p>
          </div>
          <div class="modal-footer">
            <button type="submit" name="delete_request" class="btn btn-danger">Delete</button>
          </div>
        </form>
      </div>
    </div>
<?php endforeach; ?>

<?php include("config/footer.php"); ?>
<?php include("config/data_tables_js.php"); ?>

<!-- Bootstrap Bundle (needed for modals & sidebar toggle) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

