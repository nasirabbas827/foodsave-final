<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $phone = $_POST["phone"];
    $user_type = $_POST["user_type"];
    $address = $_POST["address"];
    $city = $_POST["city"];

    $sql = "INSERT INTO Users (name, email, password_hash, phone, user_type, address, city, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssssss", $name, $email, $password_hash, $phone, $user_type, $address, $city);
    if (mysqli_stmt_execute($stmt)) {
        $message = "User added successfully with pending status.";
    } else {
        $message = "Failed to add user: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add User</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5 mb-5">
    <div class="card mx-auto" style="max-width: 800px;">
        <div class="card-body">
            <h2 class="mb-4">Add New User</h2>
            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Full Name:</label>
                        <input type="text" name="name" required class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Email:</label>
                        <input type="email" name="email" required class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Password:</label>
                        <input type="password" name="password" required class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Phone:</label>
                        <input type="text" name="phone" required class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>City:</label>
                        <input type="text" name="city" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label>User Type:</label>
                        <select name="user_type" class="form-control" required>
                            <option value="">Select user type</option>
                            <option value="donor">Donor</option>
                            <option value="ngo">NGO</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Address:</label>
                    <textarea name="address" class="form-control"></textarea>
                </div>

                <button type="submit" class="btn btn-success">Add User</button>
                <a class="btn btn-secondary ml-2" href="view_users.php">View Users</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
