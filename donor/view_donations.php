<?php
session_start();
include('../config.php');

if (!isset($_SESSION["id"]) || $_SESSION["user_type"] !== 'donor') {
    header("location: ../index.php");
    exit;
}

$donor_id = $_SESSION["id"];

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM Food_Donations WHERE donation_id=$id AND donor_id=$donor_id");
    header("Location: view_donations.php");
    exit;
}

// Fetch all donations of donor
$sql = "
    SELECT fd.*, 
        (SELECT COUNT(*) FROM donation_requests dr WHERE dr.food_id = fd.donation_id) AS total_requests,
        (SELECT COUNT(*) FROM donation_requests dr WHERE dr.food_id = fd.donation_id AND dr.status='accepted') AS accepted_requests,
        (SELECT COUNT(*) FROM donation_requests dr WHERE dr.food_id = fd.donation_id AND dr.status='completed') AS completed_requests
    FROM food_donations fd
    WHERE fd.donor_id = ?
    ORDER BY fd.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Donations - Impact Tracking</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">My Donations & Impact Reports</h2>
    <a href="add_donation.php" class="btn btn-success mb-3">Add New Donation</a>
    <button onclick="window.print()" class="btn btn-info float-right mb-4">Print Report</button>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Expiration</th>
                    <th>Status</th>
                    <th>Total Requests</th>
                    <th>Accepted</th>
                    <th>Completed</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']); ?></td>
                    <td><?= htmlspecialchars($row['category']); ?></td>
                    <td><?= htmlspecialchars($row['quantity']); ?></td>
                    <td><?= $row['expiration_date']; ?></td>
                    <td><span class="badge badge-info"><?= ucfirst($row['status']); ?></span></td>
                    <td><?= $row['total_requests']; ?></td>
                    <td><?= $row['accepted_requests']; ?></td>
                    <td><?= $row['completed_requests']; ?></td>
                    <td>
                        <a href="edit_donation.php?id=<?= $row['donation_id']; ?>" class="mt-2 btn btn-sm btn-primary">Edit</a>
                        <a href="view_donations.php?delete=<?= $row['donation_id']; ?>" 
                           class="mt-2 btn btn-sm btn-danger" 
                           onclick="return confirm('Delete this donation?')">Delete</a>
                        <a href="donor_requests.php?donation_id=<?= $row['donation_id']; ?>" 
                           class="mt-2 btn btn-sm btn-secondary">View Requests</a>
                        <a href="view_feedback.php?donation_id=<?= $row['donation_id']; ?>" 
                           class="mt-2 btn btn-sm btn-warning">Feedback</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">You haven’t made any donations yet.</div>
    <?php endif; ?>
</div>
</body>
</html>
