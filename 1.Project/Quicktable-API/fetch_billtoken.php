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

// Handle GET request (fetch token number for a specific table with status 'Finished' and bill 'Unpaid')
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['table_no'])) {
        $tableNo = $_GET['table_no'];
        $token = getUnpaidFinishedTokenForTable($conn, $tableNo);
        if ($token !== null) {
            echo json_encode(['token' => $token]);
        } else {
            echo json_encode(['error' => 'No finished and unpaid token found for this table.']);
        }
    } else {
        echo json_encode(['error' => 'Table number is required.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}

// Function to get the token number with status 'Finished' and bill 'Unpaid'
function getUnpaidFinishedTokenForTable($conn, $tableNo) {
    // Query to fetch the token number with status 'Finished' and bill 'Unpaid'
    $stmt = $conn->prepare("
        SELECT token_number 
        FROM orders 
        WHERE table_no = ? 
        AND status = 'Finished' 
        AND bill = 'Unpaid'
        LIMIT 1
    ");
    $stmt->bind_param("s", $tableNo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['token_number']; // Return the token number
    } else {
        return null; // No matching token found
    }
}

$conn->close();
?>
