<?php
session_start();
include('config.php');

// Fetch donation statistics
$total_donations_sql = "SELECT COUNT(*) AS cnt FROM food_donations";
$available_donations_sql = "SELECT COUNT(*) AS cnt FROM food_donations WHERE status='available'";
$requested_donations_sql = "SELECT COUNT(*) AS cnt FROM food_donations WHERE status='requested'";
$collected_donations_sql = "SELECT COUNT(*) AS cnt FROM food_donations WHERE status='collected'";
$total_donations = mysqli_fetch_assoc(mysqli_query($conn, $total_donations_sql))['cnt'];
$available_donations = mysqli_fetch_assoc(mysqli_query($conn, $available_donations_sql))['cnt'];
$requested_donations = mysqli_fetch_assoc(mysqli_query($conn, $requested_donations_sql))['cnt'];
$collected_donations = mysqli_fetch_assoc(mysqli_query($conn, $collected_donations_sql))['cnt'];

// Search filter for donations
$search_query = isset($_GET['donation_search']) ? mysqli_real_escape_string($conn, $_GET['donation_search']) : '';
$where = $search_query ? "WHERE u.name LIKE '%$search_query%' OR u.city LIKE '%$search_query%'" : '';
$donations_sql = "
    SELECT fd.donation_id, fd.title, fd.category, fd.quantity, u.name AS donor_name, u.city AS location
    FROM food_donations fd
    JOIN users u ON fd.donor_id = u.id
    $where
    WHERE fd.status = 'available'
    ORDER BY fd.created_at DESC
    LIMIT 6";
