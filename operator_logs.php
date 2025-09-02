<?php
ob_start();
include("config/connection.php"); 
include("config/site_css_links.php");
include("config/data_tables_css.php");
include("config/header.php");
include("config/sidebar.php");

// --- Handle delete (modal submit) ---
if (isset($_POST['delete'])) {
    $delete_id = (int) $_POST['delete_id'];
    $stmt = $con->prepare("DELETE FROM operator_logs WHERE id = :id");
    $stmt->execute([':id' => $delete_id]);
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?')); // refresh without params
    exit;
}

// --- Handle search ---
$search = $_GET['search'] ?? '';

// --- Handle sort ---
$sortable_columns = ['id','user','action','created_at'];
$sort = in_array($_GET['sort'] ?? '', $sortable_columns) ? $_GET['sort'] : 'created_at';
$order = ($_GET['order'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

// --- Fetch logs ---
if($search){
    $stmt = $con->prepare("SELECT * FROM operator_logs 
                           WHERE user LIKE :search OR action LIKE :search 
                           ORDER BY $sort $order");
    $stmt->execute([':search' => "%$search%"]);
} else {
    $stmt = $con->prepare("SELECT * FROM operator_logs ORDER BY $sort $order");
    $stmt->execute();
}
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper
function toggle_order($current_order){
    return $current_order === 'ASC' ? 'desc' : 'asc';
}
$current_order = $order;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Operator Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table thead th {
            background-color: #001f3f !important;
            color: white !important;
            cursor: pointer;
        }
        .main-content {
            padding-top: 60px;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="content-wrapper">
<div class="main-content">    
<div class="container-fluid ">
    <section class="content-header">
    <h2>Operator Logs</h2>

    <!-- Search Form -->
    <form class="row g-3 mb-3" method="GET">
        <div class="col-auto">
            <input type="text" name="search" class="form-control" 
                   placeholder="Search by User or Action" 
                   value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary mb-3">Search</button>
            <!-- FIX RESET: clear query params -->
            <a href="<?= strtok($_SERVER['REQUEST_URI'], '?') ?>" 
               class="btn btn-secondary mb-3" type="button">Reset</a>
        </div>
    </form>
    </section>

    <div class="card">
      <div class="card-body">
        <table id="logsTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th onclick="sortTable('id')">ID</th>
                    <th onclick="sortTable('user')">User</th>
                    <th onclick="sortTable('action')">Action</th>
                    <th>Details</th>
                    <th onclick="sortTable('created_at')">Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($logs): ?>
                    <?php foreach($logs as $log): ?>
                    <tr>
                        <td><?= $log['id'] ?></td>
                        <td><?= htmlspecialchars($log['user']) ?></td>
                        <td><?= htmlspecialchars($log['action']) ?></td>
                        <td><?= htmlspecialchars($log['details']) ?></td>
                        <td><?= $log['created_at'] ?></td>
                        <td>
                            <!-- Delete button triggers modal -->
                            <button class="btn btn-danger btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deleteModal" 
                                    data-id="<?= $log['id'] ?>">
                                Delete
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">No logs found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
      </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">Confirm Delete</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete this log?
          <input type="hidden" name="delete_id" id="delete_id">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="delete" class="btn btn-danger">Delete</button>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// DataTable
$(document).ready(function(){
    $('#logsTable').DataTable({
        "lengthMenu":[5,10,25,50,100],
        "pageLength":10,
        "order":[[0,"desc"]], // order by ID desc
        "columnDefs":[{"orderable":false,"targets":5}]
    });
});

// Sorting (server side)
function sortTable(column) {
    let params = new URLSearchParams(window.location.search);
    let currentSort = params.get('sort');
    let currentOrder = params.get('order') || 'desc';
    let newOrder = (currentSort === column && currentOrder === 'asc') ? 'desc' : 'asc';
    params.set('sort', column);
    params.set('order', newOrder);
    window.location.search = params.toString();
}

// Pass ID to modal
var deleteModal = document.getElementById('deleteModal');
deleteModal.addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget;
  var logId = button.getAttribute('data-id');
  document.getElementById('delete_id').value = logId;
});
</script>
</body>
</html>