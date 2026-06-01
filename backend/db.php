<?php

$host = "zephyr.proxy.rlwy.net";
$user = "root";
$pass = "uLEmqvacbSLMJLyuafnBpgQIyGqlcqUQ";
$db   = "railway";
$port = 17352;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
$result = $conn->query("SELECT 1");
if (!$result) {
    die("DB broken: " . $conn->error);
}
?>
