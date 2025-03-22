<?php
session_start();
require_once 'db.php'; // Include the database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}

$user_id = $_SESSION['user_id']; // Fetch the user_id from the session

// Check if hall_name and price are provided in the URL
if (!isset($_GET['hall_name']) || !isset($_GET['price'])) {
    echo "<script>alert('Invalid request. Please try again.');</script>";
    header("Location: index.php");
    exit;
}

$hall_name = $_GET['hall_name'];
$price = $_GET['price'];

// Handle form submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = trim($_POST['user_name']);
    $mobile_number = trim($_POST['mobile_number']);
    $address = trim($_POST['address']);
    $booking_date = $_POST['booking_date'];
    $event_type = trim($_POST['event_type']);

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

    if (empty($booking_date)) {
        $errors[] = "Booking date is required.";
    } elseif (strtotime($booking_date) < strtotime(date('Y-m-d'))) {
        $errors[] = "Booking date cannot be a past date.";
    }

    if (empty($event_type)) {
        $errors[] = "Event type is required.";
    }

    // If there are no errors, proceed with booking
    if (empty($errors)) {
        try {
            // Insert the booking into the database
            $stmt = $pdo->prepare("
                INSERT INTO hall_bookings (user_id, hall_name, price, booking_date, event_type, status)
                VALUES (?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([$user_id, $hall_name, $price, $booking_date, $event_type]);

            // Increase the booked_halls count in the halls table
            $stmt = $pdo->prepare("
                UPDATE halls 
                SET booked_halls = booked_halls + 1 
                WHERE hall_name = ?
            ");
            $stmt->execute([$hall_name]);

            echo "<script>alert('Hall booking successful!');</script>";
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
    <title>Book Hall - <?= htmlspecialchars($hall_name) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="container mt-5">
        <h2>Book Hall - <?= htmlspecialchars($hall_name) ?></h2>

        <!-- Display Hall Details -->
        <div class="mb-4">
            <p><strong>Hall Name:</strong> <?= htmlspecialchars($hall_name) ?></p>
            <p><strong>Price per Day:</strong> ₹<?= number_format($price, 2) ?></p>
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

            <!-- User Name -->
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

            <!-- Booking Date -->
            <div class="mb-3">
                <label for="booking_date" class="form-label">Booking Date</label>
                <input type="date" class="form-control" id="booking_date" name="booking_date" min="<?= date('Y-m-d') ?>" required>
            </div>

            <!-- Event Type -->
            <div class="mb-3">
                <label for="event_type" class="form-label">Event Type</label>
                <input type="text" class="form-control" id="event_type" name="event_type" placeholder="e.g., Wedding, Conference" required>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary">Confirm Booking</button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>