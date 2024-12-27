<?php
session_start();
if (!isset($_SESSION['st_idcard'])) {
    header("Location: login.php");
    exit();
}

// ไฟล์นี้เปิดจาก Links ของ line notify เพื่อเข้ามาดูรายละเอียอและกดรับงาน หรือ ไม่รับ
// เมื่อกดรับงานแล้ว จะมีการส่งข้อมูลของผู้รบงานไปในไลน์อีกคร้งแจ้งว่า ได้รับงานเรียบร้อยแล้ว

include 'conf.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($request_id === 0) {
    echo "Invalid request ID.";
    exit();
}

$sql = "SELECT hr.*, s.st_prefix, s.st_firstname, s.st_lastname, s.st_level, s.st_room, s.st_number 
        FROM help_requests hr
        JOIN users s ON hr.user_id = s.st_idcard
        WHERE hr.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $request_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "No help request found with the given ID.";
    exit();
}

$request = $result->fetch_assoc();

// $conn->close();

function updateRequestStatus( $status, $request_id)
{
    global $conn;
    $sql = "UPDATE help_requests SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $status, $request_id);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status'];
    updateRequestStatus($status, $request_id);
    header("Location: view_request.php?id=$request_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดการขอความช่วยเหลือ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h2>รายละเอียดการขอความช่วยเหลือ</h2>
            </div>
            <div class="card-body">
                <p><strong>ชื่อ นามสกุล:</strong>
                    <?php echo htmlspecialchars($request['st_prefix'] . $request['st_firstname'] . " " . $request['st_lastname']); ?>
                </p>
                <p><strong>ชั้น:</strong> <?php echo htmlspecialchars($request['st_level']); ?></p>
                <p><strong>เลขที่:</strong> <?php echo htmlspecialchars($request['st_number']); ?></p>
                <p><strong>อาการ:</strong> <?php echo htmlspecialchars($request['symptoms']); ?></p>
                <p><strong>สถานที่:</strong> <?php echo htmlspecialchars($request['location']); ?></p>
                <p><strong>สถานะ:</strong> <?php echo htmlspecialchars($request['status']); ?></p>

                <form action="" method="POST">
                    <input type="hidden" name="status" value="กำลังดำเนินการ">
                    <input type="text" name="id" value="<?php echo $request_id; ?>">
                    <button type="submit" class="btn btn-primary">รับงาน</button>
                    <button type="reset" class="btn btn-danger">ไม่รับงาน</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>