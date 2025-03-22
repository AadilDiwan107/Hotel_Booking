<?php
session_start();
require_once 'db.php'; // Include the database connection file

// Check if the user is logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle form submission for updating a room
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_room'])) {
    $room_id = $_POST['room_id'];
    $room_type = $_POST['room_type'];
    $total_rooms = $_POST['total_rooms'];
    $price_per_night = $_POST['price_per_night']; // Use price_per_night for rooms

    // Handle image upload
    $image_path = null;
    if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/rooms/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $image_name = uniqid('room_', true) . '.' . pathinfo($_FILES['room_image']['name'], PATHINFO_EXTENSION);
        $image_path = $upload_dir . $image_name;

        move_uploaded_file($_FILES['room_image']['tmp_name'], $image_path);
    }

    // Update the room details in the database
    $stmt = $pdo->prepare("
        UPDATE rooms 
        SET room_type = ?, total_rooms = ?, price_per_night = ?" . ($image_path ? ", image = ?" : "") . "
        WHERE id = ?
    ");

    if ($image_path) {
        $stmt->execute([$room_type, $total_rooms, $price_per_night, $image_path, $room_id]);
    } else {
        $stmt->execute([$room_type, $total_rooms, $price_per_night, $room_id]);
    }

    echo "<script>alert('Room details updated successfully!');</script>";
}

// Handle form submission for updating a hall
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_hall'])) {
    $hall_id = $_POST['hall_id'];
    $hall_name = $_POST['hall_name'];
    $total_halls = $_POST['total_halls'];
    $price_per_day = $_POST['price_per_day']; // Use price_per_day for halls

    // Handle image upload
    $image_path = null;
    if (isset($_FILES['hall_image']) && $_FILES['hall_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/halls/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $image_name = uniqid('hall_', true) . '.' . pathinfo($_FILES['hall_image']['name'], PATHINFO_EXTENSION);
        $image_path = $upload_dir . $image_name;

        move_uploaded_file($_FILES['hall_image']['tmp_name'], $image_path);
    }

    // Update the hall details in the database
    $stmt = $pdo->prepare("
        UPDATE halls 
        SET hall_name = ?, total_halls = ?, price_per_day = ?" . ($image_path ? ", image = ?" : "") . "
        WHERE id = ?
    ");

    if ($image_path) {
        $stmt->execute([$hall_name, $total_halls, $price_per_day, $image_path, $hall_id]);
    } else {
        $stmt->execute([$hall_name, $total_halls, $price_per_day, $hall_id]);
    }

    echo "<script>alert('Hall details updated successfully!');</script>";
}

// Fetch all rooms for the dropdown
$stmt = $pdo->query("SELECT * FROM rooms");
$rooms = $stmt->fetchAll();

// Fetch all halls for the dropdown
$stmt = $pdo->query("SELECT * FROM halls");
$halls = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Rooms/Halls</title>
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
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <!-- Brand/logo -->
            <a class="navbar-brand" href="dashboard.php">Hotel Booking</a>

            <!-- Toggle button for mobile view -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="adminadd.php">Add Room/Hall</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="aup.php">Update Room/Hall</a>
                    </li>
                </ul>

                <!-- Right-aligned links -->
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="signup.php">Signup</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h2>Update Rooms/Halls</h2>

        <!-- Update Room Form -->
        <h4>Update Room Details</h4>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="room_id" class="form-label">Select Room</label>
                <select class="form-select" id="room_id" name="room_id" required>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?= $room['id'] ?>"><?= ucfirst($room['room_type']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="room_type" class="form-label">Room Type</label>
                <input type="text" class="form-control" id="room_type" name="room_type" required>
            </div>
            <div class="mb-3">
                <label for="total_rooms" class="form-label">Total Rooms</label>
                <input type="number" class="form-control" id="total_rooms" name="total_rooms" min="1" required>
            </div>
            <div class="mb-3">
                <label for="price_per_night" class="form-label">Price per Night</label>
                <input type="number" class="form-control" id="price_per_night" name="price_per_night" step="0.01" min="0" required>
            </div>
            <div class="mb-3">
                <label for="room_image" class="form-label">Upload New Image</label>
                <input type="file" class="form-control" id="room_image" name="room_image">
            </div>
            <button type="submit" name="update_room" class="btn btn-primary">Update Room</button>
        </form>

        <hr>

        <!-- Update Hall Form -->
        <h4>Update Hall Details</h4>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="hall_id" class="form-label">Select Hall</label>
                <select class="form-select" id="hall_id" name="hall_id" required>
                    <?php foreach ($halls as $hall): ?>
                        <option value="<?= $hall['id'] ?>"><?= $hall['hall_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="hall_name" class="form-label">Hall Name</label>
                <input type="text" class="form-control" id="hall_name" name="hall_name" required>
            </div>
            <div class="mb-3">
                <label for="total_halls" class="form-label">Total Halls</label>
                <input type="number" class="form-control" id="total_halls" name="total_halls" min="1" required>
            </div>
            <div class="mb-3">
                <label for="price_per_day" class="form-label">Price per Day</label>
                <input type="number" class="form-control" id="price_per_day" name="price_per_day" step="0.01" min="0" required>
            </div>
            <div class="mb-3">
                <label for="hall_image" class="form-label">Upload New Image</label>
                <input type="file" class="form-control" id="hall_image" name="hall_image">
            </div>
            <button type="submit" name="update_hall" class="btn btn-primary">Update Hall</button>
        </form>
    </div>
</body>

</html>
