<?php
session_start();
require 'dhb.inc.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // We use backticks (`) around column names that contain spaces
        $sql = "INSERT INTO users (Username, `Password Hash`, Email, FullName, Phone, Faculty, StudentID, `Bio Text`, `Is Verified`) 
                VALUES (:username, :password, :email, :fullname, :phone, :faculty, :studentid, :bio, :is_verified)";
        
        $stmt = $pdo->prepare($sql);
        
        // Passing empty strings for the required fields that aren't on the signup form
        $stmt->execute([
            ':username' => $username,
            ':password' => $hashed_password,
            ':email' => $email,
            ':fullname' => '',
            ':phone' => '',
            ':faculty' => '',
            ':studentid' => '',
            ':bio' => '',
            ':is_verified' => 0
        ]);

        echo "Signup successful! You can now <a href='../login.html'>Login</a>";

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo "Username or Email already exists. Please choose another.";
        } else {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>