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
    echo "<script>alert('Invalid request. Please try again.');</script>";
    header("Location: index.php");
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
        $price_per_night = $room['price_per_night'];

        // Calculate the number of days and total amount
        $check_in_timestamp = strtotime($check_in_date);
        $check_out_timestamp = strtotime($check_out_date);
        $number_of_days = ($check_out_timestamp - $check_in_timestamp) / (60 * 60 * 24); // Difference in seconds divided by seconds in a day
        $total_amount = $number_of_days * $price_per_night;

        try {
            // Insert the booking into the database including the total amount
            $stmt = $pdo->prepare("
                INSERT INTO bookings (user_id, room_type, price_per_night, check_in_date, check_out_date, amount, status)
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([$user_id, $room_type, $price_per_night, $check_in_date, $check_out_date, $total_amount]);

            // Increase the booked_rooms count in the rooms table
            $stmt = $pdo->prepare("
                UPDATE rooms 
                SET booked_rooms = booked_rooms + 1 
                WHERE id = ?
            ");
            $stmt->execute([$room_id]);

            echo "<script>alert('Booking successful! Total Amount: ₹" . number_format($total_amount, 2) . "');</script>";
            header("Location: bookings.php");
            exit;
        } catch (PDOException $e) {
            // Debugging: Display detailed error message
            echo "<script>alert('Database Error: " . addslashes($e->getMessage()) . "');</script>";
            error_log("Database Error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Room - <?= ucfirst($room['room_type']) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="container mt-5">
        <h2>Book Room - <?= ucfirst($room['room_type']) ?></h2>

        <!-- Display Room Details -->
        <div class="mb-4">
            <p><strong>Room Type:</strong> <?= ucfirst($room['room_type']) ?></p>
            <p><strong>Price per Night:</strong> ₹<?= number_format($room['price_per_night'], 2) ?></p>
        </div>

        <!-- Booking Form -->
        <form method="POST" action="">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Name -->
            <div class="mb-3">
                <label for="user_name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="user_name" name="user_name" required>
            </div>

            <!-- Mobile Number -->
            <div class="mb-3">
                <label for="mobile_number" class="form-label">Mobile Number</label>
                <input type="tel" class="form-control" id="mobile_number" name="mobile_number" pattern="[0-9]{10}" required>
            </div>

            <!-- Address -->
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
            </div>

            <!-- Check-in Date -->
            <div class="mb-3">
                <label for="check_in_date" class="form-label">Check-in Date</label>
                <input type="date" class="form-control" id="check_in_date" name="check_in_date" min="<?= date('Y-m-d') ?>" required>
            </div>

            <!-- Check-out Date -->
            <div class="mb-3">
                <label for="check_out_date" class="form-label">Check-out Date</label>
                <input type="date" class="form-control" id="check_out_date" name="check_out_date" min="<?= date('Y-m-d') ?>" required>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary">Confirm Booking</button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>