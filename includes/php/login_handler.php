<?php
session_start(); 
require 'dhb.inc.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE Username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify using the exact column name from your DB
        if ($user && password_verify($password, $user['Password_Hash'])) {
            
            // Store their UserID in the session so they can post tasks
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['username'] = $user['Username'];

            // Redirect to dashboard
            header("Location: ../../dashboard.html");
            exit();

        } else {
            echo "Invalid username or password.";
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>