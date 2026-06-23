<?php
session_start();
require 'dhb.inc.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in first.'); window.location.href='../../login.html';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {

    $fileTmpPath = $_FILES['profile_image']['tmp_name'];
    $fileName = $_FILES['profile_image']['name'];

    $allowedExtensions = ['jpg', 'jpeg', 'png'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($fileExtension, $allowedExtensions)) {
        echo "<script>alert('Invalid file type. Only JPG and PNG are allowed.'); window.history.back();</script>";
        exit();
    }

    // Create a unique file name
    $newFileName = 'user_' . $user_id . '_' . time() . '.' . $fileExtension;

    // THE FIX: Bulletproof Absolute Pathing
    // __DIR__ gets the exact folder this PHP script is in. 
    // We then go up two folders (/../../) and into /images/profiles/
    $uploadFileDir = __DIR__ . '/../../images/upload/';
    $destFilePath = $uploadFileDir . $newFileName;

    // Move the file
    if (move_uploaded_file($fileTmpPath, $destFilePath)) {

        // Save the correct relative URL to the database for HTML to read
        $db_image_path = 'images/uploads/' . $newFileName;

        try {
            $sql = "UPDATE users SET Profile_Pic_URL = :pic_url WHERE UserID = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':pic_url' => $db_image_path,
                ':user_id' => $user_id
            ]);

            echo "<script>alert('Profile picture updated successfully!'); window.location.href='../../profile.html';</script>";
            exit();

        } catch (PDOException $e) {
            echo "<script>alert('Database error: " . $e->getMessage() . "'); window.history.back();</script>";
        }

    } else {
        echo "<script>alert('Error moving file. Make sure the images/profiles/ folder exists!'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('No file uploaded or an upload error occurred.'); window.history.back();</script>";
}
?>