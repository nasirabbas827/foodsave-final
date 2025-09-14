<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST["title"];
    $content = $_POST["content"];
    $category = $_POST["category"];

    $sql = "INSERT INTO Food_Safety_Guidelines (title, content, category) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $title, $content, $category);
    if (mysqli_stmt_execute($stmt)) {
        $message = "Guideline added successfully.";
    } else {
        $message = "Failed to add guideline: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Food Safety Guideline</title>
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
            <h2 class="mb-4">Add New Food Safety Guideline</h2>
            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label>Title:</label>
                    <input type="text" name="title" required class="form-control">
                </div>

                <div class="form-group">
                    <label>Content:</label>
                    <textarea name="content" required class="form-control" rows="6"></textarea>
                </div>

                <div class="form-group">
                    <label>Category:</label>
                    <select name="category" class="form-control" required>
                        <option value="">Select category</option>
                        <option value="storage">Storage</option>
                        <option value="handling">Handling</option>
                        <option value="donation">Donation</option>
                        <option value="safety">Safety</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Add Guideline</button>
                <a class="btn btn-secondary ml-2" href="view_guidelines.php">View Guidelines</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
