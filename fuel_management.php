<?php
include("config/connection.php");
include("config/site_css_links.php");
include("config/data_tables_css.php");
include("config/header.php");
include("config/sidebar.php");

// --- CREATE ---
if (isset($_POST['submit'])) {
    $vehicle_id  = $_POST['vehicle_id'];
    $fuel_date   = $_POST['fuel_date'];
    $fuel_type   = $_POST['fuel_type'];
    $fuel_liters = $_POST['fuel_liters'];
    $cost        = $_POST['cost'];
    $location    = $_POST['location'];
    $driver_name = $_POST['driver_name'];
    $notes       = $_POST['notes'];

    try {
        $sql = "INSERT INTO fuel_logs 
                (vehicle_id, fuel_date, fuel_type, fuel_liters, cost, location, driver_name, notes)
                VALUES (:vehicle_id, :fuel_date, :fuel_type, :fuel_liters, :cost, :location, :driver_name, :notes)";
        $stmt = $con->prepare($sql);
        $stmt->execute([
            ':vehicle_id'  => $vehicle_id,
            ':fuel_date'   => $fuel_date,
            ':fuel_type'   => $fuel_type,
            ':fuel_liters' => $fuel_liters,
            ':cost'        => $cost,
            ':location'    => $location,
            ':driver_name' => $driver_name,
            ':notes'       => $notes
        ]);
        echo "<div class='alert alert-success'>Fuel log added successfully!</div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error adding fuel log: " . $e->getMessage() . "</div>";
    }
}

// --- UPDATE ---
if (isset($_POST['update'])) {
    $id          = $_POST['id'];
    $vehicle_id  = $_POST['vehicle_id'];
    $fuel_date   = $_POST['fuel_date'];
    $fuel_type   = $_POST['fuel_type'];
    $fuel_liters = $_POST['fuel_liters'];
    $cost        = $_POST['cost'];
    $location    = $_POST['location'];
    $driver_name = $_POST['driver_name'];
    $notes       = $_POST['notes'];

    $sql = "UPDATE fuel_logs 
            SET vehicle_id=?, fuel_date=?, fuel_type=?, fuel_liters=?, cost=?, location=?, driver_name=?, notes=? 
            WHERE id=?";
    $stmt = $con->prepare($sql);
    $stmt->execute([$vehicle_id, $fuel_date, $fuel_type, $fuel_liters, $cost, $location, $driver_name, $notes, $id]);
}

// --- DELETE ---
if (isset($_POST['delete']) && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    try {
        $stmt = $con->prepare("DELETE FROM fuel_logs WHERE id = :id");
        $stmt->bindParam(':id', $delete_id, PDO::PARAM_INT);
        $stmt->execute();

        echo "<div class='alert alert-success'>Record deleted successfully!</div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fuel Logs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS (local) -->
    <link href="plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .main-content { padding-top: 60px; }
        .table-darkblue th {
            background-color: #1b263b !important;
            color: #ffffff;
            height: 50px;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <div class="content-wrapper">
        <section class="main-content">
            <div class="container-fluid">
                <h2 class="mb-4">Fuel Logs</h2>

                <!-- Add Fuel Button -->
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addFuelModal">
                    <i class="bi bi-plus-circle"></i> Add Fuel
                </button>

                <!-- Modal for Adding Fuel -->
                <div class="modal fade" id="addFuelModal" tabindex="-1" aria-labelledby="addFuelModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST">
                                <div class="modal-header bg-primary text-dark">
                                    <h5 class="modal-title" id="addFuelModalLabel">Add Fuel Entry</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- Vehicle Dropdown -->
                                    <div class="mb-3">
                                        <label class="form-label">Vehicle</label>
                                        <select name="vehicle_id" class="form-select" required>
                                            <option value="">-- Select Vehicle --</option>
                                            <?php
                                            $vehicles = $con->query("SELECT id, type FROM vehicles ORDER BY type ASC");
                                            while ($row = $vehicles->fetch(PDO::FETCH_ASSOC)) {
                                                echo "<option value='{$row['id']}'>{$row['type']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="mb-3"><label class="form-label">Fuel Date</label><input type="date" name="fuel_date" class="form-control" required></div>
                                    <div class="mb-3"><label class="form-label">Fuel Type</label><input type="text" name="fuel_type" class="form-control" required></div>
                                    <div class="mb-3"><label class="form-label">Liters</label><input type="number" step="0.01" name="fuel_liters" class="form-control" required></div>
                                    <div class="mb-3"><label class="form-label">Cost</label><input type="number" step="0.01" name="cost" class="form-control" required></div>
                                    <div class="mb-3"><label class="form-label">Location</label><input type="text" name="location" class="form-control" required></div>
                                    <div class="mb-3"><label class="form-label">Driver Name</label><input type="text" name="driver_name" class="form-control" required></div>
                                    <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control"></textarea></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="submit" class="btn btn-success">Save</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete this fuel log entry?
          <input type="hidden" name="delete_id" id="delete_id">
        </div>
        <div class="modal-footer">
          <button type="submit" name="delete" class="btn btn-danger">Delete</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>
                <!-- Logs Table -->
                <hr class="my-4">
                <h4>Fuel Logs History</h4>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-darkblue table-striped table-sm dataTable" style="height:150px">
                                <thead class="table-darkblue">
                                <tr>
                                    <th>ID</th>
                                    <th>Vehicle</th>
                                    <th>Date</th>
                                    <th>Fuel Type</th>
                                    <th>Liters</th>
                                    <th>Cost</th>
                                    <th>Location</th>
                                    <th>Driver</th>
                                    <th>Notes</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $logs = $con->query("SELECT fuel_logs.*, vehicles.vehicle_name 
                                                    FROM fuel_logs 
                                                    JOIN vehicles ON fuel_logs.vehicle_id = vehicles.id 
                                                    ORDER BY fuel_logs.fuel_date DESC");
                                while ($row = $logs->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr>
                                            <td>{$row['id']}</td>
                                            <td>{$row['vehicle_name']}</td>
                                            <td>{$row['fuel_date']}</td>
                                            <td>{$row['fuel_type']}</td>
                                            <td>{$row['fuel_liters']}</td>
                                            <td>{$row['cost']}</td>
                                            <td>{$row['location']}</td>
                                            <td>{$row['driver_name']}</td>
                                            <td>{$row['notes']}</td>
                                            <td>
                                                <button type='button' 
                                                        class='btn btn-sm btn-danger' 
                                                        data-bs-toggle='modal' 
                                                        data-bs-target='#deleteModal' 
                                                        data-id='{$row['id']}'>
                                                    <i class='bi bi-trash'></i>
                                                </button>
                     
                                            </td>
                                        </tr>";
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
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
                "order": [[0, "desc"]],
                "columnDefs": [
                    { "orderable": false, "targets": 9 } // Action column not sortable
                ]
            });
        });

const deleteModal = document.getElementById('deleteModal');
deleteModal.addEventListener('show.bs.modal', function (event) {
  let button = event.relatedTarget;
  let id = button.getAttribute('data-id');
  document.getElementById('delete_id').value = id;
});

    </script>
</div>
</body>
</html>