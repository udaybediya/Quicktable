<?php
header("Content-Type: application/json");

// Database Connection
$host = "localhost"; // Change if using a different server
$user = "root"; // Database username
$password = ""; // Database password (leave empty if none)
$database = "Quicktable"; // Database name

$conn = new mysqli($host, $user, $password, $database);

// Check for connection errors
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]));
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['table_no']) || !isset($data['status'])) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit();
}

$table_no = trim($data['table_no']);
$status = trim($data['status']);

// Ensure the table_no is in the correct format (e.g., "Table-1")
if (strpos($table_no, "Table-") === 0) {
    $table_no = "Table-" . substr($table_no, 6); // Remove any redundant "Table-" prefix and add it back
}

// Validate table_no format (e.g., "Table-1", "Table-2")
if (!preg_match("/^Table-\d+$/", $table_no)) {
    echo json_encode(["success" => false, "message" => "Invalid table number format"]);
    exit();
}

// Validate status (only allow "occupied" or "unoccupied")
$valid_statuses = ["occupied", "unoccupied"];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(["success" => false, "message" => "Invalid status value"]);
    exit();
}

// Check if the table exists in `table_status`
$checkQuery = "SELECT * FROM table_status WHERE table_no = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("s", $table_no);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // If table exists, update status
    $updateQuery = "UPDATE table_status SET status = ? WHERE table_no = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ss", $status, $table_no);
} else {
    // If table doesn't exist, insert a new row
    $insertQuery = "INSERT INTO table_status (table_no, status) VALUES (?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ss", $table_no, $status);
}

// Execute the query and return response
if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Table status updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Database error"]);
}

// Close statement and database connection
$stmt->close();
$conn->close();
?>