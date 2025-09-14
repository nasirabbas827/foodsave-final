<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$donor_id = $_SESSION["id"];
$email = htmlspecialchars($_SESSION["email"]);

// Fetch total donations
$total_sql = "SELECT COUNT(*) as total FROM food_donations WHERE donor_id=?";
$stmt = $conn->prepare($total_sql);
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$total_result = $stmt->get_result()->fetch_assoc();
$total_donations = $total_result['total'];

// Fetch status counts
$status_sql = "SELECT status, COUNT(*) as count FROM food_donations WHERE donor_id=? GROUP BY status";
$stmt = $conn->prepare($status_sql);
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$status_result = $stmt->get_result();

$stats = [];
while ($row = $status_result->fetch_assoc()) {
    $stats[$row['status']] = $row['count'];
}

// Fetch recent donations (last 5)
$recent_sql = "SELECT donation_id, title, category, quantity, status, created_at 
               FROM food_donations 
               WHERE donor_id=? 
               ORDER BY created_at DESC 
               LIMIT 5";
$stmt = $conn->prepare($recent_sql);
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$recent_donations = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Donor Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .card-stats {
            transition: transform 0.2s;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .card-stats:hover {
            transform: translateY(-5px);
        }
        .status-available { color: #28a745; }
        .status-requested { color: #ffc107; }
        .status-accepted { color: #17a2b8; }
        .status-collected { color: #6f42c1; }
        .status-expired { color: #dc3545; }
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .btn-custom {
            border-radius: 25px;
            padding: 10px 25px;
        }
    </style>
</head>
<body class="bg-light">
    <?php include('navbar.php'); ?>

    <div class="container py-5">
        <!-- Welcome Section -->
        <div class="mb-5">
            <h1 class="display-4 font-weight-bold">Welcome, <?= $email ?></h1>
            <p class="lead text-muted">Track your food donation journey and make a difference</p>
        </div>

        <!-- Stats Section -->
        <div class="row mb-5">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card card-stats p-4 bg-white">
                    <h4 class="font-weight-bold"><?= $total_donations ?></h4>
                    <p class="text-muted mb-0">Total Donations</p>
                    <i class="fas fa-donate text-primary mt-2" style="font-size: 1.5rem;"></i>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card card-stats p-4 bg-white">
                    <h4 class="font-weight-bold"><?= $stats['available'] ?? 0 ?></h4>
                    <p class="text-muted mb-0">Available</p>
                    <i class="fas fa-box-open text-success mt-2" style="font-size: 1.5rem;"></i>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card card-stats p-4 bg-white">
                    <h4 class="font-weight-bold"><?= $stats['collected'] ?? 0 ?></h4>
                    <p class="text-muted mb-0">Collected</p>
                    <i class="fas fa-check-circle text-info mt-2" style="font-size: 1.5rem;"></i>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card card-stats p-4 bg-white">
                    <h4 class="font-weight-bold"><?= $stats['expired'] ?? 0 ?></h4>
                    <p class="text-muted mb-0">Expired</p>
                    <i class="fas fa-exclamation-circle text-danger mt-2" style="font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex flex-wrap mb-5">
            <a href="add_donation.php" class="btn btn-primary btn-custom mr-3 mb-2">
                <i class="fas fa-plus mr-2"></i>Add New Donation
            </a>
            <a href="view_donations.php" class="btn btn-outline-primary btn-custom mb-2">
                <i class="fas fa-list mr-2"></i>View All Donations
            </a>
        </div>

        <!-- Recent Donations -->
        <div class="table-container">
            <h3 class="mb-4">Recent Donations</h3>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($donation = $recent_donations->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($donation['title']) ?></td>
                                <td><?= ucfirst($donation['category']) ?></td>
                                <td><?= htmlspecialchars($donation['quantity']) ?></td>
                                <td>
                                    <span class="status-<?= strtolower($donation['status']) ?>">
                                        <?= ucfirst($donation['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y', strtotime($donation['created_at'])) ?></td>
                                
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>