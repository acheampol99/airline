<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Info</title>
</head>
<body>
    <h1>Session Debug Info</h1>
    <pre>
        <?php
        echo "Session Data:\n";
        print_r($_SESSION);
        echo "\n\nServer Variables:\n";
        echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
        echo "HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "\n";
        ?>
    </pre>
    <a href="logout.php">Logout</a> | <a href="login.php">Login</a>
</body>
</html>
