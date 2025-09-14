<?php
session_start();
include('../config.php');

if (!isset($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$donor_id = $_SESSION["id"];
$id = intval($_GET['id']);
$msg = "";

// Fetch donation
$sql = "SELECT * FROM Food_Donations WHERE donation_id=? AND donor_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $donor_id);
$stmt->execute();
$result = $stmt->get_result();
$donation = $result->fetch_assoc();

if (!$donation) {
    die("Donation not found.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $quantity = trim($_POST['quantity']);
    $expiration_date = $_POST['expiration_date'];
    $status = $_POST['status'];
    $latitude = trim($_POST['latitude']);
    $longitude = trim($_POST['longitude']);

    $sql = "UPDATE Food_Donations 
            SET title=?, description=?, category=?, quantity=?, expiration_date=?, status=?, latitude=?, longitude=? 
            WHERE donation_id=? AND donor_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssiii", $title, $description, $category, $quantity, $expiration_date, $status, $latitude, $longitude, $id, $donor_id);

    if ($stmt->execute()) {
        $msg = '<div class="alert alert-success">Donation updated successfully!</div>';
    } else {
        $msg = '<div class="alert alert-danger">Error: ' . $conn->error . '</div>';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Donation</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="card mx-auto shadow" style="max-width: 700px;">
        <div class="card-body">
            <h2 class="mb-4 text-center">Edit Donation</h2>
            <?= $msg; ?>
            <form method="post">
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($donation['title']); ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Description</label>
                        <textarea name="description" class="form-control"><?= htmlspecialchars($donation['description']); ?></textarea>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Category</label>
                        <select name="category" class="form-control">
                            <?php
                            $categories = ['fruits','vegetables','meals','bakery','dairy','other'];
                            foreach ($categories as $cat) {
                                $selected = ($donation['category'] == $cat) ? 'selected' : '';
                                echo "<option value='$cat' $selected>$cat</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Quantity</label>
                        <input type="text" name="quantity" class="form-control" value="<?= htmlspecialchars($donation['quantity']); ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Expiration Date</label>
                        <input type="date" name="expiration_date" class="form-control" value="<?= $donation['expiration_date']; ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <?php
                            $statuses = ['available','requested','accepted','collected','expired'];
                            foreach ($statuses as $st) {
                                $selected = ($donation['status'] == $st) ? 'selected' : '';
                                echo "<option value='$st' $selected>$st</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <!-- Latitude & Longitude -->
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Latitude</label>
                        <input type="text" name="latitude" class="form-control" value="<?= htmlspecialchars($donation['latitude']); ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Longitude</label>
                        <input type="text" name="longitude" class="form-control" value="<?= htmlspecialchars($donation['longitude']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col text-center">
                        <button type="submit" class="btn btn-primary">Update Donation</button>
                        <a href="view_donations.php" class="btn btn-secondary">Back</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
