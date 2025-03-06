<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $eventId = (int)$_POST['event_id'];
    $userId = $_SESSION['user_id'];

    // ตรวจสอบว่าสถานะเป็น approved และยังไม่ได้เช็คชื่อ
    $stmt = $pdo->prepare("SELECT status, checked_in FROM registrations WHERE event_id = ? AND user_id = ?");
    $stmt->execute([$eventId, $userId]);
    $registration = $stmt->fetch();

    if ($registration && $registration['status'] == 'approved' && !$registration['checked_in']) {
        // อัปเดตสถานะการเช็คชื่อ
        $updateStmt = $pdo->prepare("UPDATE registrations SET checked_in = 1 WHERE event_id = ? AND user_id = ?");
        $updateStmt->execute([$eventId, $userId]);

        // อัปเดตจำนวนคนที่เข้าร่วมใน event_statistics
        $updateStats = $pdo->prepare("UPDATE event_statistics SET total_checked_in = total_checked_in + 1 WHERE event_id = ?");
        $updateStats->execute([$eventId]);
    }
}

header("Location: my_registrations.php");
exit();
