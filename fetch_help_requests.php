<?php
session_start();
if (!isset($_SESSION['st_idcard'])) {
    header("HTTP/1.1 401 Unauthorized");
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

include 'conf.php';

$user_id = $_SESSION['st_idcard'];

if ($conn->connect_error) {
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Fetch help requests for the logged-in user
$sql = "SELECT * FROM help_requests WHERE user_id = ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$help_requests = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $help_requests[] = $row;
    }
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($help_requests);
?>
