<?php
session_start();
require_once 'db.php';

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ตรวจสอบว่า event_id ถูกส่งมาหรือไม่
if (!isset($_POST['event_id']) || empty($_POST['event_id'])) {
    header("Location: my_events.php");
    exit();
}

// รับ event_id
$event_id = $_POST['event_id'];

// ตรวจสอบว่าผู้ใช้เป็นเจ้าของกิจกรรมหรือไม่
$stmt = $pdo->prepare("SELECT user_id FROM events WHERE id = :event_id");
$stmt->bindParam(':event_id', $event_id);
$stmt->execute();
$event = $stmt->fetch();

if (!$event) {
    echo "Event not found!";
    exit();
}

// ถ้าผู้ใช้ไม่ใช่เจ้าของกิจกรรม, ให้ปฏิเสธการลบ
if ($event['user_id'] != $_SESSION['user_id']) {
    echo "You are not authorized to delete this event.";
    exit();
}

// ลบกิจกรรม
$stmt = $pdo->prepare("DELETE FROM events WHERE id = :event_id");
$stmt->bindParam(':event_id', $event_id);
$stmt->execute();

// ลบการลงทะเบียนที่เกี่ยวข้อง
$stmt = $pdo->prepare("DELETE FROM registrations WHERE event_id = :event_id");
$stmt->bindParam(':event_id', $event_id);
$stmt->execute();

// ลบรูปภาพกิจกรรมที่เกี่ยวข้อง
$stmt = $pdo->prepare("DELETE FROM event_images WHERE event_id = :event_id");
$stmt->bindParam(':event_id', $event_id);
$stmt->execute();

// หลังจากลบกิจกรรม, ส่งกลับไปที่หน้า my_events.php
header("Location: my_events.php");
exit();
?>
