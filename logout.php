<?php
session_start();

// ลบข้อมูล session ที่เกี่ยวข้อง
session_unset();

// ทำลาย session
session_destroy();

// นำผู้ใช้กลับไปยังหน้า login หรือ index
header("Location: login.php");
exit();
?>
