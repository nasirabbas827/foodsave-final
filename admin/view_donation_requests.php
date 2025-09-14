<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Fetch requests
$sql = "SELECT dr.*, 
               fd.title AS donation_title, 
               u1.name AS ngo_name, u1.email AS ngo_email,
               u2.name AS donor_name, u2.email AS donor_email
        FROM donation_requests dr
        JOIN food_donations fd ON dr.food_id = fd.donation_id
        JOIN users u1 ON dr.ngo_id = u1.id
        JOIN users u2 ON fd.donor_id = u2.id";

if ($status_filter && in_array($status_filter, ['pending','accepted','rejected','cancelled'])) {
    $sql .= " WHERE dr.status = ?";
}

$sql .= " ORDER BY dr.request_date DESC";

$stmt = $conn->prepare($sql);
if ($status_filter && in_array($status_filter, ['pending','accepted','rejected','cancelled'])) {
    $stmt->bind_param("s", $status_filter);
}
$stmt->execute();
$requests = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Donation Requests</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">Donation Requests</h2>

    <!-- Filter -->
    <form method="get" class="form-inline mb-4">
        <label class="mr-2">Filter by Status:</label>
        <select name="status" class="form-control mr-2">
            <option value="">All</option>
            <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="accepted" <?= $status_filter === 'accepted' ? 'selected' : '' ?>>Accepted</option>
            <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
        <button type="submit" class="btn btn-primary">Apply</button>
        <a href="view_donation_requests.php" class="btn btn-secondary ml-2">Reset</a>
    </form>

    <?php if ($requests->num_rows > 0): ?>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Request ID</th>
                    <th>NGO</th>
                    <th>Donor</th>
                    <th>Donation</th>
                    <th>Requested Qty</th>
                    <th>Pickup Time</th>
                    <th>Status</th>
                    <th>Requested On</th>
                    <th>Pickup</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $requests->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['request_id'] ?></td>
                    <td>
                        <?= htmlspecialchars($row['ngo_name']); ?><br>
                        <small><?= htmlspecialchars($row['ngo_email']); ?></small>
                    </td>
                    <td>
                        <?= htmlspecialchars($row['donor_name']); ?><br>
                        <small><?= htmlspecialchars($row['donor_email']); ?></small>
                    </td>
                    <td><?= htmlspecialchars($row['donation_title']); ?></td>
                    <td><?= $row['requested_quantity']; ?></td>
                    <td><?= $row['proposed_pickup_time']; ?></td>
                    <td>
                        <?php
                        $badgeClass = "badge-secondary";
                        if ($row['status'] === 'pending') $badgeClass = "badge-warning";
                        elseif ($row['status'] === 'accepted') $badgeClass = "badge-success";
                        elseif ($row['status'] === 'rejected') $badgeClass = "badge-danger";
                        elseif ($row['status'] === 'cancelled') $badgeClass = "badge-dark";
                        ?>
                        <span class="badge <?= $badgeClass ?>"><?= ucfirst($row['status']); ?></span>
                    </td>
                    <td><?= $row['request_date']; ?></td>
                    <td>
                        <a href="view_pickup.php?request_id=<?= $row['request_id']; ?>" class="btn btn-sm btn-info">
                            View Pickup
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No requests found.</div>
    <?php endif; ?>
</div>

</body>
</html>
