<?php
// check if user is logged in
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["status" => "error", "message" => "Please login first."]);
    exit();
}

require_once "db.php";

$user_id = $_SESSION["user_id"];
$source = trim($_POST["source"] ?? "");
$external_id = trim($_POST["external_id"] ?? "");
$title = trim($_POST["title"] ?? "");
$year = trim($_POST["year"] ?? "");
$poster = trim($_POST["poster"] ?? "");

// validate required fields
if (empty($source) || empty($external_id) || empty($title)) {
    echo json_encode(["status" => "error", "message" => "Missing required information."]);
    exit();
}

// check if already added
$check_query = "SELECT id FROM favorites WHERE user_id = ? AND source = ? AND external_id = ?";
$stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($stmt, "iss", $user_id, $source, $external_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    echo json_encode(["status" => "exists", "message" => "Already in your favorites!"]);
    mysqli_stmt_close($stmt);
    exit();
}
mysqli_stmt_close($stmt);

// insert favorite
$insert_query = "INSERT INTO favorites (user_id, source, external_id, title, year, poster) VALUES (?, ?, ?, ?, ?, ?)";
$stmt2 = mysqli_prepare($conn, $insert_query);
mysqli_stmt_bind_param($stmt2, "isssss", $user_id, $source, $external_id, $title, $year, $poster);

if (mysqli_stmt_execute($stmt2)) {
    echo json_encode(["status" => "success", "message" => "Added to favorites!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Could not add favorite."]);
}

mysqli_stmt_close($stmt2);
?>
