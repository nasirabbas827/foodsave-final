<?php
session_start();
include('../config.php');

if (!isset($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$donor_id = $_SESSION["id"];
$title = $description = $category = $quantity = $expiration_date = $latitude = $longitude = "";
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $quantity = trim($_POST['quantity']);
    $expiration_date = $_POST['expiration_date'];
    $latitude = trim($_POST['latitude']);
    $longitude = trim($_POST['longitude']);

    $sql = "INSERT INTO Food_Donations 
            (donor_id, title, description, category, quantity, expiration_date, latitude, longitude) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssss", $donor_id, $title, $description, $category, $quantity, $expiration_date, $latitude, $longitude);

    if ($stmt->execute()) {
        $msg = '<div class="alert alert-success">Donation added successfully!</div>';
    } else {
        $msg = '<div class="alert alert-danger">Error: ' . $conn->error . '</div>';
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Donation</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <div class="card mx-auto" style="max-width: 700px;">
        <div class="card-body">
            <h2 class="mb-4">Add Food Donation</h2>
            <?= $msg; ?>
            <form method="post">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Category</label>
                        <select name="category" class="form-control" required>
                            <option value="fruits">Fruits</option>
                            <option value="vegetables">Vegetables</option>
                            <option value="meals">Meals</option>
                            <option value="bakery">Bakery</option>
                            <option value="dairy">Dairy</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Quantity</label>
                        <input type="text" name="quantity" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Expiration Date</label>
                        <input type="date" name="expiration_date" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Latitude</label>
                        <input type="text" name="latitude" class="form-control" placeholder="e.g. 31.5204" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Longitude</label>
                        <input type="text" name="longitude" class="form-control" placeholder="e.g. 74.3587" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Add Donation</button>
                <a href="view_donations.php" class="btn btn-secondary ml-2">View Donations</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
