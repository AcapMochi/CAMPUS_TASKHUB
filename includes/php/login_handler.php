<?php
session_start(); 
require 'dhb.inc.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Using 'identifier' to represent either Email or StudentID
    $identifier = trim($_POST['identifier']);
    $password = $_POST['password'];

    try {
        // Look up the user by either their Email OR their Student ID
        $stmt = $pdo->prepare("SELECT * FROM users WHERE Email = :identifier OR StudentID = :identifier LIMIT 1");
        $stmt->execute([':identifier' => $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify using the exact column name 'Password_Hash'
        if ($user && password_verify($password, $user['Password_Hash'])) {
            
            // Regenerate session ID for security
            session_regenerate_id(true);

            // Store UserID and Username in the session
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['username'] = $user['Username'];
            $_SESSION['role'] = $user['Role']; // Useful in case you need to check if they are an admin later

            // Redirect to dashboard
            header("Location: ../../dashboard.html");
            exit();

        } else {
            // Redirect back to login with an error
            header("Location: ../../login.html?error=invalidcredentials");
            exit();
        }

    } catch (PDOException $e) {
        error_log("Login Error: " . $e->getMessage());
        header("Location: ../../login.html?error=sqlerror");
        exit();
    }
} else {
    header("Location: ../../login.html");
    exit();
}
?>