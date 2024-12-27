<?php
// เปิดการแสดงข้อผิดพลาด (ในกรณีที่คุณต้องการตรวจสอบข้อผิดพลาด)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// เชื่อมต่อฐานข้อมูล
$conn = new mysqli("localhost", "roacth_mongkol", "Mongkol2567", "roacth_mongkol");
// include 'conf.php';

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = $conn->real_escape_string($_POST['email']);

    // ตรวจสอบว่าอีเมล์นี้อยู่ในฐานข้อมูลหรือไม่
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // สร้างโทเค็นสำหรับรีเซ็ตรหัสผ่าน
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // โทเค็นหมดอายุใน 1 ชั่วโมง

        // เก็บโทเค็นในฐานข้อมูล
        $sql = "INSERT INTO password_resets (email, token, expiry) VALUES ('$email', '$token', '$expiry')
                ON DUPLICATE KEY UPDATE token='$token', expiry='$expiry'";
        if ($conn->query($sql) === TRUE) {
            // ส่งอีเมลพร้อมกับลิงก์รีเซ็ตรหัสผ่าน
            $resetLink = "https://ro.ac.th/mongkol/filemanager/reset_password.php?token=$token";
            $to = $email;
            $subject = "Password Reset Request";
            $message = "Click here to reset your password: ";
            $message = "$resetLink";
            $headers = "From: noreply@ro.ac.th";

            if (mail($to, $subject, $message, $headers)) {
                echo "<div class='alert alert-success'>A password reset link has been sent to your email.</div>";
                header("Location: login.php");
                exit;
            } else {
                echo "<div class='alert alert-danger'>Failed to send the email.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Failed to store the reset token.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Email not found in the system.</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet"> -->

    <style>
        .form-container {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .form-label {
            font-weight: bold;
        }

        .container {
            margin-top: 50px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="form-container">
                    <h2 class="mb-4 text-center">Forgot Password</h2>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>