<?php
// Movya - Details Page
require_once "db.php";

$source = strtolower(trim($_GET["source"] ?? ""));
$id = trim($_GET["id"] ?? "");
$error = "";
$item = null;

// Validate incoming parameters
if (empty($source) || empty($id)) {
    $error = "Missing content identifier or source.";
} else {
    if ($source === "omdb") {
        $omdbKey = getenv("OMDB_API_KEY") ?: "trilogy";
        $url = "https://www.omdbapi.com/?apikey=" . urlencode($omdbKey) . "&i=" . urlencode($id) . "&plot=full";
        $res = @file_get_contents($url);
        if ($res !== false) {
            $data = json_decode($res, true);
            if (isset($data["Response"]) && $data["Response"] === "True") {
                $item = [
                    "source" => "OMDb",
                    "id" => $data["imdbID"] ?? $id,
                    "title" => $data["Title"] ?? "Unknown Title",
                    "type" => ucfirst($data["Type"] ?? "Movie"),
                    "year" => $data["Year"] ?? "N/A",
                    "released" => $data["Released"] ?? "N/A",
                    "genres" => $data["Genre"] ?? "N/A",
                    "rating" => ($data["imdbRating"] ?? "N/A") !== "N/A" ? $data["imdbRating"] . " / 10" : "Not Rated",
                    "plot" => $data["Plot"] ?? "No synopsis available.",
                    "runtime" => $data["Runtime"] ?? "N/A",
                    "director" => $data["Director"] ?? "N/A",
                    "writers" => $data["Writer"] ?? "N/A",
                    "cast" => $data["Actors"] ?? "N/A",
                    "language" => $data["Language"] ?? "N/A",
                    "country" => $data["Country"] ?? "N/A",
                    "rated" => $data["Rated"] ?? "N/A",
                    "awards" => $data["Awards"] ?? "N/A",
                    "poster" => (!empty($data["Poster"]) && $data["Poster"] !== "N/A") ? $data["Poster"] : "",
                    "imdb_id" => $data["imdbID"] ?? "",
                    "streaming" => "OMDb does not supply streaming availability."
                ];
            } else {
                $error = $data["Error"] ?? "Movie not found on OMDb.";
            }
        } else {
            $error = "Unable to connect to OMDb API.";
        }
    } elseif ($source === "tvmaze") {
        $url = "https://api.tvmaze.com/shows/" . urlencode($id) . "?embed=cast";
        $res = @file_get_contents($url);
        if ($res !== false) {
            $data = json_decode($res, true);
            if (isset($data["id"])) {
                // compile cast list
                $castList = [];
                if (!empty($data["_embedded"]["cast"])) {
                    foreach ($data["_embedded"]["cast"] as $c) {
                        if (isset($c["person"]["name"])) {
                            $castList[] = $c["person"]["name"];
                        }
                    }
                }
                $castStr = !empty($castList) ? implode(", ", array_slice($castList, 0, 8)) : "N/A";

                // genres
                $genresStr = !empty($data["genres"]) ? implode(", ", $data["genres"]) : "N/A";

                // streaming network or webChannel
                $streaming = "N/A";
                if (!empty($data["webChannel"]["name"])) {
                    $streaming = $data["webChannel"]["name"];
                } elseif (!empty($data["network"]["name"])) {
                    $streaming = $data["network"]["name"] . " (Network)";
                }

                $ratingVal = isset($data["rating"]["average"]) && $data["rating"]["average"] ? $data["rating"]["average"] . " / 10" : "Not Rated";
                $yearStr = !empty($data["premiered"]) ? substr($data["premiered"], 0, 4) : "N/A";

                $item = [
                    "source" => "TVmaze",
                    "id" => (string)$data["id"],
                    "title" => $data["name"] ?? "Unknown Title",
                    "type" => "TV Series",
                    "year" => $yearStr,
                    "released" => $data["premiered"] ?? "N/A",
                    "genres" => $genresStr,
                    "rating" => $ratingVal,
                    "plot" => !empty($data["summary"]) ? strip_tags($data["summary"]) : "No synopsis available.",
                    "runtime" => !empty($data["averageRuntime"]) ? $data["averageRuntime"] . " min/ep" : (!empty($data["runtime"]) ? $data["runtime"] . " min/ep" : "N/A"),
                    "director" => "N/A",
                    "writers" => "N/A",
                    "cast" => $castStr,
                    "language" => $data["language"] ?? "N/A",
                    "country" => $data["network"]["country"]["name"] ?? "N/A",
                    "rated" => "N/A",
                    "awards" => "N/A",
                    "poster" => $data["image"]["medium"] ?? ($data["image"]["original"] ?? ""),
                    "imdb_id" => $data["externals"]["imdb"] ?? "",
                    "streaming" => $streaming
                ];
            } else {
                $error = "TV Show not found on TVmaze.";
            }
        } else {
            $error = "Unable to connect to TVmaze API.";
        }
    } else {
        $error = "Unsupported source specified.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $item ? htmlspecialchars($item["title"]) . " - Movya" : "Content Details - Movya"; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark px-3 py-2">
    <a class="navbar-brand brand-text" href="index.php">Movya</a>
    <div class="ms-auto d-flex align-items-center">
        <a href="index.php" class="btn btn-outline-light btn-sm me-2">Home</a>
        <a href="genre.php?genre=Action" class="btn btn-outline-light btn-sm me-2">Discover</a>
        <?php if (isset($_SESSION["user_id"])): ?>
            <a href="favorites.php" class="btn btn-outline-warning btn-sm me-2">Favorites</a>
            <span class="text-secondary me-2">| <?php echo htmlspecialchars($_SESSION["full_name"] ?? "User"); ?></span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-primary btn-sm me-2">Login</a>
            <a href="register.php" class="btn btn-outline-secondary btn-sm">Register</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container py-4">
    <div class="mb-3">
        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm">&larr; Back to Results</a>
        <a href="index.php" class="btn btn-outline-secondary btn-sm ms-2">Home</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger my-4">
            <h5>Error</h5>
            <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php elseif ($item): ?>
        <div class="card p-4">
            <div class="row g-4">
                <!-- Left: Poster & Action -->
                <div class="col-md-4 col-lg-3 text-center">
                    <?php if (!empty($item["poster"])): ?>
                        <img src="<?php echo htmlspecialchars($item["poster"]); ?>" class="details-poster mb-3" alt="<?php echo htmlspecialchars($item["title"]); ?>">
                    <?php else: ?>
                        <div class="movie-poster-placeholder mb-3">No Poster Available</div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION["user_id"])): ?>
                        <button class="btn btn-warning w-100 add-fav-btn"
                            data-source="<?php echo htmlspecialchars($item["source"]); ?>"
                            data-id="<?php echo htmlspecialchars($item["id"]); ?>"
                            data-title="<?php echo htmlspecialchars($item["title"]); ?>"
                            data-year="<?php echo htmlspecialchars($item["year"]); ?>"
                            data-poster="<?php echo htmlspecialchars($item["poster"]); ?>">
                            Add to Favorites
                        </button>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-warning w-100">Login to Favorite</a>
                    <?php endif; ?>
                </div>

                <!-- Right: Metadata -->
                <div class="col-md-8 col-lg-9">
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                        <span class="badge bg-primary"><?php echo htmlspecialchars($item["type"]); ?></span>
                        <span class="badge bg-secondary"><?php echo htmlspecialchars($item["source"]); ?></span>
                        <?php if ($item["rated"] !== "N/A"): ?>
                            <span class="badge bg-dark border"><?php echo htmlspecialchars($item["rated"]); ?></span>
                        <?php endif; ?>
                    </div>

                    <h2 class="mb-2"><?php echo htmlspecialchars($item["title"]); ?> <span class="text-secondary">(<?php echo htmlspecialchars($item["year"]); ?>)</span></h2>
                    
                    <div class="mb-3 text-warning fw-bold">
                        Rating: <?php echo htmlspecialchars($item["rating"]); ?>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-secondary mb-1">Synopsis</h6>
                        <p class="text-light leading-relaxed"><?php echo htmlspecialchars($item["plot"]); ?></p>
                    </div>

                    <hr class="border-secondary my-3">

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <strong>Genres:</strong> <span class="text-secondary"><?php echo htmlspecialchars($item["genres"]); ?></span>
                        </div>
                        <div class="col-sm-6">
                            <strong>Runtime:</strong> <span class="text-secondary"><?php echo htmlspecialchars($item["runtime"]); ?></span>
                        </div>
                        <div class="col-sm-6">
                            <strong>Release Date:</strong> <span class="text-secondary"><?php echo htmlspecialchars($item["released"]); ?></span>
                        </div>
                        <div class="col-sm-6">
                            <strong>Language:</strong> <span class="text-secondary"><?php echo htmlspecialchars($item["language"]); ?></span>
                        </div>
                        <div class="col-sm-6">
                            <strong>Country:</strong> <span class="text-secondary"><?php echo htmlspecialchars($item["country"]); ?></span>
                        </div>
                        <div class="col-sm-6">
                            <strong>Platform / Streaming:</strong> <span class="text-secondary"><?php echo htmlspecialchars($item["streaming"]); ?></span>
                        </div>
                        <div class="col-12">
                            <strong>Cast / Actors:</strong> <span class="text-secondary"><?php echo htmlspecialchars($item["cast"]); ?></span>
                        </div>
                        <?php if ($item["director"] !== "N/A"): ?>
                            <div class="col-12">
                                <strong>Director:</strong> <span class="text-secondary"><?php echo htmlspecialchars($item["director"]); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($item["writers"] !== "N/A"): ?>
                            <div class="col-12">
                                <strong>Writers:</strong> <span class="text-secondary"><?php echo htmlspecialchars($item["writers"]); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($item["awards"] !== "N/A"): ?>
                            <div class="col-12">
                                <strong>Awards:</strong> <span class="text-secondary"><?php echo htmlspecialchars($item["awards"]); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($item["imdb_id"])): ?>
                            <div class="col-12 mt-2">
                                <strong>IMDb Link:</strong> 
                                <a href="https://www.imdb.com/title/<?php echo urlencode($item["imdb_id"]); ?>/" target="_blank" class="text-info text-decoration-none">
                                    View on IMDb
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<footer class="text-center py-4 mt-5">
    <div class="container text-secondary small">
        &copy; <?php echo date("Y"); ?> Movya — Movie and TV Discovery. Powered by TVmaze & OMDb API.
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
</body>
</html>
