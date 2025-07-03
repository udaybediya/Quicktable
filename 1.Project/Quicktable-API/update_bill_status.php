<?php
header("Content-Type: application/json");

$host = "localhost";
$user = "root";
$password = "";
$database = "Quicktable";
$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['table_no']) || !isset($data['token_number'])) {
    echo json_encode(["success" => false, "message" => "Missing parameters"]);
    exit();
}

$table_no = $conn->real_escape_string($data['table_no']);
$token_number = $conn->real_escape_string($data['token_number']);

// Update the bill status to 'Paid'
$stmt = $conn->prepare("UPDATE orders SET bill = 'Paid' WHERE table_no = ? AND token_number = ?");
$stmt->bind_param("ss", $table_no, $token_number);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Bill marked as Paid"]);
} else {
    echo json_encode(["success" => false, "message" => "Update failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>