<?php
session_start();
require 'dhb.inc.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_code = trim($_POST['otp']);

    // 1. Check if the code exists in session and matches what the user typed
    if (isset($_SESSION['otp']) && $user_code == $_SESSION['otp']) {
        
        // 2. Retrieve the user details we saved during signup
        $user = $_SESSION['temp_user'];

        try {
            // 3. Insert the official record into the database
            $sql = "INSERT INTO users (Username, `Password_Hash`, Email, FullName, Phone, Faculty, StudentID, `Bio_Text`, `Is_Verified`) 
                    VALUES (:username, :password, :email, :fullname, :phone, :faculty, :studentid, :bio, :is_verified)";
            
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ':username' => $user['username'],
                ':password' => $user['password'], // This was already hashed in the signup handler
                ':email' => $user['email'],
                ':fullname' => '',
                ':phone' => '',
                ':faculty' => '',
                ':studentid' => '',
                ':bio' => '',
                ':is_verified' => 1 // Set to 1 because they successfully verified!
            ]);

            // 4. Clear the session data so it can't be reused
            unset($_SESSION['otp']);
            unset($_SESSION['temp_user']);

            // 5. Redirect to login with a success message
            echo "<script>alert('Email verified successfully! You can now log in.'); window.location.href='../../login.html';</script>";
            exit();

        } catch (PDOException $e) {
            echo "Database Error: " . $e->getMessage();
        }

    } else {
        // The code was incorrect
        echo "<script>alert('Incorrect verification code. Please try again.'); window.history.back();</script>";
    }
}
?>