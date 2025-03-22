<?php
// dashboard.php
session_start();
require_once 'db.php';

// Ensure only admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: dlogin.php");
    exit;
}

// Handle "Check In/Out" button submission for room bookings
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['check_status'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];
    $table = $_POST['table']; // Identify whether it's a room or hall booking

    if ($table === 'bookings') {
        // Update room booking status
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$status, $booking_id]);
    } elseif ($table === 'hall_bookings') {
        // Update hall booking status
        $stmt = $pdo->prepare("UPDATE hall_bookings SET status = ? WHERE id = ?");
        $stmt->execute([$status, $booking_id]);
    }

    // Redirect back to the dashboard to reflect changes
    header("Location: dashboard.php");
    exit;
}

// Fetch all room bookings with user details
$stmt = $pdo->query("
    SELECT bookings.id, users.username, users.email, bookings.room_type, bookings.check_in_date, bookings.check_out_date, bookings.status
    FROM bookings
    JOIN users ON bookings.user_id = users.id
");
$room_bookings = $stmt->fetchAll();

// Fetch all hall bookings with user details
$stmt = $pdo->query("
    SELECT hall_bookings.id, users.username, users.email, hall_bookings.hall_name, hall_bookings.event_type, hall_bookings.booking_date, hall_bookings.booking_time, hall_bookings.status
    FROM hall_bookings
    JOIN users ON hall_bookings.user_id = users.id
");
$hall_bookings = $stmt->fetchAll();

// Fetch room availability
$stmt = $pdo->query("SELECT * FROM rooms");
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch hall availability
$stmt = $pdo->query("SELECT * FROM halls");
$halls = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - All Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <!-- Room Availability Section -->
        <h2 class="text-center">Room Availability</h2>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Room Type</th>
                    <th>Total Rooms</th>
                    <th>Booked Rooms</th>
                    <th>Available Rooms</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rooms as $room): ?>
                    <tr>
                        <td><?= ucfirst($room['room_type']) ?></td>
                        <td><?= $room['total_rooms'] ?></td>
                        <td><?= $room['booked_rooms'] ?></td>
                        <td><?= $room['total_rooms'] - $room['booked_rooms'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Hall Availability Section -->
        <h2 class="text-center mt-5">Hall Availability</h2>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Hall Name</th>
                    <th>Total Halls</th>
                    <th>Booked Halls</th>
                    <th>Available Halls</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($halls as $hall): ?>
                    <tr>
                        <td><?= $hall['hall_name'] ?></td>
                        <td><?= $hall['total_halls'] ?></td>
                        <td><?= $hall['booked_halls'] ?></td>
                        <td><?= $hall['total_halls'] - $hall['booked_halls'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Room Bookings Section -->
        <h2 class="text-center mt-5">All Room Bookings</h2>
        <?php if (empty($room_bookings)): ?>
            <div class="alert alert-info text-center">
                No room bookings found.
            </div>
        <?php else: ?>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Room Type</th>
                        <th>Check-in Date</th>
                        <th>Check-out Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($room_bookings as $index => $booking): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($booking['username']) ?></td>
                            <td><?= htmlspecialchars($booking['email']) ?></td>
                            <td><?= ucfirst($booking['room_type']) ?></td>
                            <td><?= $booking['check_in_date'] ?></td>
                            <td><?= $booking['check_out_date'] ?></td>
                            <td><?= ucfirst($booking['status']) ?></td>
                            <td>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                    <input type="hidden" name="table" value="bookings">
                                    <select name="status" class="form-select form-select-sm d-inline" style="width: auto;">
                                        <option value="pending" <?= $booking['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="checked_in" <?= $booking['status'] === 'checked_in' ? 'selected' : '' ?>>Checked In</option>
                                        <option value="checked_out" <?= $booking['status'] === 'checked_out' ? 'selected' : '' ?>>Checked Out</option>
                                    </select>
                                    <button type="submit" name="check_status" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Hall Bookings Section -->
        <h2 class="text-center mt-5">All Hall Bookings</h2>
        <?php if (empty($hall_bookings)): ?>
            <div class="alert alert-info text-center">
                No hall bookings found.
            </div>
        <?php else: ?>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Hall Name</th>
                        <th>Event Type</th>
                        <th>Booking Date</th>
                        <th>Booking Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hall_bookings as $index => $booking): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($booking['username']) ?></td>
                            <td><?= htmlspecialchars($booking['email']) ?></td>
                            <td><?= $booking['hall_name'] ?></td>
                            <td><?= $booking['event_type'] ?></td>
                            <td><?= $booking['booking_date'] ?></td>
                            <td><?= $booking['booking_time'] ?></td>
                            <td><?= ucfirst($booking['status'] ?? 'Pending') ?></td>
                            <td>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                    <input type="hidden" name="table" value="hall_bookings">
                                    <select name="status" class="form-select form-select-sm d-inline" style="width: auto;">
                                        <option value="pending" <?= $booking['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="confirmed" <?= $booking['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                        <option value="cancelled" <?= $booking['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="check_status" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>
</body>

</html>