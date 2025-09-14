<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Get user counts
$total_users_sql = "SELECT COUNT(*) AS cnt FROM users";
$pending_users_sql = "SELECT COUNT(*) AS cnt FROM users WHERE status='pending'";
$active_users_sql = "SELECT COUNT(*) AS cnt FROM users WHERE status='active'";
$inactive_users_sql = "SELECT COUNT(*) AS cnt FROM users WHERE status='inactive'";
$total_users = mysqli_fetch_assoc(mysqli_query($conn, $total_users_sql))['cnt'];
$pending_users = mysqli_fetch_assoc(mysqli_query($conn, $pending_users_sql))['cnt'];
$active_users = mysqli_fetch_assoc(mysqli_query($conn, $active_users_sql))['cnt'];
$inactive_users = mysqli_fetch_assoc(mysqli_query($conn, $inactive_users_sql))['cnt'];

// Get donation request counts
$total_requests_sql = "SELECT COUNT(*) AS cnt FROM donation_requests";
$pending_requests_sql = "SELECT COUNT(*) AS cnt FROM donation_requests WHERE status='pending'";
$total_requests = mysqli_fetch_assoc(mysqli_query($conn, $total_requests_sql))['cnt'];
$pending_requests = mysqli_fetch_assoc(mysqli_query($conn, $pending_requests_sql))['cnt'];

// Get pickup counts
$total_pickups_sql = "SELECT COUNT(*) AS cnt FROM pickups";
$completed_pickups_sql = "SELECT COUNT(*) AS cnt FROM pickups WHERE status='completed'";
$total_pickups = mysqli_fetch_assoc(mysqli_query($conn, $total_pickups_sql))['cnt'];
$completed_pickups = mysqli_fetch_assoc(mysqli_query($conn, $completed_pickups_sql))['cnt'];

// Get pending users
$pending_result = mysqli_query($conn, "SELECT * FROM users WHERE status='pending' ORDER BY created_at DESC");

// Get recent donation requests (last 5)
$requests_result = mysqli_query($conn, "
    SELECT dr.request_id, dr.status, dr.request_date, u.name AS ngo_name, fd.title AS donation_title
    FROM donation_requests dr
    JOIN users u ON dr.ngo_id = u.id
    JOIN food_donations fd ON dr.food_id = fd.donation_id
    ORDER BY dr.request_date DESC
    LIMIT 5
");

// Get all donations (with donor info)
$donations_result = mysqli_query($conn, "
    SELECT fd.*, u.name AS donor_name, u.email AS donor_email 
    FROM food_donations fd 
    JOIN users u ON fd.donor_id = u.id 
    ORDER BY fd.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
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
        .status-pending { color: #ffc107; }
        .status-rejected { color: #dc3545; }
        .status-cancelled { color: #6c757d; }
        .status-scheduled { color: #17a2b8; }
        .status-in_progress { color: #007bff; }
        .status-completed { color: #28a745; }
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
    <?php include('admin_navbar.php'); ?>

    <div class="container py-5">
        <!-- Welcome Section -->
        <div class="mb-5">
            <h1 class="display-4 font-weight-bold">Admin Dashboard</h1>
            <p class="lead text-muted">Manage users, donations, and requests</p>
        </div>

        <!-- Stats Section -->
        <div class="row mb-5">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card card-stats p-4 bg-white">
                    <h4 class="font-weight-bold"><?= $total_users ?></h4>
                    <p class="text-muted mb-0">Total Users</p>
                    <i class="fas fa-users text-primary mt-2" style="font-size: 1.5rem;"></i>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card card-stats p-4 bg-white">
                    <h4 class="font-weight-bold"><?= $pending_users ?></h4>
                    <p class="text-muted mb-0">Pending Users</p>
                    <i class="fas fa-hourglass-half text-warning mt-2" style="font-size: 1.5rem;"></i>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card card-stats p-4 bg-white">
                    <h4 class="font-weight-bold"><?= $total_requests ?></h4>
                    <p class="text-muted mb-0">Total Requests</p>
                    <i class="fas fa-hand-holding-heart text-info mt-2" style="font-size: 1.5rem;"></i>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card card-stats p-4 bg-white">
                    <h4 class="font-weight-bold"><?= $completed_pickups ?></h4>
                    <p class="text-muted mb-0">Completed Pickups</p>
                    <i class="fas fa-truck text-success mt-2" style="font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex flex-wrap mb-5">
            <a href="view_users.php" class="btn btn-primary btn-custom mr-3 mb-2">
                <i class="fas fa-users-cog mr-2"></i>Manage Users
            </a>
            <a href="view_food_donations.php" class="btn btn-outline-primary btn-custom mr-3 mb-2">
                <i class="fas fa-box-open mr-2"></i>Manage Donations
            </a>
            <a href="view_guidelines.php" class="btn btn-outline-primary btn-custom mb-2">
                <i class="fas fa-book mr-2"></i>Manage Guidelines
            </a>
        </div>

        <!-- Pending Users Table -->
        <div class="table-container mb-5">
            <h3 class="mb-4">Pending Users</h3>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>User Type</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($pending_result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($pending_result)): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['phone'] ?? '-') ?></td>
                                    <td><?= ucfirst($row['user_type']) ?></td>
                                    <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                    <td>
                                        <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success">
                                            <i class="fas fa-edit mr-1"></i>Review
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No pending users</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Donation Requests Table -->
        <div class="table-container mb-5">
            <h3 class="mb-4">Recent Donation Requests</h3>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Request ID</th>
                            <th>NGO</th>
                            <th>Donation Title</th>
                            <th>Status</th>
                            <th>Request Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($requests_result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($requests_result)): ?>
                                <tr>
                                    <td><?= $row['request_id'] ?></td>
                                    <td><?= htmlspecialchars($row['ngo_name']) ?></td>
                                    <td><?= htmlspecialchars($row['donation_title']) ?></td>
                                    <td>
                                        <span class="status-<?= strtolower($row['status']) ?>">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($row['request_date'])) ?></td>
                                    
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No recent requests</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- All Donations Table -->
        <div class="table-container">
            <h3 class="mb-4">All Food Donations</h3>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Donor</th>
                            <th>Email</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Expiration</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($donations_result) > 0): ?>
                            <?php while ($d = mysqli_fetch_assoc($donations_result)): ?>
                                <tr>
                                    <td><?= $d['donation_id'] ?></td>
                                    <td><?= htmlspecialchars($d['donor_name']) ?></td>
                                    <td><?= htmlspecialchars($d['donor_email']) ?></td>
                                    <td><?= htmlspecialchars($d['title']) ?></td>
                                    <td><?= ucfirst($d['category']) ?></td>
                                    <td><?= htmlspecialchars($d['quantity']) ?></td>
                                    <td><?= $d['expiration_date'] ?></td>
                                    <td>
                                        <span class="status-<?= strtolower($d['status']) ?>">
                                            <?= ucfirst($d['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($d['created_at'])) ?></td>
                                    
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted">No donations found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>