<?php
// ตรวจสอบการส่งค่า 'event_id', 'user_id', 'registration_id' ผ่าน GET
if (isset($_GET['event_id'], $_GET['user_id'], $_GET['registration_id'])) {
    $eventId = (int)$_GET['event_id'];
    $userId = (int)$_GET['user_id'];
    $registrationId = (int)$_GET['registration_id'];

    // เชื่อมต่อฐานข้อมูล
    require_once 'db.php';

    // อัปเดตสถานะการลงทะเบียนเป็น 'rejected'
    $sql = "UPDATE registrations SET status = 'rejected' WHERE id = :registration_id AND event_id = :event_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':registration_id', $registrationId);
    $stmt->bindParam(':event_id', $eventId);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();

    // อัปเดตจำนวนผู้ที่ถูกปฏิเสธใน event_statistics
    $sql_update = "UPDATE event_statistics SET total_checked_in = total_checked_in - 1 WHERE event_id = :event_id";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->bindParam(':event_id', $eventId);
    $stmt_update->execute();

    // รีไดเร็กต์กลับไปหน้า "My Events"
    header("Location: my_events.php");
    exit();
} else {
    echo "Invalid parameters.";
    exit();
}
