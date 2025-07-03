<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Quicktable";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, 3306); // Change port if needed

if ($conn->connect_error) {
    die(json_encode(['error' => "Connection failed: " . $conn->connect_error]));
}

// Debug: Check if database exists
$check_db = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'Quicktable'");
if ($check_db->num_rows == 0) {
    die(json_encode(['error' => "Database 'Quicktable' does not exist"]));
}

// Run query
$sql = "SELECT table_no, status FROM table_status";
$result = $conn->query($sql);

if (!$result) {
    die(json_encode(['error' => "Query failed: " . $conn->error]));
}

$tables = array();
while ($row = $result->fetch_assoc()) {
    $tables[] = $row;
}

echo json_encode($tables);
$conn->close();

?>
