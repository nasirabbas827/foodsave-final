<?php
session_start();
include('../config.php');

if (!isset($_SESSION["id"]) || $_SESSION["user_type"] !== 'donor') {
    header("location: ../index.php");
    exit;
}

$donor_id = $_SESSION["id"];

// Validate donation_id
if (!isset($_GET['donation_id'])) {
    die("Donation ID is required.");
}
$donation_id = intval($_GET['donation_id']);

// Verify that donation belongs to logged-in donor
$check_sql = "SELECT * FROM food_donations WHERE donation_id=? AND donor_id=?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("ii", $donation_id, $donor_id);
$stmt->execute();
$donation = $stmt->get_result()->fetch_assoc();

if (!$donation) {
    die("Invalid donation or you don’t have permission to view this feedback.");
}

// Fetch feedback for this donation
$sql = "
    SELECT f.*, u.name as ngo_name
    FROM feedback f
    JOIN users u ON f.ngo_id = u.id
    WHERE f.donation_id = ?
    ORDER BY f.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $donation_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Feedback - <?= htmlspecialchars($donation['title']); ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css" />

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">Feedback for Donation: <span class="text-primary"><?= htmlspecialchars($donation['title']); ?></span></h2>
    <a href="view_donations.php" class="btn btn-secondary mb-3">Back to My Donations</a>
    <button onclick="window.print()" class="btn btn-info mb-3 float-right">Print Feedback</button>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>NGO Name</th>
                    <th>Rating</th>
                    <th>Comments</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['ngo_name']); ?></td>
                    <td>
                        <?php for($i=1; $i<=5; $i++): ?>
                            <span style="color:<?= $i <= $row['rating'] ? 'gold' : '#ccc'; ?>;">★</span>
                        <?php endfor; ?>
                    </td>
                    <td><?= htmlspecialchars($row['comments']); ?></td>
                    <td><?= $row['created_at']; ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No feedback has been submitted for this donation yet.</div>
    <?php endif; ?>
</div>

</body>
</html>
