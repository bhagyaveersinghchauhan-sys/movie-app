<?php
// Movya - User Favorites
require_once "db.php";

// check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$name = $_SESSION["full_name"] ?? "User";

// handle remove favorite
if (isset($_GET["delete"])) {
    $del_id = intval($_GET["delete"]);
    $del_query = "DELETE FROM favorites WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $del_query);
    mysqli_stmt_bind_param($stmt, "ii", $del_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: favorites.php");
    exit();
}

// fetch favorites for current user
$query = "SELECT * FROM favorites WHERE user_id = ? ORDER BY id DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Favorites - Movya</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark px-3 py-2 sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand brand-text" href="index.php">Movya</a>
        <div class="ms-auto d-flex align-items-center">
            <a href="index.php" class="btn btn-outline-light btn-sm me-2">Home</a>
            <a href="genre.php?genre=Action" class="btn btn-outline-light btn-sm me-2">Discover</a>
            <a href="favorites.php" class="btn btn-warning btn-sm me-2">Favorites</a>
            <span class="text-secondary me-2 d-none d-sm-inline">| <?php echo htmlspecialchars($name); ?></span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
        <div>
            <h3 class="mb-1">My Saved Favorites</h3>
            <p class="text-secondary mb-0">Your saved movies and TV shows from OMDb and TVmaze</p>
        </div>
        <a href="index.php" class="btn btn-outline-primary btn-sm">+ Discover More</a>
    </div>

    <?php if (mysqli_num_rows($result) == 0): ?>
        <div class="alert alert-info">
            You haven't saved any movies or shows yet! <a href="index.php" class="fw-bold text-decoration-none">Click here to explore and add some.</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <?php
                    $sourceParam = strtolower($row["source"]);
                    $detailUrl = "details.php?source=" . urlencode($sourceParam) . "&id=" . urlencode($row["external_id"]);
                ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="card h-100">
                        <a href="<?php echo $detailUrl; ?>" class="text-decoration-none">
                            <?php if (!empty($row["poster"]) && $row["poster"] != "N/A"): ?>
                                <img src="<?php echo htmlspecialchars($row["poster"]); ?>" class="card-img-top movie-poster" alt="<?php echo htmlspecialchars($row["title"]); ?>">
                            <?php else: ?>
                                <div class="movie-poster-placeholder">
                                    <span>No Poster</span>
                                </div>
                            <?php endif; ?>
                        </a>
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title">
                                <a href="<?php echo $detailUrl; ?>" class="text-white text-decoration-none">
                                    <?php echo htmlspecialchars($row["title"]); ?>
                                </a>
                            </h6>
                            <p class="card-text text-secondary mb-1 small">Year: <?php echo htmlspecialchars($row["year"]); ?></p>
                            <span class="badge bg-secondary mb-3 w-50"><?php echo htmlspecialchars($row["source"]); ?></span>
                            
                            <div class="mt-auto d-flex gap-2">
                                <a href="<?php echo $detailUrl; ?>" class="btn btn-sm btn-outline-info flex-grow-1">Details</a>
                                <a href="favorites.php?delete=<?php echo $row["id"]; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove from favorites?')">Remove</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<footer class="text-center py-4 mt-5">
    <div class="container text-secondary small">
        &copy; <?php echo date("Y"); ?> Movya. Powered by TVmaze & OMDb API.
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
mysqli_stmt_close($stmt);
?>
