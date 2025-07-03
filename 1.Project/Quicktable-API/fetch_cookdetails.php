<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "quicktable";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT table_no, token_number, item_name, quantity FROM orders WHERE status IS NULL ORDER BY table_no";
$result = $conn->query($sql);

$response = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $table_no = str_replace("Table-", "", $row["table_no"]);
        if (!isset($response[$table_no])) {
            $response[$table_no] = [
                "table_no" => $table_no,
                "token_number" => $row["token_number"],
                "items" => []
            ];
        }
        $response[$table_no]["items"][] = [
            "item_name" => $row["item_name"],
            "quantity" => $row["quantity"]
        ];
    }
}

$conn->close();

echo json_encode(array_values($response));
?>