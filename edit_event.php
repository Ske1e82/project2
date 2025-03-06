<?php
session_start();
require 'db.php';

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบแล้วหรือยัง
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ตรวจสอบว่า ID ของกิจกรรมถูกส่งมา
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: my_events.php");
    exit();
}

// รับ ID ของกิจกรรม
$event_id = $_GET['id'];

// ตรวจสอบว่าผู้ใช้เป็นเจ้าของกิจกรรมหรือไม่
$stmt = $pdo->prepare("SELECT user_id FROM events WHERE id = :event_id");
$stmt->bindParam(':event_id', $event_id);
$stmt->execute();
$event = $stmt->fetch();

if (!$event) {
    echo "Event not found!";
    exit();
}

// ถ้าผู้ใช้ไม่ใช่เจ้าของกิจกรรม, ให้ปฏิเสธการแก้ไข
if ($event['user_id'] != $_SESSION['user_id']) {
    echo "You are not authorized to edit this event.";
    exit();
}

// แก้ไขกิจกรรม
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // อัปเดตข้อมูลกิจกรรม
    $update_sql = "UPDATE events SET title = ?, description = ?, start_date = ?, end_date = ? WHERE id = ?";
    $stmt = $pdo->prepare($update_sql);
    $stmt->execute([$title, $description, $start_date, $end_date, $event_id]);

    // อัปโหลดไฟล์ภาพหลายไฟล์ถ้ามี
    if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_count = count($_FILES['images']['name']);

        for ($i = 0; $i < $file_count; $i++) {
            $file_name = $_FILES['images']['name'][$i];
            $file_tmp_name = $_FILES['images']['tmp_name'][$i];
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);

            // ตรวจสอบประเภทไฟล์
            if (in_array(strtolower($file_extension), $allowed_extensions)) {
                // ตั้งชื่อไฟล์ใหม่เพื่อป้องกันการซ้ำ
                $image_url = 'uploads/' . uniqid() . '-' . basename($file_name);

                if (!is_dir('uploads')) {
                    mkdir('uploads', 0777, true);
                }

                if (move_uploaded_file($file_tmp_name, $image_url)) {
                    // เพิ่มรูปภาพใน event_images
                    $image_sql = "INSERT INTO event_images (event_id, image_url) VALUES (?, ?)";
                    $image_stmt = $pdo->prepare($image_sql);
                    $image_stmt->execute([$event_id, $image_url]);
                }
            }
        }
    }

    // ตรวจสอบการลบภาพ
    if (isset($_POST['delete_images']) && count($_POST['delete_images']) > 0) {
        $delete_image_ids = $_POST['delete_images'];

        foreach ($delete_image_ids as $image_id) {
            // ดึง URL ของภาพที่ต้องการลบ
            $delete_sql = "SELECT image_url FROM event_images WHERE id = ?";
            $delete_stmt = $pdo->prepare($delete_sql);
            $delete_stmt->execute([$image_id]);
            $image_data = $delete_stmt->fetch();

            if ($image_data) {
                // ลบไฟล์จากเซิร์ฟเวอร์
                if (file_exists($image_data['image_url'])) {
                    unlink($image_data['image_url']);
                }

                // ลบข้อมูลจากฐานข้อมูล
                $delete_sql = "DELETE FROM event_images WHERE id = ?";
                $delete_stmt = $pdo->prepare($delete_sql);
                $delete_stmt->execute([$image_id]);
            }
        }
    }

    header("Location: my_events.php");
    exit();
}

// ดึงข้อมูลกิจกรรม
$event_sql = "SELECT * FROM events WHERE id = ?";
$stmt_event = $pdo->prepare($event_sql);
$stmt_event->execute([$event_id]);
$event_data = $stmt_event->fetch();

// ดึงข้อมูลภาพกิจกรรม
$image_sql = "SELECT * FROM event_images WHERE event_id = ?";
$stmt_images = $pdo->prepare($image_sql);
$stmt_images->execute([$event_id]);
$images = $stmt_images->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
</head>
<body>
    <h1>Edit Event</h1>

    <form method="POST" enctype="multipart/form-data">
        <label for="title">Event Title:</label><br>
        <input type="text" name="title" value="<?= htmlspecialchars($event_data['title']); ?>" required><br><br>

        <label for="description">Event Description:</label><br>
        <textarea name="description" required><?= htmlspecialchars($event_data['description']); ?></textarea><br><br>

        <label for="start_date">Start Date:</label><br>
        <input type="date" name="start_date" value="<?= htmlspecialchars($event_data['start_date']); ?>" required><br><br>

        <label for="end_date">End Date:</label><br>
        <input type="date" name="end_date" value="<?= htmlspecialchars($event_data['end_date']); ?>" required><br><br>

        <label for="images">Event Images (You can upload multiple):</label><br>
        <input type="file" name="images[]" multiple><br><br>

        <h3>Current Event Images:</h3>
        <?php foreach ($images as $image): ?>
            <div>
                <img src="<?= htmlspecialchars($image['image_url']); ?>" alt="Event Image" width="150"><br>
                <label>
                    <input type="checkbox" name="delete_images[]" value="<?= $image['id']; ?>"> Delete Image
                </label>
            </div>
        <?php endforeach; ?>
        
        <button type="submit">Update Event</button>
    </form>

    <a href="my_events.php">Back to My Events</a>
</body>
</html>
