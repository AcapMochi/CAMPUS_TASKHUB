<?php
// 1. Include your database connection
require 'dhb.inc.php';

// 2. Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Grab the data from the form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 3. Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // 4. Prepare the SQL statement to prevent SQL injection
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (:username, :password)");
        
        // 5. Execute the statement with our variables
        $stmt->execute([
            ':username' => $username,
            ':password' => $hashed_password
        ]);

        echo "Signup successful! You can now <a href='/CAMPUS_TASKHUB/login.html'>Login</a>";
        // Alternatively, redirect them: header("Location: login.html"); exit();

    } catch (PDOException $e) {
        // If the username already exists, it will throw an error because of the UNIQUE constraint
        if ($e->getCode() == 23000) {
            echo "Username already exists. Please choose another.";
        } else {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>