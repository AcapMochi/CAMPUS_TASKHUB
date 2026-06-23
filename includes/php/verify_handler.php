<?php
session_start();
require 'dhb.inc.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $entered_code = trim($_POST['otp_code']);

    // 1. Ensure the session data exists (prevents direct URL access)
    if (!isset($_SESSION['otp']) || !isset($_SESSION['temp_user'])) {
        echo "<script>alert('Session expired. Please sign up again.'); window.location.href='../../register.html';</script>";
        exit();
    }

    $actual_otp = $_SESSION['otp'];
    $user_data = $_SESSION['temp_user'];

    // 2. Check if the entered code matches the emailed code
    if ($entered_code == $actual_otp) {
        
        try {
            // 3. Insert the user into the database
            // Note: FullName, Phone, and Faculty are passed as empty strings ""
            // Is_Verified is set to 1 (True)
            $sql = "INSERT INTO users (Username, Password_Hash, FullName, Email, Phone, Faculty, StudentID, Role, Is_Verified)
                    VALUES (:username, :password_hash, '', :email, '', '', :studentid, 'User', 1)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':username' => $user_data['username'],
                ':password_hash' => $user_data['password'],
                ':email' => $user_data['email'],
                ':studentid' => $user_data['studentid']
            ]);

            // 4. Clean up session variables so the OTP can't be reused
            unset($_SESSION['otp']);
            unset($_SESSION['temp_user']);

            // 5. Redirect to login with a success flag
            header("Location: ../../login.html?signup=success");
            exit();

        } catch (PDOException $e) {
            error_log("Verification Insert Error: " . $e->getMessage());
            echo "<script>alert('Database error during verification. Please try again.'); window.history.back();</script>";
            exit();
        }

    } else {
        // OTP did not match
        echo "<script>alert('Invalid verification code. Please try again.'); window.history.back();</script>";
        exit();
    }
} else {
    header("Location: ../../verify.html");
    exit();
}
?>