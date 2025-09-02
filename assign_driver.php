<?php
ob_start();
include("config/connection.php"); // $con as PDO
include("config/site_css_links.php");
include("config/data_tables_css.php");
include("config/header.php");
include("config/sidebar.php");

// Current user (for logs or tracking)
$current_user = $_SESSION['username'] ?? 'system';

// Handle Add Assignment
if(isset($_POST['add'])){
    $vehicle_id = $_POST['vehicle_id'] ?? '';
    $driver_id = $_POST['driver_id'] ?? '';
    $date_assigned = $_POST['date_assigned'] ?? date('Y-m-d');

    if(empty($vehicle_id) || empty($driver_id)) exit('Vehicle and driver are required');

    $stmt = $con->prepare("INSERT INTO driver_assignments (vehicle_id, driver_id, date_assigned, assigned_by) VALUES (:vehicle_id, :driver_id, :date_assigned, :assigned_by)");
    $stmt->execute([
        ':vehicle_id' => $vehicle_id,
        ':driver_id' => $driver_id,
        ':date_assigned' => $date_assigned,
        ':assigned_by' => $current_user
    ]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Handle Edit Assignment
if(isset($_POST['edit'])){
    $id = $_POST['id'];
    $vehicle_id = $_POST['vehicle_id'] ?? '';
    $driver_id = $_POST['driver_id'] ?? '';
    $date_assigned = $_POST['date_assigned'] ?? date('Y-m-d');

    $stmt = $con->prepare("UPDATE driver_assignments SET vehicle_id=:vehicle_id, driver_id=:driver_id, date_assigned=:date_assigned WHERE id=:id");
    $stmt->execute([
        ':vehicle_id'=>$vehicle_id,
        ':driver_id'=>$driver_id,
        ':date_assigned'=>$date_assigned,
        ':id'=>$id
    ]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Handle Delete
if(isset($_POST['delete'])){
    $id = $_POST['delete_id'];
    $stmt = $con->prepare("DELETE FROM driver_assignments WHERE id=:id");
    $stmt->execute([':id'=>$id]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Fetch Assignments
$stmt = $con->prepare("SELECT da.id, v.type, d.driver_name, da.date_assigned 
                       FROM driver_assignments da
                       JOIN vehicles v ON da.vehicle_id=v.id
                       JOIN drivers d ON da.driver_id=d.id
                       ORDER BY da.date_assigned DESC");
$stmt->execute();
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Vehicles and Drivers for dropdown
$vehicles = $con->query("SELECT id, type FROM vehicles")->fetchAll(PDO::FETCH_ASSOC);
$drivers = $con->query("SELECT id, driver_name FROM drivers")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Driver</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .table thead th { background-color: #001f3f; color: white; }
        .modal-header { background-color: #0d47a1; color: white; }
        .main-content { padding-top: 60px; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="content-wrapper">
<div class="main-content">
    <div class="container-fluid">
        <section class="content-header">
        <h2>Assign Driver</h2>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">Add Assignment</button>
</section>
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered table-striped" id="assignmentsTable">
                    <thead>
                        <tr>
                            <th>Vehicle</th>
                            <th>Driver</th>
                            <th>Assign Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($assignments as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['type']) ?></td>
                            <td><?= htmlspecialchars($a['driver_name']) ?></td>
                            <td><?= htmlspecialchars($a['date_assigned']) ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm editBtn"
                                        data-id="<?= $a['id'] ?>"
                                        data-vehicle="<?= $a['type'] ?>"
                                        data-driver="<?= $a['driver_name'] ?>"
                                        data-date="<?= $a['date_assigned'] ?>">
                                    Edit
                                </button>
                                <button class="btn btn-danger btn-sm deleteBtn"
                                        data-id="<?= $a['id'] ?>"
                                        data-driver="<?= htmlspecialchars($a['driver_name'], ENT_QUOTES) ?>">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Add Assignment</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
                <label>Vehicle</label>
                <select name="vehicle_id" class="form-select" required>
                    <?php foreach($vehicles as $v): ?>
                        <option value="<?= $v['id'] ?>"><?= $v['type'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Driver</label>
                <select name="driver_id" class="form-select" required>
                    <?php foreach($drivers as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= $d['driver_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Assign Date</label>
                <input type="date" name="date_assigned" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" name="add" class="btn btn-primary">Add Assignment</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
    </form>
  </div>
</div>

<!-- Edit Modal (Single Dynamic) -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Assignment</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id" id="edit_id">
            <div class="mb-3">
                <label>Vehicle</label>
                <select name="vehicle_id" id="edit_vehicle" class="form-select" required>
                    <?php foreach($vehicles as $v): ?>
                        <option value="<?= $v['id'] ?>"><?= $v['type'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Driver</label>
                <select name="driver_id" id="edit_driver" class="form-select" required>
                    <?php foreach($drivers as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= $d['driver_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Assign Date</label>
                <input type="date" name="date_assigned" id="edit_date" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" name="edit" class="btn btn-primary">Save Changes</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
    </form>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title">Confirm Delete</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="delete_id" id="delete_id">
            <p>Are you sure you want to delete assignment for <strong id="delete_driver"></strong>?</p>
          </div>
          <div class="modal-footer">
            <button type="submit" name="delete" class="btn btn-danger">Delete</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </div>
    </form>
  </div>
</div>

<?php
include("config/footer.php");
include("config/site_js_links.php");
include("config/data_tables_js.php");
?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function(){
    // DataTable
    $('#assignmentsTable').DataTable({
        "lengthMenu":[5,10,25,50,100],
        "pageLength":10,
        "order":[[2,"desc"]],
        "columnDefs":[{"orderable":false,"targets":3}]
    });

    // Edit modal
    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
    $('.editBtn').click(function(){
        let tr = $(this).closest('tr');
        let id = $(this).data('id');
        let vehicle = tr.find('td:eq(0)').text();
        let driver = tr.find('td:eq(1)').text();
        let date = tr.find('td:eq(2)').text();

        // set values
        $('#edit_id').val(id);
        $('#edit_vehicle option').filter(function(){ return $(this).text()==vehicle }).prop('selected', true);
        $('#edit_driver option').filter(function(){ return $(this).text()==driver }).prop('selected', true);
        $('#edit_date').val(date);

        editModal.show();
    });

    // Delete modal
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    $('.deleteBtn').click(function(){
        let id = $(this).data('id');
        let driver = $(this).data('driver');
        $('#delete_id').val(id);
        $('#delete_driver').text(driver);
        deleteModal.show();
    });
});
</script>
</body>
</html>
<?php ob_end_flush(); ?>