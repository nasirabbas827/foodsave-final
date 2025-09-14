<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM Food_Safety_Guidelines WHERE guideline_id=$id");
    header("Location: view_guidelines.php");
    exit;
}

// Fetch all guidelines
$result = $conn->query("SELECT * FROM Food_Safety_Guidelines ORDER BY created_date DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Guidelines</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">Food Safety Guidelines</h2>
    <a href="admin_add_guideline.php" class="btn btn-success mb-3">Add New Guideline</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Content</th>
                <th>Created</th>
                <th>Last Updated</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['title']); ?></td>
                <td><?= ucfirst($row['category']); ?></td>
                <td style="max-width:300px; white-space:pre-wrap;"><?= nl2br(htmlspecialchars($row['content'])); ?></td>
                <td><?= $row['created_date']; ?></td>
                <td><?= $row['last_updated']; ?></td>
                <td>
                    <a href="edit_guideline.php?id=<?= $row['guideline_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                    <a href="view_guidelines.php?delete=<?= $row['guideline_id']; ?>" 
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Are you sure you want to delete this guideline?');">
                       Delete
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
