<?php 
  //if(!(isset($_SESSION['user_id']))) {
  //header("location:index.php");
  //exit;
  //}
  // Delete this comment after the account creation in users.php
?>
<aside class="main-sidebar sidebar-dark-primary bg-black elevation-4">
    <a href="./" class="brand-link logo-switch" style="background-color: black;">
      <h4 class="brand-image-xl logo-xs mb-0 text-center"><b>CTMS</b></h4>
      <h4 class="brand-image-xl logo-xl mb-0 text-center">Crane<b>SYSTEM</b></h4>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img 
          src="user_images/<?php echo $_SESSION['profile_picture'];?>" class="img-circle elevation-2" alt="User Image" />
        </div>
        <div class="info">
          <a href="#" class="d-block"><?php echo $_SESSION['display_name'];?></a>
        </div>
      </div>

      
      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          <li class="nav-item" id="mnu_dashboard">
            <a href="dashboard.php" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Dashboard
              </p>
            </a>
          </li>

          
        <li class="nav-item" id="mnu_patients">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-gas-pump"></i>
              <p>
                Fuel Management
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="fuel_management.php" class="nav-link" 
                id="mi_new_prescription">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Fuel logs</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="fuel_report.php"  class="nav-link"
                id="mi_fuel_history">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Fuel Consumption Report</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="fuel_records.php"  class="nav-link"
                id="mi_fuel_history">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Fuel Records</p>
                </a>
              </li>
              </ul>
              </li>



          <li class="nav-item" id="mnu_medicines">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-truck"></i>
              <p>
                Fleet Manangement
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="fleet_dashboard.php" class="nav-link" 
                id="mi_medicines">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Fleet Availability</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="vehicle_list.php" class="nav-link" 
                id="mi_medicine_details">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Vehicle List</p>
                </a>
              </li>
                     
              <li class="nav-item">
                <a href="maintenance.php" class="nav-link" 
                id="mi_medicine_details">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Maintenance Records</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="maintenance_schedule.php" class="nav-link" 
                id="mi_medicine_details">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Maintenance Schedule</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="request_budget.php" class="nav-link" 
                id="mi_medicine_details">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Request Budget</p>
                </a>
              </li>
             <li class="nav-item">
                <a href="vehicle_tracking.php" class="nav-link" 
                id="mi_medicine_details">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Tracking</p>
                </a>
              </li>
            </ul>
          </li>

          <li class="nav-item" id="mnu_reports">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-calendar-alt"></i>
              <p>
               Dispatch Job & Scheduling
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="dispatch_scheduling.php" class="nav-link" 
                id="mi_reports">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Dispatch Scheduling</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="active_jobs.php" class="nav-link" 
                id="mi_reports">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Active Jobs</p>
                </a>
                <li class="nav-item">
                <a href="dispatch_history.php" class="nav-link" 
                id="mi_reports">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Dispatch History</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="dispatch_jobs.php" class="nav-link" 
                id="mi_reports">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Dispatch job</p>
                </a>
              </li>
            </ul>
          </li> 
		  
		  
		   <li class="nav-item" id="mnu_reports">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-project-diagram"></i>
              <p>
               Project Management
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="project_monitoring.php" class="nav-link" 
                id="mi_reports">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Project Monitoring</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="active_projects.php" class="nav-link" 
                id="mi_reports">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Active Projects</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="project_history.php" class="nav-link" 
                id="mi_reports">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Project History</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="project_share.php" class="nav-link" 
                id="mi_reports">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Project Data</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="project_tasks.php" class="nav-link" 
                id="mi_reports">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Send Task</p>
                </a>
              </li>
            </ul>
          </li> 
		  
		  <li class="nav-item" id="mnu_reports">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-id-card"></i>
              <p>
               Assign Driver/Operator
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="assign_driver.php" class="nav-link" 
                id="mi_reports">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Assign Now</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="driver_list.php" class="nav-link" 
                id="mi_reports">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Driver List</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="Operator_logs.php" class="nav-link" 
                id="mi_reports">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Operator Logs</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="equipment.php" class="nav-link" 
                id="mi_reports">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Equipments</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="request_driver.php" class="nav-link" 
                id="mi_reports">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Request Driver</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="request_equipment.php" class="nav-link" 
                id="mi_reports">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Request Equipment</p>
                </a>
              </li>
             </li> 
             </ul>
</li>


          <li class="nav-item" id="mnu_users">
            <a href="users.php" class="nav-link">
              <i class="nav-icon fa fa-users"></i>
              <p>
                Users
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="logout.php" class="nav-link">
              <i class="nav-icon fa fa-sign-out-alt"></i>
              <p>
                Logout
              </p>
            </a>
          </li>

        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>