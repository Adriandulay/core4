<?php
ob_start();
include("config/connection.php");
include("config/site_css_links.php");
include("config/data_tables_css.php");
include("config/header.php");
include("config/sidebar.php");

// ====== CREATE / ADD SCHEDULE ======
if (isset($_POST['add_schedule'])) {
    $vehicle_id    = $_POST['vehicle_id'];
    $schedule_date = $_POST['schedule_date'];
    $status        = "Scheduled"; // default
    $remarks       = "Shared from Fleet Management Module";

    $sql = "INSERT INTO maintenance_schedule (vehicle_id, schedule_date, status, remarks) 
            VALUES (:vehicle_id, :schedule_date, :status, :remarks)";
    $stmt = $con->prepare($sql);
    $stmt->execute([
        ':vehicle_id'    => $vehicle_id,
        ':schedule_date' => $schedule_date,
        ':status'        => $status,
        ':remarks'       => $remarks
    ]);

    header("Location: maintenance_schedule.php");
    exit;
}

// ====== UPDATE ======
if (isset($_POST['update_schedule'])) {
    $id            = $_POST['id'];
    $vehicle_id    = $_POST['vehicle_id'];
    $schedule_date = $_POST['schedule_date'];
    $status        = $_POST['status'];
    $remarks       = $_POST['remarks'];

    $sql = "UPDATE maintenance_schedule 
            SET vehicle_id=:vehicle_id, schedule_date=:schedule_date, status=:status, remarks=:remarks 
            WHERE id=:id";
    $stmt = $con->prepare($sql);
    $stmt->execute([
        ':id'            => $id,
        ':vehicle_id'    => $vehicle_id,
        ':schedule_date' => $schedule_date,
        ':status'        => $status,
        ':remarks'       => $remarks
    ]);
}

// ====== DELETE ======
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM maintenance_schedule WHERE id=:id";
    $stmt = $con->prepare($sql);
    $stmt->execute([':id' => $id]);

    header("Location: maintenance_schedule.php");
    exit;
}

// ====== SHARE BUTTON ======
if (isset($_GET['share'])) {
    $id = $_GET['share'];
    $sql = "UPDATE maintenance_schedule SET status='Shared to Core 3' WHERE id=:id";
    $stmt = $con->prepare($sql);
    $stmt->execute([':id' => $id]);

    header("Location: maintenance_schedule.php");
    exit;
}

// ====== FETCH DATA ======
$stmt = $con->query("SELECT * FROM maintenance_schedule ORDER BY schedule_date DESC");
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Fleet Management - Share Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .main-content {
        padding-top: 85px;
    }
    .table thead th {
        background-color: #001f3f !important; /* navy blue */
            color: white;
    }
    .modal-header { background-color: #0d47a1; color: white;
    
    }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <div class="content-wrapper">
    <div class="main-content">
    <div class="container-fluid">
        <section class="content-header">
    <h2 class="mb-4">Fleet Management - Share Preventive Maintenance</h2>
</section>
    <!-- Add Form -->
    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-3">
            <input type="text" name="vehicle_id" class="form-control" placeholder="Vehicle ID" required>
        </div>
        <div class="col-md-3">
            <input type="date" name="schedule_date" class="form-control" required>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-control" required>
                <option value="Schedule">Scheduled</option>
                <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" name="add_schedule" class="btn btn-primary">Add Schedule</button>
        </div>
    </form>

    <!-- Table -->
     <div class="card">
        <div class="card-body">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Vehicle ID</th>
                <th>Schedule Date</th>
                <th>Status</th>
                <th>Remarks</th>
                <th width="250">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($schedules as $row): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['vehicle_id'] ?></td>
                <td><?= $row['schedule_date'] ?></td>
                <td><?= $row['status'] ?></td>
                <td><?= $row['remarks'] ?></td>
                <td>
                    <!-- Edit Button -->
                    <button class="btn btn-sm btn-warning" 
                            data-bs-toggle="modal" 
                            data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>

                    <!-- Delete Button -->
                    <button class="btn btn-sm btn-danger text-white" data-bs-toggle="modal" 
                               data-bs-target="#deleteModal<?= $row['id'] ?>">Delete</button>

                    <!-- Share Button -->
                    <?php if ($row['status'] != "Shared to Core 3"): ?>
                        <a href="?share=<?= $row['id'] ?>" 
                           class="btn btn-sm btn-success"
                           onclick="return confirm('Share this schedule to Core 3?')">Share</a>
                    <?php else: ?>
                        <span class="badge bg-success">Already Shared</span>
                    <?php endif; ?>
                </td>
            </tr>


            <!-- Edit Modal -->
            <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="POST">
                      <div class="modal-header bg-">
                          <h5 class="modal-title">Edit Schedule</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                          <input type="hidden" name="id" value="<?= $row['id'] ?>">
                          <div class="mb-3">
                              <label>Vehicle ID</label>
                              <input type="text" name="vehicle_id" value="<?= $row['vehicle_id'] ?>" class="form-control" required>
                          </div>
                          <div class="mb-3">
                              <label>Schedule Date</label>
                              <input type="date" name="schedule_date" value="<?= $row['schedule_date'] ?>" class="form-control" required>
                          </div>
                          <div class="mb-3">
                              <label>Status</label>
                              <select name="status" class="form-control">
                                  <option <?= $row['status']=='Scheduled'?'selected':'' ?>>Scheduled</option>
                                  <option <?= $row['status']=='Completed'?'selected':'' ?>>Completed</option>
                                  <option <?= $row['status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
                                  <option <?= $row['status']=='Shared to Core 3'?'selected':'' ?>>Shared to Core 3</option>
                              </select>
                          </div>
                          <div class="mb-3">
                              <label>Remarks</label>
                              <textarea name="remarks" class="form-control"><?= $row['remarks'] ?></textarea>
                          </div>
                      </div>
                      <div class="modal-footer">
                          <button type="submit" name="update_schedule" class="btn btn-success">Update</button>
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                      </div>
                  </form>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<!--DELETE MODAL-->
<?php foreach ($schedules as $row): ?>
<div class="modal fade" id="deleteModal<?= $row['id'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger ">
          <h5 class="modal-title">Confirm Delete</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
          Are you sure you want to delete schedule for Vehicle ID: <strong><?= $row['vehicle_id'] ?></strong> on <strong><?= $row['schedule_date'] ?></strong>?
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger">Delete</a>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>
<?php
include("config/footer.php");
include("config/site_js_links.php");
include("config/data_tables_js.php");
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<? ob_end_flush(); ?>