<?php
session_start();

if (!isset($_SESSION['patientID'])) {
    header("Location: index.php");
    exit();
}

$patientID = $_SESSION['patientID'];  

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kidsbooking";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $diseaseName = $_POST['diseaseID'];
    $checkInDate = $_POST['checkInDate'];
    $dischargeDate = $_POST['dischargeDate'];
    $bookingTime = $_POST['bookingTime'];
    $wardID = $_POST['wardID'];

    $stmt = $conn->prepare("SELECT diseaseID FROM Disease WHERE diseaseName = ?");
    $stmt->bind_param("s", $diseaseName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $diseaseID = $row['diseaseID'];
    } else {
        $stmt = $conn->prepare("INSERT INTO Disease (diseaseName) VALUES (?)");
        $stmt->bind_param("s", $diseaseName);
        $stmt->execute();
        $diseaseID = $stmt->insert_id;
    }

    $stmt = $conn->prepare("INSERT INTO booking (bookingDate, bookingTime, dischargeDate, patientID, diseaseID, wardID) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssissi", $checkInDate, $bookingTime, $dischargeDate, $patientID, $diseaseID, $wardID);
    $stmt->execute();

    $stmt = $conn->prepare("UPDATE Wards SET availableBed = availableBed - 1 WHERE wardID = ? AND availableBed > 0");
    $stmt->bind_param("i", $wardID);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        echo "No available beds.";
    } else {
        echo "Booking successful!";
    }
}

$conn->close();
?>