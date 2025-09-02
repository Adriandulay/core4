<?php
include("config/connection.php");
include("config/site_css_links.php");
include("config/data_tables_css.php");
include("config/header.php");
include("config/sidebar.php");

// Handle status updates
if (isset($_GET['update_status']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $new_status = $_GET['update_status'];

    $allowed = ['Scheduled','Active','Completed','Cancelled'];
    if (in_array($new_status, $allowed)) {
        $stmt = $con->prepare("UPDATE dispatch_jobs SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $new_status, ':id' => $id]);
    }
}

// Fetch jobs (Scheduled only)
$stmt = $con->prepare("
    SELECT dj.id, v.type, d.driver_name, p.project_name, 
           dj.dispatch_date, dj.schedule_time, dj.status, dj.remarks
    FROM dispatch_jobs dj
    JOIN vehicles v ON dj.vehicle_id = v.id
    JOIN drivers d ON dj.driver_id = d.id
    JOIN projects p ON dj.project_id = p.id
    WHERE dj.status = 'Scheduled'
");
$stmt->execute();
$scheduled = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dispatch Scheduling</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Table custom style */
    .table-darkblue thead th {
        background-color:#1b263b !important;
        color:#fff !important;
    }
    .table-darkblue tbody tr {
        background-color:#0d1b2a;
        color:#fff;
    }
    .main-content {
        padding-top: 60px;
    
    }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed"> 
  <div class="content-wrapper">
<div class="main-content">
<div class="container-fluid">
  <section class="content-header">
  <h2 class="mb-4">Dispatch Scheduling</h2>

  <!-- Navigation -->
  <div class="mb-3">
    <a href="active_jobs.php" class="btn btn-primary"> View Active Jobs</a>
    <a href="dispatch_history.php" class="btn btn-primary">Dispatch History</a>
    <button class="btn btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#addDispatchModal">
      + Add Dispatch
    </button>
  </div>
</section>
  <!-- Table -->
   <div class="card">
  <div class="card-body">
  <table class="table table-bordered table-hover table-darkblue">
    <thead>
      <tr>
        <th>ID</th><th>Vehicle</th><th>Driver</th><th>Project</th>
        <th>Date</th><th>Time</th><th>Status</th><th>Remarks</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($scheduled as $row): ?>
      <tr>
        <td><?= htmlspecialchars($row['id']) ?></td>
        <td><?= htmlspecialchars($row['type']) ?></td>
        <td><?= htmlspecialchars($row['driver_name']) ?></td>
        <td><?= htmlspecialchars($row['project_name']) ?></td>
        <td><?= htmlspecialchars($row['dispatch_date']) ?></td>
        <td><?= htmlspecialchars($row['schedule_time']) ?></td>
        <td><?= htmlspecialchars($row['status']) ?></td>
        <td><?= htmlspecialchars($row['remarks']) ?></td>
        <td>
          <a href="?update_status=Active&id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Activate</a>
          <a href="?update_status=Completed&id=<?= $row['id'] ?>" class="btn btn-sm btn-success">Complete</a>
          <a href="?update_status=Cancelled&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger">Cancel</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Add Dispatch Modal -->
<div class="modal fade" id="addDispatchModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="save_dispatch.php">
      <div class="modal-header bg-primary text-dark">
        <h5 class="modal-title">Add Dispatch</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        <label>Vehicle</label>
        <select class="form-control mb-2" name="vehicle_id" required>
          <?php
          $vehicles = $con->query("SELECT id, type FROM vehicles")->fetchAll(PDO::FETCH_ASSOC);
          foreach($vehicles as $v) {
              echo "<option value='{$v['id']}'>{$v['type']}</option>";
          }
          ?>
        </select>

        <label>Driver</label>
        <select class="form-control mb-2" name="driver_id" required>
          <?php
          $drivers = $con->query("SELECT id, driver_name FROM drivers")->fetchAll(PDO::FETCH_ASSOC);
          foreach($drivers as $d) {
              echo "<option value='{$d['id']}'>{$d['driver_name']}</option>";
          }
          ?>
        </select>

        <label>Project</label>
        <select class="form-control mb-2" name="project_id" required>
          <?php
          $projects = $con->query("SELECT id, project_name FROM projects")->fetchAll(PDO::FETCH_ASSOC);
          foreach($projects as $p) {
              echo "<option value='{$p['id']}'>{$p['project_name']}</option>";
          }
          ?>
        </select>

        <label>Date</label>
        <input type="date" class="form-control mb-2" name="dispatch_date" required>
        <label>Time</label>
        <input type="time" class="form-control mb-2" name="schedule_time" required>
        <label>Remarks</label>
        <textarea class="form-control" name="remarks"></textarea>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Save</button>
      </div>
    </form>
  </div>
</div>

<?php
include("config/footer.php");
include("config/site_js_links.php");
include("config/data_tables_js.php");
?>
<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>