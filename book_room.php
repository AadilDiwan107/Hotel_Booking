<?php
// book_room.php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch room availability with images and prices
$stmt = $pdo->query("SELECT * FROM rooms");
$rooms = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $room_type = $_POST['room_type'];
    $check_in_date = $_POST['check_in_date'];
    $check_out_date = $_POST['check_out_date'];
    $user_id = $_SESSION['user_id'];
    $name = $_POST['name'];
    $mobile_number = $_POST['mobile_number'];
    $address = $_POST['address'];

    // Debugging: Check if user_id is valid
    if (!isset($user_id) || empty($user_id)) {
        die("Error: User ID is not set or invalid.");
    }

    // Get today's date
    $current_date = date('Y-m-d');

    // Validate check-in and check-out dates
    if ($check_in_date < $current_date) {
        echo "<script>alert('Check-in date cannot be in the past. Please select a valid date.');</script>";
    } elseif ($check_out_date < $current_date) {
        echo "<script>alert('Check-out date cannot be in the past. Please select a valid date.');</script>";
    } elseif ($check_out_date <= $check_in_date) {
        echo "<script>alert('Check-out date must be after the check-in date.');</script>";
    } else {
        // Check room availability and fetch price
        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE room_type = ?");
        $stmt->execute([$room_type]);
        $room = $stmt->fetch();

        if ($room['total_rooms'] > $room['booked_rooms']) {
            // Calculate the total price
            $check_in = new DateTime($check_in_date);
            $check_out = new DateTime($check_out_date);
            $nights = $check_out->diff($check_in)->days; // Number of nights
            $total_price = $room['price_per_night'] * $nights;

            // Book the room
            $stmt = $pdo->prepare("
                INSERT INTO bookings (user_id, room_type, check_in_date, check_out_date, name, mobile_number, address, total_price)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id,
                $room_type,
                $check_in_date,
                $check_out_date,
                $name,
                $mobile_number,
                $address,
                $total_price
            ]);

            // Update booked_rooms count
            $stmt = $pdo->prepare("UPDATE rooms SET booked_rooms = booked_rooms + 1 WHERE room_type = ?");
            $stmt->execute([$room_type]);

            echo "<script>alert('Room booked successfully! Total Price: ₹" . $total_price . "');</script>";
        } else {
            echo "<script>alert('No rooms available for this type.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Room</title>
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
        }

        h2 {
            color: #0d6efd;
            text-align: center;
            margin-bottom: 30px;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-10px);
        }

        .card-img-top {
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0d6efd;
        }

        .card-text {
            font-size: 1rem;
            color: #333;
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
        <h2>Available Rooms</h2>
        <div class="row">
            <?php foreach ($rooms as $room): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <!-- Room Image -->
                        <?php
                        // Use the default image if no image is available
                        $default_image = 'https://via.placeholder.com/400x200?text=No+Image';
                        $room_image = !empty($room['image']) ? htmlspecialchars($room['image']) : $default_image;
                        ?>
                        <img src="<?= $room_image ?>" class="card-img-top" alt="<?= ucfirst($room['room_type']) ?> Room">
                        <div class="card-body">
                            <h5 class="card-title"><?= ucfirst($room['room_type']) ?></h5>
                            <p class="card-text">Price per Night: ₹<?= $room['price_per_night'] ?></p>
                            <p class="card-text">Total Rooms: <?= $room['total_rooms'] ?></p>
                            <p class="card-text">Available Rooms: <?= $room['total_rooms'] - $room['booked_rooms'] ?></p>
                            <form method="POST" action="">
                                <input type="hidden" name="room_type" value="<?= $room['room_type'] ?>">
                                <div class="mb-3">
                                    <label for="name_<?= $room['room_type'] ?>" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name_<?= $room['room_type'] ?>" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="mobile_number_<?= $room['room_type'] ?>" class="form-label">Mobile Number</label>
                                    <input type="text" class="form-control" id="mobile_number_<?= $room['room_type'] ?>" name="mobile_number" required>
                                </div>
                                <div class="mb-3">
                                    <label for="address_<?= $room['room_type'] ?>" class="form-label">Address</label>
                                    <textarea class="form-control" id="address_<?= $room['room_type'] ?>" name="address" rows="3" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="check_in_date_<?= $room['room_type'] ?>" class="form-label">Check-in Date</label>
                                    <input type="date" class="form-control" id="check_in_date_<?= $room['room_type'] ?>" name="check_in_date" required>
                                </div>
                                <div class="mb-3">
                                    <label for="check_out_date_<?= $room['room_type'] ?>" class="form-label">Check-out Date</label>
                                    <input type="date" class="form-control" id="check_out_date_<?= $room['room_type'] ?>" name="check_out_date" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Book Now</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</body>

</html>