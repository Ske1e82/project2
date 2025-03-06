<?php
session_start();
require 'db.php';

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบแล้วหรือยัง
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ตรวจสอบว่ามีการส่ง event_id มาหรือไม่
if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
    header("Location: my_registrations.php");
    exit();
}

$event_id = $_GET['event_id'];
$user_id = $_SESSION['user_id'];

// ตรวจสอบว่า user_id และ event_id ตรงกับข้อมูลในตาราง registrations หรือไม่
$sql = "SELECT * FROM registrations WHERE user_id = ? AND event_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id, $event_id]);
$registration = $stmt->fetch();

if (!$registration) {
    echo "You are not registered for this event.";
    exit();
}

// ถ้าเป็นการยืนยันยกเลิก
if (isset($_POST['confirm_cancel']) && $_POST['confirm_cancel'] == 'yes') {
    // ลบการลงทะเบียนจากตาราง registrations
    $sql_delete = "DELETE FROM registrations WHERE user_id = ? AND event_id = ?";
    $stmt_delete = $pdo->prepare($sql_delete);
    $stmt_delete->execute([$user_id, $event_id]);

    // แจ้งเตือนผู้ใช้และเปลี่ยนเส้นทางกลับไปยังหน้ากิจกรรมที่ลงทะเบียน
    header("Location: my_registrations.php?message=Registration cancelled successfully.");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Registration</title>
</head>
<body>
    <h1>Cancel Registration</h1>
    
    <p>Are you sure you want to cancel your registration for this event?</p>

    <form method="POST">
        <button type="submit" name="confirm_cancel" value="yes">Yes, Cancel Registration</button>
        <a href="my_registrations.php">No, Go Back</a>
    </form>
</body>
</html>
