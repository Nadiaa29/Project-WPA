<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "kidsbooking"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$patientName = $_POST['txtPatientName'];
$password = $_POST['txtPassword'];

if (empty($patientName) || empty($password)) {
    echo "<script>alert('Please enter both Patient Name and Password.'); window.location.href='login.php';</script>";
    exit();
}

$sql = "SELECT * FROM users WHERE patientName = ? AND password = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $patientName, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    header("Location: homepage.php");
    exit();
} else {
    echo "<script>alert('You need to sign up first!'); window.location.href='login.php';</script>";
}

$stmt->close();
$conn->close();
?>
