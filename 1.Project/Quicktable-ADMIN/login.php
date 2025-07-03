<?php
// Process login if form is submitted
session_start();

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

// Handle login request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Check if username and password match in 'employee' table
    $sql = "SELECT * FROM employee WHERE Username = '$user' AND Password = '$pass' AND (position = 'owner' OR position = 'admin')";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $_SESSION['username'] = $user;
        header("Location: appearance.html"); // Redirect to appearance.html after successful login
        exit();
    } else {
        // JavaScript alert for invalid credentials
        echo "<script>alert('Invalid username, password, or position!');</script>";
        
        // Redirect to avoid form resubmission
        echo "<script>window.location.replace('login.php');</script>";
        exit();
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="CSS/style.css">
    <style>

        /* General styles */
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #E4E9F7;
            font-family: Arial, sans-serif;
        }

        /* Container for the form */
        .login-container {
            background-color: #fff;
            padding: 30px;
            width: 350px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        /* Heading styles */
        .login-container h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        /* Input field styles */
        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group label {
            font-size: 16px;
            margin-bottom: 5px;
            display: block;
            color: #555;
        }

        .input-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }

        /* Button styles */
        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: #007bff; /* Blue button */
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-submit:hover {
            background-color: #0056b3;
        }

        /* Optional: Add space for better mobile experience */
        @media (max-width: 480px) {
            .login-container {
                width: 90%;
            }
        }
    </style>
</head>
<body>

    <!-- Login Form Container -->
    <div class="login-container">
        <h2>Login</h2>

        <!-- Login Form -->
        <form action="" method="POST">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-submit">Login</button>
        </form>
    </div>

</body>
</html>

