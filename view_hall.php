<?php
session_start();
require_once 'db.php'; // Include the database connection file

// Fetch all halls from the database
$stmt = $pdo->query("SELECT * FROM halls");
$halls = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hall Details</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 1200px;
            margin-top: 50px;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #0d6efd;
            text-align: center;
            margin-bottom: 30px;
        }
        .hall-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .hall-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        .hall-image {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border: 2px solid #ddd;
            border-radius: 10px;
        }
        .no-image {
            font-size: 1rem;
            color: #6c757d;
            text-align: center;
        }
        .book-button {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            border-radius: 5px;
            background-color: #0d6efd;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .book-button:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="container mt-5">
        <h2>Hall Details</h2>
        <?php if (empty($halls)): ?>
            <div class="alert alert-info text-center">
                No halls found in the database.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($halls as $hall): ?>
                    <div class="col-md-4 mb-4">
                        <div class="hall-card">
                            <!-- Hall Image -->
                            <?php if (!empty($hall['image'])): ?>
                                <img src="<?= htmlspecialchars($hall['image']) ?>" alt="<?= $hall['hall_name'] ?> Image" class="hall-image">
                            <?php else: ?>
                                <div class="no-image">No Image Available</div>
                            <?php endif; ?>

                            <!-- Hall Name -->
                            <h5><?= ucfirst($hall['hall_name']) ?></h5>

                            <!-- Price Per Day -->
                            <p><strong>Price per Day:</strong> ₹<?= number_format($hall['price_per_day'], 2) ?></p>

                            <!-- Available Halls -->
                            <p><strong>Available Halls:</strong> <?= $hall['total_halls'] - $hall['booked_halls'] ?></p>

                            <!-- Book Button -->
                            <a href="hall_detail.php?hall_id=<?= $hall['id'] ?>" class="book-button d-block">View</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>