<?php
include("config/connection.php");
include("config/site_css_links.php");
include("config/data_tables_css.php");
include("config/header.php");
include("config/sidebar.php");

// ================== FUNCTION TO FETCH DATA ==================
function fetchData($con, $query) {
    try {
        $stmt = $con->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// ================== DATA ==================
$fuelData     = fetchData($con, "SELECT SUM(fuel_liters) AS liters, DATE(log_date) AS log_date FROM fuel_logs GROUP BY DATE(log_date) ORDER BY log_date ASC");
$totalFuel    = fetchData($con, "SELECT SUM(fuel_liters) AS total FROM fuel_logs")[0]['total'] ?? 0;

$fleetData    = fetchData($con, "SELECT IFNULL(status,'Unknown') AS status, COUNT(*) AS count FROM vehicles GROUP BY status");
$totalVehicles= array_sum(array_column($fleetData,'count'));

$dispatchData = fetchData($con, "SELECT DATE(dispatch_date) AS dispatch_day, COUNT(*) AS count FROM dispatch_jobs GROUP BY DATE(dispatch_date) ORDER BY dispatch_day ASC");
$totalDispatch= fetchData($con, "SELECT COUNT(*) AS total FROM dispatch_jobs")[0]['total'] ?? 0;

$projectData  = fetchData($con, "SELECT IFNULL(status,'Unknown') AS status, COUNT(*) AS count FROM projects GROUP BY status");
$totalProject = fetchData($con, "SELECT COUNT(*) AS total FROM projects")[0]['total'] ?? 0;

$driverData   = fetchData($con, "SELECT driver_id, COUNT(*) AS count FROM driver_assignments GROUP BY driver_id");
$totalDrivers = fetchData($con, "SELECT COUNT(DISTINCT driver_id) AS total FROM driver_assignments")[0]['total'] ?? 0;
?>

<style>
body, html { margin:0; padding:0; padding-top: 30px; font-family: Arial,sans-serif; }

main.content {
    margin-left:250px;
    padding:20px;
    background:#f4f6f9;
    overflow:auto; /* scrollable */
    padding-bottom: 60px;
    padding-top: 60px;
    position: relative;
    z-index: 1;
}
.main-sidebar {
    z-index: 1015;
}
h1 { text-align:center; margin-bottom:30px; }

/* Cards */
.cards-row {
    display:grid;
    grid-template-columns: repeat(5, 1fr);
    gap:20px;
    margin-bottom:30px;
}
.card {
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 4px 10px rgba(0,0,0,0.1);
    text-align:center;
    font-size:16px;
}

/* Charts */
.charts-grid {
    display:grid;
    grid-template-columns: repeat(3, 1fr);
    gap:20px;
    margin-bottom:20px;
}
.charts-bottom {
    display:grid;
    grid-template-columns: repeat(2, 1fr);
    gap:20px;
    margin-bottom:20px;
    padding-bottom: 80px;
}
.chart-container {
    background:#fff;
    border-radius:12px;
    box-shadow:0 4px 10px rgba(0,0,0,0.1);
    padding:10px;
    display:flex;
    flex-direction:column;
}
.chart-container h3 { margin:5px 0; font-size:16px; }
.chart-container canvas {
    width:100% !important;
    height:300px !important;
}
</style>


<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <div class="content-wrapper">
<div class="content">
<div class="container-fluid">
    <section class="content-header">
      <h4>Welcome to Dashboard</h4>
        


    <!-- Top 5 Cards -->
    <div class="cards-row">
        <div class="card"><h2><?= $totalFuel ?></h2><p>Liters of Fuel</p></div>
        <div class="card"><h2><?= $totalVehicles ?></h2><p>Total Vehicles</p></div>
        <div class="card"><h2><?= $totalDispatch ?></h2><p>Dispatch Jobs</p></div>
        <div class="card"><h2><?= $totalProject ?></h2><p>Projects</p></div>
        <div class="card"><h2><?= $totalDrivers ?></h2><p>Drivers / Operators</p></div>
    </div>

    <!-- Top Row Charts -->
    <div class="charts-grid">
        <div class="chart-container"><h3>⛽ Fuel Usage</h3><canvas id="fuelChart"></canvas></div>
        <div class="chart-container"><h3>🚛 Fleet Status</h3><canvas id="fleetChart"></canvas></div>
        <div class="chart-container"><h3>🚚 Dispatch Jobs</h3><canvas id="dispatchChart"></canvas></div>
    </div>

    <!-- Bottom Row Charts -->
    <div class="charts-bottom">
        <div class="chart-container"><h3>📂 Projects</h3><canvas id="projectChart"></canvas></div>
        <div class="chart-container"><h3>👷 Driver Assignments</h3><canvas id="driverChart"></canvas></div>
    </div>
</div>
</div>
</div>
</div>
</body>

</section>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const fuelChart = new Chart(document.getElementById('fuelChart'), {
    type:'line',
    data:{
        labels: <?= json_encode(array_column($fuelData,'log_date')) ?>,
        datasets:[{ label:'Liters Used', data: <?= json_encode(array_column($fuelData,'liters')) ?>, borderColor:'blue', fill:false, tension:0.3 }]
    },
    options:{ maintainAspectRatio:false }
});

const fleetChart = new Chart(document.getElementById('fleetChart'), {
    type:'doughnut',
    data:{
        labels: <?= json_encode(array_column($fleetData,'status')) ?>,
        datasets:[{ data: <?= json_encode(array_column($fleetData,'count')) ?>, backgroundColor:['green','red','orange','blue','purple'] }]
    },
    options:{ maintainAspectRatio:false }
});

const dispatchChart = new Chart(document.getElementById('dispatchChart'), {
    type:'bar',
    data:{
        labels: <?= json_encode(array_column($dispatchData,'dispatch_day')) ?>,
        datasets:[{ label:'Dispatch Jobs', data: <?= json_encode(array_column($dispatchData,'count')) ?>, backgroundColor:'teal' }]
    },
    options:{ maintainAspectRatio:false }
});

const projectChart = new Chart(document.getElementById('projectChart'), {
    type:'bar',
    data:{
        labels: <?= json_encode(array_column($projectData,'status')) ?>,
        datasets:[{ label:'Projects', data: <?= json_encode(array_column($projectData,'count')) ?>, backgroundColor:'purple' }]
    },
    options:{ maintainAspectRatio:false }
});

const driverChart = new Chart(document.getElementById('driverChart'), {
    type:'pie',
    data:{
        labels: <?= json_encode(array_column($driverData,'driver_id')) ?>,
        datasets:[{ data: <?= json_encode(array_column($driverData,'count')) ?>, backgroundColor:['#ff6384','#36a2eb','#ffce56','#4bc0c0','#9966ff'] }]
    },
    options:{ maintainAspectRatio:false }
});
</script>
<?php include("config/footer.php");