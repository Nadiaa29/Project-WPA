<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kidsbooking";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT nurseID, nurseName, roleID FROM nurse WHERE nurseName = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($nurseID, $nurseName, $roleID);
        $stmt->fetch();

        $_SESSION['nurseID'] = $nurseID;
        $_SESSION['nurseName'] = $nurseName;
        $_SESSION['roleID'] = $roleID;

        header("Location: nursemanage.php");
        exit();
    }

    $stmt->close(); 
    $stmt = $conn->prepare("SELECT patientID, patientName, roleID FROM patient WHERE patientName = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($patientID, $patientName, $roleID);
        $stmt->fetch();

        $_SESSION['patientID'] = $patientID;
        $_SESSION['patientName'] = $patientName;
        $_SESSION['roleID'] = $roleID;

        header("Location: patientbooking.php");
        exit();
    } else {
        echo "Invalid username or password. Please sign up first.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="form.css" type="text/css">
    <style> 
        .btn {
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #EEE091;
            color: black;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
        }

        .btn:hover {
            background-color: #D8C576;
        }
    </style>
    <title>Login</title>
</head>
<body>
<form method="POST" action="">
    <label for="username">Username (Patient/Nurse Name):</label>
    <input type="text" id="username" name="username" required>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>

    <button type="submit" class="btn">Login</button>
    <button type="button" class="btn" onclick="location.href='signup.html';">Sign Up</button>
</form>
</body>
</html>