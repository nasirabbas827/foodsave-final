<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle status filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Fetch donations
$sql = "SELECT fd.*, u.name AS donor_name, u.email AS donor_email 
        FROM food_donations fd
        JOIN users u ON fd.donor_id = u.id";

if ($status_filter && in_array($status_filter, ['available','requested','accepted','collected','expired'])) {
    $sql .= " WHERE fd.status = ?";
}

$sql .= " ORDER BY fd.created_at DESC";

$stmt = $conn->prepare($sql);

if ($status_filter && in_array($status_filter, ['available','requested','accepted','collected','expired'])) {
    $stmt->bind_param("s", $status_filter);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Food Donations</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">Food Donations</h2>

    <!-- Filter Form -->
    <form method="get" class="form-inline mb-4">
        <label class="mr-2">Filter by Status:</label>
        <select name="status" class="form-control mr-2">
            <option value="">All</option>
            <option value="available" <?= $status_filter === 'available' ? 'selected' : '' ?>>Available</option>
            <option value="requested" <?= $status_filter === 'requested' ? 'selected' : '' ?>>Requested</option>
            <option value="accepted" <?= $status_filter === 'accepted' ? 'selected' : '' ?>>Accepted</option>
            <option value="collected" <?= $status_filter === 'collected' ? 'selected' : '' ?>>Collected</option>
            <option value="expired" <?= $status_filter === 'expired' ? 'selected' : '' ?>>Expired</option>
        </select>
        <button type="submit" class="btn btn-primary">Apply</button>
        <a href="view_food_donations.php" class="btn btn-secondary ml-2">Reset</a>
    </form>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Donor</th>
                    <th>Email</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Expiration Date</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Location</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['donor_name']); ?></td>
                    <td><?= htmlspecialchars($row['donor_email']); ?></td>
                    <td><?= htmlspecialchars($row['title']); ?></td>
                    <td><?= htmlspecialchars($row['category']); ?></td>
                    <td><?= htmlspecialchars($row['quantity']); ?></td>
                    <td><?= htmlspecialchars($row['expiration_date']); ?></td>
                    <td>
                        <?php
                        $badgeClass = "badge-secondary";
                        if ($row['status'] === 'available') $badgeClass = "badge-success";
                        elseif ($row['status'] === 'requested') $badgeClass = "badge-warning";
                        elseif ($row['status'] === 'accepted') $badgeClass = "badge-primary";
                        elseif ($row['status'] === 'collected') $badgeClass = "badge-dark";
                        elseif ($row['status'] === 'expired') $badgeClass = "badge-danger";
                        ?>
                        <span class="badge <?= $badgeClass ?>"><?= ucfirst($row['status']); ?></span>
                    </td>
                    <td><?= $row['created_at']; ?></td>
                    <td>
                        Lat: <?= htmlspecialchars($row['latitude']); ?><br>
                        Lng: <?= htmlspecialchars($row['longitude']); ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No donations found.</div>
    <?php endif; ?>
</div>

</body>
</html>
