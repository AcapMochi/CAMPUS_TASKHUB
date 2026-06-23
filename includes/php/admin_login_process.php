<?php
// includes/php/admin_login_process.php
session_start();
require 'dhb.inc.php'; // Your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Grab the submitted form data
    $identifier = trim($_POST['admin_identifier']);
    $password = $_POST['admin_password'];

    // 2. Check for empty inputs
    if (empty($identifier) || empty($password)) {
        header("Location: ../../adminLogin.html?error=emptyfields");
        exit();
    }

    try {
        // 3. Query the database 
        // Using the 'users' table and checking for the 'Admin' role based on your schema
        $sql = "SELECT * FROM users WHERE (Username = :identifier OR Email = :identifier) AND Role = 'Admin' LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':identifier' => $identifier]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // 4. Verify user exists and password is correct
        if ($admin) {
            // Using your exact column name: Password_Hash
            if (password_verify($password, $admin['Password_Hash'])) {
                
                // --- SUCCESSFUL LOGIN ---
                
                // Prevent Session Fixation attacks
                session_regenerate_id(true); 

                // Set admin session variables
                $_SESSION['admin_id'] = $admin['UserID']; // Using your exact column name
                $_SESSION['admin_username'] = $admin['Username'];
                $_SESSION['is_admin'] = true; // Crucial flag to protect admin pages later

                // Redirect to the Admin Dashboard
                header("Location: ../../adminDashboard.php");
                exit();

            } else {
                // Password incorrect
                header("Location: ../../adminLogin.html?error=wrongpassword");
                exit();
            }
        } else {
            // No admin found with that username/email
            header("Location: ../../adminLogin.html?error=nouser");
            exit();
        }

    } catch (PDOException $e) {
        // Catch database errors
        error_log("Admin Login Error: " . $e->getMessage()); 
        header("Location: ../../adminLogin.html?error=sqlerror");
        exit();
    }

} else {
    // If someone tries to access this file directly without submitting the form
    header("Location: ../../adminLogin.html");
    exit();
}
?>