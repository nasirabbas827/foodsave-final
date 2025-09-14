<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

$request_id = isset($_GET['request_id']) ? intval($_GET['request_id']) : 0;

$sql = "SELECT * FROM pickups WHERE request_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();
$pickup = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pickup Details</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
<?php include('admin_navbar.php'); ?>
<div class="container mt-5">
    <h2 class="mb-4">Pickup Details</h2>
    <?php if ($pickup): ?>
        <table class="table table-bordered">
            <tr><th>Driver Name</th><td><?= htmlspecialchars($pickup['driver_name']); ?></td></tr>
            <tr><th>Driver Contact</th><td><?= htmlspecialchars($pickup['driver_contact']); ?></td></tr>
            <tr><th>Scheduled Pickup</th><td><?= $pickup['scheduled_pickup']; ?></td></tr>
            <tr><th>Status</th><td><?= ucfirst($pickup['status']); ?></td></tr>
            <tr><th>Notes</th><td><?= nl2br(htmlspecialchars($pickup['pickup_notes'])); ?></td></tr>
            <tr><th>Created</th><td><?= $pickup['created_at']; ?></td></tr>
            <tr><th>Updated</th><td><?= $pickup['updated_at']; ?></td></tr>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No pickup found for this request.</div>
    <?php endif; ?>
    <a href="view_donation_requests.php" class="btn btn-secondary">Back</a>
</div>
</body>
</html>
