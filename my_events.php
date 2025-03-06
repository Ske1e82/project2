<?php
session_start();
require 'db.php';

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ตรวจสอบการอนุมัติ, ปฏิเสธ หรือ เช็คชื่อ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['registration_id'], $_POST['action'])) {
        $registration_id = (int)$_POST['registration_id'];
        $action = $_POST['action'];

        if ($action == 'approve' || $action == 'reject') {
            $status = ($action == 'approve') ? 'approved' : 'rejected';
            $update_sql = "UPDATE registrations SET status = ? WHERE id = ?";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([$status, $registration_id]);
        } elseif ($action == 'checkin') {
            $update_sql = "UPDATE registrations SET checked_in = 1 WHERE id = ?";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([$registration_id]);
        } elseif ($action == 'uncheckin') { // ✅ เพิ่ม Uncheck-In
            $update_sql = "UPDATE registrations SET checked_in = 0 WHERE id = ?";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([$registration_id]);
        }

        header("Location: my_events.php");
        exit();
    }
}

// ดึงข้อมูลกิจกรรมที่ผู้ใช้เป็นเจ้าของ
$sql = "SELECT * FROM events WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Events</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
    <h1>My Events</h1>
    <a href="create_event.php">Create Event</a> |
    <a href="my_registrations.php">My Registrations</a> | 
    <a href="event_list.php">Event List</a> | 
    <a style="background-color: red;" href="logout.php">Logout</a>

    <?php if (count($events) > 0): ?>
        <?php foreach ($events as $event): ?>
            <div class="event">
                <h3><?= htmlspecialchars($event['title']); ?></h3>
                <p><?= htmlspecialchars($event['description']); ?></p>
                <p><strong>Start Date:</strong> <?= date('d/m/Y', strtotime($event['start_date'])); ?></p>
                <p><strong>End Date:</strong> <?= date('d/m/Y', strtotime($event['end_date'])); ?></p>

                <h4>Event Images</h4>
                <?php
                $image_sql = "SELECT * FROM event_images WHERE event_id = ?";
                $image_stmt = $pdo->prepare($image_sql);
                $image_stmt->execute([$event['id']]);
                $images = $image_stmt->fetchAll();
                ?>
                
                <?php if (count($images) > 0): ?>
                    <div class="event">
                        <?php foreach ($images as $image): ?>
                            <img src="<?= htmlspecialchars($image['image_url']); ?>" alt="Event Image" style="max-width: 200px; margin: 10px;">
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No images available for this event.</p>
                <?php endif; ?>

                <a href="edit_event.php?id=<?= $event['id']; ?>">Edit</a> |
                <form method="POST" action="delete_event.php" style="display:inline;">
                    <input type="hidden" name="event_id" value="<?= $event['id']; ?>">
                    <button style="background-color: red;" type="submit" onclick="return confirm('Are you sure you want to delete this event?');">Delete</button>
                </form>

                <h4>Participants</h4>
                <?php
                $sql_participants = "SELECT r.id, r.user_id, u.first_name, u.last_name, r.status, r.checked_in
                                     FROM registrations r
                                     JOIN users u ON r.user_id = u.id
                                     WHERE r.event_id = ?";
                $stmt_participants = $pdo->prepare($sql_participants);
                $stmt_participants->execute([$event['id']]);
                $participants = $stmt_participants->fetchAll();
                ?>

                <?php if (count($participants) > 0): ?>
                    <table border="1">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Checked In</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($participants as $participant): ?>
                                <tr>
                                    <td><?= htmlspecialchars($participant['first_name'] . " " . $participant['last_name']); ?></td>
                                    <td><?= htmlspecialchars(ucfirst($participant['status'])); ?></td>
                                    <td>
                                        <?php if ($participant['checked_in']): ?>
                                            ✅ Yes 
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="registration_id" value="<?= $participant['id']; ?>">
                                                <input type="hidden" name="action" value="uncheckin">
                                                <button type="submit" onclick="return confirm('Uncheck this participant?');">🔄 Uncheck</button>
                                            </form>
                                        <?php else: ?>
                                            ❌ No
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="registration_id" value="<?= $participant['id']; ?>">
                                                <input type="hidden" name="action" value="checkin">
                                                <button type="submit" onclick="return confirm('Check in this participant?');">📌 Check-In</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($participant['status'] == 'pending'): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="registration_id" value="<?= $participant['id']; ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" onclick="return confirm('Approve this participant?');">✅ Approve</button>
                                            </form>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="registration_id" value="<?= $participant['id']; ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" onclick="return confirm('Reject this participant?');">❌ Reject</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No registrations for this event.</p>
                <?php endif; ?>
                <hr>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>You have not created any events yet.</p>
    <?php endif; ?>
    </div>
    <a href="index.php">Back to Homepage</a>
</body>
</html>
