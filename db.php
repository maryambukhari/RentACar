<?php
$host = "localhost";
$dbname = "db2hxahh84hsv5";
$username = "uxhc7qjwxxfub";
$password = "g4t0vezqttq6";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
