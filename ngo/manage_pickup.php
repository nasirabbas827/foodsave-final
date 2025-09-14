<?php
session_start();
include('../config.php');

if (!isset($_SESSION["id"]) || $_SESSION["user_type"] !== 'ngo') {
    header("location: ../index.php");
    exit;
}

$ngo_id = $_SESSION["id"];
$request_id = intval($_GET['request_id'] ?? 0);

// Fetch existing pickup if any
$stmt = $conn->prepare("SELECT * FROM pickups WHERE request_id = ?");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$pickup = $stmt->get_result()->fetch_assoc();

// Handle form submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $driver_name = $_POST['driver_name'];
    $driver_contact = $_POST['driver_contact'];
    $scheduled_pickup = $_POST['scheduled_pickup'];
    $pickup_notes = $_POST['pickup_notes'];

    if ($pickup) {
        // Update existing pickup
        $sql = "UPDATE pickups SET driver_name=?, driver_contact=?, scheduled_pickup=?, pickup_notes=? WHERE request_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $driver_name, $driver_contact, $scheduled_pickup, $pickup_notes, $request_id);
    } else {
        // Insert new pickup
        $sql = "INSERT INTO pickups (request_id, driver_name, driver_contact, scheduled_pickup, pickup_notes) VALUES (?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $request_id, $driver_name, $driver_contact, $scheduled_pickup, $pickup_notes);
    }
    $stmt->execute();
    header("Location: ngo_requests.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Pickup</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="../css/style.css" />
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
        <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
    <h2>Manage Pickup</h2>
    <form method="post">
        <div class="form-group">
            <label>Driver Name</label>
            <input type="text" name="driver_name" class="form-control" value="<?= htmlspecialchars($pickup['driver_name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Driver Contact</label>
            <input type="text" name="driver_contact" class="form-control" value="<?= htmlspecialchars($pickup['driver_contact'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Scheduled Pickup</label>
            <input type="datetime-local" name="scheduled_pickup" class="form-control" value="<?= isset($pickup['scheduled_pickup']) ? date('Y-m-d\TH:i', strtotime($pickup['scheduled_pickup'])) : '' ?>" required>
        </div>
        <div class="form-group">
            <label>Pickup Notes</label>
            <textarea name="pickup_notes" class="form-control"><?= htmlspecialchars($pickup['pickup_notes'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-success">Save Pickup</button>
        <a href="ngo_requests.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</div>
</div>
</body>
</html>
