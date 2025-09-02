<?php
include("config/connection.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from modal form
    $vehicle_id    = $_POST['vehicle_id'] ?? null;
    $driver_id     = $_POST['driver_id'] ?? null;
    $project_id    = $_POST['project_id'] ?? null;
    $dispatch_date = $_POST['dispatch_date'] ?? null;
    $schedule_time = $_POST['schedule_time'] ?? null;
    $remarks       = $_POST['remarks'] ?? '';

    // Basic validation
    if ($vehicle_id && $driver_id && $project_id && $dispatch_date && $schedule_time) {
        try {
            $stmt = $con->prepare("
                INSERT INTO dispatch_jobs 
                    (vehicle_id, driver_id, project_id, dispatch_date, schedule_time, status, remarks) 
                VALUES 
                    (:vehicle_id, :driver_id, :project_id, :dispatch_date, :schedule_time, 'Scheduled', :remarks)
            ");

            $stmt->execute([
                ':vehicle_id'    => $vehicle_id,
                ':driver_id'     => $driver_id,
                ':project_id'    => $project_id,
                ':dispatch_date' => $dispatch_date,
                ':schedule_time' => $schedule_time,
                ':remarks'       => $remarks
            ]);

            // Redirect back to dispatch scheduling page
            header("Location: dispatch_scheduling.php?success=1");
            exit;

        } catch (PDOException $e) {
            die("Database Error: " . $e->getMessage());
        }
    } else {
        // Missing required field
        header("Location: dispatch_scheduling.php?error=1");
        exit;
    }
} else {
    // Direct access without POST
    header("Location: dispatch_scheduling.php");
    exit;
}
?>