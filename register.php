<?php
session_start();
require_once 'db.php'; // เชื่อมต่อฐานข้อมูล

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $gender = $_POST['gender'];
    $role = $_POST['role'];

    // ตรวจสอบว่ามีอีเมลนี้ในฐานข้อมูลหรือไม่
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_user) {
        $error_message = "Email already exists!";
    } else {
        // แฮชรหัสผ่าน
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // เพิ่มผู้ใช้ใหม่ลงในฐานข้อมูล
        $stmt = $pdo->prepare("INSERT INTO users (email, password, first_name, last_name, gender, role) 
                               VALUES (:email, :password, :first_name, :last_name, :gender, :role)");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':role', $role);
        $stmt->execute();

        // นำผู้ใช้ไปยังหน้า login
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <h1>Register</h1>
    <?php if (isset($error_message)): ?>
        <p style="color: red;"><?= $error_message ?></p>
    <?php endif; ?>
    <form action="register.php" method="POST">
        <label for="email">Email:</label>
        <input type="email" name="email" required>
        <br>
        <label for="first_name">First Name:</label>
        <input type="text" name="first_name" required>
        <br>
        <label for="last_name">Last Name:</label>
        <input type="text" name="last_name" required>
        <br>
        <label for="gender">Gender:</label>
        <select name="gender">
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
        </select>
        <br>
        <label for="password">Password:</label>
        <input type="password" name="password" required>
        <br>
        <label for="role">Role:</label>
        <select name="role">
            <option value="volunteer">Volunteer</option>
            <option value="admin">Admin</option>
        </select>
        <br>
        <button type="submit">Register</button>
    </form>
    <a href="login.php">Already have an account? Login here</a>
</body>
</html>
