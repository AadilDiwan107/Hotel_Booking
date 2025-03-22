<?php
session_start();
require_once 'db.php'; // Include the database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}

$user_id = $_SESSION['user_id']; // Fetch the user_id from the session

// Check if hall_id is provided in the URL
if (!isset($_GET['hall_id'])) {
    header("Location: index.php"); // Redirect to homepage if no hall_id is provided
    exit;
}

$hall_id = $_GET['hall_id'];

// Fetch hall details from the database
$stmt = $pdo->prepare("SELECT * FROM halls WHERE id = ?");
$stmt->execute([$hall_id]);
$hall = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$hall) {
    echo "<script>alert('Hall not found!');</script>";
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hall Details - <?= ucfirst($hall['hall_name']) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="container mt-5">
        <h2>Details of <?= ucfirst($hall['hall_name']) ?></h2>
        <div class="row">
            <div class="col-md-6">
                <!-- Hall Image -->
                <?php if (!empty($hall['image'])): ?>
                    <img src="<?= htmlspecialchars($hall['image']) ?>" alt="<?= $hall['hall_name'] ?> Image" class="img-fluid rounded">
                <?php else: ?>
                    <div class="text-center p-5 bg-light rounded">No Image Available</div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <!-- Hall Details -->
                <p><strong>Price per Day:</strong> ₹<?= number_format($hall['price_per_day'], 2) ?></p>
                <p><strong>Total Halls:</strong> <?= $hall['total_halls'] ?></p>
                <p><strong>Booked Halls:</strong> <?= $hall['booked_halls'] ?></p>
                <p><strong>Available Halls:</strong> <?= $hall['total_halls'] - $hall['booked_halls'] ?></p>

                <!-- Book Now Button -->
                <div class="text-center mt-4">
                    <a href="hall_book.php?hall_name=<?= urlencode($hall['hall_name']) ?>&price=<?= urlencode($hall['price_per_day']) ?>" class="btn btn-success">Book Now</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>