<?php
// update_profile.php
session_start();
require 'dhb.inc.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];

    $fullname = $_POST['fullname'] ?? '';
    $nickname = $_POST['nickname'] ?? '';
    $student_id = $_POST['student_id'] ?? '';
    $faculty = $_POST['faculty'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $bio = $_POST['bio'] ?? '';

    try {
        $pdo->beginTransaction();

        $sql = "UPDATE users SET FullName = :fn, Username = :un, StudentID = :sid, Faculty = :fac, Phone = :ph, Email = :em, `Bio_Text` = :bio WHERE UserID = :uid";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':fn' => $fullname,
            ':un' => $nickname,
            ':sid' => $student_id,
            ':fac' => $faculty,
            ':ph' => $phone,
            ':em' => $email,
            ':bio' => $bio,
            ':uid' => $user_id
        ]);

        if (isset($_FILES['profile_avatar']) && $_FILES['profile_avatar']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_avatar']['tmp_name'];
            $fileName = $_FILES['profile_avatar']['name'];
            $fileSize = $_FILES['profile_avatar']['size'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExts = ['jpg', 'jpeg', 'png'];

            if (in_array($fileExtension, $allowedExts) && $fileSize < 2097152) {
                $newFileName = "user_" . $user_id . "_" . time() . "." . $fileExtension;
                $uploadFileDir = 'images/uploads/';

                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }

                $dest_path = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $picSql = "UPDATE users SET `Profile_Pic_URL` = :pic WHERE UserID = :uid";
                    $pdo->prepare($picSql)->execute([':pic' => $dest_path, ':uid' => $user_id]);
                }
            }
        }

        $pdo->commit();
        header("Location: ../../myProfile.html?success=1");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error saving profile: " . $e->getMessage();
    }
}
?>