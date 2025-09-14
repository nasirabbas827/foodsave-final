<?php
include('config.php');
session_start();

// Fetch all guidelines
$sql = "SELECT * FROM food_safety_guidelines ORDER BY created_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Food Safety Guidelines</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        body {
            background: #f9f9f9;
        }
        .card {
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }
        .card h5 {
            color: #2c3e50;
        }
        .category-badge {
            font-size: 0.85rem;
            padding: 5px 10px;
            border-radius: 12px;
        }
        .storage { background: #17a2b8; color: white; }
        .handling { background: #28a745; color: white; }
        .donation { background: #ffc107; color: black; }
        .safety { background: #dc3545; color: white; }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
<div class="container mt-5 mb-5">
    <h2 class="mb-4 text-center">Food Safety Guidelines</h2>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <?= htmlspecialchars($row['title']) ?>
                        <span class="category-badge <?= $row['category'] ?>">
                            <?= ucfirst($row['category']) ?>
                        </span>
                    </h5>
                    <p class="card-text"><?= nl2br(htmlspecialchars($row['content'])) ?></p>
                    <small class="text-muted">
                        Created: <?= $row['created_date'] ?> | Updated: <?= $row['last_updated'] ?>
                    </small>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">No guidelines available yet.</div>
    <?php endif; ?>
</div>

</body>
</html>
