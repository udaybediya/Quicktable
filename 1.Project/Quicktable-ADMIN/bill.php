<?php
// Handle AJAX request for bill items
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table_no'], $_POST['token_number'], $_POST['order_date'])) {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "quicktable";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die(json_encode(['error' => 'Connection failed']));
    }

    $table_no = $_POST['table_no'];
    $token_number = $_POST['token_number'];
    $order_date = $_POST['order_date'];

    $stmt = $conn->prepare("SELECT item_name, quantity, item_price, total_price 
                           FROM orders 
                           WHERE table_no = ? 
                           AND token_number = ? 
                           AND DATE(order_date) = ?");
    $stmt->bind_param("sss", $table_no, $token_number, $order_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    echo json_encode($items);
    $stmt->close();
    $conn->close();
    exit();
}

// Handle AJAX request for deleting bills
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_bills') {
    header('Content-Type: application/json');
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "quicktable";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'message' => 'Connection failed']));
    }

    $bills = json_decode($_POST['bills'], true);

    $success = true;
    $message = '';

    foreach ($bills as $bill) {
        $table_no = $bill['table_no'];
        $token_number = $bill['token_number'];
        $order_date = $bill['order_date'];

        $stmt = $conn->prepare("DELETE FROM orders WHERE table_no = ? AND token_number = ? AND DATE(order_date) = ?");
        $stmt->bind_param("sss", $table_no, $token_number, $order_date);
        if (!$stmt->execute()) {
            $success = false;
            $message = 'Error deleting records: ' . $conn->error;
            break;
        }
        $stmt->close();
    }

    $conn->close();

    echo json_encode(['success' => $success, 'message' => $message]);
    exit();
}

// Handle main page request
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "quicktable";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT DISTINCT table_no, token_number, DATE(order_date) as order_date 
        FROM orders 
        WHERE status='finished' AND bill='paid'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Page</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/bill.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div id="sidebar-container"></div>

    <section class="home-section">
        <div class="text">Bill Page</div>
        <button class="action-btn">Select</button>

        <?php if ($result->num_rows > 0) : 
            $counter = 1;
            while($row = $result->fetch_assoc()) : ?>
                <div class="bill-card">
                    <input type="checkbox" class="bill-checkbox">
                    <p><strong><?= $counter ?>.</strong></p>
                    <p><strong>Table No:</strong> <?= $row["table_no"] ?></p>
                    <p><strong>Token View:</strong> <?= $row["token_number"] ?></p>
                    <p><strong>Date:</strong> <?= $row["order_date"] ?></p>
                    <button class="see-bill" 
                            data-table="<?= $row["table_no"] ?>"
                            data-token="<?= $row["token_number"] ?>"
                            data-date="<?= $row["order_date"] ?>">
                        See Bill
                    </button>
                </div>
            <?php 
            $counter++;
            endwhile;
        else : ?>
            <p>No bills found</p>
        <?php endif; ?>
    </section>

    <!-- Bill Popup Modal -->
    <div id="billModal" class="modal">
        <div class="bill-container">
            <span class="close-btn">&times;</span>
            <div class="restaurant-name">
                <h2>Restaurant</h2>
            </div>
            <div class="bill-header">
                <div class="left">
                    <p><strong>Table No:</strong> <span id="modalTableNo"></span></p>
                    <p><strong>Token No:</strong> <span id="modalToken"></span></p>
                </div>
                <div class="right">
                    <p><strong>Bill Date:</strong> <span id="modalDate"></span></p>
                </div>
            </div>
            <hr>
            <table class="bill-table">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total Price</th>
                    </tr>
                </thead>
                <tbody id="billItems">
                </tbody>
                <tfoot>
                    <tr class="grand-total-row">
                        <td></td>
                        <td colspan="2"><strong>Grand Total</strong></td>
                        <td><strong>₹<span id="grandTotal">0</span></strong></td>
                    </tr>
                </tfoot>
            </table>
            <div class="bill-buttons">
                <button id="printBill">Print</button>
                <button id="closeBill">Close</button>
            </div>
        </div>
    </div>

    <script src="JS/slidebar.js"></script>
    <script src="JS/bill.js"></script>
</body>
</html>

<?php
$conn->close();
?>
