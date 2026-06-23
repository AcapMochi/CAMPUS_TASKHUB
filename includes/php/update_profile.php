<?php
session_start();
require 'dhb.inc.php';

if (!isset($_SESSION['user_id'])) {
    // If not logged in, kick them back to login
    header("Location: ../../login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Grab all the text inputs from the form
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['nickname'] ?? ''); 
    $faculty = trim($_POST['faculty'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    // Variable to hold the query part for the profile picture
    $profile_pic_query = "";
    $params = [
        ':fullname' => $fullname,
        ':username' => $username,
        ':faculty' => $faculty,
        ':phone' => $phone,
        ':email' => $email,
        ':bio' => $bio,
        ':uid' => $user_id
    ];

    // 2. Handle the Image Upload
    if (isset($_FILES['profile_avatar']) && $_FILES['profile_avatar']['error'] === UPLOAD_ERR_OK) {
        
        $fileTmpPath = $_FILES['profile_avatar']['tmp_name'];
        $fileName = $_FILES['profile_avatar']['name'];
        
        // Extract file extension and validate
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedfileExtensions = array('jpg', 'jpeg', 'png');

        if (in_array($fileExtension, $allowedfileExtensions)) {
            
            // Create a unique file name to prevent overwriting
            $newFileName = "user_" . $user_id . "_" . time() . '.' . $fileExtension;
            
            // The path where the image will be saved on your server
            // Ensure the 'images/uploads/' folder exists in your project root!
            $uploadFileDir = '../../images/uploads/';
            $dest_path = $uploadFileDir . $newFileName;

            // Move the file from the temporary directory to your uploads folder
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // Prepare the database path (relative to where the HTML files are)
                $db_image_path = 'images/uploads/' . $newFileName;
                
                // Add the image update to our SQL query
                $profile_pic_query = ", Profile_Pic_URL = :profile_pic";
                $params[':profile_pic'] = $db_image_path;
            } else {
                echo "<script>alert('Error moving the uploaded file. Check folder permissions.'); window.history.back();</script>";
                exit();
            }
        } else {
            echo "<script>alert('Upload failed. Allowed file types: JPG, JPEG, PNG.'); window.history.back();</script>";
            exit();
        }
    }

    try {
        // 3. Update the Database
        $sql = "UPDATE users SET 
                FullName = :fullname, 
                Username = :username, 
                Faculty = :faculty, 
                Phone = :phone, 
                Email = :email, 
                Bio_Text = :bio 
                " . $profile_pic_query . " 
                WHERE UserID = :uid";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Update the session variable in case they changed their username
        $_SESSION['username'] = $username;

        // Redirect back to profile page on success
        header("Location: ../../myProfile.html?update=success");
        exit();

    } catch (PDOException $e) {
        echo "<script>alert('Database Error: " . $e->getMessage() . "'); window.history.back();</script>";
        exit();
    }

} else {
    header("Location: ../../myProfile.html");
    exit();
}
?>