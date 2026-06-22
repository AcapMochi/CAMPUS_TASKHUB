<?php
session_start();
require 'dhb.inc.php';

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// MANUALLY LOAD PHPMAILER FILES 
// (Assuming the PHPMailer folder is in the same directory as this file)
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        // 1. Check if the username or email already exists
        $check_sql = "SELECT Username, Email FROM users WHERE Username = :username OR Email = :email";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([':username' => $username, ':email' => $email]);
        
        if ($check_stmt->rowCount() > 0) {
            echo "<script>alert('Username or Email already exists. Please choose another.'); window.history.back();</script>";
            exit();
        }

        // 2. Hash the password and generate a 5-digit code
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $otp = rand(10000, 99999);

        // 3. Save the code and user details temporarily in a session
        $_SESSION['otp'] = $otp;
        $_SESSION['temp_user'] = [
            'username' => $username,
            'email' => $email,
            'password' => $hashed_password
        ];

        // 4. Send the verification email using PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();                                            
            $mail->Host       = 'smtp.gmail.com';                     
            $mail->SMTPAuth   = true;                                   
            
            // YOUR GMAIL AND APP PASSWORD GO HERE
            $mail->Username   = 'acapmochi.bot25@gmail.com';                     
            $mail->Password   = 'cksf qqox esyp viaz'; // Your 16-letter App Password
            
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            
            $mail->Port       = 587;                                    

            // Recipients
            $mail->setFrom('your.email@gmail.com', 'Campus Taskhub');
            $mail->addAddress($email, $username);

            // Content
            $mail->isHTML(true);                                  
            $mail->Subject = 'Your CAMPUS TASKHUB Verification Code';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;'>
                    <div style='background-color: #ffffff; padding: 20px; border-radius: 10px;'>
                        <h2 style='color: #333;'>Welcome to Campus Taskhub, $username!</h2>
                        <p>Your 5-digit verification code is:</p>
                        <h1 style='color: #4CAF50; letter-spacing: 5px;'>$otp</h1>
                        <p>Please enter this code on the website to complete your registration.</p>
                    </div>
                </div>
            ";

            $mail->send();
            
            // 5. Redirect to the verify page
            header("Location: ../../verify.html"); 
            exit();

        } catch (Exception $e) {
            echo "<script>alert('Message could not be sent. Mailer Error: {$mail->ErrorInfo}'); window.history.back();</script>";
        }

    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    }
}
?>