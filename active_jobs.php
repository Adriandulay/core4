<?php
ob_start();
include("config/connection.php");
include("config/site_css_links.php");
include("config/data_tables_css.php");
include("config/header.php");
include("config/sidebar.php");

// --- UPDATE ---
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $vehicle_id = $_POST['vehicle_id'];
    $driver_id  = $_POST['driver_id'];
    $project_id = $_POST['project_id'];
    $dispatch_date = $_POST['dispatch_date'];
    $schedule_time = $_POST['schedule_time'];
    $status = $_POST['status'];
    $remarks = $_POST['remarks'];

    $stmt = $con->prepare("UPDATE dispatch_jobs 
        SET vehicle_id=?, driver_id=?, project_id=?, dispatch_date=?, schedule_time=?, status=?, remarks=? 
        WHERE id=?");
    $stmt->execute([$vehicle_id, $driver_id, $project_id, $dispatch_date, $schedule_time, $status, $remarks, $id]);
    header("Location: active_jobs.php"); exit;
}

// --- DELETE ---
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $stmt = $con->prepare("DELETE FROM dispatch_jobs WHERE id=?");
    $stmt->execute([$id]);
    header("Location: active_jobs.php"); exit;
}

// Fetch Active Jobs
$stmt = $con->prepare("
    SELECT dj.id, v.vehicle_name, d.driver_name, p.project_name, 
           dj.dispatch_date, dj.schedule_time, dj.status, dj.remarks,
           v.id as vehicle_id, d.id as driver_id, p.id as project_id
    FROM dispatch_jobs dj
    JOIN vehicles v ON dj.vehicle_id = v.id
    JOIN drivers d ON dj.driver_id = d.id
    JOIN projects p ON dj.project_id = p.id
    WHERE dj.status = 'Active'
");
$stmt->execute();
$active = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Dropdowns
$vehicles = $con->query("SELECT id, vehicle_name FROM vehicles")->fetchAll(PDO::FETCH_ASSOC);
$drivers  = $con->query("SELECT id, driver_name FROM drivers")->fetchAll(PDO::FETCH_ASSOC);
$projects = $con->query("SELECT id, project_name FROM projects")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Active Jobs</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .table-darkblue thead th { background-color:#1b263b; color:#fff; }
    .table-darkblue tbody tr { background-color:#0d1b2a; color:#fff; }
    .table-darkblue tbody tr:hover { background-color:#243447 !important; }
    .main-content { padding-top: 60px; }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
   <div class="content-wrapper">
<div class="main-content">
  <div class="container-fluid">
 <section class="content-header">
  <h2 class="mb-4">Active Jobs</h2>

  <div class="mb-3">
    <a href="dispatch_scheduling.php" class="btn btn-primary">Dispatch Scheduling</a>
    <a href="dispatch_history.php" class="btn btn-primary">Dispatch History</a>
  </div>
</section>

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
      <?php foreach($active as $row): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['vehicle_name']) ?></td>
        <td><?= htmlspecialchars($row['driver_name']) ?></td>
        <td><?= htmlspecialchars($row['project_name']) ?></td>
        <td><?= htmlspecialchars($row['dispatch_date']) ?></td>
        <td><?= htmlspecialchars($row['schedule_time']) ?></td>
        <td><span class="badge bg-success"><?= $row['status'] ?></span></td>
        <td><?= htmlspecialchars($row['remarks']) ?></td>
        <td>
          <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
          <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['id'] ?>">Delete</button>
        </td>
      </tr>

      <!-- Edit Modal -->
      <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <form method="post">
              <div class="modal-header">
                <h5 class="modal-title">Edit Active Job #<?= $row['id'] ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">

                <div class="mb-3">
                  <label>Vehicle</label>
                  <select name="vehicle_id" class="form-control" required>
                    <?php foreach($vehicles as $v): ?>
                      <option value="<?= $v['id'] ?>" <?= ($v['id']==$row['vehicle_id'])?'selected':'' ?>>
                        <?= $v['vehicle_name'] ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label>Driver</label>
                  <select name="driver_id" class="form-control" required>
                    <?php foreach($drivers as $d): ?>
                      <option value="<?= $d['id'] ?>" <?= ($d['id']==$row['driver_id'])?'selected':'' ?>>
                        <?= $d['driver_name'] ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label>Project</label>
                  <select name="project_id" class="form-control" required>
                    <?php foreach($projects as $p): ?>
                      <option value="<?= $p['id'] ?>" <?= ($p['id']==$row['project_id'])?'selected':'' ?>>
                        <?= $p['project_name'] ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label>Date</label>
                  <input type="date" name="dispatch_date" class="form-control" value="<?= $row['dispatch_date'] ?>" required>
                </div>

                <div class="mb-3">
                  <label>Time</label>
                  <input type="time" name="schedule_time" class="form-control" value="<?= $row['schedule_time'] ?>" required>
                </div>

                <div class="mb-3">
                  <label>Status</label>
                  <select name="status" class="form-control">
                    <option <?= ($row['status']=="Active")?'selected':'' ?>>Active</option>
                    <option <?= ($row['status']=="Completed")?'selected':'' ?>>Completed</option>
                    <option <?= ($row['status']=="Cancelled")?'selected':'' ?>>Cancelled</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label>Remarks</label>
                  <textarea name="remarks" class="form-control"><?= $row['remarks'] ?></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" name="update" class="btn btn-success">Update</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Delete Modal -->
      <div class="modal fade" id="deleteModal<?= $row['id'] ?>" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="post">
              <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                Are you sure you want to delete active job <b>#<?= $row['id'] ?></b>?
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
              </div>
              <div class="modal-footer">
                <button type="submit" name="delete" class="btn btn-danger">Delete</button>
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

<?php
include("config/footer.php");
include("config/site_js_links.php");
include("config/data_tables_js.php");
?>
<script>
  $(document).ready(function() {
    $('.table').DataTable({
        "lengthMenu": [5, 10, 25, 50, 100],
        "pageLength": 10,
        "order": [[0, "desc"]]
    });
  });
</script>
</div>
</div>
</body>
</html>
<?php ob_end_flush(); ?> 