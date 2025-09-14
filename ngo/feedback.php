<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$ngo_id = $_SESSION["id"];
$donation_id = $_GET['food_id'] ?? null;

if (!$donation_id) {
    die("Invalid donation ID");
}

// Handle feedback form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = intval($_POST['rating']);
    $comments = trim($_POST['comments']);

    if ($rating >= 1 && $rating <= 5) {
        $stmt = $conn->prepare("INSERT INTO feedback (ngo_id, donation_id, rating, comments) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $ngo_id, $donation_id, $rating, $comments);
        if ($stmt->execute()) {
            $success = "Feedback submitted successfully!";
        } else {
            $error = "Error submitting feedback.";
        }
    } else {
        $error = "Rating must be between 1 and 5.";
    }
}

// Fetch existing feedbacks for this donation
$stmt = $conn->prepare("SELECT f.*, u.name AS ngo_name 
                        FROM feedback f
                        JOIN users u ON f.ngo_id = u.id
                        WHERE f.donation_id = ?
                        ORDER BY f.created_at DESC");
$stmt->bind_param("i", $donation_id);
$stmt->execute();
$feedbacks = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Food Feedback</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css" />

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">Feedback for Donation #<?= htmlspecialchars($donation_id) ?></h2>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <!-- Feedback Form -->
    <div class="card mx-auto mb-4" style="max-width: 500px;">
        <div class="card-body">
            <form method="post">
                <div class="form-group">
                    <label for="rating">Rating (1-5)</label>
                    <select name="rating" id="rating" class="form-control" required>
                        <option value="">Select Rating</option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="comments">Comments</label>
                    <textarea name="comments" id="comments" class="form-control" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Submit Feedback</button>
            </form>
        </div>
    </div>

    <!-- Existing Feedbacks -->
    <h4>Existing Feedbacks</h4>
    <?php if ($feedbacks->num_rows > 0): ?>
        <?php while ($fb = $feedbacks->fetch_assoc()): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">
                        By: <?= htmlspecialchars($fb['ngo_name']) ?> | Rating: <?= htmlspecialchars($fb['rating']) ?>/5
                    </h6>
                    <p class="card-text"><?= nl2br(htmlspecialchars($fb['comments'])) ?></p>
                    <small class="text-muted">Posted on <?= $fb['created_at'] ?></small>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">No feedback available yet.</div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
