<?php
session_start();
require 'db.php';

// เริ่มต้นตัวแปร events เป็นค่าว่าง
$events = [];
$search_term = $start_date = $end_date = "";

// ตรวจสอบว่าผู้ใช้ส่งคำค้นหาหรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $search_term = $_POST['search_term'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // ถ้าผู้ใช้กรอกช่วงวันที่
    if (!empty($start_date) && !empty($end_date)) {
        // ค้นหาจากคำค้นหาพร้อมกับช่วงวันที่
        $stmt = $pdo->prepare("SELECT e.*, u.first_name, u.last_name 
                               FROM events e 
                               LEFT JOIN users u ON e.user_id = u.id 
                               WHERE (e.title LIKE :search_term OR e.description LIKE :search_term)
                               AND e.start_date >= :start_date AND e.end_date <= :end_date");
        $stmt->bindValue(':search_term', '%' . $search_term . '%');
        $stmt->bindValue(':start_date', $start_date); // ตรวจสอบให้แน่ใจว่า start_date ถูกส่งในรูปแบบ 'YYYY-MM-DD'
        $stmt->bindValue(':end_date', $end_date); // ตรวจสอบให้แน่ใจว่า end_date ถูกส่งในรูปแบบ 'YYYY-MM-DD'
    } else {
        // ค้นหาจากคำค้นหาแบบธรรมดา (โดยไม่ระบุช่วงวันที่)
        $stmt = $pdo->prepare("SELECT e.*, u.first_name, u.last_name 
                               FROM events e 
                               LEFT JOIN users u ON e.user_id = u.id 
                               WHERE e.title LIKE :search_term OR e.description LIKE :search_term");
        $stmt->bindValue(':search_term', '%' . $search_term . '%');
    }
    $stmt->execute();
    $events = $stmt->fetchAll();
} else {
    // ถ้าไม่ได้ค้นหา ให้ดึงรายการกิจกรรมทั้งหมด
    $stmt = $pdo->prepare("SELECT e.*, u.first_name, u.last_name 
                           FROM events e 
                           LEFT JOIN users u ON e.user_id = u.id");
    $stmt->execute();
    $events = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event List</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>Event List</h1>

    <!-- ฟอร์มค้นหากิจกรรม -->
    <form method="POST" action="event_list.php">
        <input type="text" name="search_term" placeholder="ค้นหากิจกรรม" value="<?= htmlspecialchars($search_term ?? '') ?>">

        <!-- ช่วงวันที่ -->
        <label for="start_date">Start Date:</label>
        <input type="date" name="start_date" value="<?= htmlspecialchars($start_date ?? '') ?>">

        <label for="end_date">End Date:</label>
        <input type="date" name="end_date" value="<?= htmlspecialchars($end_date ?? '') ?>">

        <button style="background-color: #40eb30;" type="submit">ค้นหา</button>
    </form>

    <?php if (isset($_SESSION['user_id'])): ?>
        
            <p>Welcome, <?= isset($_SESSION['first_name']) && isset($_SESSION['last_name']) ? htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) : 'User'; ?>!</p>
            
            <a href="my_events.php">My Events</a> |
            <a href="my_registrations.php">My Registrations</a> |
            <a href="create_event.php">Create Event</a> |
            <a style="background-color: red;" href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a> |
            <a href="register.php">Register</a>
        <?php endif; ?>
        
        <h2>All Events</h2>
        <div class="event">
            <?php if (count($events) > 0): ?>
                <?php foreach ($events as $event): ?>
                    <div class="event">
                        <h3><?= htmlspecialchars($event['title']); ?></h3>
                        <p><?= htmlspecialchars($event['description']); ?></p>
                        <p><strong>Organized by:</strong> <?= !empty($event['first_name']) && !empty($event['last_name']) ? htmlspecialchars($event['first_name'] . ' ' . $event['last_name']) : 'Unknown'; ?></p>

                        <!-- แสดงวันที่เริ่มต้นและวันที่สิ้นสุด -->
                        <p><strong>Start Date:</strong> <?= htmlspecialchars($event['start_date']); ?></p>
                        <p><strong>End Date:</strong> <?= htmlspecialchars($event['end_date']); ?></p>

                        <!-- ดึงและแสดงภาพทั้งหมดที่เกี่ยวข้องกับกิจกรรม -->
                        <?php
                        // ดึงข้อมูลภาพจากตาราง event_images ที่เกี่ยวข้องกับกิจกรรมนี้
                        $imageStmt = $pdo->prepare("SELECT image_url FROM event_images WHERE event_id = ?");
                        $imageStmt->execute([$event['id']]);
                        $images = $imageStmt->fetchAll();
                        ?>

                        <?php if (count($images) > 0): ?>
                            <p><strong>Event Images:</strong></p>
                            <?php foreach ($images as $image): ?>
                                <img src="<?= htmlspecialchars($image['image_url']); ?>" alt="<?= htmlspecialchars($event['title']); ?>" width="200">
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <br>
                        <a class="register-btn" href="register_event.php?event_id=<?= $event['id']; ?>">Register</a>
                    </div>
                    <hr>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No events available.</p>
            <?php endif; ?>
        </div>

        <a href="index.php">กลับไปที่หน้าแรก</a>
</body>

</html>