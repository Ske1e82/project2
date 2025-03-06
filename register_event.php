<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$event_id = $_GET['event_id'];

// ตรวจสอบว่าผู้ใช้ได้ลงทะเบียนกิจกรรมนี้หรือยัง
$stmt = $pdo->prepare("SELECT * FROM registrations WHERE user_id = :user_id AND event_id = :event_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':event_id', $event_id);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $error_message = "คุณได้ลงทะเบียนกิจกรรมนี้ไปแล้ว";
} else {
    // ลงทะเบียนขอเข้าร่วมกิจกรรม
    $stmt = $pdo->prepare("INSERT INTO registrations (user_id, event_id, status) VALUES (:user_id, :event_id, 'pending')");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':event_id', $event_id);
    
    if ($stmt->execute()) {
        // อัปเดตจำนวนผู้ลงทะเบียนในตาราง event_statistics
        $stmt_update = $pdo->prepare("UPDATE event_statistics SET total_registrations = total_registrations + 1 WHERE event_id = :event_id");
        $stmt_update->bindParam(':event_id', $event_id);
        $stmt_update->execute();

        $success_message = "ขอเข้าร่วมกิจกรรมเรียบร้อยแล้ว";
    } else {
        $error_message = "เกิดข้อผิดพลาดในการลงทะเบียน";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for Event</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>ขอเข้าร่วมกิจกรรม</h1>

    <?php if (isset($error_message)): ?>
        <p style="color: red;"><?= $error_message ?></p>
    <?php elseif (isset($success_message)): ?>
        <p style="color: green;"><?= $success_message ?></p>
    <?php endif; ?>

    <a href="index.php">กลับไปที่หน้าแรก</a>
</body>
</html>
