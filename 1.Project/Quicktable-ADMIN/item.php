<?php
$servername = "localhost";
$username = "root"; // Change if necessary
$password = ""; // Change if necessary
$database = "quicktable";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// Handle AJAX Requests
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetch'])) {
    header("Content-Type: application/json");
    $sql = "SELECT * FROM item ORDER BY sr_no";
    $result = $conn->query($sql);

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    echo json_encode($items);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add') {
        $item_name = $_POST['item_name'];
        $item_price = $_POST['item_price'];
        $item_type = $_POST['item_type'];

        $sql = "INSERT INTO item (item_name, item_price, item_type) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sds", $item_name, $item_price, $item_type);

        if ($stmt->execute()) {
            $new_id = $conn->insert_id;
            echo json_encode(["status" => "success", "message" => "Item added successfully", "sr_no" => $new_id]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to add item"]);
        }

        $stmt->close();
        exit;
    }

    if ($action === 'update') {
        $sr_no = $_POST['sr_no'];
        $item_name = $_POST['item_name'];
        $item_price = $_POST['item_price'];
        $item_type = $_POST['item_type'];

        $sql = "UPDATE item SET item_name=?, item_price=?, item_type=? WHERE sr_no=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdsi", $item_name, $item_price, $item_type, $sr_no);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Item updated successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update item"]);
        }

        $stmt->close();
        exit;
    }

    if ($action === 'delete') {
        $sr_no = $_POST['sr_no'];

        // Step 1: Delete the selected item
        $sql = "DELETE FROM item WHERE sr_no=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $sr_no);

        if ($stmt->execute()) {
            // Step 2: Reorder sr_no after deletion
            $conn->query("SET @new_sr = 0;");
            $conn->query("UPDATE item SET sr_no = (@new_sr := @new_sr + 1) ORDER BY sr_no;");

            echo json_encode(["status" => "success", "message" => "Item deleted and sr_no reordered"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete item"]);
        }

        $stmt->close();
        exit;
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Page</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/item.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div id="sidebar-container"></div>

    <section class="home-section">
        <div class="text">Item Page</div>

        <table class="item-table">
            <thead>
                <tr>
                    <th>Sr. No</th>
                    <th>Item Name</th>
                    <th>Item Price</th>
                    <th>Item Type</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody id="item-table-body">
                <?php
                $conn = new mysqli($servername, $username, $password, $database);
                $sql = "SELECT * FROM item ORDER BY sr_no";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo "<tr data-id='{$row['sr_no']}'>
                        <td>{$row['sr_no']}</td>
                        <td>{$row['item_name']}</td>
                        <td>{$row['item_price']}</td>
                        <td>{$row['item_type']}</td>
                        <td><button class='edit-btn' onclick='editItem({$row['sr_no']})'><i class='bx bx-edit'></i></button></td>
                        <td><button class='delete-btn' onclick='deleteItem({$row['sr_no']}, \"{$row['item_name']}\")'><i class='bx bx-trash'></i></button></td>
                    </tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>

        <div class="button-container">
            <button id="add-new-item-btn" class="add-item-btn">+ Add New Item</button>
        </div>
    </section>

    <script src="JS/slidebar.js"></script>
    <script src="JS/item.js"></script>

</body>
</html>
