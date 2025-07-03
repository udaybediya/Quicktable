<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "quicktable";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch table statuses
$sql = "SELECT table_no, status FROM table_status";
$result = $conn->query($sql);

$tables = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tables[$row['table_no']] = $row['status'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Table Status Page</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        .table-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            padding: 20px;
            max-width: 600px;
            margin: 0 auto;
        }
        .table-btn {
            margin-left: -250%;
            width: 150px;
            height: 50px;
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 18px;
            cursor: pointer;
            transition: 0.3s;
            justify-self: center;
        }
        .table-btn.unoccupied {
            background-color: #4CAF50; /* Green for unoccupied */
        }
        .table-btn.occupied {
            background-color: #ff0000; /* Red for occupied */
        }
        .table-btn:hover {
            opacity: 0.9;
            transform: scale(1.05);
        }
        .text {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    <div id="sidebar-container"></div>

    <section class="home-section">
        <div class="text">Table Details</div>
        
        <div class="table-container">
            <?php for ($i = 1; $i <= 10; $i++): 
                $tableNo = "Table-$i"; // Correct key
                $status = isset($tables[$tableNo]) ? $tables[$tableNo] : 'unoccupied';
                $buttonText = "Table " . $i;
            ?>
                <button class="table-btn <?php echo $status; ?>" 
                        data-status="<?php echo $status; ?>"
                        id="<?php echo $tableNo; ?>">
                    <?php echo $buttonText; ?>
                </button>
            <?php endfor; ?>
        </div>
    </section>

    <script src="JS/slidebar.js"></script>
    <script>
        // Add click handlers to table buttons
        document.querySelectorAll('.table-btn').forEach(button => {
            button.addEventListener('click', function() {
                const status = this.dataset.status;
                const tableNumber = this.textContent.trim();
                alert(`${tableNumber} is ${status}`);
            });
        });
    </script>
</body>
</html>
