<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$ngo_id = $_SESSION["id"];
$email = htmlspecialchars($_SESSION["email"]);

// Initialize dismissed alerts array in session if not set
if (!isset($_SESSION['dismissed_alerts'])) {
    $_SESSION['dismissed_alerts'] = [];
}

// Fetch summary stats
$total_requests_sql = "SELECT COUNT(*) as total FROM donation_requests WHERE ngo_id=?";
$stmt = $conn->prepare($total_requests_sql);
$stmt->bind_param("i", $ngo_id);
$stmt->execute();
$total_requests = $stmt->get_result()->fetch_assoc()['total'];

$request_status_sql = "SELECT status, COUNT(*) as count FROM donation_requests WHERE ngo_id=? GROUP BY status";
$stmt = $conn->prepare($request_status_sql);
$stmt->bind_param("i", $ngo_id);
$stmt->execute();
$request_status_result = $stmt->get_result();

$request_stats = [];
while ($row = $request_status_result->fetch_assoc()) {
    $request_stats[$row['status']] = $row['count'];
}

// Fetch urgent donations (expiring within 3 days and available)
$urgent_sql = "SELECT donation_id, title, expiration_date 
               FROM food_donations 
               WHERE status='available' 
               AND expiration_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY) 
               AND expiration_date >= CURDATE()";
$urgent_result = $conn->query($urgent_sql);

// Filtering for donations
$where = [];
$params = [];
$types = "";

if (!empty($_GET['category'])) {
    $where[] = "d.category = ?";
    $params[] = $_GET['category'];
    $types .= "s";
}
if (!empty($_GET['expiration_date'])) {
    $where[] = "d.expiration_date <= ?";
    $params[] = $_GET['expiration_date'];
    $types .= "s";
}

$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

// Fetch donations
$sql = "SELECT d.donation_id, d.title, d.category, d.quantity, d.status, d.expiration_date, 
               d.latitude, d.longitude, u.email AS donor_email
        FROM food_donations d
        JOIN users u ON d.donor_id = u.id
        $whereSQL
        ORDER BY d.created_at DESC";

