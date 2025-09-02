<?php
ob_start();
include("config/connection.php"); // $con as PDO
include("config/site_css_links.php");
include("config/data_tables_css.php");
include("config/header.php");
include("config/sidebar.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vehicle Tracking</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- Optional custom style -->
    <style>
        body {
            background: #f8f9fa;
        }
        h3, h5 {
            margin-top: 20px;
        }
        .container {
            max-width: 1200px;
        }
        .main-content {
            padding-top: 50px;
        }
        thead.table-dark th {
            background-color: #001f3f !important; /* navy blue */
            color: white;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
<div class="content-wrapper">
<div class="main-content">
<div class="container-fluid">
    <section class="content-header">
    <h3>Vehicle Tracking</h3>
  </section>
    <!-- PHP check-out / check-in logic -->
<?php
    // Handle Check-Out
    if (isset($_POST['checkout'])) {
        $vehicle_id = $_POST['vehicle_id'];
        $driver_id = $_POST['driver_id'];
        $project_id = $_POST['project_id'];
        $odometer_start = $_POST['odometer_start'];

        $check = $con->prepare("SELECT COUNT(*) FROM vehicle_logs 
                                WHERE vehicle_id = ? AND check_in_time IS NULL");
        $check->execute([$vehicle_id]);
        if ($check->fetchColumn() == 0) {
            $sql = "INSERT INTO vehicle_logs (vehicle_id, driver_id, project_id, check_out_time, odometer_start) 
                    VALUES (?, ?, ?, NOW(), ?)";
            $stmt = $con->prepare($sql);
            $stmt->execute([$vehicle_id, $driver_id, $project_id, $odometer_start]);
            echo "<div class='alert alert-success'>Vehicle checked out successfully!</div>";
        } else {
            echo "<div class='alert alert-warning'>This vehicle is already checked out!</div>";
        }
    }

    // Handle Check-In
    if (isset($_POST['checkin'])) {
        $log_id = $_POST['log_id'];
        $odometer_end = $_POST['odometer_end'];
        $remarks = $_POST['remarks'];

        $sql = "UPDATE vehicle_logs 
                SET check_in_time = NOW(), odometer_end = ?, remarks = ?
                WHERE id = ?";
        $stmt = $con->prepare($sql);
        $stmt->execute([$odometer_end, $remarks, $log_id]);

        echo "<div class='alert alert-success'>Vehicle checked in successfully!</div>";
    }

    // Fetch active logs
    $sql = "SELECT vl.id, v.vehicle_name, d.driver_name, p.project_name, 
                   vl.check_out_time, vl.odometer_start
            FROM vehicle_logs vl
            JOIN vehicles v ON vl.vehicle_id = v.id
            JOIN drivers d ON vl.driver_id = d.id
            JOIN projects p ON vl.project_id = p.id
            WHERE vl.check_in_time IS NULL";
    $activeLogs = $con->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    // Fetch history
    $sql = "SELECT vl.*, v.vehicle_name, d.driver_name, p.project_name
            FROM vehicle_logs vl
            JOIN vehicles v ON vl.vehicle_id = v.id
            JOIN drivers d ON vl.driver_id = d.id
            JOIN projects p ON vl.project_id = p.id
            ORDER BY vl.id DESC LIMIT 20";
    $history = $con->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <!-- Check-Out Form -->
    <form method="post" class="mb-4 card card-body shadow-sm">
        <h5>Check-Out Vehicle</h5>
        <select name="vehicle_id" class="form-select mb-2" required>
            <option value="">Select Vehicle</option>
            <?php
            $res = $con->query("SELECT * FROM vehicles");
            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='{$row['id']}'>{$row['vehicle_name']}</option>";
            }
            ?>
        </select>
        <select name="driver_id" class="form-select mb-2" required>
            <option value="">Select Driver</option>
            <?php
            $res = $con->query("SELECT * FROM drivers");
            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='{$row['id']}'>{$row['driver_name']}</option>";
            }
            ?>
        </select>
        <select name="project_id" class="form-select mb-2" required>
            <option value="">Select Project</option>
            <?php
            $res = $con->query("SELECT * FROM projects");
            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='{$row['id']}'>{$row['project_name']}</option>";
            }
            ?>
        </select>
        <input type="number" name="odometer_start" class="form-control mb-2" placeholder="Odometer Start" required>
        <button type="submit" name="checkout" class="btn btn-primary">Check-Out</button>
    </form>

    <!-- Active Vehicles -->
    <h5>Active Trips</h5>
    <table id="activeTable" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Vehicle</th><th>Driver</th><th>Project</th>
                <th>Check-Out Time</th><th>Odometer Start</th><th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($activeLogs as $row) { ?>
            <tr>
                <td><?= htmlspecialchars($row['vehicle_name']) ?></td>
                <td><?= htmlspecialchars($row['driver_name']) ?></td>
                <td><?= htmlspecialchars($row['project_name']) ?></td>
                <td><?= $row['check_out_time'] ?></td>
                <td><?= $row['odometer_start'] ?></td>
                <td>
                    <form method="post" class="d-flex">
                        <input type="hidden" name="log_id" value="<?= $row['id'] ?>">
                        <input type="number" name="odometer_end" class="form-control me-2" placeholder="Odometer End" required>
                        <input type="text" name="remarks" class="form-control me-2" placeholder="Remarks">
                        <button type="submit" name="checkin" class="btn btn-success btn-sm">Check-In</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <!-- History -->
    <h5>Recent History</h5>
    <table id="historyTable" class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Vehicle</th><th>Driver</th><th>Project</th>
                <th>Out</th><th>In</th>
                <th>Odo Start</th><th>Odo End</th><th>Remarks</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($history as $row) { ?>
            <tr>
                <td><?= htmlspecialchars($row['vehicle_name']) ?></td>
                <td><?= htmlspecialchars($row['driver_name']) ?></td>
                <td><?= htmlspecialchars($row['project_name']) ?></td>
                <td><?= $row['check_out_time'] ?></td>
                <td><?= $row['check_in_time'] ?></td>
                <td><?= $row['odometer_start'] ?></td>
                <td><?= $row['odometer_end'] ?></td>
                <td><?= htmlspecialchars($row['remarks']) ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<!-- Bootstrap + DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#activeTable').DataTable();
    $('#historyTable').DataTable();
});
</script>
<?php
        include("config/footer.php");
        include("config/site_js_links.php");
        include("config/data_tables_js.php");
        ?>
</body>
</html>