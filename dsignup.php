<?php
// signup.php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = 'user'; // Default role is 'user'

    // Check if the email already exists
    $stmt = $pdo->prepare("SELECT * FROM dash WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        echo "<script>alert('Email already exists. Please use a different email.');</script>";
    } else {
        // Insert new user into the database
        $stmt = $pdo->prepare("INSERT INTO dash (username, email, password, role) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$username, $email, $password, $role])) {
            echo "<script>alert('Signup successful! Please login.'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Error during signup. Please try again.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'nav.php'; ?>

    <div class="container mt-5">
        <h2 class="text-center">Signup</h2>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Signup</button>
        </form>
        <div class="text-center mt-3">
            Already have an account? <a href="dlogin.php">Login here</a>
        </div>
    </div>
</body>

</html>