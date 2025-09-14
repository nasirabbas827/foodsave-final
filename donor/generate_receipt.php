<?php
session_start();
include('../config.php');

if (!isset($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$request_id = intval($_GET['request_id'] ?? 0);
if ($request_id <= 0) {
    die("Invalid request.");
}

// Fetch request details + NGO + Donation + Donor
$sql = "
    SELECT dr.*, 
           fd.title AS donation_title, fd.category, fd.quantity AS donation_quantity, fd.expiration_date,
           donor.name AS donor_name, donor.email AS donor_email, donor.phone AS donor_phone,
           ngo.name AS ngo_name, ngo.email AS ngo_email, ngo.phone AS ngo_phone
    FROM donation_requests dr
    JOIN food_donations fd ON dr.food_id = fd.donation_id
    JOIN users donor ON fd.donor_id = donor.id
    JOIN users ngo ON dr.ngo_id = ngo.id
    WHERE dr.request_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$request = $stmt->get_result()->fetch_assoc();

if (!$request) {
    die("Request not found.");
}

// Fetch pickup details
$sql = "SELECT * FROM pickups WHERE request_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$pickup = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Donation Receipt</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css" />
    <style>
        .receipt-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
            font-size: 16px;
            line-height: 24px;
        }
        .receipt-box h2 {
            text-align: center;
            margin-bottom: 30px;
        }
        .table td, .table th {
            vertical-align: middle;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <div class="receipt-box bg-white">
        <h2>Donation Receipt</h2>

        <h5>Donation Details</h5>
        <table class="table table-bordered">
            <tr><th>Donation</th><td><?= htmlspecialchars($request['donation_title']); ?></td></tr>
            <tr><th>Category</th><td><?= htmlspecialchars($request['category']); ?></td></tr>
            <tr><th>Total Quantity</th><td><?= htmlspecialchars($request['donation_quantity']); ?></td></tr>
            <tr><th>Requested Quantity</th><td><?= htmlspecialchars($request['requested_quantity']); ?></td></tr>
            <tr><th>Expiration</th><td><?= htmlspecialchars($request['expiration_date']); ?></td></tr>
        </table>

        <h5>NGO Details</h5>
        <table class="table table-bordered">
            <tr><th>Name</th><td><?= htmlspecialchars($request['ngo_name']); ?></td></tr>
            <tr><th>Email</th><td><?= htmlspecialchars($request['ngo_email']); ?></td></tr>
            <tr><th>Phone</th><td><?= htmlspecialchars($request['ngo_phone']); ?></td></tr>
        </table>

        <h5>Donor Details</h5>
        <table class="table table-bordered">
            <tr><th>Name</th><td><?= htmlspecialchars($request['donor_name']); ?></td></tr>
            <tr><th>Email</th><td><?= htmlspecialchars($request['donor_email']); ?></td></tr>
            <tr><th>Phone</th><td><?= htmlspecialchars($request['donor_phone']); ?></td></tr>
        </table>

        <h5>Request Info</h5>
        <table class="table table-bordered">
            <tr><th>Status</th><td><?= ucfirst($request['status']); ?></td></tr>
            <tr><th>Request Date</th><td><?= $request['request_date']; ?></td></tr>
            <tr><th>Proposed Pickup Time</th><td><?= $request['proposed_pickup_time']; ?></td></tr>
            <tr><th>Message</th><td><?= nl2br(htmlspecialchars($request['message'])); ?></td></tr>
        </table>

        <?php if ($pickup): ?>
        <h5>Pickup Details</h5>
        <table class="table table-bordered">
            <tr><th>Driver Name</th><td><?= htmlspecialchars($pickup['driver_name']); ?></td></tr>
            <tr><th>Driver Contact</th><td><?= htmlspecialchars($pickup['driver_contact']); ?></td></tr>
            <tr><th>Scheduled Pickup</th><td><?= $pickup['scheduled_pickup']; ?></td></tr>
            <tr><th>Status</th><td><?= ucfirst($pickup['status']); ?></td></tr>
            <tr><th>Notes</th><td><?= nl2br(htmlspecialchars($pickup['pickup_notes'])); ?></td></tr>
        </table>
        <?php else: ?>
            <div class="alert alert-warning">No pickup scheduled yet for this request.</div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <button onclick="window.print()" class="btn btn-primary">Print Receipt</button>
        </div>
    </div>
</div>
</body>
</html>
