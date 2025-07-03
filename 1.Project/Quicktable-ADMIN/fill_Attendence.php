<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "quicktable";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_date = date('Y-m-d');
    $status = $_POST['status'];
    $selected_employees = $_POST['employees'] ?? [];
    
    // Determine opposite status
    $opposite_status = ($status === 'Present') ? 'Absent' : 'Present';
    
    // Fetch all employee SR numbers
    $all_employees_stmt = $conn->prepare("SELECT sr_no FROM employee");
    $all_employees_stmt->execute();
    $all_employees_result = $all_employees_stmt->get_result();
    $all_employees = $all_employees_result->fetch_all(MYSQLI_ASSOC);
    
    foreach ($all_employees as $emp) {
        $sr_no = $emp['sr_no'];
        $employee_status = in_array($sr_no, $selected_employees) ? $status : $opposite_status;
        
        // Check if record exists
        $stmt = $conn->prepare("SELECT * FROM appearance WHERE sr_no = ? AND date = ?");
        $stmt->bind_param('ss', $sr_no, $current_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing record
            $stmt = $conn->prepare("UPDATE appearance SET status = ? WHERE sr_no = ? AND date = ?");
            $stmt->bind_param('sss', $employee_status, $sr_no, $current_date);
        } else {
            // Insert new record
            $stmt = $conn->prepare("INSERT INTO appearance (sr_no, full_name, position, Department, status, date)
                                   SELECT sr_no, full_name, position, Department, ?, ? FROM employee WHERE sr_no = ?");
            $stmt->bind_param('sss', $employee_status, $current_date, $sr_no);
        }
        $stmt->execute();
    }
    
    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Get current date for display
$display_date = date('d/m/Y');
$current_date_db = date('Y-m-d');

// Fetch employee data with attendance status
$stmt = $conn->prepare("SELECT e.sr_no, e.full_name, e.position, e.Department, COALESCE(a.status, 'Pending') AS status
                        FROM employee e
                        LEFT JOIN appearance a ON e.sr_no = a.sr_no AND a.date = ?");
$stmt->bind_param('s', $current_date_db);
$stmt->execute();
$result = $stmt->get_result();
$employees = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>fill Attendence Page</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .date-box {
            background: #11101d;
            color: white;
            padding: 10px;
            text-align: center;
            font-size: 18px;
            border-radius: 5px;
            margin-bottom: 20px;
            width: 200px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th {
            background: #11101d;
            color: white;
            padding: 10px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        .btn {
            padding: 10px 16px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            border-radius: 5px;
            margin: 5px;
        }

        .btn-present {
            background: green;
            color: white;
        }

        .btn-absent {
            background: red;
            color: white;
        }

        .status-present {
            color: green;
            font-weight: bold;
        }

        .status-absent {
            color: red;
            font-weight: bold;
        }

        .status-pending {
            color: orange;
            font-weight: bold;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .checkbox-cell {
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <div id="sidebar-container"></div>

    <section class="home-section">
        <div class="text">Appearance Page > fill Attendence</div>

        <!-- Date Box -->
        <div class="date-box">
            Date: <span id="currentDate"><?php echo $display_date; ?></span>
        </div>

        <form method="post" id="attendanceForm">
            <input type="hidden" name="status" id="statusInput">
            
            <table>
                <thead>
                    <tr>
                        <th>Select</th>
                        <th>Sr No</th>
                        <th>Full Name</th>
                        <th>Position</th>
                        <th>Department</th>
                        <th>Attendance Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $index => $emp): ?>
                    <tr id="row-<?php echo $emp['sr_no']; ?>">
                        <td class="checkbox-cell">
                            <input type="checkbox" name="employees[]" 
                                   value="<?php echo $emp['sr_no']; ?>"
                                   class="attendance-checkbox"
                                   <?php echo ($emp['status'] === 'Present') ? 'checked' : ''; ?>>
                        </td>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($emp['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($emp['position']); ?></td>
                        <td><?php echo htmlspecialchars($emp['Department']); ?></td>
                        <td class="status-<?php echo strtolower($emp['status']); ?>">
                            <?php echo $emp['status']; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="action-buttons">
                <button type="button" class="btn btn-present" onclick="confirmAttendance('Present')">Present</button>
                <button type="button" class="btn btn-absent" onclick="confirmAttendance('Absent')">Absent</button>
            </div>
        </form>
    </section>

    <script src="JS/slidebar.js"></script>
    <script>
        function confirmAttendance(status) {
            const checkboxes = document.querySelectorAll('.attendance-checkbox:checked');
            const selectedEmployees = Array.from(checkboxes).map(checkbox => {
                return checkbox.closest('tr').children[2].textContent;
            });

            if (status === 'Present' && checkboxes.length === 0) {
                alert("Please select at least one employee when marking as Present.");
                return;
            }

            const confirmation = confirm(`Are you sure you want to mark ${selectedEmployees.join(', ')} as ${status}? All other employees will be marked as ${status === 'Present' ? 'Absent' : 'Present'}.`);
            if (confirmation) {
                document.getElementById('statusInput').value = status;
                document.getElementById('attendanceForm').submit();
            }
        }

        // Add row highlighting based on initial status
        document.querySelectorAll('tr[id^="row-"]').forEach(row => {
            const statusCell = row.querySelector('td:last-child');
            if (statusCell.classList.contains('status-present')) {
                row.style.backgroundColor = '#d4edda';
            } else if (statusCell.classList.contains('status-absent')) {
                row.style.backgroundColor = '#f8d7da';
            }
        });
    </script>
</body>
</html>