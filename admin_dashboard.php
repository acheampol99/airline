<?php
session_start();
include('connect.php');

// SECURITY CHECK
if (!isset($_SESSION['user_id']) || $_SESSION['utype'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// FETCH USERS
$query = "SELECT user_id, lastname, firstname, middlename, gender, email, dob, username, utype 
          FROM user ORDER BY user_id DESC";

$result = $conn->query($query);
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>

    <style>
        body { font-family: Arial; background:#f4f4f4; padding:20px; }
        .container { background:#fff; padding:20px; border-radius:10px; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:10px; border-bottom:1px solid #ddd; }
        th { background:#4f46e5; color:white; }
        .topbar { display:flex; justify-content:space-between; margin-bottom:15px; }
        a { text-decoration:none; color:#4f46e5; font-weight:bold; }
    </style>
</head>

<body>

<div class="container">

    <div class="topbar">
        <h2>Admin Dashboard</h2>
        <a href="logout.php">Logout</a>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Last</th>
            <th>First</th>
            <th>Middle</th>
            <th>Gender</th>
            <th>Email</th>
            <th>DOB</th>
            <th>Username</th>
            <th>Type</th>
        </tr>

        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= $u['user_id'] ?></td>
            <td><?= htmlspecialchars($u['lastname']) ?></td>
            <td><?= htmlspecialchars($u['firstname']) ?></td>
            <td><?= htmlspecialchars($u['middlename']) ?></td>
            <td><?= htmlspecialchars($u['gender']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= htmlspecialchars($u['dob']) ?></td>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td><?= htmlspecialchars($u['utype']) ?></td>
        </tr>
        <?php endforeach; ?>

    </table>

</div>

</body>
</html>