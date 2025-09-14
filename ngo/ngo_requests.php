<?php
session_start();
include('../config.php');

if (!isset($_SESSION["id"]) || $_SESSION["user_type"] !== 'ngo') {
    header("location: ../index.php");
    exit;
}

$ngo_id = $_SESSION["id"];
$msg = "";

// Handle status filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where_clause = "";
$params = [$ngo_id];
$types = "i";

if (!empty($status_filter) && in_array($status_filter, ['pending','accepted','rejected','cancelled'])) {
    $where_clause = " AND dr.status = ? ";
    $params[] = $status_filter;
    $types .= "s";
}

// Fetch all requests made by this NGO
$sql = "
    SELECT dr.*, fd.title AS donation_title, u.name AS donor_name, u.email AS donor_email
    FROM donation_requests dr
    JOIN food_donations fd ON dr.food_id = fd.donation_id
    JOIN users u ON fd.donor_id = u.id
    WHERE dr.ngo_id = ? $where_clause
    ORDER BY dr.request_date DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$requests = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Donation Requests</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">My Donation Requests</h2>
    <?= $msg; ?>

    <!-- Filter Form -->
    <form method="get" class="form-inline mb-4">
        <label for="status" class="mr-2">Filter by Status:</label>
        <select name="status" id="status" class="form-control mr-2">
            <option value="">All</option>
            <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="accepted" <?= $status_filter === 'accepted' ? 'selected' : '' ?>>Accepted</option>
            <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
        <button type="submit" class="btn btn-primary">Apply</button>
        <a href="ngo_requests.php" class="btn btn-secondary ml-2">Reset</a>
    </form>

    <?php if ($requests->num_rows > 0): ?>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Donation</th>
                    <th>Donor</th>
                    <th>Donor Email</th>
                    <th>Requested Qty</th>
                    <th>Pickup Time</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Requested On</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $requests->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['donation_title']); ?></td>
                    <td><?= htmlspecialchars($row['donor_name']); ?></td>
                    <td><?= htmlspecialchars($row['donor_email']); ?></td>
                    <td><?= $row['requested_quantity']; ?></td>
                    <td><?= $row['proposed_pickup_time']; ?></td>
                    <td><?= nl2br(htmlspecialchars($row['message'])); ?></td>
                    <td>
                        <?php
                        $badgeClass = "badge-secondary";
                        if ($row['status'] === 'pending') $badgeClass = "badge-warning";
                        elseif ($row['status'] === 'accepted') $badgeClass = "badge-success";
                        elseif ($row['status'] === 'rejected') $badgeClass = "badge-danger";
                        elseif ($row['status'] === 'cancelled') $badgeClass = "badge-dark";
                        ?>
                        <span class="badge <?= $badgeClass ?>"><?= ucfirst($row['status']); ?></span>

                        <?php if ($row['status'] === 'accepted'): ?>
                            <div class="mt-2">
                                <a href="manage_pickup.php?request_id=<?= $row['request_id'] ?>" class="m-2 btn btn-sm btn-primary">Manage Pickup</a>
                                <a href="generate_receipt.php?request_id=<?= $row['request_id'] ?>" class="btn btn-sm btn-success">Generate Receipt</a>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td><?= $row['request_date']; ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No donation requests found.</div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
