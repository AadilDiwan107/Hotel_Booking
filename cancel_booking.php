<?php
session_start();
require_once 'db.php'; // Include the database connection file

// Check if the user is logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle form submission for adding a new room type
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_room'])) {
    $room_type = $_POST['room_type'];
    $total_rooms = $_POST['total_rooms'];

    // Insert the new room type into the rooms table
    $stmt = $pdo->prepare("
        INSERT INTO rooms (room_type, total_rooms, booked_rooms)
        VALUES (?, ?, 0)
    ");
    $stmt->execute([$room_type, $total_rooms]);

    echo "<script>alert('New room type added successfully!');</script>";
}

// Handle form submission for adding a new hall type
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_hall'])) {
    $hall_name = $_POST['hall_name'];
    $total_halls = $_POST['total_halls'];

    // Insert the new hall type into the halls table
    $stmt = $pdo->prepare("
        INSERT INTO halls (hall_name, total_halls, booked_halls)
        VALUES (?, ?, 0)
    ");
    $stmt->execute([$hall_name, $total_halls]);

    echo "<script>alert('New hall type added successfully!');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Room/Hall Type</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }

        .container {
            max-width: 600px;
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

        .form-label {
            font-weight: bold;
        }

        .btn-primary {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            border-radius: 5px;
            background-color: #0d6efd;
            border: none;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>

<body>
    <?php include 'nav.php'; ?>

    <div class="container mt-5">
        <h2>Add New Room/Hall Type</h2>

        <!-- Add New Room Type Form -->
        <h4>Add New Room Type</h4>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="room_type" class="form-label">Room Type</label>
                <input type="text" class="form-control" id="room_type" name="room_type" required>
            </div>
            <div class="mb-3">
                <label for="total_rooms" class="form-label">Total Rooms</label>
                <input type="number" class="form-control" id="total_rooms" name="total_rooms" min="1" required>
            </div>
            <button type="submit" name="add_room" class="btn btn-primary">Add Room Type</button>
        </form>

        <hr>

        <!-- Add New Hall Type Form -->
        <h4>Add New Hall Type</h4>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="hall_name" class="form-label">Hall Name</label>
                <input type="text" class="form-control" id="hall_name" name="hall_name" required>
            </div>
            <div class="mb-3">
                <label for="total_halls" class="form-label">Total Halls</label>
                <input type="number" class="form-control" id="total_halls" name="total_halls" min="1" required>
            </div>
            <button type="submit" name="add_hall" class="btn btn-primary">Add Hall Type</button>
        </form>
    </div>
</body>

</html>