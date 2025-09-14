<?php
session_start();
include('../config.php');

if (!isset($_SESSION["id"]) || $_SESSION["user_type"] !== 'donor') {
    header("location: ../index.php");
    exit;
}

$donor_id = $_SESSION["id"];
$request_id = isset($_GET['request_id']) ? intval($_GET['request_id']) : 0;
$msg = "";

// Verify request belongs to donor
$sql = "
    SELECT dr.*, fd.title AS donation_title, u.name AS ngo_name
    FROM donation_requests dr
    JOIN food_donations fd ON dr.food_id = fd.donation_id
    JOIN users u ON dr.ngo_id = u.id
    WHERE dr.request_id = ? AND fd.donor_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $request_id, $donor_id);
$stmt->execute();
$request = $stmt->get_result()->fetch_assoc();

if (!$request) {
    die("Invalid request.");
}

// Handle pickup form submission
if (isset($_POST['save_pickup'])) {
    $driver_name = $_POST['driver_name'];
    $driver_contact = $_POST['driver_contact'];
    $scheduled_pickup = $_POST['scheduled_pickup'];
    $status = $_POST['status'];
    $pickup_notes = $_POST['pickup_notes'];

    // Check if pickup exists
    $check = $conn->prepare("SELECT pickup_id FROM pickups WHERE request_id=?");
    $check->bind_param("i", $request_id);
    $check->execute();
    $exists = $check->get_result()->fetch_assoc();

    if ($exists) {
        $update = $conn->prepare("
            UPDATE pickups
            SET driver_name=?, driver_contact=?, scheduled_pickup=?, status=?, pickup_notes=?, updated_at=NOW()
            WHERE request_id=?
        ");
        $update->bind_param("sssssi", $driver_name, $driver_contact, $scheduled_pickup, $status, $pickup_notes, $request_id);
        if ($update->execute()) {
            $msg = '<div class="alert alert-success">Pickup updated successfully.</div>';
        }
    } else {
        $insert = $conn->prepare("
            INSERT INTO pickups (request_id, driver_name, driver_contact, scheduled_pickup, status, pickup_notes, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $insert->bind_param("isssss", $request_id, $driver_name, $driver_contact, $scheduled_pickup, $status, $pickup_notes);
        if ($insert->execute()) {
            $msg = '<div class="alert alert-success">Pickup created successfully.</div>';
        }
    }
}

// Fetch pickup if exists
$pickup = null;
$stmt2 = $conn->prepare("SELECT * FROM pickups WHERE request_id=?");
$stmt2->bind_param("i", $request_id);
$stmt2->execute();
$pickup = $stmt2->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pickup Management</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="../css/style.css" />
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
        <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">

    <h2>Pickup Management for Request #<?= $request_id ?></h2>
    <p><strong>Donation:</strong> <?= htmlspecialchars($request['donation_title']); ?></p>
    <p><strong>NGO:</strong> <?= htmlspecialchars($request['ngo_name']); ?></p>
    <?= $msg; ?>

    <form method="post">
        <div class="form-group">
            <label>Driver Name</label>
            <input type="text" name="driver_name" class="form-control" value="<?= $pickup['driver_name'] ?? ''; ?>" required>
        </div>
        <div class="form-group">
            <label>Driver Contact</label>
            <input type="text" name="driver_contact" class="form-control" value="<?= $pickup['driver_contact'] ?? ''; ?>" required>
        </div>
        <div class="form-group">
            <label>Scheduled Pickup</label>
            <input type="datetime-local" name="scheduled_pickup" class="form-control"
                   value="<?= isset($pickup['scheduled_pickup']) ? date('Y-m-d\TH:i', strtotime($pickup['scheduled_pickup'])) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control">
                <?php
                $statuses = ['scheduled','in_progress','completed','cancelled'];
                foreach ($statuses as $s) {
                    $sel = ($pickup && $pickup['status'] === $s) ? 'selected' : '';
                    echo "<option value='$s' $sel>" . ucfirst($s) . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label>Pickup Notes</label>
            <textarea name="pickup_notes" class="form-control"><?= $pickup['pickup_notes'] ?? ''; ?></textarea>
        </div>
        <button type="submit" name="save_pickup" class="btn btn-primary">Save Pickup</button>
    </form>
</div>
</div>
</div>
</body>
</html>
