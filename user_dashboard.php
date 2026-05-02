<?php
session_start();
include('connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// GET ALL USER PREDICTIONS
$stmt = $conn->prepare("
    SELECT * FROM classification 
    WHERE user_id = ? 
    ORDER BY id DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <style>
        body { font-family: Arial; background:#f4f4f4; padding:20px; }
        .container { background:white; padding:20px; border-radius:10px; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { padding:10px; border-bottom:1px solid #ddd; font-size:14px; }
        th { background:#4f46e5; color:white; }
        .topbar { display:flex; justify-content:space-between; }
        a { text-decoration:none; color:#4f46e5; font-weight:bold; }
        .btn { padding:8px 12px; background:#4f46e5; color:white; border-radius:5px; }
    </style>
</head>

<body>

<div class="container">

    <div class="topbar">
        <h2>Welcome, <?php echo $_SESSION['username']; ?></h2>
        <div>
            <a class="btn" href="airlines_classification.php">New Prediction</a>
            <a class="btn" href="logout.php">Logout</a>
        </div>
    </div>

    <h3>Your Prediction History</h3>

    <table>
        <tr>
            <th>ID</th>
            <th>Gender</th>
            <th>Travel</th>
            <th>Class</th>
            <th>Prediction</th>
            <th>Confidence</th>
        </tr>

        <?php foreach ($data as $row): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['Gender'] ?></td>
            <td><?= $row['Type_of_Travel'] ?></td>
            <td><?= $row['Class'] ?></td>
            <td><b><?= $row['target'] ?></b></td>
            <td><?= round($row['confidence'] * 100, 2) ?>%</td>
        </tr>
        <?php endforeach; ?>

    </table>

</div>

</body>
</html>