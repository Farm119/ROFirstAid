<?php
session_start();
if (!isset($_SESSION['st_idcard'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['st_idcard'];
$st_prefix = $_SESSION['st_prefix'];
$st_firstname = $_SESSION['st_firstname'];
$st_lastname = $_SESSION['st_lastname'];
$st_level = $_SESSION['st_level'];
$st_room = $_SESSION['st_room'];
$st_number = $_SESSION['st_number'];

include 'conf.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// LINE Notify access token
$line_notify_token = 'Vb7v85UthJQSdjpc9pNZcEPclgAJjEo2PqWOnFIMWTn';

// Function to send LINE Notify message
function sendLineNotify($message, $token) {
    $url = 'https://notify-api.line.me/api/notify';
    $data = [
        'message' => $message
    ];
    $headers = [
        'Content-Type: application/x-www-form-urlencoded',
        'Authorization: Bearer ' . $token
    ];

    $options = [
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", $headers),
            'content' => http_build_query($data),
        ],
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    return $result;
}


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $symptoms = $conn->real_escape_string($_POST['symptoms']);
    $location = $conn->real_escape_string($_POST['location']);
    $status = $conn->real_escape_string($_POST['status']);

    $sql = "INSERT INTO help_requests (user_id, symptoms, location, status) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssss', $user_id, $symptoms, $location, $status);
    if ($stmt->execute()) {
        $last_id = $stmt->insert_id;
        $_SESSION['Success'] = "ส่งข้อมูลสำเร็จ";
        
        // URL to view the request details
        $base_url = 'https://yourdomain.com'; // Change this to your domain
        $view_request_url = $base_url . "/view_request.php?id=" . $last_id;

        // Send notification to LINE
        $message = "มีการแจ้งขอความช่วยเหลือใหม่: ID $last_id\nดูรายละเอียดเพิ่มเติม: $view_request_url";
        sendLineNotify($message, $line_notify_token);

        header("Location: form_aid.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch help requests for the logged-in user
$sql = "SELECT * FROM help_requests WHERE user_id = ? ORDER BY id DESC;";
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h2>Welcome, <?php echo htmlspecialchars($st_prefix . $st_firstname . " " . $st_lastname); ?> ชั้น <?php echo htmlspecialchars($st_level . "/" . $st_room); ?>
                    <a href="logout.php?logout=true" class="btn btn-danger">ออกจากระบบ</a>
                </h2>
            </div>
            <div class="card-body">
                <form action="" method="POST" onsubmit="return validateForm()">
                    <div class="mb-3">
                        <label for="symptoms" class="form-label">แจ้งอาการที่ขอรับการช่วยเหลือ</label>
                        <textarea class="form-control" id="symptoms" name="symptoms" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">สถานที่</label>
                        <input type="text" class="form-control" id="location" name="location">
                    </div>
                    <div>
                        <input type="hidden" name="status" id="status" value="รอดำเนินการ">
                    </div>
                    <button type="submit" class="btn btn-primary">ส่งข้อความ</button>
                </form>
            </div>
        </div>

        <div class="card mt-5">
            <div class="card-header">
                <h3>ข้อมูลที่แจ้งขอความช่วยเหลือ</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="help-requests-table">
                        <thead>
                            <tr>
                                <th style="width: 15%;">วันที่แจ้ง</th>
                                <th style="width: 35%;">อาการ</th>
                                <th style="width: 25%;">สถานที่</th>
                                <th style="width: 25%;">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($help_requests as $request) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($request['created_at']); ?></td>
                                    <td><?php echo htmlspecialchars($request['symptoms']); ?></td>
                                    <td><?php echo htmlspecialchars($request['location']); ?></td>
                                    <td class="<?php echo $request['status'] === 'รอดำเนินการ' ? 'bg-warning text-dark' : ($request['status'] == 'ดำเนินการแล้ว' ? 'bg-success text-white' : ''); ?>">
                                        <?php echo htmlspecialchars($request['status']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function validateForm() {
            const symptoms = document.getElementById('symptoms').value;
            const location = document.getElementById('location').value;
            if (!symptoms || !location) {
                Swal.fire({
                    icon: 'error',
                    title: 'ข้อมูลไม่ครบถ้วน',
                    text: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                });
                return false;
            }
            return true;
        }

        async function fetchHelpRequests() {
            try {
                const response = await fetch('fetch_help_requests.php'); // Correct endpoint URL
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const helpRequests = await response.json();
                console.log(helpRequests); // Add this line to check the data received
                updateTable(helpRequests);
            } catch (error) {
                console.error('Error fetching help requests:', error);
            }
        }

        function updateTable(helpRequests) {
            const tbody = document.querySelector('#help-requests-table tbody');
            tbody.innerHTML = '';

            helpRequests.forEach(request => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${request.created_at}</td>
                    <td>${request.symptoms}</td>
                    <td>${request.location}</td>
                    <td class="${request.status == 'รอดำเนินการ' ? 'bg-warning text-dark' : (request.status == 'ดำเนินการแล้ว' ? 'bg-success text-white' : (request.status == 'กำลังดำเนินการ' ? 'bg-primary text-white' : ''))}">
                        ${request.status}
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        setInterval(fetchHelpRequests, 5000); // Fetch new data every 5 seconds

        // Initial fetch to populate the table immediately on page load
        fetchHelpRequests();
    </script>

    <?php if (isset($_SESSION['Success'])) { ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'ส่งข้อมูลได้สำเร็จ',
                text: 'คุณจะได้รับความช่วยเหลืออย่างรวดเร็ว',
            });
            <?php unset($_SESSION["Success"]); ?>
        </script>
    <?php } ?>
</body>

</html>
