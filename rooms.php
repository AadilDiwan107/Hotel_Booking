<?php
session_start();
require_once 'db.php'; // Include the database connection file

// Fetch all rooms from the database
$stmt = $pdo->query("SELECT * FROM rooms");
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Details</title>
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
        .room-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        .room-image {
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
        <h2>Room Details</h2>
        <?php if (empty($rooms)): ?>
            <div class="alert alert-info text-center">
                No rooms found in the database.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($rooms as $room): ?>
                    <div class="col-md-4 mb-4">
                        <div class="room-card">
                            <!-- Room Image -->
                            <?php if (!empty($room['image'])): ?>
                                <img src="<?= htmlspecialchars($room['image']) ?>" alt="<?= $room['room_type'] ?> Image" class="room-image">
                            <?php else: ?>
                                <div class="no-image">No Image Available</div>
                            <?php endif; ?>

                            <!-- Room Type -->
                            <h5><?= ucfirst($room['room_type']) ?></h5>

                            <!-- Price Per Night -->
                            <p><strong>Price per Night:</strong> ₹<?= number_format($room['price_per_night'], 2) ?></p>

                            <!-- Available Rooms -->
                            <p><strong>Available Rooms:</strong> <?= $room['total_rooms'] - $room['booked_rooms'] ?></p>

                            <!-- Book Button -->
                            <a href="detail_room.php?room_id=<?= $room['id'] ?>" class="book-button d-block">veiw</a>
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