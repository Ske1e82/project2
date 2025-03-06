<?php
session_start();
require 'db.php';

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบแล้วหรือยัง
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// สร้างกิจกรรมใหม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // สร้างกิจกรรมใหม่
    $create_sql = "INSERT INTO events (title, description, start_date, end_date, user_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($create_sql);
    $stmt->execute([$title, $description, $start_date, $end_date, $_SESSION['user_id']]);

    // ดึง id ของกิจกรรมที่เพิ่งสร้าง
    $event_id = $pdo->lastInsertId();

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
                $image_url = 'uploads/' . basename($file_name);

                // ตรวจสอบว่ามีโฟลเดอร์ uploads หรือไม่ ถ้าไม่มีให้สร้าง
                if (!is_dir('uploads')) {
                    mkdir('uploads', 0777, true);
                }

                // ย้ายไฟล์ที่อัปโหลด
                if (move_uploaded_file($file_tmp_name, $image_url)) {
                    // เพิ่มรูปภาพใน event_images
                    $image_sql = "INSERT INTO event_images (event_id, image_url) VALUES (?, ?)";
                    $image_stmt = $pdo->prepare($image_sql);
                    $image_stmt->execute([$event_id, $image_url]);
                }
            }
        }
    }

    // หลังจากสร้างกิจกรรมแล้ว, นำผู้ใช้กลับไปที่หน้า 'my_events.php'
    header("Location: my_events.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    
    <h1>Create Event</h1>
    <div class="event">
    <form method="POST" enctype="multipart/form-data">
        <label for="title">Event Title:</label><br>
        <input type="text" name="title" required><br><br>

        <label for="description">Event Description:</label><br>
        <textarea name="description" required></textarea><br><br>

        <label for="start_date">Start Date:</label><br>
        <input type="date" name="start_date" required><br><br>

        <label for="end_date">End Date:</label><br>
        <input type="date" name="end_date" required><br><br>

        <label for="images">Event Images (You can upload multiple):</label><br>
        <input type="file" name="images[]" multiple><br><br>

        <button type="submit">Create</button>
    </form>
    </div>
    <a href="my_events.php">Back to My Events</a>
    
</body>
</html>
