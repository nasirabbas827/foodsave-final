<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: view_users.php");
    exit;
}

$id = (int)$_GET['id'];
$message = "";

// Fetch user
$result = mysqli_query($conn, "SELECT * FROM Users WHERE id = $id");
$user = mysqli_fetch_assoc($result);

if (!$user) {
    die("User not found.");
}

// Update user
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $user_type = $_POST["user_type"];
    $address = $_POST["address"];
    $city = $_POST["city"];
    $status = $_POST["status"];
    $password = $_POST["password"]; // New password field

    if (!empty($password)) {
        // Hash the new password before saving
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE Users SET name=?, email=?, phone=?, user_type=?, address=?, city=?, status=?, password_hash=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssssi", $name, $email, $phone, $user_type, $address, $city, $status, $hashedPassword, $id);
    } else {
        // No password update
        $sql = "UPDATE Users SET name=?, email=?, phone=?, user_type=?, address=?, city=?, status=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssssi", $name, $email, $phone, $user_type, $address, $city, $status, $id);
    }

    if (mysqli_stmt_execute($stmt)) {
        $message = "User updated successfully.";
        // refresh user data
        $result = mysqli_query($conn, "SELECT * FROM Users WHERE id = $id");
        $user = mysqli_fetch_assoc($result);
    } else {
        $message = "Failed to update user: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5 mb-5">
    <div class="card mx-auto" style="max-width: 700px;">
        <div class="card-body">
            <h2 class="mb-4">Edit User</h2>
            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Name:</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Email:</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="form-control">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Phone:</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label>User Type:</label>
                        <select name="user_type" class="form-control" required>
                            <option value="donor" <?= $user['user_type']=='donor'?'selected':'' ?>>Donor</option>
                            <option value="ngo" <?= $user['user_type']=='ngo'?'selected':'' ?>>NGO</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Address:</label>
                    <textarea name="address" class="form-control"><?= htmlspecialchars($user['address']) ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>City:</label>
                        <input type="text" name="city" value="<?= htmlspecialchars($user['city']) ?>" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Status:</label>
                        <select name="status" class="form-control" required>
                            <option value="pending" <?= $user['status']=='pending'?'selected':'' ?>>Pending</option>
                            <option value="active" <?= $user['status']=='active'?'selected':'' ?>>Active</option>
                            <option value="inactive" <?= $user['status']=='inactive'?'selected':'' ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>New Password (leave blank to keep unchanged):</label>
                    <input type="password" name="password" class="form-control">
                </div>

                <button type="submit" class="btn btn-success">Update User</button>
                <a class="btn btn-secondary ml-2" href="view_users.php">Back</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
