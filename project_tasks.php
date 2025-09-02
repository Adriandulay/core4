<?php
ob_start();
include("config/connection.php"); 
include("config/site_css_links.php");
include("config/data_tables_css.php"); 
include("config/header.php"); 
include("config/sidebar.php"); 

// ================== FETCH TASKS LINKED TO CLIENTS ==================
$sql = "SELECT 
            t.id as task_id, 
            t.project_id,   -- important for edit modal
            t.task_name, 
            t.assigned_to, 
            t.due_date, 
            t.status,
            p.project_name, 
            c.client_name,
            c.email as client_email
        FROM tasks t
        LEFT JOIN projects p ON t.project_id = p.id
        LEFT JOIN core1_clients c ON p.client_id = c.id";
$tasks = $con->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// ================== FETCH PROJECTS ==================
$projects = $con->query("SELECT id, project_name FROM projects")->fetchAll(PDO::FETCH_ASSOC);

// ================== ADD TASK ==================
if (isset($_POST['add_task'])) {
    $stmt = $con->prepare("INSERT INTO tasks (project_id, task_name, assigned_to, due_date, status) 
                           VALUES (?,?,?,?,?)");
    $stmt->execute([
        $_POST['project_id'],
        $_POST['task_name'],
        $_POST['assigned_to'],
        $_POST['due_date'],
        $_POST['status']
    ]);
    header("Location: project_tasks.php");
    exit;
}

// ================== UPDATE TASK ==================
if (isset($_POST['update_task'])) {
    $stmt = $con->prepare("UPDATE tasks 
                           SET project_id=?, task_name=?, assigned_to=?, due_date=?, status=? 
                           WHERE id=?");
    $stmt->execute([
        $_POST['project_id'],
        $_POST['task_name'],
        $_POST['assigned_to'],
        $_POST['due_date'],
        $_POST['status'],
        $_POST['task_id']
    ]);
    header("Location: project_tasks.php");
    exit;
}

// ================== DELETE TASK ==================
if (isset($_POST['delete_task'])) {
    $stmt = $con->prepare("DELETE FROM tasks WHERE id=?");
    $stmt->execute([$_POST['task_id']]);
    header("Location: project_tasks.php");
    exit;
}

// ================== SEND TASK TO CLIENT ==================
if (isset($_POST['send_task'])) {
    $stmt = $con->prepare("UPDATE tasks SET status='Sent to Client' WHERE id=?");
    $stmt->execute([$_POST['task_id']]);
    header("Location: project_tasks.php");
    exit;
}
?>
<style>
    .main-content {
        padding-top: 80px;
    }
    .table thead th {
        background-color: #001f3f !important; /* navy blue */
            color: white;
    }
    .modal-header { background-color: #0d47a1; color: white;
    
    }
    </style>
    <body class="hold-transition sidebar-mini layout-fixed">
        <div class="wrapper">
           <div class="content-wrapper">
             <div class="main-content">
              <div class="container-fluid">
      <h1>Project Tasks</h1>
      <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addTaskModal">+ Add Task</button>
    </div>
  </div>
  </section>

  <div class="card">
    <div class="card-body">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Task</th>
            <th>Project</th>
            <th>Client</th>
            <th>Assigned To</th>
            <th>Due Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tasks as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['task_name']) ?></td>
            <td><?= htmlspecialchars($row['project_name']) ?></td>
            <td><?= htmlspecialchars($row['client_name']) ?></td>
            <td><?= htmlspecialchars($row['assigned_to']) ?></td>
            <td><?= htmlspecialchars($row['due_date']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td>
              <!-- Edit -->
              <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editTask<?= $row['task_id'] ?>">Edit</button>
              <!-- Delete -->
              <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteTask<?= $row['task_id'] ?>">Delete</button>
              <!-- Send -->
              <form method="post" style="display:inline;">
                <input type="hidden" name="task_id" value="<?= $row['task_id'] ?>">
                <button type="submit" name="send_task" class="btn btn-info btn-sm">Send</button>
              </form>
            </td>
          </tr>

          <!-- Edit Modal -->
          <div class="modal fade" id="editTask<?= $row['task_id'] ?>" tabindex="-1">
            <div class="modal-dialog">
              <div class="modal-content bg-white">
                <div class="modal-header">
                  <h5 class="modal-title">Edit Task</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                  <div class="modal-body">
                    <input type="hidden" name="task_id" value="<?= $row['task_id'] ?>">
                    <div class="mb-3">
                      <label>Project</label>
                      <select name="project_id" class="form-control" required>
                        <?php foreach ($projects as $proj): 
                          $selected = $proj['id'] == $row['project_id'] ? "selected" : ""; ?>
                          <option value="<?= $proj['id'] ?>" <?= $selected ?>><?= $proj['project_name'] ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="mb-3"><label>Task Name</label>
                      <input type="text" name="task_name" class="form-control" value="<?= htmlspecialchars($row['task_name']) ?>" required>
                    </div>
                    <div class="mb-3"><label>Assigned To</label>
                      <input type="text" name="assigned_to" class="form-control" value="<?= htmlspecialchars($row['assigned_to']) ?>" required>
                    </div>
                    <div class="mb-3"><label>Due Date</label>
                      <input type="date" name="due_date" class="form-control" value="<?= htmlspecialchars($row['due_date']) ?>" required>
                    </div>
                    <div class="mb-3"><label>Status</label>
                      <select name="status" class="form-control" required>
                        <option <?= $row['status']=="Pending"?"selected":"" ?>>Pending</option>
                        <option <?= $row['status']=="In Progress"?"selected":"" ?>>In Progress</option>
                        <option <?= $row['status']=="Completed"?"selected":"" ?>>Completed</option>
                        <option <?= $row['status']=="Sent to Client"?"selected":"" ?>>Sent to Client</option>
                      </select>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" name="update_task" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- Delete Modal -->
          <div class="modal fade" id="deleteTask<?= $row['task_id'] ?>" tabindex="-1">
            <div class="modal-dialog">
              <div class="modal-content bg-white">
                <div class="modal-header">
                  <h5 class="modal-title">Delete Task</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                  <div class="modal-body">
                    <p>Are you sure you want to delete this task?</p>
                    <input type="hidden" name="task_id" value="<?= $row['task_id'] ?>">
                  </div>
                  <div class="modal-footer">
                    <button type="submit" name="delete_task" class="btn btn-danger">Delete</button>
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

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content bg-white">
      <div class="modal-header">
        <h5 class="modal-title">Add Task</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post">
        <div class="modal-body">
          <div class="mb-3">
            <label>Project</label>
            <select name="project_id" class="form-control" required>
              <?php foreach ($projects as $proj): ?>
                <option value="<?= $proj['id'] ?>"><?= $proj['project_name'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="mb-3"><label>Task Name</label>
            <input type="text" name="task_name" class="form-control" required>
          </div>
          <div class="mb-3"><label>Assigned To</label>
            <input type="text" name="assigned_to" class="form-control" required>
          </div>
          <div class="mb-3"><label>Due Date</label>
            <input type="date" name="due_date" class="form-control" required>
          </div>
          <div class="mb-3"><label>Status</label>
            <select name="status" class="form-control" required>
              <option>Pending</option>
              <option>In Progress</option>
              <option>Completed</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="add_task" class="btn btn-success">Add</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ✅ Bootstrap 5 JS (needed for modals to work) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  $(document).ready(function(){
      // ✅ Fixed DataTable to target driversTable
      $('#driversTable').DataTable({
          "lengthMenu":[5,10,25,50,100],
          "pageLength":10,
          "order":[[0,"desc"]],
          "columnDefs":[{"orderable":false,"targets":7}]
      });
  });
</script>
<?php
include("config/footer.php");
include("config/site_js_links.php");
include("config/data_tables_js.php");
?>
<?php ob_end_flush(); ?>