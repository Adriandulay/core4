<?php
ob_start();
include("config/connection.php"); // <-- PDO connection
include("config/site_css_links.php");
include("config/data_tables_css.php");
include("config/header.php");
include("config/sidebar.php");

// --- FETCH VEHICLES & DRIVERS FROM DATABASE ---
$vehicles = $con->query("SELECT id, plate_number FROM vehicles ORDER BY plate_number ASC")->fetchAll(PDO::FETCH_ASSOC);
$drivers = $con->query("SELECT id, driver_name FROM drivers ORDER BY driver_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// --- Helper functions ---
function getVehiclePlate($vehicles, $id) {
    foreach ($vehicles as $v) {
        if ($v['id'] == $id) return $v['plate_number'];
    }
    return 'Unknown';
}

function getDriverName($drivers, $id) {
    foreach ($drivers as $d) {
        if ($d['id'] == $id) return $d['driver_name'];
    }
    return 'Unknown';
}

// --- CREATE (Add Dispatch) ---
if (isset($_POST['add_dispatch'])) {
    $job_order_id = 'JO-' . date('Ymd') . '-' . rand(100,999); // auto-generate Job Order ID
    $vehicle_id = $_POST['vehicle_id'];
    $driver_id = $_POST['driver_id'];
    $dispatch_date = $_POST['dispatch_date'];
    $schedule_time = $_POST['schedule_time'];
    $remarks = $_POST['remarks'];

    $stmt = $con->prepare("INSERT INTO dispatch_jobs 
        (job_order_id, vehicle_id, driver_id, dispatch_date, schedule_time, status, remarks) 
        VALUES (?, ?, ?, ?, ?, 'Pending', ?)");
    $stmt->execute([$job_order_id, $vehicle_id, $driver_id, $dispatch_date, $schedule_time, $remarks]);
    header("Location: dispatch_jobs.php");
    exit();
}

// --- UPDATE (Edit Dispatch) ---
if (isset($_POST['update_dispatch'])) {
    $id = $_POST['id'];
    $job_order_id = $_POST['job_order_id'];
    $vehicle_id = $_POST['vehicle_id'];
    $driver_id = $_POST['driver_id'];
    $dispatch_date = $_POST['dispatch_date'];
    $schedule_time = $_POST['schedule_time'];
    $status = $_POST['status'];
    $remarks = $_POST['remarks'];

    $stmt = $con->prepare("UPDATE dispatch_jobs 
        SET job_order_id=?, vehicle_id=?, driver_id=?, dispatch_date=?, schedule_time=?, status=?, remarks=? 
        WHERE id=?");
    $stmt->execute([$job_order_id, $vehicle_id, $driver_id, $dispatch_date, $schedule_time, $status, $remarks, $id]);
    header("Location: dispatch_jobs.php");
    exit();
}

// --- DELETE ---
if (isset($_POST['delete_dispatch'])) {
    $id = $_POST['id'];
    $stmt = $con->prepare("DELETE FROM dispatch_jobs WHERE id=?");
    $stmt->execute([$id]);
    header("Location: dispatch_jobs.php");
    exit();
}

// --- FETCH DISPATCH JOBS ---
$jobs = $con->query("SELECT * FROM dispatch_jobs ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dispatch Job Scheduling</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .main-content { padding-top: 75px; }
    .table-darkblue thead th { background-color:#1b263b;  color:#fff;  } 
</style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <div class="content-wrapper">
        <div class="main-content">
            <div class="container-fluid">
                <section class="content-header">
                <h2 class="mb-4">Dispatch Job Scheduling</h2>

                <!-- Add Button -->
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">+ Add Dispatch</button>
                </section>
                <!-- Dispatch Table -->
                <div class="card">
                    <div class="card-body">
                        <table class="table table-bordered  table-darkblue table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Job Order ID</th>
                                    <th>Vehicle</th>
                                    <th>Driver</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($jobs as $row): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= $row['job_order_id'] ?></td>
                                    <td><?= getVehiclePlate($vehicles, $row['vehicle_id']) ?></td>
                                    <td><?= getDriverName($drivers, $row['driver_id']) ?></td>
                                    <td><?= $row['dispatch_date'] ?></td>
                                    <td><?= $row['schedule_time'] ?></td>
                                    <td><?= $row['status'] ?></td>
                                    <td><?= $row['remarks'] ?></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
                                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['id'] ?>">Delete</button>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST">
                                                <div class="modal-header bg-primary text-white">
                                                    <h5 class="modal-title">Edit Dispatch Job</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">

                                                    <label>Job Order ID</label>
                                                    <input type="text" name="job_order_id" class="form-control" value="<?= $row['job_order_id'] ?>" required><br>

                                                    <label>Vehicle</label>
                                                    <select name="vehicle_id" class="form-control" required>
                                                        <?php foreach ($vehicles as $v): ?>
                                                        <option value="<?= $v['id'] ?>" <?= $v['id']==$row['vehicle_id'] ? 'selected':'' ?>><?= $v['plate_number'] ?></option>
                                                        <?php endforeach; ?>
                                                    </select><br>

                                                    <label>Driver</label>
                                                    <select name="driver_id" class="form-control" required>
                                                        <?php foreach ($drivers as $d): ?>
                                                        <option value="<?= $d['id'] ?>" <?= $d['id']==$row['driver_id'] ? 'selected':'' ?>><?= $d['driver_name'] ?></option>
                                                        <?php endforeach; ?>
                                                    </select><br>

                                                    <label>Dispatch Date</label>
                                                    <input type="date" name="dispatch_date" class="form-control" value="<?= $row['dispatch_date'] ?>"><br>

                                                    <label>Schedule Time</label>
                                                    <input type="time" name="schedule_time" class="form-control" value="<?= $row['schedule_time'] ?>"><br>

                                                    <label>Status</label>
                                                    <select name="status" class="form-control">
                                                        <option <?= $row['status']=='Pending'?'selected':'' ?>>Pending</option>
                                                        <option <?= $row['status']=='Active'?'selected':'' ?>>Active</option>
                                                        <option <?= $row['status']=='Completed'?'selected':'' ?>>Completed</option>
                                                    </select><br>

                                                    <label>Remarks</label>
                                                    <textarea name="remarks" class="form-control"><?= $row['remarks'] ?></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" name="update_dispatch" class="btn btn-success">Update</button>
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
                                            <form method="POST">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                    <p>Are you sure you want to delete this dispatch job?</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" name="delete_dispatch" class="btn btn-danger">Delete</button>
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

                <!-- Add Modal -->
                <div class="modal fade" id="addModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST">
                                <div class="modal-header">
                                    <h5 class="modal-title">Add Dispatch Job</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <label>Vehicle</label>
                                    <select name="vehicle_id" class="form-control" required>
                                        <?php foreach ($vehicles as $v): ?>
                                        <option value="<?= $v['id'] ?>"><?= $v['plate_number'] ?></option>
                                        <?php endforeach; ?>
                                    </select><br>

                                    <label>Driver</label>
                                    <select name="driver_id" class="form-control" required>
                                        <?php foreach ($drivers as $d): ?>
                                        <option value="<?= $d['id'] ?>"><?= $d['driver_name'] ?></option>
                                        <?php endforeach; ?>
                                    </select><br>

                                    <label>Dispatch Date</label>
                                    <input type="date" name="dispatch_date" class="form-control" required><br>

                                    <label>Schedule Time</label>
                                    <input type="time" name="schedule_time" class="form-control" required><br>

                                    <label>Remarks</label>
                                    <textarea name="remarks" class="form-control"></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="add_dispatch" class="btn btn-success">Add</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
include("config/footer.php");
include("config/site_js_links.php");
include("config/data_tables_js.php");
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  $(document).ready(function() {
    $('.table').DataTable({
        "lengthMenu": [5, 10, 25, 50, 100],
        "pageLength": 10,
        "order": [[0, "desc"]]
    });
  });
</script>

</body>
</html>
<?php ob_end_flush(); ?>