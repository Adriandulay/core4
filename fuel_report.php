<?php
include("config/connection.php");
include("config/site_css_links.php");
include("config/data_tables_css.php");
include("config/header.php");
include("config/sidebar.php");

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date   = $_GET['end_date'] ?? date('Y-m-d');

// PDO: Fuel report per vehicle
$stmt = $con->prepare("SELECT v.vehicle_name,  SUM(f.fuel_liters) AS total_liters,  SUM(f.cost) AS total_cost,  ROUND(AVG(f.cost / f.fuel_liters), 2) AS avg_price_per_liter,  COUNT(f.id) AS total_entries FROM fuel_logs f JOIN vehicles v ON f.vehicle_id = v.id WHERE f.fuel_date BETWEEN :start_date AND :end_date GROUP BY f.vehicle_id ORDER BY total_liters DESC");
$stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
$report_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// PDO: Totals
$totals_stmt = $con->prepare("SELECT SUM(fuel_liters) AS total_liters, SUM(cost) AS total_cost FROM fuel_logs WHERE fuel_date BETWEEN :start_date AND :end_date");
$totals_stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
$totals = $totals_stmt->fetch(PDO::FETCH_ASSOC);

$total_liters = $totals['total_liters'] ?? 0;
$total_cost   = $totals['total_cost'] ?? 0;
$avg_price    = ($total_liters > 0) ? $total_cost / $total_liters : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fuel Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        
        .table-darkblue th {
            background-color: #1b263b !important;
            color: #ffffff;
            padding-top: 0.35rem;
            padding-bottom: 0.35rem;
            font-size: 0.98rem;
            height: 50px;
        }
        h2.compact-title {
            font-size: 1.5rem;
            margin-bottom: 1.2rem;
            margin-top: 0.5rem;
            font-weight: 600;
            padding-top: 50px;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <div class="content-wrapper">
        <section class="main-content">
            <div class="container-fluid">
                <section class="content-header">
                <h2 class="compact-title">Fuel Consumption Report</h2>
                </section>
                <form method="get" class="row g-3 mb-4 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
                <div class="mb-4">
                    <div class="alert alert-info">
                        <strong>Filtered Totals:</strong><br>
                        Total Liters: <strong><?= number_format($total_liters, 2) ?></strong><br>
                        Total Cost: <strong>₱<?= number_format($total_cost, 2) ?></strong><br>
                        Average Price/Liter: <strong>₱<?= number_format($avg_price, 2) ?></strong>
                    </div>
                </div>
                <div class="card">
                 <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-darkblue table-striped table-sm dataTable" style="height:130px;">
                        <thead class="table-darkblue">
                            <tr>
                                <th>Vehicle</th>
                                <th>Total Entries</th>
                                <th>Total Liters</th>
                                <th>Total Cost (₱)</th>
                                <th>Average Price/Liter (₱)</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $grand_liters = $grand_cost = 0;
                        foreach ($report_rows as $row) {
                            $grand_liters += $row['total_liters'];
                            $grand_cost   += $row['total_cost'];
                            echo "<tr>
                                    <td>" . htmlspecialchars($row['vehicle_name']) . "</td>
                                    <td>{$row['total_entries']}</td>
                                    <td>" . number_format($row['total_liters'], 2) . "</td>
                                    <td>₱" . number_format($row['total_cost'], 2) . "</td>
                                    <td>₱" . number_format($row['avg_price_per_liter'], 2) . "</td>
                                </tr>";
                        }
                        ?>
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold bg-light">
                                <td colspan="2">TOTAL</td>
                                <td><?= number_format($grand_liters, 2) ?></td>
                                <td>₱<?= number_format($grand_cost, 2) ?></td>
                                <td>-</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>
<?php
include("config/footer.php");
include("config/site_js_links.php");
include("config/data_tables_js.php");
?>
</body>
</html>