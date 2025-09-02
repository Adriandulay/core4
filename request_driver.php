<?php
ob_start();
include("config/connection.php"); 
include("config/site_css_links.php");
include("config/data_tables_js.php");
include("config/header.php");
include("config/sidebar.php");

$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// --- ADD REQUEST ---
if (isset($_POST['send_request'])) {
    $role = $_POST['role'] ?? '';
    $skill_request = $_POST['skill_request'] ?? '';
    $requester = $_POST['requester'] ?? '';
    $request_date = date("Y-m-d H:i:s");

    $stmt = $con->prepare("INSERT INTO hr_requests (role, skill_request, requester, request_date, status) 
                           VALUES (:role, :skill_request, :requester, :request_date, 'Pending')");
    $stmt->execute([
        ':role' => $role,
        ':skill_request' => $skill_request,
        ':requester' => $requester,
        ':request_date' => $request_date
    ]);
}

// --- EDIT REQUEST ---
if (isset($_POST['edit_request'])) {
    $id = $_POST['request_id'];
    $role = $_POST['role'] ?? '';
    $skill_request = $_POST['skill_request'] ?? '';
    $requester = $_POST['requester'] ?? '';

    $stmt = $con->prepare("UPDATE hr_requests SET role=:role, skill_request=:skill_request, requester=:requester WHERE id=:id");
    $stmt->execute([
        ':role' => $role,
        ':skill_request' => $skill_request,
        ':requester' => $requester,
        ':id' => $id
    ]);
}

// --- DELETE REQUEST ---
if (isset($_POST['delete_request'])) {
    $id = $_POST['request_id'];
    $stmt = $con->prepare("DELETE FROM hr_requests WHERE id=:id");
    $stmt->execute([':id' => $id]);
}

// --- FETCH ALL REQUESTS ---
$requests = $con->query("SELECT * FROM hr_requests ORDER BY request_date DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
.main-content { padding-top: 75px; }
.table thead th { background-color: #001f3f; color: white; }
.modal-header { background-color: #0d47a1; color: white; }
</style>

<div class="content-wrapper">
    <div class="main-content container-fluid">
        <h3>Request Skilled Driver / Operator</h3>
        <button class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#requestModal">New Request</button>

        <div class="card">
            <div class="card-body">
                <table class="table table-bordered" id="requestsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Requester</th>
                            <th>Role</th>
                            <th>Skill Requested</th>
                            <th>Status</th>
                            <th>Request Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($requests as $i => $r): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= htmlspecialchars($r['requester']) ?></td>
                            <td><?= htmlspecialchars($r['role']) ?></td>
                            <td><?= htmlspecialchars($r['skill_request']) ?></td>
                            <td><?= htmlspecialchars($r['status']) ?></td>
                            <td><?= htmlspecialchars($r['request_date']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning editBtn" 
                                    data-id="<?= $r['id'] ?>"
                                    data-requester="<?= htmlspecialchars($r['requester']) ?>"
                                    data-role="<?= $r['role'] ?>"
                                    data-skill="<?= htmlspecialchars($r['skill_request']) ?>"
                                    data-bs-toggle="modal" data-bs-target="#editModal">
                                    Edit
                                </button>
                                <button class="btn btn-sm btn-danger deleteBtn" 
                                    data-id="<?= $r['id'] ?>" 
                                    data-bs-toggle="modal" data-bs-target="#deleteModal">
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

<!-- Add Request Modal -->
<div class="modal fade" id="requestModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">New Request to HR</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label>Requester</label>
        <input type="text" name="requester" class="form-control" placeholder="Your Name / Department" required>
        <label>Role</label>
        <select name="role" class="form-select" required>
            <option value="">Select Role</option>
            <option value="Driver">Driver</option>
            <option value="Operator">Operator</option>
        </select>
        <label>Skill Request</label>
        <input type="text" name="skill_request" class="form-control" placeholder="Describe required skills" required>
      </div>
      <div class="modal-footer">
        <button type="submit" name="send_request" class="btn btn-primary">Send Request</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Request Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Request</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="request_id" id="edit_id">
        <label>Requester</label>
        <input type="text" name="requester" id="edit_requester" class="form-control" required>
        <label>Role</label>
        <select name="role" id="edit_role" class="form-select" required>
            <option value="Driver">Driver</option>
            <option value="Operator">Operator</option>
        </select>
        <label>Skill Request</label>
        <input type="text" name="skill_request" id="edit_skill" class="form-control" required>
      </div>
      <div class="modal-footer">
        <button type="submit" name="edit_request" class="btn btn-warning">Update Request</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title text-white">Delete Request</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this request?
        <input type="hidden" name="request_id" id="delete_id">
      </div>
      <div class="modal-footer">
        <button type="submit" name="delete_request" class="btn btn-danger">Yes, Delete</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('edit_id').value = this.dataset.id;
        document.getElementById('edit_requester').value = this.dataset.requester;
        document.getElementById('edit_role').value = this.dataset.role;
        document.getElementById('edit_skill').value = this.dataset.skill;
    });
});

document.querySelectorAll('.deleteBtn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('delete_id').value = this.dataset.id;
    });
});

$(document).ready(function(){
    $('#requestsTable').DataTable({
        "lengthMenu":[5,10,25,50,100],
        "pageLength":10,
        "order":[[0,"desc"]],
        "columnDefs":[{"orderable":false,"targets":6}]
    });
});
</script>

<?php include("config/footer.php"); ?>