$stmt = $conn->prepare($sql);
if ($where) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Handle alert dismissal
if (isset($_POST['dismiss_alert'])) {
    $dismiss_id = $_POST['dismiss_alert'];
    if (!in_array($dismiss_id, $_SESSION['dismissed_alerts'])) {
        $_SESSION['dismissed_alerts'][] = $dismiss_id;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NGO Dashboard</title>
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
        .donation-card {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .btn-custom {
            border-radius: 25px;
            padding: 10px 25px;
        }
        .filter-form {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .alert-dismissible .close {
            padding: 0.75rem 1.25rem;
        }
        .alert-urgent {
            border-left: 5px solid #dc3545;
            background-color: #fff3f3;
        }
    </style>
</head>
<body class="bg-light">
    <?php include('navbar.php'); ?>

    <div class="container py-5">
        <!-- Welcome Section -->
        <div class="mb-5">
            <h1 class="display-4 font-weight-bold">Welcome, <?= $email ?></h1>
            <p class="lead text-muted">Manage your food donation requests and browse available donations</p>
        </div>

        <!-- Urgent Donation Alerts -->
        <?php if ($urgent_result->num_rows > 0): ?>
            <?php while ($urgent = $urgent_result->fetch_assoc()): ?>
                <?php if (!in_array($urgent['donation_id'], $_SESSION['dismissed_alerts'])): ?>
                    <div class="alert alert-urgent alert-dismissible fade show mb-4" role="alert">
                        <strong>Urgent!</strong> Donation "<?= htmlspecialchars($urgent['title']) ?>" is expiring on <?= htmlspecialchars($urgent['expiration_date']) ?>.
                        <a href="request_donation.php?food_id=<?= $urgent['donation_id'] ?>" class="btn btn-sm btn-success ml-2">
                            <i class="fas fa-hand-holding-heart mr-1"></i>Request Now
                        </a>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="dismiss_alert" value="<?= $urgent['donation_id'] ?>">
                            <button type="submit" class="close" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            <?php endwhile; ?>
        <?php endif; ?>

        <!-- Stats Section -->
        <div class="row mb-5">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card card-stats p-4 bg-white">
                    <h4 class="font-weight-bold"><?= $total_requests ?></h4>
                    <p class="text-muted mb-0">Total Requests</p>
                    <i class="fas fa-hand-holding-heart text-primary mt-2" style="font-size: 1.5rem;"></i>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card card-stats p-4 bg-white">
                    <h4 class="font-weight-bold"><?= $request_stats['pending'] ?? 0 ?></h4>
                    <p class="text-muted mb-0">Pending</p>
                    <i class="fas fa-hourglass-half text-warning mt-2" style="font-size: 1.5rem;"></i>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card card-stats p-4 bg-white">
                    <h4 class="font-weight-bold"><?= $request_stats['accepted'] ?? 0 ?></h4>
                    <p class="text-muted mb-0">Accepted</p>
                    <i class="fas fa-check-circle text-success mt-2" style="font-size: 1.5rem;"></i>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card card-stats p-4 bg-white">
                    <h4 class="font-weight-bold"><?= ($request_stats['rejected'] ?? 0) + ($request_stats['cancelled'] ?? 0) ?></h4>
                    <p class="text-muted mb-0">Rejected/Cancelled</p>
                    <i class="fas fa-times-circle text-danger mt-2" style="font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex flex-wrap mb-5">
            <a href="request_donation.php" class="btn btn-primary btn-custom mr-3 mb-2">
                <i class="fas fa-plus mr-2"></i>New Request
            </a>
            <a href="ngo_requests.php" class="btn btn-outline-primary btn-custom mb-2">
                <i class="fas fa-list mr-2"></i>View My Requests
            </a>
        </div>

        <!-- Filtering Form -->
        <div class="filter-form mb-5">
            <h3 class="mb-4">Filter Donations</h3>
            <form method="get">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="category">Category</label>
                        <select name="category" id="category" class="form-control">
                            <option value="">All</option>
                            <?php
                            $categories = ['fruits', 'vegetables', 'meals', 'bakery', 'dairy', 'other'];
                            foreach ($categories as $cat) {
                                $selected = (isset($_GET['category']) && $_GET['category'] == $cat) ? "selected" : "";
                                echo "<option value='$cat' $selected>" . ucfirst($cat) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="expiration_date">Expiration Before</label>
                        <input type="date" name="expiration_date" id="expiration_date" 
                               class="form-control" value="<?= htmlspecialchars($_GET['expiration_date'] ?? '') ?>">
                    </div>
                    <div class="form-group col-md-4 align-self-end">
                        <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Donation Cards -->
        <div class="row">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card donation-card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                                <p class="card-text"><strong>Category:</strong> <?= ucfirst($row['category']) ?></p>
                                <p class="card-text"><strong>Quantity:</strong> <?= htmlspecialchars($row['quantity']) ?></p>
                                <p class="card-text"><strong>Donor:</strong> <?= htmlspecialchars($row['donor_email']) ?></p>
                                <p class="card-text"><strong>Expires:</strong> <?= htmlspecialchars($row['expiration_date']) ?></p>
                                <p class="card-text">
                                    <span class="status-<?= strtolower($row['status']) ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </p>
                                <?php if (!empty($row['latitude']) && !empty($row['longitude'])): ?>
                                    <p class="mt-2">
                                        <a href="https://www.google.com/maps?q=<?= $row['latitude'] ?>,<?= $row['longitude'] ?>" 
                                           target="_blank" class="btn btn-sm btn-info">
                                            <i class="fas fa-map-marker-alt mr-1"></i>View Location
                                        </a>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer text-center">
                                <?php if ($row['status'] === 'available'): ?>
                                    <a href="request_donation.php?food_id=<?= $row['donation_id'] ?>" 
                                       class="btn btn-success btn-sm mr-2">
                                        <i class="fas fa-hand-holding-heart mr-1"></i>Request Now
                                    </a>
                                <?php endif; ?>
                                <a href="feedback.php?food_id=<?= $row['donation_id'] ?>" 
                                   class="btn btn-warning btn-sm">
                                    <i class="fas fa-comment mr-1"></i>Feedback
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">No donations found</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>