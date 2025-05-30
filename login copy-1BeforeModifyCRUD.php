<?php
ob_start();
session_start();
// if (isset($_SESSION['user_id'])) {
    //require_once('header.php');
    require_once('db.php');
// }    

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch user data from the database
    $stmt = $conn->prepare("
    SELECT us.*, ro.role_name as role__name FROM users us
    LEFT JOIN roles ro ON us.role_id = ro.id 
    WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify user and password
    // if ($user && password_verify($password, $user['password'])) {
    // if ($user && password_verify($password, $user['password'])) {
    if ((isset($username)) && (isset($user['username']))) {
        if (($username == $user['username'])) {
            if (($password == $user['password'])) {
                // Set session variables
                $_SESSION['language'] = 'english';
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_img'] = $user['img'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['role_name'] = $user['role__name'];

                // Redirect to dashboard
                // header("Location: dashboard.php");
                header("Location: index1.php");
                exit();
            }
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .login-container {
            width: 300px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ccc;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
        }

        .btn {
            padding: 10px 15px;
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        .error {
            color: red;
        }
    </style>
</head>

<body>

    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($error)) {
            echo "<p class='error'>$error</p>";
        } ?>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>

    <?php require_once('footer.php'); ?>
    </body>

</html>