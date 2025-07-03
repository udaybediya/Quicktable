<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Allow requests from any origin

$servername = "localhost";  
$username = "root";  
$password = "";  // Keep blank if no password is set
$dbname = "quicktable";  // Your correct database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// Get the query parameter
$query = isset($_GET['query']) ? $_GET['query'] : '';

// Query to fetch item names based on the query
$sql = "SELECT item_name FROM item WHERE item_name LIKE '%$query%'";  // Filtering based on query
$result = $conn->query($sql);

$itemNames = [];
while ($row = $result->fetch_assoc()) {
    $itemNames[] = $row['item_name'];
}

$conn->close();
echo json_encode($itemNames, JSON_UNESCAPED_UNICODE); // Encode as JSON
?>
