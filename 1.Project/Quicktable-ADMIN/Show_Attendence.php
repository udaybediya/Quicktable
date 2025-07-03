<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "quicktable";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch employee names for dropdown
$employees = [];
$sql = "SELECT DISTINCT full_name FROM appearance ORDER BY full_name";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $employees[] = $row['full_name'];
}

// Fetch dates for dropdown
$dates = [];
$sql = "SELECT DISTINCT date FROM appearance ORDER BY date DESC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $dates[] = $row['date'];
}

// Handle AJAX Request for Attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['employee'])) {
    $employee = $_POST['employee'];
    $date = $_POST['date'];

    $sql = "SELECT * FROM appearance WHERE full_name = ?";
    $params = [$employee];
    $types = "s";

    if ($date !== '' && $date !== 'all') {
        $sql .= " AND date = ?";
        $params[] = $date;
        $types .= "s";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $attendanceData = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($attendanceData);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Show Attendance Page</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .attendance-container {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-top: 20px;
        }
        select {
            padding: 10px;
            font-size: 16px;
            border-radius: 8px;
            border: 1px solid #ccc;
            background-color: #f8f9fa;
            cursor: pointer;
            text-align: center;
            outline: none;
        }
        #employeeName {
            width: 500px;
        }
        #attendanceDate {
            width: 200px;
        }
        select:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }
        .see-attendance-btn {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            background-color: #007bff;
            color: white;
            cursor: pointer;
            transition: background 0.3s;
        }
        .see-attendance-btn:hover {
            background-color: #0056b3;
        }
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            border-spacing: 0 8px; 
            display: none; /* Initially hidden */
        }
        .attendance-table th, .attendance-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        .attendance-table th {
            background-color: #11101d;
            color: white;
        }
        .attendance-table tbody tr {
            background: white;

            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>    
    <!-- Include Sidebar -->
    <div id="sidebar-container"></div>
    
    <section class="home-section">
        <div class="text">Appearance Page > Show Attendance</div>

        <div class="attendance-container">
            <select id="employeeName">
                <option value="">Select Employee</option>
                <?php foreach ($employees as $employee): ?>
                    <option value="<?php echo htmlspecialchars($employee); ?>">
                        <?php echo htmlspecialchars($employee); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="attendanceDate">
                <option value="">Select Date</option>
                <option value="all">All Dates</option>
                <?php foreach ($dates as $date): ?>
                    <option value="<?php echo $date; ?>">
                        <?php echo date("d/m/Y", strtotime($date)); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button class="see-attendance-btn" onclick="showAttendance()">See Attendance</button>
        </div>

        <table class="attendance-table" id="attendanceTable">
            <thead>
                <tr>
                    <th>Sr No</th>
                    <th>Full Name</th>
                    <th>Position</th>
                    <th>Date</th>
                    <th>Department</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="attendanceBody"></tbody>
        </table>
    </section>

    <script src="JS/slidebar.js"></script>
    <script>
        function showAttendance() {
            var employee = document.getElementById("employeeName").value;
            var date = document.getElementById("attendanceDate").value;
            var table = document.getElementById("attendanceTable");
            var tbody = document.getElementById("attendanceBody");

            // Validate both fields before proceeding
            if (employee === "" || date === "") {
                alert("Please select both Employee and Date!");
                return;
            }

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "<?php echo $_SERVER['PHP_SELF']; ?>", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        tbody.innerHTML = "";

                        if (response.length === 0) {
                            alert("No attendance records found.");
                            table.style.display = "none";
                            return;
                        }

                        response.forEach((record, index) => {
                            var formattedDate = formatDate(record.date);
                            var row = `<tr>
                                <td>${index + 1}</td>
                                <td>${record.full_name}</td>
                                <td>${record.position}</td>
                                <td>${formattedDate}</td>
                                <td>${record.Department}</td>
                                <td>${record.status}</td>
                            </tr>`;
                            tbody.innerHTML += row;
                        });

                        table.style.display = "table";
                    } catch (error) {
                        console.error("JSON Parse Error:", error);
                    }
                }
            };

            xhr.send("employee=" + encodeURIComponent(employee) + "&date=" + encodeURIComponent(date));
        }

        function formatDate(dateString) {
            var parts = dateString.split("-");
            return parts[2] + "/" + parts[1] + "/" + parts[0];
        }
    </script>
</body>
</html>
