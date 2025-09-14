<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $feedback_id = intval($_GET['delete']);
    $sql = "DELETE FROM feedback WHERE feedback_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $feedback_id);
    if ($stmt->execute()) {
        $msg = "Feedback deleted successfully.";
    } else {
        $msg = "Error deleting feedback: " . $conn->error;
    }
}

// Fetch all feedback with NGO and Donation info
$sql = "SELECT f.*, 
               u.name AS ngo_name, 
               fd.title AS donation_title
        FROM feedback f
        JOIN users u ON f.ngo_id = u.id
        JOIN food_donations fd ON f.donation_id = fd.donation_id
        ORDER BY f.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Feedback</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css" />

</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">Feedback Management</h2>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>NGO</th>
                    <th>Donation</th>
                    <th>Rating</th>
                    <th>Comments</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['feedback_id'] ?></td>
                    <td><?= htmlspecialchars($row['ngo_name']) ?></td>
                    <td><?= htmlspecialchars($row['donation_title']) ?></td>
                    <td><?= $row['rating'] ?></td>
                    <td><?= nl2br(htmlspecialchars($row['comments'])) ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <a href="view_feedback.php?delete=<?= $row['feedback_id'] ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Delete this feedback?');">
                           Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No feedback available.</div>
    <?php endif; ?>
</div>
</body>
</html>
