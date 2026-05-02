<?php
session_start();
include('connect.php');

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM user WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['utype'] = $user['utype'];

            // log login
            $logStmt = $conn->prepare("INSERT INTO user_log (user_id) VALUES (?)");
            $logStmt->bind_param("i", $user['user_id']);
            $logStmt->execute();

            $_SESSION['log_id'] = $conn->insert_id;

            // ROUTING FIX
            if ($user['utype'] === "admin") {
                header("Location: admin_dashboard.php");
                exit();
            } else {
                header("Location: user_dashboard.php");
                exit();
            }

        } else {
            $errors[] = "Invalid password.";
        }

    } else {
        $errors[] = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>

<form method="POST" class="login-form">
    <h2>Login</h2>

    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>

    <?php if (!empty($errors)): ?>
        <div style="color:red;">
            <?php foreach ($errors as $e) echo $e . "<br>"; ?>
        </div>
    <?php endif; ?>

    <button type="submit">Login</button>

    <!-- REGISTER LINK -->
    <p style="margin-top:10px;">
        Don't have an account?
        <a href="register.php">Register here</a>
    </p>
</form>

</body>
</html>