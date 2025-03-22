<?php
require_once 'nav.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <!-- Add Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        /* Set a fixed height for carousel images */
        .carousel-item img {
            height: 400px; /* Adjust this value as needed */
            object-fit: cover; /* Ensures the image covers the area without distortion */
            width: 100%; /* Ensure the image spans the full width of the carousel */
        }

        /* Styling for booking-related cards */
        .booking-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .booking-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .booking-card h5 {
            color: #0d6efd;
        }

        .booking-card p {
            font-size: 14px;
            color: #555;
        }

        .booking-card a {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .booking-card a:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>

<body>

    <!-- Carousel Section -->
    <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <!-- First Image -->
            <div class="carousel-item active">
                <img src="https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2" class="d-block w-100" alt="Image 1">
            </div>
            <!-- Second Image -->
            <div class="carousel-item">
                <img src="https://images.pexels.com/photos/262047/pexels-photo-262047.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2" class="d-block w-100" alt="Image 2">
            </div>
            <!-- Third Image -->
            <div class="carousel-item">
                <img src="https://images.pexels.com/photos/261395/pexels-photo-261395.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2" class="d-block w-100" alt="Image 3">
            </div>
        </div>
        <!-- Carousel Controls -->
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <div class="container mt-5">
        <h1>Welcome to the Hotel Booking System</h1>
        <p>This is the homepage of the hotel booking system. Explore our luxurious rooms and elegant halls for your next stay or event.</p>

        <!-- Booking Options -->
        <div class="row mt-5">
            <div class="col-md-6">
                <div class="booking-card">
                    <h5>Book a Room</h5>
                    <p>Enjoy a comfortable stay in one of our luxurious rooms. Choose from a variety of room types to suit your needs.</p>
                    <a href="rooms.php" class="btn btn-primary">Book Now</a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="booking-card">
                    <h5>Book a Hall</h5>
                    <p>Host your next event in one of our spacious and elegant halls. Perfect for weddings, conferences, and more.</p>
                    <a href="view_hall.php" class="btn btn-success">Book Now</a>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="row mt-5">
            <div class="col-md-4">
                <div class="booking-card">
                    <h5>Easy Booking</h5>
                    <p>Our user-friendly interface makes it easy to book rooms and halls in just a few clicks.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="booking-card">
                    <h5>Secure Payments</h5>
                    <p>All transactions are processed securely to ensure your peace of mind.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="booking-card">
                    <h5>24/7 Support</h5>
                    <p>Our support team is available around the clock to assist you with any queries or issues.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var myCarousel = document.getElementById('carouselExample');
        var carousel = new bootstrap.Carousel(myCarousel, {
            interval: 3000 // Change slide every 3 seconds
        });
    </script>
    <?php include 'footer.php'; ?>
</body>

</html>