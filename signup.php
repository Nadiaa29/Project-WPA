<?php
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "kidsbooking"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $roleID = $_POST['roleID'];
    $password = $_POST['password'];

    if ($roleID == "1") {  
        $patientName = $_POST['patientName'];
        $dateOfBirth = $_POST['dateOfBirth'];
        $address = $_POST['address'];
        $noPhone = $_POST['noPhone'];
        $email = $_POST['email']; 

        $stmt = $conn->prepare("INSERT INTO patient (patientName, password, dateofBirth, address, noPhone, email, roleID) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $patientName, $password, $dateOfBirth, $address, $noPhone, $email, $roleID);

    } elseif ($roleID == "2") {  
        $nurseName = $_POST['nurseName'];
        $department = $_POST['department'];
        $workPhone = $_POST['workPhone'];
        $workEmail = $_POST['workEmail'];

        $stmt = $conn->prepare("INSERT INTO nurse (nurseName, password, department, workPhone, workEmail, roleID) 
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $nurseName, $password, $department, $workPhone, $workEmail, $roleID);
    }

    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>