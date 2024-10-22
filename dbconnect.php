<?php
$host="localhost";
$patientName="root";
$password="";
$dbconnect="kidsbooking";

$conn = new mysqli($host, $patientName, $password, $dbconnect);

if ($conn->connect_error){
    die ("Connection Failed: " . $conn->connect_error);
}
echo"Connection succesfully.";