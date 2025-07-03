<?php
header('Content-Type: application/json');

$host = "localhost";
$username_db = "root";
$password_db = "";
$database = "Quicktable";

$conn = new mysqli($host, $username_db, $password_db, $database);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed"]));
}

// Check if username and password are provided
if (!isset($_POST['Username']) || !isset($_POST['Password'])) {
    die(json_encode(["success" => false, "message" => "Missing username or password"]));
}

$username = $_POST['Username'];
$password = $_POST['Password'];

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("SELECT full_name, position, Department FROM employee WHERE Username = ? AND Password = ?");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'full_name' => $row['full_name'],
        'position' => $row['position'],
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
}

$stmt->close();
$conn->close();
?>
