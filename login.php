<?php
// Movya - User Login
require_once "db.php";

// redirect if already logged in
if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$error = "";

// handle login form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // validation
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // check user in database
        $query = "SELECT id, full_name, password_hash FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            // verify password
            if (password_verify($password, $row["password_hash"])) {
                // store user info in session
                $_SESSION["user_id"] = $row["id"];
                $_SESSION["full_name"] = $row["full_name"];
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "No user found with that email.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login - Movya</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark px-3 py-2">
        <a class="navbar-brand brand-text" href="index.php">Movya</a>
        <div class="ms-auto d-flex align-items-center">
            <a href="index.php" class="btn btn-outline-light btn-sm me-2">Home</a>
            <a href="register.php" class="btn btn-outline-light btn-sm">Register</a>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card p-4 shadow-sm">
                    <h3 class="text-center mb-1 brand-text">Movya</h3>
                    <h6 class="text-center text-secondary mb-4">Login to your account</h6>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>

                    <p class="text-center mt-3 mb-0 small text-secondary">
                        Don't have an account? <a href="register.php" class="text-info text-decoration-none">Register
                            here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>