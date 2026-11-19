<?php
if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

$host = getenv("MYSQL_HOST") ?: getenv("MYSQLHOST") ?: "localhost";
$user = getenv("MYSQL_USER") ?: getenv("MYSQLUSER") ?: "root";
$pass = getenv("MYSQL_PASSWORD") ?: getenv("MYSQLPASSWORD") ?: "";
$db_name = getenv("MYSQL_DATABASE") ?: getenv("MYSQLDATABASE") ?: "cineverse_db";
$port = (int) (getenv("MYSQL_PORT") ?: getenv("MYSQLPORT") ?: 3306);

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @mysqli_connect($host, $user, $pass, $db_name, $port);

if (!$conn) {
   error_log("Database connection failed: " . mysqli_connect_error());
   http_response_code(500);
   die("Database connection unavailable. Check the MySQL environment variables and service connection.");
}
?>
