    <?php
    // login.php
    session_start();
    require_once 'db.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Fetch user from the `dash` table
        $stmt = $pdo->prepare("SELECT * FROM dash WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: dashboard.php");
                exit;
            } else {
                header("Location: user_dashboard.php"); // Redirect regular users to their dashboard
                exit;
            }
        } else {
            echo "<script>alert('Invalid email or password.');</script>";
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>

    <body>
        <?php include 'nav.php'; ?>

        <div class="container mt-5">
            <h2 class="text-center">Login</h2>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            <div class="text-center mt-3">
                Don't have an account? <a href="dsignup.php">Signup here</a>
            </div>
        </div>
    </body>

    </html>