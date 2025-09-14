<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Check if ID is passed
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: view_guidelines.php");
    exit;
}

$id = intval($_GET['id']);
$message = "";

// Fetch existing guideline
$sql = "SELECT * FROM Food_Safety_Guidelines WHERE guideline_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$guideline = $result->fetch_assoc();
$stmt->close();

if (!$guideline) {
    header("Location: view_guidelines.php");
    exit;
}

// Handle update form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST["title"];
    $content = $_POST["content"];
    $category = $_POST["category"];

    $sql = "UPDATE Food_Safety_Guidelines 
            SET title = ?, content = ?, category = ?, last_updated = NOW() 
            WHERE guideline_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $title, $content, $category, $id);

    if ($stmt->execute()) {
        $message = "Guideline updated successfully.";
        // Refresh guideline after update
        $guideline['title'] = $title;
        $guideline['content'] = $content;
        $guideline['category'] = $category;
    } else {
        $message = "Failed to update guideline: " . $conn->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Guideline</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5 mb-5">
    <div class="card mx-auto" style="max-width: 600px;">
        <div class="card-body">
            <h2 class="mb-4">Edit Food Safety Guideline</h2>

            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label>Title:</label>
                    <input type="text" name="title" class="form-control" 
                           value="<?= htmlspecialchars($guideline['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Content:</label>
                    <textarea name="content" class="form-control" rows="6" required><?= htmlspecialchars($guideline['content']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Category:</label>
                    <select name="category" class="form-control" required>
                        <option value="storage" <?= $guideline['category'] == 'storage' ? 'selected' : ''; ?>>Storage</option>
                        <option value="handling" <?= $guideline['category'] == 'handling' ? 'selected' : ''; ?>>Handling</option>
                        <option value="donation" <?= $guideline['category'] == 'donation' ? 'selected' : ''; ?>>Donation</option>
                        <option value="safety" <?= $guideline['category'] == 'safety' ? 'selected' : ''; ?>>Safety</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Update Guideline</button>
                <a href="view_guidelines.php" class="btn btn-secondary ml-2">Back</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
