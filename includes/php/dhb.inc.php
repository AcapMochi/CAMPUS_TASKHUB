<?php

$dsn = "mysql:host=localhost;dbname=campus_taskhub";
$dbusername = "root";
$dbpassword = "";

try {
    $pdo = new PDO($dsn, $dbusername, $dbpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Use die() instead of echo so the script stops completely if the database is down
    die("Connection failed: ". $e->getMessage());
}

?>