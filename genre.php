<?php
// Movya - Genre-Based Discovery Page
require_once "db.php";

$genreList = [
    "Action",
    "Adventure",
    "Comedy",
    "Drama",
    "Thriller",
    "Horror",
    "Romance",
    "Science Fiction",
    "Fantasy",
    "Mystery",
    "Crime",
    "Animation",
    "Documentary",
    "Family",
    "History",
    "War",
    "Music",
    "Western",
    "Biography",
    "Superhero",
    "Psychological Thriller",
    "Anime",
    "Sports",
    "Mystery & Suspense"
];

$selectedGenre = trim($_GET["genre"] ?? "Action");
if (!in_array($selectedGenre, $genreList)) {
    $selectedGenre = "Action";
}

$typeFilter = trim($_GET["type"] ?? "all");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($selectedGenre); ?> Discovery - Movya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark px-3 py-2">
    <a class="navbar-brand brand-text" href="index.php">Movya</a>
    <div class="ms-auto d-flex align-items-center">
        <a href="index.php" class="btn btn-outline-light btn-sm me-2">Home</a>
        <a href="genre.php?genre=Action" class="btn btn-primary btn-sm me-2">Discover</a>
        <?php if (isset($_SESSION["user_id"])): ?>
            <a href="favorites.php" class="btn btn-outline-warning btn-sm me-2">Favorites</a>
            <span class="text-secondary me-2">| <?php echo htmlspecialchars($_SESSION["full_name"] ?? "User"); ?></span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-outline-light btn-sm me-2">Login</a>
            <a href="register.php" class="btn btn-outline-secondary btn-sm">Register</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container py-4">
    <!-- Header with title & Shuffle -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 pb-2 border-bottom border-secondary">
        <div>
            <h2 class="mb-1"><?php echo htmlspecialchars($selectedGenre); ?></h2>
            <p class="text-secondary mb-0">Browse titles from TVmaze & OMDb</p>
        </div>
        <div class="d-flex align-items-center gap-2 mt-3 mt-md-0">
            <!-- Content Type Filter -->
            <select id="genre-type-select" class="form-select form-select-sm w-auto">
                <option value="all" <?php if ($typeFilter === "all") echo "selected"; ?>>All (Movies & Series)</option>
                <option value="movie" <?php if ($typeFilter === "movie") echo "selected"; ?>>Movies Only</option>
                <option value="series" <?php if ($typeFilter === "series") echo "selected"; ?>>TV Series Only</option>
            </select>

            <!-- Shuffle Button -->
            <button id="genre-shuffle-btn" class="btn btn-outline-info btn-sm">
                Shuffle Results
            </button>
        </div>
    </div>

    <!-- Genre Selector Pills Carousel -->
    <div class="mb-4 d-flex gap-2 overflow-auto pb-2" style="white-space: nowrap;">
        <?php foreach ($genreList as $gName): ?>
            <a href="genre.php?genre=<?php echo urlencode($gName); ?>&type=<?php echo urlencode($typeFilter); ?>" 
               class="btn btn-sm <?php echo $gName === $selectedGenre ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                <?php echo htmlspecialchars($gName); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Status message -->
    <div id="genre-status" class="text-center text-muted my-3">
        Loading <?php echo htmlspecialchars($selectedGenre); ?> titles...
    </div>

    <!-- Results Grid -->
    <div id="genre-results" class="row g-4"></div>
</div>

<footer class="text-center py-4 mt-5">
    <div class="container text-secondary small">
        &copy; <?php echo date("Y"); ?> Movya. Powered by TVmaze & OMDb API.
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
<script>
$(document).ready(function() {
    var currentGenre = "<?php echo addslashes($selectedGenre); ?>";
    var currentType = "<?php echo addslashes($typeFilter); ?>";

    loadGenreContent(currentGenre, currentType, false);

    $("#genre-shuffle-btn").on("click", function() {
        loadGenreContent(currentGenre, $("#genre-type-select").val(), true);
    });

    $("#genre-type-select").on("change", function() {
        currentType = $(this).val();
        loadGenreContent(currentGenre, currentType, false);
    });
});
</script>
</body>
</html>
