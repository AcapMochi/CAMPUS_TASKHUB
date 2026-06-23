<?php
session_start();
require 'dhb.inc.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 1. VALIDATION: Ensure it is a valid student email
    // Adjust this domain if your university uses something different!
    $allowed_domain = "@student.utem.edu.my";
    if (strpos($email, $allowed_domain) === false) {
        // Redirect back with an error code
        header("Location: ../../signup.html?error=invalidemail");
        exit();
    }

    // Extract StudentID from the email (everything before the '@')
    $emailParts = explode('@', $email);
    $studentid = strtoupper($emailParts[0]); 

    try {
        // 2. Check if the username, email, OR StudentID already exists
        $check_sql = "SELECT Username, Email FROM users WHERE Username = :username OR Email = :email OR StudentID = :studentid";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([
            ':username' => $username, 
            ':email' => $email,
            ':studentid' => $studentid
        ]);
        
        if ($check_stmt->rowCount() > 0) {
            // Better UX: Send them back to the form with an error in the URL
            header("Location: ../../signup.html?error=userexists");
            exit();
        }

        // 3. Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 4. Insert the user into the database
        $insert_sql = "INSERT INTO users (Username, Password_Hash, FullName, Email, Phone, Faculty, StudentID, Role, Is_Verified)
                       VALUES (:username, :password_hash, '', :email, '', '', :studentid, 'User', 1)";

        $insert_stmt = $pdo->prepare($insert_sql);
        $insert_stmt->execute([
            ':username' => $username,
            ':password_hash' => $hashed_password,
            ':email' => $email,
            ':studentid' => $studentid
        ]);

        // 5. Success! Redirect directly to the login page
        header("Location: ../../login.html?signup=success"); 
        exit();

    } catch (PDOException $e) {
        // Log the actual error for yourself, but show a generic error to the user
        error_log("Signup Database Error: " . $e->getMessage());
        header("Location: ../../signup.html?error=sqlerror");
        exit();
    }
} else {
    // If they tried to access this file directly without submitting the form
    header("Location: ../../signup.html");
    exit();
}
?>