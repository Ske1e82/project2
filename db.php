<?php
// กำหนดค่าการเชื่อมต่อฐานข้อมูล
$host = 'localhost';  // เซิร์ฟเวอร์ฐานข้อมูล
$dbname = 'event_db'; // ชื่อฐานข้อมูล
$username = 'event';   // ชื่อผู้ใช้ฐานข้อมูล
$password = 'abc123';       // รหัสผ่านฐานข้อมูล (เปลี่ยนตามที่ใช้จริง)

// สร้างการเชื่อมต่อฐานข้อมูล
try {
    // สร้างการเชื่อมต่อ PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // กำหนดให้ PDO แสดงข้อผิดพลาดหากเกิดปัญหาการเชื่อมต่อ
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // แสดงข้อความเมื่อเชื่อมต่อสำเร็จ
    // echo "Connected successfully";  // เปิดแสดงหากต้องการตรวจสอบ
    
} catch (PDOException $e) {
    // ถ้ามีข้อผิดพลาดในการเชื่อมต่อจะแสดงข้อความที่เกี่ยวข้อง
    echo "Connection failed: " . $e->getMessage();
    exit;
}
?>
