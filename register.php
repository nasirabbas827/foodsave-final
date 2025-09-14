<?php
include('config.php');
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $phone = trim($_POST["phone"]);
    $user_type = $_POST["user_type"];
    $address = trim($_POST["address"]);
    $city = trim($_POST["city"]);

    // Hash password securely
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $check = mysqli_prepare($conn, "SELECT id FROM Users WHERE email = ?");
    mysqli_stmt_bind_param($check, "s", $email);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);

    if (mysqli_stmt_num_rows($check) > 0) {
        $message = "This email is already registered. Please login.";
    } else {
        $sql = "INSERT INTO Users (name, email, password_hash, phone, user_type, address, city, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssss", $name, $email, $password_hash, $phone, $user_type, $address, $city);
        if (mysqli_stmt_execute($stmt)) {
            // Redirect to login
            header("Location: login.php?registered=1");
            exit;
        } else {
            $message = "Failed to register user: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_stmt_close($check);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>
    

<div class="container mt-5 mb-5">
    <div class="card mx-auto" style="max-width: 800px;">
        <div class="card-body">
            <h2 class="mb-4 text-center text-primary">Register</h2>
            <?php if ($message): ?>
                <div class="alert alert-warning"><?= htmlspecialchars($message) ?></div>
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

                <button type="submit" class="btn btn-primary btn-block">Register</button>
                <p class="mt-3 text-center">
                    Already have an account? <a href="login.php">Login here</a>
                </p>
            </form>
        </div>
    </div>
</div>

</body>
</html>
