<?php
session_start();
include 'conf.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];


    mysqli_set_charset($conn, "utf8");

    // การใช้ prepared statement เพื่อตรวจสอบ username และ password
    $stmt = $conn->prepare("SELECT * FROM users WHERE st_idcard = ? AND st_password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $_SESSION['st_idcard'] = $row['st_idcard'];
        $_SESSION['st_prefix'] = $row['st_prefix'];
        $_SESSION['st_firstname'] = $row['st_firstname'];
        $_SESSION['st_lastname'] = $row['st_lastname'];
        $_SESSION['st_level'] = $row['st_level'];
        $_SESSION['st_room'] = $row['st_room'];
        $_SESSION['st_number'] = $row['st_number'];
        header("Location: form_aid.php");
        exit();
    } else {
        $_SESSION["login_fail"] = "<p>Your Username or Password is invalid</p>";
        header("Location: login.php");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <!-- Include Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .login-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div class="login-container mt-5">
        <h2 class="text-center mb-4">RO First AID</h2>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Username:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>
        <p class="text-center mt-3">Don't have an account? <a href="register.php">Register here</a></p>
        <p class="text-center mt-3">Forgot Password? <a href="forgot_password.php">Click here</a></p>
    </div>

    <!-- Include Bootstrap 5 JavaScript and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <?php
    // Display alert if login failed
    if (isset($_SESSION['login_fail'])) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'ไม่สามารถเข้าสู่ระบบได้',
                text: 'คุณกรอก Username หรือ Password ไม่ถูกต้อง',
            });
        </script>";
        unset($_SESSION["login_fail"]); // Unset the login fail message after displaying it
    }
    ?>
</body>

</html>