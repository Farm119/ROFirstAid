<?php
// เชื่อมต่อฐานข้อมูล
// $conn = new mysqli("localhost", "roacth_mongkol", "Mongkol2567", "roacth_mongkol");
include 'conf.php';

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['token']) && isset($_POST['password'])) {
    $token = $conn->real_escape_string($_POST['token']);
    $newPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // ตรวจสอบโทเค็น
    $sql = "SELECT email FROM password_resets WHERE token='$token' AND expiry > NOW()";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // ดึงข้อมูลอีเมล์ของผู้ใช้
        $row = $result->fetch_assoc();
        $email = $row['email'];

        // อัปเดตรหัสผ่านในฐานข้อมูล
        $sql = "UPDATE users SET password='$newPassword' WHERE email='$email'";
        if ($conn->query($sql) === TRUE) {
            echo "Password has been reset successfully.";
            // ลบโทเค็นหลังจากใช้งาน
            $sql = "DELETE FROM password_resets WHERE token='$token'";
            $conn->query($sql);
            header("Location: login.php");
            exit;
        } else {
            echo "Failed to reset the password.";
        }
    } else {
        echo "Invalid or expired token.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- <link href="https://maxcdn.bootstrapcdn.com/bootstrap/5.0.0/css/bootstrap.min.css" rel="stylesheet"> -->
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="mb-4">Reset Password</h2>
            <form method="POST" action="">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
