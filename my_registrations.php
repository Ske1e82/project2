<?php
session_start();
require 'db.php';

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ดึงข้อมูลกิจกรรมที่ผู้ใช้สมัครเข้าร่วม
$sql = "SELECT e.*, r.status, r.checked_in
        FROM registrations r
        JOIN events e ON r.event_id = e.id
        WHERE r.user_id = ?
        ORDER BY e.start_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$registrations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Registrations</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <h1>My Registrations</h1>
    <a href="event_list.php">Browse Events</a> |
    <a href="my_events.php">My Events</a> |
    <a style="background-color: red;" href="logout.php">Logout</a>

    <?php if (count($registrations) > 0): ?>
        <?php foreach ($registrations as $event): ?>
            <div class="event">
                <h3><?= htmlspecialchars($event['title']); ?></h3>
                <p><?= nl2br(htmlspecialchars($event['description'])); ?></p>
                <p><strong>Start Date:</strong> <?= date('d/m/Y', strtotime($event['start_date'])); ?></p>
                <p><strong>End Date:</strong> <?= date('d/m/Y', strtotime($event['end_date'])); ?></p>
                <p><strong>Status:</strong> <?= ucfirst($event['status']); ?></p>
                <p><strong>Checked In:</strong> <?= $event['checked_in'] ? '✅ Yes' : '❌ No'; ?></p>

                <!-- ดึงข้อมูลรูปภาพของกิจกรรม -->
                <?php
                $image_sql = "SELECT * FROM event_images WHERE event_id = ?";
                $image_stmt = $pdo->prepare($image_sql);
                $image_stmt->execute([$event['id']]);
                $images = $image_stmt->fetchAll();
                ?>

                <h4>Event Images</h4>
                <?php if (count($images) > 0): ?>
                    <div class="event">
                        <?php foreach ($images as $image): ?>
                            <img src="<?= htmlspecialchars($image['image_url']); ?>"
                                alt="Event Image"
                                style="max-width: 200px; margin: 10px;">
                        <?php endforeach; ?>
                    </div class="event">
                <?php else: ?>
                    <p>No images available for this event.</p>
                <?php endif; ?>

                <hr>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>You have not registered for any events yet.</p>
    <?php endif; ?>

    <a href="index.php">Back to Homepage</a>
</body>

</html>