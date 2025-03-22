<?php
// book_hall.php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all halls from the halls table
$stmt = $pdo->query("SELECT * FROM halls");
$halls = $stmt->fetchAll();

// Handle hall booking form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hall_name = $_POST['hall_name'];
    $event_type = $_POST['event_type'];
    $booking_date = $_POST['booking_date'];
    $check_out_date = $_POST['check_out_date']; // New field for check-out date
    $booking_time = $_POST['booking_time'];
    $name = $_POST['name'];
    $mobile_number = $_POST['mobile_number'];
    $address = $_POST['address'];

    // Validate booking date (must not be in the past)
    $current_date = date('Y-m-d'); // Get today's date in YYYY-MM-DD format
    if ($booking_date < $current_date) {
        echo "<script>alert('Booking date cannot be in the past. Please select a valid date.');</script>";
    } elseif ($check_out_date < $booking_date) {
        echo "<script>alert('Check-out date cannot be earlier than the booking date.');</script>";
    } else {
        // Check hall availability and fetch price
        $stmt = $pdo->prepare("SELECT * FROM halls WHERE hall_name = ?");
        $stmt->execute([$hall_name]);
        $hall = $stmt->fetch();

        if ($hall['total_halls'] > $hall['booked_halls']) {
            // Calculate the number of days
            $booking_date_timestamp = strtotime($booking_date);
            $check_out_date_timestamp = strtotime($check_out_date);
            $number_of_days = ceil(($check_out_date_timestamp - $booking_date_timestamp) / (60 * 60 * 24)) + 1; // Include the start date

            // Calculate the total price
            $price_per_day = $hall['price_per_day'];
            $total_price = $price_per_day * $number_of_days;

            // Book the hall
            $stmt = $pdo->prepare("
                INSERT INTO hall_bookings (user_id, hall_name, event_type, booking_date, check_out_date, booking_time, name, mobile_number, address, total_price)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id,
                $hall_name,
                $event_type,
                $booking_date,
                $check_out_date, // Store check-out date
                $booking_time,
                $name,
                $mobile_number,
                $address,
                $total_price
            ]);

            // Update booked_halls count
            $stmt = $pdo->prepare("UPDATE halls SET booked_halls = booked_halls + 1 WHERE hall_name = ?");
            $stmt->execute([$hall_name]);

            // Insert booking details into the history table
            $stmt = $pdo->prepare("
                INSERT INTO history (user_id, hall_name, event_type, booking_date, check_out_date, booking_time, name, mobile_number, address, booking_status, total_price)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Booked', ?)
            ");
            $stmt->execute([
                $user_id,
                $hall_name,
                $event_type,
                $booking_date,
                $check_out_date, // Store check-out date
                $booking_time,
                $name,
                $mobile_number,
                $address,
                $total_price
            ]);

            echo "<script>alert('Hall booked successfully! Total Price: ₹" . $total_price . "');</script>";
        } else {
            echo "<script>alert('No halls available for this type.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Hall</title>
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

        .no-halls {
            text-align: center;
            color: #6c757d;
            font-size: 1.2rem;
        }
    </style>
</head>

<body>
    <?php include 'nav.php'; ?>
    <div class="container mt-5">
        <h2>Available Halls</h2>
        <div class="row">
            <?php if (empty($halls)): ?>
                <p class="no-halls">No halls are available at the moment.</p>
            <?php else: ?>
                <?php foreach ($halls as $hall): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <!-- Hall Image -->
                            <?php
                            // Use the default image if no image is available
                            $default_image = 'https://via.placeholder.com/400x200?text=No+Image';
                            $hall_image = !empty($hall['image']) ? htmlspecialchars($hall['image']) : $default_image;
                            ?>
                            <img src="<?= $hall_image ?>" class="card-img-top" alt="<?= ucfirst($hall['hall_name']) ?> Hall">
                            <div class="card-body">
                                <h5 class="card-title"><?= ucfirst($hall['hall_name']) ?></h5>
                                <p class="card-text">Price per Day: ₹<?= $hall['price_per_day'] ?></p>
                                <p class="card-text">Total Halls: <?= $hall['total_halls'] ?></p>
                                <p class="card-text">Available Halls: <?= $hall['total_halls'] - $hall['booked_halls'] ?></p>
                                <form method="POST" action="">
                                    <input type="hidden" name="hall_name" value="<?= $hall['hall_name'] ?>">
                                    <div class="mb-3">
                                        <label for="event_type_<?= $hall['hall_name'] ?>" class="form-label">Event Type</label>
                                        <input type="text" class="form-control" id="event_type_<?= $hall['hall_name'] ?>" name="event_type" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="booking_date_<?= $hall['hall_name'] ?>" class="form-label">Booking Date</label>
                                        <input type="date" class="form-control" id="booking_date_<?= $hall['hall_name'] ?>" name="booking_date" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="booking_time_<?= $hall['hall_name'] ?>" class="form-label">Booking Time</label>
                                        <input type="time" class="form-control" id="booking_time_<?= $hall['hall_name'] ?>" name="booking_time" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="name_<?= $hall['hall_name'] ?>" class="form-label">Name</label>
                                        <input type="text" class="form-control" id="name_<?= $hall['hall_name'] ?>" name="name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="mobile_number_<?= $hall['hall_name'] ?>" class="form-label">Mobile Number</label>
                                        <input type="text" class="form-control" id="mobile_number_<?= $hall['hall_name'] ?>" name="mobile_number" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="address_<?= $hall['hall_name'] ?>" class="form-label">Address</label>
                                        <textarea class="form-control" id="address_<?= $hall['hall_name'] ?>" name="address" rows="3" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Book Now</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>