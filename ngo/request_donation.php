<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || $_SESSION["user_type"] !== 'ngo') {
    header("location: ../index.php");
    exit;
}

$ngo_id = $_SESSION["id"];
$food_id = intval($_GET['food_id']);
$msg = "";

// Fetch donation details
$sql = "SELECT * FROM food_donations WHERE donation_id = ? AND status = 'available'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $food_id);
$stmt->execute();
$result = $stmt->get_result();
$donation = $result->fetch_assoc();

if (!$donation) {
    die("Donation not found or not available.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $requested_quantity = intval($_POST['requested_quantity']);
    $proposed_pickup_time = $_POST['proposed_pickup_time'];
    $message = trim($_POST['message']);

    $sql = "INSERT INTO donation_requests (ngo_id, food_id, requested_quantity, proposed_pickup_time, message) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiss", $ngo_id, $food_id, $requested_quantity, $proposed_pickup_time, $message);

    if ($stmt->execute()) {
        $msg = '<div class="alert alert-success">Request submitted successfully!</div>';
    } else {
        $msg = '<div class="alert alert-danger">Error: ' . $conn->error . '</div>';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Request Donation</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
        <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
    <h2>Request Donation: <?= htmlspecialchars($donation['title']); ?></h2>
    <?= $msg; ?>
    <form method="post">
        <div class="form-group">
            <label>Requested Quantity</label>
            <input type="number" name="requested_quantity" class="form-control" min="1" required>
        </div>
        <div class="form-group">
            <label>Proposed Pickup Time</label>
            <input type="datetime-local" name="proposed_pickup_time" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Message (Optional)</label>
            <textarea name="message" class="form-control" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Request</button>
        <a href="ngo_dashboard.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</div>
</div>
</body>
</html>
