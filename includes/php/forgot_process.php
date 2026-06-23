<?php
// includes/php/forgot_process.php
session_start();
require 'dhb.inc.php'; // Uses your established PDO database connection file

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $password_confirmation = $_POST["password_confirmation"];

    // 1. Validation: Ensure fields aren't empty
    if (empty($email) || empty($password) || empty($password_confirmation)) {
        echo "<script>alert('Please fill in all fields.'); window.history.back();</script>";
        exit();
    }

    // 2. Validation: Ensure passwords match
    if ($password !== $password_confirmation) {
        echo "<script>alert('Password entries do not match. Please try again.'); window.history.back();</script>";
        exit();
    }

    try {
        // 3. Check if the Student Email actually exists in your database table
        $check_sql = "SELECT Username FROM users WHERE Email = :email";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([':email' => $email]);

        if ($check_stmt->rowCount() === 0) {
            echo "<script>alert('This email address is not registered in our system.'); window.history.back();</script>";
            exit();
        }

        // 4. Securely hash the new password entry to match your signup structure
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 5. Update the row directly in the database
        $sql = "UPDATE users 
                SET Password_Hash = :password_hash 
                WHERE Email = :email";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':password_hash' => $hashed_password,
            ':email' => $email
        ]);

        // 6. Alert success and redirect directly to your login screen
        echo "<script>
            alert('Your password has been changed successfully!'); 
            window.location.href='../../login.html';
        </script>";
        exit();

    } catch (PDOException $e) {
        error_log("Direct Password Reset SQL Error: " . $e->getMessage());
        echo "<script>alert('Database error occurred. Please try again.'); window.history.back();</script>";
        exit();
    }
} else {
    header("Location: ../../forgot-password.html");
    exit();
}