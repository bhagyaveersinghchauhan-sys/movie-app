<?php
// Movya - Home & Discover
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movya - Movie & TV Discovery</title>
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
            <a href="index.php" class="btn btn-outline-light btn-sm me-2 active">Home</a>
            <a href="#genres-section" class="btn btn-outline-light btn-sm me-2">Discover</a>
            <?php if (isset($_SESSION["user_id"])): ?>
                <a href="favorites.php" class="btn btn-outline-warning btn-sm me-2">Favorites</a>
                <span class="text-secondary me-2 d-none d-sm-inline">| <?php echo htmlspecialchars($_SESSION["full_name"] ?? "User"); ?></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary btn-sm me-2">Login</a>
                <a href="register.php" class="btn btn-outline-secondary btn-sm">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container py-4">
    <!-- Hero & Search Bar -->
    <div class="text-center py-3 mb-4">
        <h1 class="fw-bold mb-2">Explore <span class="text-primary">Movies & TV Shows</span></h1>
        <p class="text-secondary mb-4">Search across TVmaze and OMDb API datasets by title, filter, and genre.</p>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form id="search-form" class="row g-2">
                    <div class="col-md-6">
                        <input type="text" id="query-input" class="form-control" placeholder="Search title (e.g. Batman, Friends)..." required>
                    </div>
                    <div class="col-md-4">
                        <select id="source-select" class="form-select">
                            <option value="all">All Sources (TVmaze + OMDb)</option>
                            <option value="tvmaze">TVmaze API (TV Shows)</option>
                            <option value="omdb">OMDb API (Movies & Shows)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Discovery Toolbar / Multi-Filter Taskbar -->
    <div class="discovery-toolbar mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-2">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold text-white">Discovery Toolbar:</span>
                <button type="button" id="btn-feed-popular" class="btn btn-sm btn-primary filter-feed-btn" data-feed="popular">Popular</button>
                <button type="button" id="btn-feed-trending" class="btn btn-sm btn-outline-secondary filter-feed-btn" data-feed="trending">Top Rated</button>
            </div>
            <div>
                <button type="button" id="btn-reset-filters" class="btn btn-sm btn-outline-danger">Reset Filters</button>
            </div>
        </div>
        <div class="row g-2 align-items-center">
            <!-- Year filter -->
            <div class="col-sm-4 col-md-3">
                <label class="small text-secondary mb-1">Release Year</label>
                <select id="filter-year" class="form-select form-select-sm">
                    <option value="">All Years</option>
                    <option value="2026">2026</option>
                    <option value="2025">2025</option>
                    <option value="2024">2024</option>
                    <option value="2023">2023</option>
                    <option value="2022">2022</option>
                    <option value="2021">2021</option>
                    <option value="2020">2020</option>
                    <option value="2015">2015 - 2019</option>
                    <option value="2010">2010 - 2014</option>
                    <option value="2000">2000 - 2009</option>
                    <option value="classic">Before 2000 (Classics)</option>
                </select>
            </div>
            <!-- Content Type filter -->
            <div class="col-sm-4 col-md-3">
                <label class="small text-secondary mb-1">Content Type</label>
                <select id="filter-type" class="form-select form-select-sm">
                    <option value="all">All Types</option>
                    <option value="movie">Movies</option>
                    <option value="series">TV Series</option>
                    <option value="ott">OTT / Streaming</option>
                </select>
            </div>
            <!-- Minimum Rating filter -->
            <div class="col-sm-4 col-md-3">
                <label class="small text-secondary mb-1">Minimum Rating</label>
                <select id="filter-rating" class="form-select form-select-sm">
                    <option value="0">Any Rating</option>
                    <option value="5">Rating 5+</option>
                    <option value="6">Rating 6+</option>
                    <option value="7">Rating 7+</option>
                    <option value="8">Rating 8+</option>
                    <option value="9">Rating 9+</option>
                </select>
            </div>
            <!-- Filter active summary -->
            <div class="col-md-3 text-md-end small text-secondary pt-3">
                <span id="active-filter-summary">Showing: Popular (All Content)</span>
            </div>
        </div>
    </div>

    <!-- Status message -->
    <div id="status-msg" class="text-center text-muted mb-3"></div>

    <!-- Content Results Grid -->
    <div id="results-grid" class="row g-4 mb-5"></div>

    <!-- Genre Explorer Section (24 Clickable Genres) -->
    <div id="genres-section" class="pt-4 border-top border-secondary">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h3 class="fw-bold mb-1">Genre Explorer</h3>
                <p class="text-secondary mb-0">Select a genre to browse and filter titles.</p>
            </div>
            <span class="badge bg-secondary">24 Genres Available</span>
        </div>

        <div class="row g-3">
            <?php foreach ($genreList as $name): ?>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <a href="genre.php?genre=<?php echo urlencode($name); ?>" class="genre-card">
                        <span class="genre-title"><?php echo htmlspecialchars($name); ?></span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<footer class="text-center py-4 mt-5">
    <div class="container text-secondary small">
        <p class="mb-1">&copy; <?php echo date("Y"); ?> Movya — Movie and TV Discovery.</p>
        <p class="mb-0">Powered by TVmaze API and OMDb API. Developed with PHP, MySQL, Bootstrap 5 & jQuery.</p>
    </div>
</footer>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>window.MOVYA_OMDB_API_KEY = <?php echo json_encode(getenv("OMDB_API_KEY") ?: "trilogy", JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;</script>
<script src="script.js"></script>
<script>
$(document).ready(function() {
    loadDiscoveryFeed("popular");
});
</script>
</body>
</html>