$donations_result = mysqli_query($conn, $donations_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave: Online Food Waste Management System</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="./css/style.css">
    <style>
        :root {
            --primary: #007bff;
            --secondary: #6c757d;
            --success: #28a745;
            --info: #17a2b8;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
            --accent: #ff6f61;
            --highlight: #6f42c1;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--light) 0%, #e0e7ff 100%);
            color: var(--dark);
        }
        .navbar {
            background-color: var(--dark);
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .navbar-brand:hover, .nav-link:hover {
            color: var(--accent) !important;
        }
        .hero-section {
            height: 600px;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('./images/hotel.jpg') center/cover no-repeat;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            border-radius: 0 0 30px 30px;
            animation: fadeIn 1.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .hero-section .btn-primary {
            background-color: var(--accent);
            border-color: var(--accent);
            padding: 12px 30px;
            font-size: 1.2rem;
            transition: transform 0.3s, background-color 0.3s;
        }
        .hero-section .btn-primary:hover {
            background-color: var(--highlight);
            border-color: var(--highlight);
            transform: scale(1.05);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            transition: transform 0.3s, box-shadow 0.3s;
            background-color: white;
        }
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .card-img-top {
            height: 220px;
            object-fit: cover;
            border-radius: 15px 15px 0 0;
        }
        .search-form {
            background-color: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            margin-bottom: 40px;
            border-left: 5px solid var(--accent);
        }
        .section {
            padding: 80px 0;
        }
        .section-title {
            color: var(--highlight);
            margin-bottom: 40px;
            font-weight: 700;
            position: relative;
        }
        .section-title::after {
            content: '';
            width: 60px;
            height: 4px;
            background-color: var(--accent);
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }
        .feature-icon {
            font-size: 3em;
            color: var(--accent);
            margin-bottom: 20px;
            transition: color 0.3s;
        }
        .card:hover .feature-icon {
            color: var(--highlight);
        }
        .chart-container {
            max-width: 450px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .card-stats {
            transition: transform 0.2s;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .card-stats:hover {
            transform: translateY(-5px);
        }
        footer {
            background-color: var(--dark);
            color: white;
            padding: 30px 0;
            border-top: 4px solid var(--accent);
        }
        .btn-success {
            background-color: var(--success);
            border-color: var(--success);
        }
        .btn-success:hover {
            background-color: var(--highlight);
            border-color: var(--highlight);
        }
        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 8px rgba(255, 111, 97, 0.3);
        }
    </style>
</head>
<body>
    <?php include('navbar.php'); ?>

    <!-- Hero Section -->
    <div class="hero-section">
        <h1 class="display-4 font-weight-bold">Online Food Waste Management</h1>
        <p class="lead mb-4">Connect, Donate, and Reduce Food Waste</p>
        <?php if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true): ?>
            <a href="login.php" class="btn btn-primary"><i class="fas fa-sign-in-alt mr-2"></i>Login to Donate or Request</a>
        <?php else: ?>
            <a href="dashboard.php" class="btn btn-primary"><i class="fas fa-tachometer-alt mr-2"></i>Go to Dashboard</a>
        <?php endif; ?>
    </div>

    <!-- Stats Section -->
    <div class="section">
        <div class="container">
            <h2 class="text-center section-title">Our Impact</h2>
            <div class="row text-center">
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card card-stats p-4 bg-white">
                        <h4 class="font-weight-bold"><?= $total_donations ?></h4>
                        <p class="text-muted mb-0">Total Donations</p>
                        <i class="fas fa-donate text-primary mt-2" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card card-stats p-4 bg-white">
                        <h4 class="font-weight-bold"><?= $available_donations ?></h4>
                        <p class="text-muted mb-0">Available Donations</p>
                        <i class="fas fa-box-open text-success mt-2" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card card-stats p-4 bg-white">
                        <h4 class="font-weight-bold"><?= $requested_donations ?></h4>
                        <p class="text-muted mb-0">Requested Donations</p>
                        <i class="fas fa-hand-holding-heart text-info mt-2" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card card-stats p-4 bg-white">
                        <h4 class="font-weight-bold"><?= $collected_donations ?></h4>
                        <p class="text-muted mb-0">Collected Donations</p>
                        <i class="fas fa-check-circle text-warning mt-2" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- About Section -->
    <div class="section bg-light">
        <div class="container">
            <h2 class="text-center section-title">About FoodSave</h2>
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="lead">FoodSave is a platform connecting restaurants, grocery stores, and individuals with NGOs and food banks to donate surplus food. We ensure excess food reaches those in need, reducing waste and promoting sustainability.</p>
                    <p>Our mission is to combat hunger and support environmental goals through real-time inventory tracking, expiration alerts, and an interactive map for locating food sources.</p>
                    <?php if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true): ?>
                        <a href="signup.php" class="btn btn-success btn-custom mt-3">
                            <i class="fas fa-user-plus mr-2"></i>Join Now
                        </a>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <div class="chart-container">
                        <h4 class="text-center mb-4">Donation Distribution</h4>
                        <canvas id="donationChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="section">
        <div class="container">
            <h2 class="text-center section-title">Key Features</h2>
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="fas fa-donate feature-icon"></i>
                            <h5 class="card-title">List Donations</h5>
                            <p class="card-text">Easily list surplus food items with details like quantity and expiration date.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="fas fa-map-marker-alt feature-icon"></i>
                            <h5 class="card-title">Locate Donors</h5>
                            <p class="card-text">Find nearby food donors using an interactive map.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="fas fa-bell feature-icon"></i>
                            <h5 class="card-title">Expiration Alerts</h5>
                            <p class="card-text">Receive automatic alerts for food nearing expiration.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Donations Section -->
    <div class="section bg-light">
        <div class="container">
            <h2 class="text-center section-title">Available Donations</h2>
            <div class="row">
                <?php if (mysqli_num_rows($donations_result) > 0): ?>
                    <?php while ($donation = mysqli_fetch_assoc($donations_result)): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($donation['title']) ?></h5>
                                    <p class="card-text">
                                        <strong>Donor:</strong> <?= htmlspecialchars($donation['donor_name']) ?><br>
                                        <strong>Location:</strong> <?= htmlspecialchars($donation['location'] ?? 'N/A') ?><br>
                                        <strong>Category:</strong> <?= ucfirst($donation['category']) ?><br>
                                        <strong>Quantity:</strong> <?= htmlspecialchars($donation['quantity']) ?>
                                    </p>
                                    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['user_type'] === 'ngo'): ?>
                                        <a href="request_donation.php?food_id=<?= $donation['donation_id'] ?>" 
                                           class="btn btn-success btn-sm">
                                            <i class="fas fa-hand-holding-heart mr-1"></i>Request Now
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">No available donations found.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <p class="mb-0">© 2025 FoodSave. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Donation Distribution Pie Chart
        const ctx = document.getElementById('donationChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Available', 'Requested', 'Collected'],
                datasets: [{
                    data: [<?= $available_donations ?>, <?= $requested_donations ?>, <?= $collected_donations ?>],
                    backgroundColor: ['#28a745', '#ffc107', '#6f42c1'],
                    borderColor: 'rgba(255,255,255,0.8)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 14, family: 'Segoe UI' },
                            color: 'var(--dark)'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'var(--dark)',
                        titleFont: { size: 16 },
                        bodyFont: { size: 14 }
                    }
                }
            }
        });
    </script>
</body>
</html>