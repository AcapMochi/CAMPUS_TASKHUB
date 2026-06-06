<?php
// Start a session so we can keep the user logged in across pages
session_start(); 

// 1. Include your database connection
require 'dhb.inc.php';

// 2. Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // 3. Find the user in the database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 4. Check if user exists AND password is correct
        if ($user && password_verify($password, $user['password'])) {
            
            // Success! Store user info in the session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            echo "Login successful! Welcome back, " . htmlspecialchars($user['username']);
            // Usually, you redirect them to the dashboard here:
            // header("Location: dashboard.php");
            // exit();

        } else {
            // Generic error message is safer so attackers don't know if they guessed a valid username
            echo "Invalid username or password.";
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>