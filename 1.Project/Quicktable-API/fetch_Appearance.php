<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "quicktable";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Existing POST handling code
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['full_name'])) {
        $full_name = $_GET['full_name'];
        
        $stmt = $conn->prepare("SELECT date, status FROM appearance WHERE full_name = ? ORDER BY date DESC");
        $stmt->bind_param("s", $full_name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        echo json_encode(["success" => true, "data" => $data]);
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Username parameter missing"]);
    }
}

$conn->close();
?>