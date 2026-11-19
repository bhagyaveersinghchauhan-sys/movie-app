<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$getEnv = static function (array $names): ?string {
    foreach ($names as $name) {
        $value = getenv($name);
        if ($value !== false && trim($value) !== "") {
            return trim($value);
        }
    }

    return null;
};

$databaseUrl = $getEnv(["MYSQL_URL", "MYSQL_PUBLIC_URL", "DATABASE_URL"]);
$database = null;

if ($databaseUrl !== null) {
    $database = parse_url($databaseUrl);
    if (
        $database === false
        || !isset($database["scheme"])
        || !in_array(strtolower($database["scheme"]), ["mysql", "mariadb"], true)
        || empty($database["host"])
        || empty($database["user"])
        || !isset($database["path"])
        || trim($database["path"], "/") === ""
    ) {
        error_log("Database connection URL is invalid. Check MYSQL_URL, MYSQL_PUBLIC_URL, or DATABASE_URL.");
        http_response_code(500);
        die("Database configuration is invalid. Check the service's MySQL connection URL.");
    }

    $host = $database["host"];
    $port = $database["port"] ?? 3306;
    $user = rawurldecode($database["user"]);
    $pass = rawurldecode($database["pass"] ?? "");
    $dbName = rawurldecode(trim($database["path"], "/"));
} else {
    $host = $getEnv(["MYSQL_HOST", "MYSQLHOST"]);
    $user = $getEnv(["MYSQL_USER", "MYSQLUSER"]);
    $pass = $getEnv(["MYSQL_PASSWORD", "MYSQLPASSWORD"]);
    $dbName = $getEnv(["MYSQL_DATABASE", "MYSQLDATABASE"]);
    $port = $getEnv(["MYSQL_PORT", "MYSQLPORT"]) ?? "3306";

    $isHosted = $getEnv(["RENDER", "RAILWAY_ENVIRONMENT", "PORT"]) !== null;
    if ($host === null && $isHosted) {
        error_log("Database host is not configured in this hosted environment.");
        http_response_code(500);
        die("Database host is missing. Configure MYSQL_URL or MYSQL_HOST with the correct reachable database hostname.");
    }

    $host = $host ?? "localhost";
    $user = $user ?? "root";
    $pass = $pass ?? "";
    $dbName = $dbName ?? "cineverse_db";
}

if (!is_numeric($port) || (int) $port < 1 || (int) $port > 65535) {
    error_log("Database port is invalid. Check MYSQL_PORT or the database connection URL.");
    http_response_code(500);
    die("Database configuration is invalid. Check the MySQL port.");
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @mysqli_connect($host, $user, $pass, $dbName, (int) $port);

if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    http_response_code(500);
    die("Database connection unavailable. Check the database host, port, credentials, and network access.");
}

if (!mysqli_set_charset($conn, "utf8mb4")) {
    error_log("Unable to set database character set: " . mysqli_error($conn));
    http_response_code(500);
    die("Database connection unavailable. Check the database configuration.");
}
?>
