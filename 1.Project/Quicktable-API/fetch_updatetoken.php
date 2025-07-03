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

// Handle GET request (fetch next token number for a specific table)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['table_no'])) {
        $tableNo = $_GET['table_no'];
        $nextToken = getNextTokenForTable($conn, $tableNo);
        echo json_encode(['next_token' => 'TK-' . $nextToken]);
    } else {
        echo json_encode(['error' => 'Table number is required.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}

// Function to get the next token number
function getNextTokenForTable($conn, $tableNo) {
    // Check for existing non-finished tokens
    $stmt = $conn->prepare("
        SELECT MAX(CAST(SUBSTRING(token_number, 4) AS UNSIGNED)) AS max_token 
        FROM orders 
        WHERE table_no = ? 
        AND (status IS NULL OR status != 'finished')
    ");
    $stmt->bind_param("s", $tableNo);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $maxToken = $row['max_token'];

    if ($maxToken !== null) {
        return $maxToken; // Return existing unfinished token
    } else {
        // Get the highest token (finished or unfinished) and increment
        $stmt = $conn->prepare("
            SELECT MAX(CAST(SUBSTRING(token_number, 4) AS UNSIGNED)) AS max_token 
            FROM orders 
            WHERE table_no = ?
        ");
        $stmt->bind_param("s", $tableNo);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return ($row['max_token'] ?? 0) + 1;
    }
}

$conn->close();
?>