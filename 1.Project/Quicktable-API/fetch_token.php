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

// Handle GET request (fetch next token number)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $nextToken = getNextToken($conn);
    echo json_encode(['next_token' => 'TK-' . $nextToken]); // Ensure format "TK-1"
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}

// Function to get the next token number from the database
function getNextToken($conn) {
    $stmt = $conn->query("SELECT MAX(CAST(SUBSTRING(token_number, 4) AS UNSIGNED)) AS max_token FROM orders WHERE token_number LIKE 'TK-%'");
    $row = $stmt->fetch_assoc();
    
    if ($row && $row['max_token'] !== null) {
        return $row['max_token'] + 1; // Increment the highest token number
    } else {
        return 1; // Default to "TK-1" if no records exist
    }
}

$conn->close();
?>
