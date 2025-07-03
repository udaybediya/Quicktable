<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Page</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/employee.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        /* Overlay and Popup Styling */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .form-popup {
            padding: 20px;
            border-radius: 10px;
            width: 75%;
            max-width: 600px;
        }

        .card {
            width: 800px;
            background: #ffffff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 4px 4px 15px rgba(0, 0, 0, 0.1);
        }

        h3 {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            color: #333;
        }

        /* Form Styling */
        .form-group {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-group div {
            width: 48%;
        }

        .form-control {
            width: 100%;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
            box-sizing: border-box;
        }

        /* Button Styling */
        .btn-container {
            text-align: center;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 18px;
            font-size: 14px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
            margin-left: 10px;
        }

        .btn-danger:hover {
            background-color: #a71d2a;
        }
    </style>
</head>
<body>
    <div id="sidebar-container"></div>

    <section class="home-section">
        <div class="text">Employee Page</div>

        <?php
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "quicktable";

        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        // update Employee
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_employee'])) {
            $sr_no = $_POST['sr_no'];
            $fields = [];
        
            $allowed_fields = ["full_name", "position", "username", "password", "email", "address", "gender", "date_of_birth", "phone", "department"];
            
            foreach ($allowed_fields as $field) {
                if (isset($_POST[$field])) {
                    $fields[] = "$field = ?";
                }
            }
        
            if (!empty($fields)) {
                $sqlUpdate = "UPDATE employee SET " . implode(", ", $fields) . " WHERE sr_no = ?";
                $stmtUpdate = $conn->prepare($sqlUpdate);
        
                $types = str_repeat("s", count($fields)) . "i"; 
                $values = array_values(array_intersect_key($_POST, array_flip($allowed_fields)));
                $values[] = $sr_no;
        
                $stmtUpdate->bind_param($types, ...$values);
                $stmtUpdate->execute();
                $stmtUpdate->close();
            }
        
            echo "Employee updated successfully!";
            exit();
        }

        // deleteEmployee
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_employee'])) {
            $sr_no = $_POST['sr_no'];
        
            $sqlDelete = "DELETE FROM employee WHERE sr_no = ?";
            $stmtDelete = $conn->prepare($sqlDelete);
            $stmtDelete->bind_param("i", $sr_no);
            $stmtDelete->execute();
            $stmtDelete->close();
        
            echo "Employee deleted successfully!";
            exit();
        }        

        // Handle form submission for adding a new employee
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_employee'])) {
            $full_name = $_POST['full_name'];
            $position = $_POST['position'];
            $Username = $_POST['Username'];
            $Password = $_POST['Password'];
            $Email = $_POST['Email'];
            $address = $_POST['address'];
            $Gender = $_POST['Gender'];
            $date_of_birth = $_POST['date_of_birth'];
            $Phone = $_POST['Phone'];
            $Department = $_POST['Department'];

            $sqlInsert = "INSERT INTO employee (full_name, position, Username, Password, Email, address, Gender, date_of_birth, Phone, Department)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bind_param("ssssssssss", $full_name, $position, $Username, $Password, $Email, $address, $Gender, $date_of_birth, $Phone, $Department);
            $stmtInsert->execute();
            $stmtInsert->close();

            echo "<script>window.location.reload();</script>";
        }

        // Fetch and display employees
        $sql = "SELECT sr_no, full_name, position, Username, Password, Email, address, Gender, date_of_birth, Phone, Department FROM employee";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="employee-card">';
                echo '    <div class="employee-header">';
                echo '        <div class="employee-info">';
                echo '            <span class="sr-no">' . $row["sr_no"] . '.</span>';
                echo '            <span class="employee-name">' . $row["full_name"] . '</span>';
                echo '            <span class="employee-type">' . $row["position"] . '</span>';
                echo '        </div>';
                echo '        <div class="actions">';
                echo '            <button class="icon-btn" onclick="toggleDetails(this)"><i class=\'bx bxs-down-arrow\'></i> </button>';
                echo '        </div>';
                echo '    </div>';
                echo '    <div class="details">';
                echo '        <div class="details-content">';
                echo '            <div class="details-info">';
                foreach ($row as $key => $value) {
                    $label = ucfirst(str_replace("_", " ", $key));
                    if ($key == "date_of_birth") {
                        $value = date("d/m/Y", strtotime($value));
                    }
                    echo "<p><strong>$label:</strong> <span>$value</span></p>";
                }
                echo '            </div>';
                echo '            <div class="details-actions">';
                echo '                <button class="btn update-btn" onclick="enableEdit(this)">Update</button>';
                echo '                <button class="btn delete-btn" onclick="deleteEmployee(' . $row["sr_no"] . ')">Delete</button>';
                echo '            </div>';
                echo '        </div>';
                echo '    </div>';
                echo '</div>';
            }
        } else {
            echo "No employees found.";
        }
        $conn->close();
        ?>
        <div class="button-container">
            <button id="add-new-item-btn" class="add-item-btn">+ Add New Employee</button>
        </div>
    </section>

    <!-- Popup Form for Adding New Employee -->
    <div class="overlay" id="overlay">
        <div class="form-popup">
            <div class="card">
                <h3>Employee Details</h3>
                <form id="employeeForm">
                    <div class="form-group">
                        <div>
                            <label>Full Name:</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        <div>
                            <label>Position:</label>
                            <input type="text" class="form-control" id="position" name="position" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <div>
                            <label>Username:</label>
                            <input type="text" class="form-control" id="Username" name="Username" required>
                        </div>
                        <div>
                            <label>Password:</label>
                            <input type="password" class="form-control" id="Password" name="Password" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <div>
                            <label>Email:</label>
                            <input type="email" class="form-control" id="Email" name="Email" required>
                        </div>
                        <div>
                            <label>Phone:</label>
                            <input type="tel" class="form-control" id="Phone" name="Phone" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <div>
                            <label>Address:</label>
                            <input type="text" class="form-control" id="address" name="address" required>
                        </div>
                        <div>
                            <label>Gender:</label>
                            <select class="form-control" id="Gender" name="Gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <div>
                            <label>Birth Date:</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
                        </div>
                        <div>
                            <label>Department:</label>
                            <input type="text" class="form-control" id="Department" name="Department" required>
                        </div>
                    </div>

                    <div class="btn-container">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="reset" class="btn btn-danger" onclick="closePopup()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="JS/slidebar.js"></script>
    <script src="JS/employee.js"></script>


</body>
</html>
