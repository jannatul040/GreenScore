<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "gradedunit";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $link = new mysqli($servername, $username, $password, $dbname);
    $link->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    echo "<div class='alert alert-danger'>Database connection failed: "
       . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}
?>