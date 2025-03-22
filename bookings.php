<?php
session_start();
require_once 'db.php'; // Include the database connection file

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch the user's details (including name) from the database
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$username = $user['username'] ?? 'Guest'; // Default to 'Guest' if username is not set

// Handle cancel booking request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_booking'])) {
    $booking_id = $_POST['booking_id'];
    $table = $_POST['table']; // Identify whether it's a room or hall booking
    $password = $_POST['password'];
    // Verify user credentials
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        if ($table === 'bookings') {
            // Fetch room type before deleting the booking
            $stmt = $pdo->prepare("SELECT room_type FROM bookings WHERE id = ? AND user_id = ?");
            $stmt->execute([$booking_id, $user_id]);
            $booking = $stmt->fetch();
            if ($booking) {
                $room_type = $booking['room_type'];
                // Delete the booking
                $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
                $delete_result = $stmt->execute([$booking_id, $user_id]);
                if ($delete_result) {
                    // Decrease booked_rooms count in rooms table
                    $stmt = $pdo->prepare("
                        UPDATE rooms 
                        SET booked_rooms = booked_rooms - 1 
                        WHERE room_type = ?
                    ");
                    $update_result = $stmt->execute([$room_type]);
                    // Debugging: Check if the update was successful
                    if ($update_result && $stmt->rowCount() > 0) {
                        echo "<script>alert('Room booking canceled successfully!');</script>";
                    } else {
                        echo "<script>alert('Failed to update room availability. Please try again.');</script>";
                    }
                } else {
                    echo "<script>alert('Failed to delete booking. Please try again.');</script>";
                }
            } else {
                echo "<script>alert('Booking not found or unauthorized access.');</script>";
            }
        } elseif ($table === 'hall_bookings') {
            // Fetch hall name before deleting the booking
            $stmt = $pdo->prepare("SELECT hall_name FROM hall_bookings WHERE id = ? AND user_id = ?");
            $stmt->execute([$booking_id, $user_id]);
            $booking = $stmt->fetch();
            if ($booking) {
                $hall_name = $booking['hall_name'];
                // Delete the booking
                $stmt = $pdo->prepare("DELETE FROM hall_bookings WHERE id = ? AND user_id = ?");
                $delete_result = $stmt->execute([$booking_id, $user_id]);
                if ($delete_result) {
                    // Decrease booked_halls count in halls table
                    $stmt = $pdo->prepare("
                        UPDATE halls 
                        SET booked_halls = booked_halls - 1 
                        WHERE hall_name = ?
                    ");
                    $update_result = $stmt->execute([$hall_name]);
                    // Debugging: Check if the update was successful
                    if ($update_result && $stmt->rowCount() > 0) {
                        echo "<script>alert('Hall booking canceled successfully!');</script>";
                    } else {
                        echo "<script>alert('Failed to update hall availability. Please try again.');</script>";
                    }
                } else {
                    echo "<script>alert('Failed to delete booking. Please try again.');</script>";
                }
            } else {
                echo "<script>alert('Booking not found or unauthorized access.');</script>";
            }
        }
    } else {
        echo "<script>alert('Invalid password. Please try again.');</script>";
    }
}

// Fetch room bookings for the logged-in user
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ?");
$stmt->execute([$user_id]);
$room_bookings = $stmt->fetchAll();

// Fetch hall bookings for the logged-in user
$stmt = $pdo->prepare("SELECT * FROM hall_bookings WHERE user_id = ?");
$stmt->execute([$user_id]);
$hall_bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings</title>
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
        h4 {
            color: #333;
            margin-top: 30px;
        }
        .table {
            margin-top: 20px;
        }
        .table th {
            background-color: #f8f9fa; /* Light background */
            color: black; /* Change text color to black */
            text-align: center;
        }
        .table td {
            text-align: center;
            vertical-align: middle;
        }
        .btn-danger {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .modal-body {
            text-align: center;
        }
        .modal-body input {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ced4da;
        }
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
            border-radius: 5px;
            padding: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container mt-5">
        <h2>Welcome, <?= htmlspecialchars($username) ?>!</h2> <!-- Display the user's name -->
        <h2>My Bookings</h2>
       <!-- Room Bookings Section -->
<h4>Room Bookings</h4>
<?php if (empty($room_bookings)): ?>
    <div class="alert alert-info text-center">
        You have no room bookings yet.
    </div>
<?php else: ?>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Room Type</th>
                <th>Check-in Date</th>
                <th>Check-out Date</th>
                <th>Status</th>
                <th>Total Price</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($room_bookings as $index => $booking): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= ucfirst($booking['room_type']) ?></td>
                    <td><?= $booking['check_in_date'] ?></td>
                    <td><?= $booking['check_out_date'] ?></td>
                    <td><?= ucfirst($booking['status']) ?></td>
                    <td>₹<?= isset($booking['amount']) ? number_format($booking['amount'], 2) : '0.00' ?></td>
                    <td>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal<?= $booking['id'] ?>">Cancel Booking</button>
                        <!-- Cancel Booking Modal -->
                        <div class="modal fade" id="cancelModal<?= $booking['id'] ?>" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="cancelModalLabel">Cancel Booking</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" action="">
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <input type="hidden" name="table" value="bookings">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                            <button type="submit" name="cancel_booking" class="btn btn-danger mt-3">Confirm Cancellation</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- Hall Bookings Section -->
<h4>Hall Bookings</h4>
<?php if (empty($hall_bookings)): ?>
    <div class="alert alert-info text-center">
        You have no hall bookings yet.
    </div>
<?php else: ?>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Hall Name</th>
                <th>Booking Date</th>
                <th>Booking Time</th>
                <th>Status</th>
                <th>Total Price</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($hall_bookings as $index => $booking): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= $booking['hall_name'] ?></td>
                    <td><?= $booking['booking_date'] ?></td>
                    <td><?= $booking['created_at'] ?></td>
                    <td><?= ucfirst($booking['status'] ?? 'Pending') ?></td>
                    <td>₹<?= isset($booking['price']) ? number_format($booking['price'], 2) : '0.00' ?></td>
                    <td>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal<?= $booking['id'] ?>">Cancel Booking</button>
                        <!-- Cancel Booking Modal -->
                        <div class="modal fade" id="cancelModal<?= $booking['id'] ?>" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="cancelModalLabel">Cancel Booking</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" action="">
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <input type="hidden" name="table" value="hall_bookings">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                            <button type="submit" name="cancel_booking" class="btn btn-danger mt-3">Confirm Cancellation</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
        <div class="text-center mt-4">
            <a href="book_room.php" class="btn btn-primary">Book Another Room</a>
            <a href="hallbooking.php" class="btn btn-success">Book a Hall</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>