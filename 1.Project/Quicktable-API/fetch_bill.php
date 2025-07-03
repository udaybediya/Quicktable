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

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['table_no']) && isset($_GET['token_number'])) {
        $tableNo = $_GET['table_no'];
        $tokenNumber = $_GET['token_number'];
        
        // Fetch order details
        $stmt = $conn->prepare("
            SELECT item_name, quantity, item_price, total_price 
            FROM orders 
            WHERE table_no = ? 
            AND token_number = ?
            AND status = 'Finished'
        ");
        $stmt->bind_param("ss", $tableNo, $tokenNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        $grandTotal = 0;
        
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
            $grandTotal += $row['total_price'];
        }
        
        echo json_encode([
            'items' => $items,
            'grand_total' => number_format($grandTotal, 2)
        ]);
        
    } else {
        echo json_encode(['error' => 'Missing parameters']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}

$conn->close();
?>