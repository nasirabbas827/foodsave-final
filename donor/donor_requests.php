<?php
session_start();
include('../config.php');

if (!isset($_SESSION["id"]) || $_SESSION["user_type"] !== 'donor') {
    header("location: ../index.php");
    exit;
}

$donor_id = $_SESSION["id"];
$msg = "";

// Handle status update
if (isset($_POST['update_status'])) {
    $request_id = intval($_POST['request_id']);
    $new_status = $_POST['status'];

    if (in_array($new_status, ['pending','accepted','rejected','cancelled','completed'])) {
        $stmt = $conn->prepare("
            UPDATE donation_requests dr
            JOIN food_donations fd ON dr.food_id = fd.donation_id
            SET dr.status = ?
            WHERE dr.request_id = ? AND fd.donor_id = ?
        ");
        $stmt->bind_param("sii", $new_status, $request_id, $donor_id);
        if ($stmt->execute()) {
            $msg = '<div class="alert alert-success">Request status updated successfully!</div>';
        } else {
            $msg = '<div class="alert alert-danger">Error updating status: ' . $conn->error . '</div>';
        }
    }
}

// Fetch all requests for donor's food
$sql = "
    SELECT dr.*, fd.title AS donation_title, u.name AS ngo_name, u.email AS ngo_email
    FROM donation_requests dr
    JOIN food_donations fd ON dr.food_id = fd.donation_id
    JOIN users u ON dr.ngo_id = u.id
    WHERE fd.donor_id = ?
    ORDER BY dr.request_date DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$requests = $stmt->get_result();

// Status color map
function statusBadge($status) {
    switch ($status) {
        case 'accepted': return '<span class="badge badge-success">Accepted</span>';
        case 'rejected': return '<span class="badge badge-danger">Rejected</span>';
        case 'cancelled': return '<span class="badge badge-secondary">Cancelled</span>';
        case 'completed': return '<span class="badge badge-primary">Completed</span>';
        default: return '<span class="badge badge-warning">Pending</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Donation Requests</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    
    <h2 class="mb-4">Donation Requests & Tracking</h2>
    <?= $msg; ?>
    <div class="mb-3">
            <button onclick="window.print()" class="btn btn-info float-right mb-4">Print Report</button>
        </div>

    <?php if ($requests->num_rows > 0): ?>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Donation</th>
                    <th>NGO</th>
                    <th>Email</th>
                    <th>Requested Qty</th>
                    <th>Pickup Time</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Update</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $requests->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['donation_title']); ?></td>
                    <td><?= htmlspecialchars($row['ngo_name']); ?></td>
                    <td><?= htmlspecialchars($row['ngo_email']); ?></td>
                    <td><?= $row['requested_quantity']; ?></td>
                    <td><?= $row['proposed_pickup_time']; ?></td>
                    <td><?= nl2br(htmlspecialchars($row['message'])); ?></td>
                    <td><?= statusBadge($row['status']); ?></td>
                    <td>
                        <form method="post" class="form-inline">
                            <input type="hidden" name="request_id" value="<?= $row['request_id']; ?>">
                            <select name="status" class="form-control mr-2">
                                <option value="pending" <?= $row['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="accepted" <?= $row['status'] === 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                                <option value="rejected" <?= $row['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                <option value="cancelled" <?= $row['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="completed" <?= $row['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                            <button type="submit" name="update_status" class="mt-2 btn btn-sm btn-primary">Update</button>
                        </form>
                    </td>
                    <td>
                        <?php if (in_array($row['status'], ['accepted','completed'])): ?>
                            <a href="generate_receipt.php?request_id=<?= $row['request_id']; ?>" 
                               class="btn btn-sm btn-success mb-2">Generate Receipt</a>
                        <?php endif; ?>
                        <a href="pickup_update.php?request_id=<?= $row['request_id']; ?>" 
                           class="btn btn-sm btn-warning">View/Update Pickup</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No requests for your donations yet.</div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
