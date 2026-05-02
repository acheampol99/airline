<?php
include('connect.php');

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $lname = $_POST['lastname'];
    $fname = $_POST['firstname'];
    $mname = $_POST['middlename'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirmpassword'];

    if (!preg_match("/^[a-zA-Z ]+$/", $lname)) {
        $errors[] = "Last name must contain letters only.";
    }
    if (!preg_match("/^[a-zA-Z ]+$/", $fname)) {
        $errors[] = "First name must contain letters only.";
    }
    if (!preg_match("/^[a-zA-Z ]+$/", $mname)) {
        $errors[] = "Middle name must contain letters only.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    $check = $conn->prepare("SELECT * FROM user WHERE username=? OR email=?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $errors[] = "Username or Email already exists.";
    }

    if (empty($errors)) {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $utype = "user";

        $stmt = $conn->prepare("INSERT INTO user (lastname, firstname, middlename, gender, email, dob, username, password, utype) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $lname, $fname, $mname, $gender, $email, $dob, $username, $hashed_password, $utype);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful'); window.location='login.php';</script>";
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/register.css">
</head>
<body>
    <div class="form-wrapper">


        <?php if (!empty($errors)): ?>
            <div style="color:red;">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="register-form" action="" method="POST">
            <h2>Create Account</h2>

            <label for="lastname">Last Name</label>
            <input type="text" id="lastname" name="lastname" placeholder="Enter your last name">

            <label for="firstname">First Name</label>
            <input type="text" id="firstname" name="firstname" placeholder="Enter your first name">

            <label for="middlename">Middle Name</label>
            <input type="text" id="middlename" name="middlename" placeholder="Enter your middle name">

            <label for="dob">Date of Birth</label>
            <input type="date" id="dob" name="dob">

            <label for="gender">Gender</label>
            <select id="gender" name="gender">
                <option value="" disabled selected>Select gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email">

            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Choose a username">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter a password">

            <label for="confirmpassword">Confirm Password</label>
            <input type="password" id="confirmpassword" name="confirmpassword" placeholder="Confirm your password">

            <button type="submit">Register</button>

            <p class="login-link">Already have an account? <a href="login.php">Login</a></p>
        </form>
    </div>
</body>
</html>