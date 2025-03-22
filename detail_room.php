<?php
session_start();
require_once 'db.php'; // Include the database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}

$user_id = $_SESSION['user_id']; // Fetch the user_id from the session

// Check if room_id is provided in the URL
if (!isset($_GET['room_id'])) {
    header("Location: index.php"); // Redirect to homepage if no room_id is provided
    exit;
}

$room_id = $_GET['room_id'];

// Fetch room details from the database
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->execute([$room_id]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    echo "<script>alert('Room not found!');</script>";
    header("Location: index.php");
    exit;
}

// Decode the services JSON into an array
$services = json_decode($room['services'], true) ?? [];

// Handle form submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = trim($_POST['user_name']);
    $mobile_number = trim($_POST['mobile_number']);
    $address = trim($_POST['address']);
    $check_in_date = $_POST['check_in_date'];
    $check_out_date = $_POST['check_out_date'];

    // Validate input
    if (empty($user_name)) {
        $errors[] = "Name is required.";
    }

    if (empty($mobile_number)) {
        $errors[] = "Mobile number is required.";
    } elseif (!preg_match('/^[0-9]{10}$/', $mobile_number)) {
        $errors[] = "Mobile number must be 10 digits.";
    }

    if (empty($address)) {
        $errors[] = "Address is required.";
    }

    if (empty($check_in_date)) {
        $errors[] = "Check-in date is required.";
    } elseif (strtotime($check_in_date) < strtotime(date('Y-m-d'))) {
        $errors[] = "Check-in date cannot be a past date.";
    }

    if (empty($check_out_date)) {
        $errors[] = "Check-out date is required.";
    } elseif (strtotime($check_out_date) < strtotime(date('Y-m-d'))) {
        $errors[] = "Check-out date cannot be a past date.";
    } elseif (strtotime($check_out_date) <= strtotime($check_in_date)) {
        $errors[] = "Check-out date must be after the check-in date.";
    }

    // If there are no errors, proceed with booking
    if (empty($errors)) {
        $room_type = $room['room_type'];

        try {
            // Insert the booking into the database
            $stmt = $pdo->prepare("
                INSERT INTO bookings (user_id, room_type, check_in_date, check_out_date, status)
                VALUES (?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([$user_id, $room_type, $check_in_date, $check_out_date]);

            // Increase the booked_rooms count in the rooms table
            $stmt = $pdo->prepare("
                UPDATE rooms 
                SET booked_rooms = booked_rooms + 1 
                WHERE id = ?
            ");
            $stmt->execute([$room_id]);

            echo "<script>alert('Booking successful!');</script>";
            header("Location: bookings.php");
            exit;
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            echo "<script>alert('An error occurred while processing your booking. Please try again.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Details - <?= ucfirst($room['room_type']) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="container mt-5">
        <h2>Details of <?= ucfirst($room['room_type']) ?></h2>
        <div class="row">
            <div class="col-md-6">
                <!-- Room Image -->
                <?php if (!empty($room['image'])): ?>
                    <img src="<?= htmlspecialchars($room['image']) ?>" alt="<?= $room['room_type'] ?> Image" class="img-fluid rounded">
                <?php else: ?>
                    <div class="text-center p-5 bg-light rounded">No Image Available</div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <!-- Room Details -->
                <p><strong>Price per Night:</strong> ₹<?= number_format($room['price_per_night'], 2) ?></p>
                <p><strong>Total Rooms:</strong> <?= $room['total_rooms'] ?></p>
                <p><strong>Booked Rooms:</strong> <?= $room['booked_rooms'] ?></p>
                <p><strong>Available Rooms:</strong> <?= $room['total_rooms'] - $room['booked_rooms'] ?></p>

                <!-- Services Section -->
                <h4>Services</h4>
                <?php if (!empty($services)): ?>
                    <ul>
                        <?php foreach ($services as $service): ?>
                            <li><?= htmlspecialchars(ucfirst($service)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No additional services available.</p>
                <?php endif; ?>

                <!-- Booking Form -->
                <h4>Book This Room</h4>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <!-- Booking Form -->
                <div class="text-center">
    <a href="book_rooms.php?room_id=<?= $room_id ?>" class="btn btn-success">Book Now</a>
</div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>