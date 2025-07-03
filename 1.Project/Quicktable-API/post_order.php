<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

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

// Get POST values
$data = json_decode(file_get_contents('php://input'), true);

// Log received JSON data
error_log("Received JSON: " . json_encode($data));

if (isset($data['orders']) && isset($data['table_no'])) {
    $table_no = $data['table_no']; // FIX: Use table_no as received

    foreach ($data['orders'] as $order) {
        $item_name = isset($order['item_name']) ? trim($order['item_name']) : 'NULL';
        $quantity = isset($order['quantity']) ? intval($order['quantity']) : 0;
        $token_number = isset($order['token_number']) ? $order['token_number'] : 'NULL';

        // Fetch item_price from 'item' table using exact match
        $price_query = "SELECT item_price FROM item WHERE item_name = ? LIMIT 1";
        $stmt_price = $conn->prepare($price_query);
        $stmt_price->bind_param("s", $item_name);
        $stmt_price->execute();
        $stmt_price->bind_result($item_price);
        $stmt_price->fetch();
        $stmt_price->close();

        // If item does not exist, return an error and stop processing
        if ($item_price === null) {
            error_log("Error: Item '$item_name' not found in item table.");
            echo json_encode(['error' => "Item '$item_name' not found"]);
            $conn->close();
            exit;
        }

        // Check if the item already exists in the 'orders' table
        $check_query = "SELECT quantity FROM orders WHERE table_no = ? AND token_number = ? AND item_name = ? LIMIT 1";
        $stmt_check = $conn->prepare($check_query);
        $stmt_check->bind_param("sss", $table_no, $token_number, $item_name);
        $stmt_check->execute();
        $stmt_check->bind_result($existing_quantity);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($existing_quantity !== null) {
            // If item exists, update the quantity and total_price
            $new_quantity = $existing_quantity + $quantity;
            $total_price = $item_price * $new_quantity;

            $update_query = "UPDATE orders SET quantity = ?, total_price = ? WHERE table_no = ? AND token_number = ? AND item_name = ?";
            $stmt_update = $conn->prepare($update_query);
            $stmt_update->bind_param("idsss", $new_quantity, $total_price, $table_no, $token_number, $item_name);

            if (!$stmt_update->execute()) {
                error_log("SQL Error (Update): " . $stmt_update->error);
                echo json_encode(['error' => $stmt_update->error]);
                $stmt_update->close();
                $conn->close();
                exit;
            }
            $stmt_update->close();
        } else {
            // If item does not exist, insert a new row
            $total_price = $item_price * $quantity;

            $insert_query = "INSERT INTO orders (table_no, token_number, item_name, quantity, item_price, total_price) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($insert_query);
            $stmt_insert->bind_param("sssidd", $table_no, $token_number, $item_name, $quantity, $item_price, $total_price);

            if (!$stmt_insert->execute()) {
                error_log("SQL Error (Insert): " . $stmt_insert->error);
                echo json_encode(['error' => $stmt_insert->error]);
                $stmt_insert->close();
                $conn->close();
                exit;
            }
            $stmt_insert->close();
        }

        // Log success
        error_log("Processed: $item_name, Quantity: $quantity, Item Price: $item_price, Total Price: $total_price, Token: $token_number");
    }
    echo json_encode(['success' => true]);
} else {
    error_log("Error: No orders received or table number missing");
    echo json_encode(['error' => 'No orders received or table number missing']);
}

$conn->close();
?>
