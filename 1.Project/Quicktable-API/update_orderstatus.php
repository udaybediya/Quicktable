<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Quicktable";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => "Connection failed: " . $conn->connect_error]));
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $tableNo = $data['table_no'];
    $tokenNumber = $data['token_number'];

    // Update the status to "Finished" and bill to "Unpaid"
    $stmt = $conn->prepare("UPDATE orders SET status = 'Finished', bill = 'Unpaid' WHERE table_no = ? AND token_number = ?");
    $stmt->bind_param("ss", $tableNo, $tokenNumber);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'No records updated']);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid request method']);
}

$conn->close();
?>
