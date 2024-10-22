<?php
session_start();

if (!isset($_SESSION['patientID']) || !isset($_SESSION['patientName'])) {
    header("Location: login.php");
    exit();
}

$patientName = $_SESSION['patientName']; 
$patientID = $_SESSION['patientID'];     

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kidsbooking";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$equipment = [
    2001 => ["TV", "Refrigerator", "Visitor Bed", "Microwave"],  
    2002 => ["TV", "Refrigerator"],                                 
    2003 => ["Refrigerator", "Visitor Bed"],       
    2004 => ["Refrigerator", "Microwave"],                
    2005 => ["TV", "Refrigerator", "Visitor Bed", "Microwave"],  
    2006 => ["Visitor Bed", "Refrigerator", "Microwave"],
    2007 => ["Visitor Bed"]
];

$availability_sql = "SELECT wardID, wardName, totalBed, availableBed FROM wards";
$availability_result = $conn->query($availability_sql);
$wards_sql = "SELECT wardID, wardName, availableBed FROM wards";
$ward_result = $conn->query($wards_sql);

if (!$availability_result) {
    die("Query failed: " . $conn->error);
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
    $stmt->bind_param("sssssi", $checkInDate, $bookingTime, $dischargeDate, $patientID, $diseaseID, $wardID);
    $stmt->execute();

    $stmt = $conn->prepare("UPDATE wards SET availableBed = availableBed - 1 WHERE wardID = ? AND availableBed > 0");
    $stmt->bind_param("i", $wardID);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        echo "No available beds.";
    } else {
        header("Location: patientbooking.php?success=true&wardName=" . urlencode($_POST['wardID']) . "&checkInDate=" . urlencode($checkInDate) . "&dischargeDate=" . urlencode($dischargeDate) . "&bookingTime=" . urlencode($bookingTime) . "&patientName=" . urlencode($patientName));
        exit();
    }

    $availability_result = $conn->query($availability_sql); // Re-fetch availability
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Harmony Health Center - Homepage</title>
    <style> 
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html, body {
    height: 100%;
    font-family: 'Poppins', sans-serif;
}

body {
    background: linear-gradient(to right, #f8f9fd, #e3f0ff); 
    background: url('https://www.pixelstalk.net/wp-content/uploads/2016/04/Spongebob-squarepants-wallpaper-HD-Cartoon.jpg') no-repeat center center fixed;
    background-size: cover;
    color: black;
    display: flex;
    flex-direction: column;
}

header {
    background-color: #e0dd1f; 
    color: #fff;
    padding: 40px;
    text-align: center;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); 
    position: relative;
}

header h1 {
    font-size: 3em;
    margin-bottom: 10px;
    animation: fadeIn 2s;
    color: black; 
}

header h2 {
    font-size: 1.5em;
    color: black;
    margin-bottom: 20px;
}

#p {
    color: black;
}

nav ul {
    list-style: none;
    display: flex;
    justify-content: center;
}

nav ul li {
    margin: 0 15px;
}

nav ul li a {
    color: white;
    text-decoration: none;
    font-weight: bold;
    padding: 10px 20px;
    background: #0326a1; 
    border-radius: 25px;
    transition: background-color 0.3s ease;
}

nav ul li a:hover {
    background-color: #5071e6;
}

#availability {
    padding: 40px;
    text-align: center;
}

.bed-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
}

.ward {
    background-color: white;
    color: black;
    padding: 20px;
    margin: 20px;
    border-radius: 10px;
    width: 200px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
}

.ward h3 {
    margin-bottom: 10px;
}

.bed {
    display: inline-block;
    width: 40px;
    height: 40px;
    margin: 5px;
    border-radius: 5px;
}

.bed.available {
    background-color: #52d156;
}

.bed.not-available {
    background-color: #b0bec5;
}

.legend {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
}

.legend-item .color-box {
    width: 20px;
    height: 20px;
    display: inline-block;
    margin-right: 10px;
}

.legend-item.available .color-box {
    background-color: #52d156;
}

.legend-item.not-available .color-box {
    background-color: #b0bec5;
}

#booking {
    padding: 40px;
    text-align: center;
}

form {
    display: inline-block;
    margin-top: 20px;
}

label {
    display: block;
    margin: 10px 0 5px;
}

input[type="text"],
input[type="date"],
input[type="time"] {
    width: 300px;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
    margin-bottom: 20px;
}

button {
    background-color: #f50f0f;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

button:hover {
    background-color: #d07b19;
}

footer {
    background-color: #e0dd1f;
    color: black;
    text-align: center;
    padding: 20px;
    margin-top: auto;
}

@keyframes fadeIn {
    0% {
        opacity: 0;
        transform: translateY(-20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
</head>
<body>
    <header>
        <h1>Welcome to Harmony Health Center</h1>
        <h2>Your Health, Our Priority</h2>
        <p id="p">Providing the best care for your well-being.</p>
        <br>
        <nav>
            <ul>
                <li><a href="index.php">Log Out</a></li>
            </ul>
        </nav>
    </header>
    <?php
    if (isset($_GET['success']) && $_GET['success'] == 'true') {
        $wardName = urldecode($_GET['wardName']);
        $checkInDate = urldecode($_GET['checkInDate']);
        $dischargeDate = urldecode($_GET['dischargeDate']);
        $bookingTime = urldecode($_GET['bookingTime']);
        $patientName = urldecode($_GET['patientName']);

        echo "<script>
        alert('Booking Successful!\\nPatient Name: $patientName\\nWard: $wardName\\nCheck-In Date: $checkInDate\\nDischarge Date: $dischargeDate\\nBooking Time: $bookingTime');
        </script>";
    }
    ?>
    <section id="availability">
        <h2 id="h2">Bed Availability Status</h2>

        <div class="legend">
            <div class="legend-item available">
                <div class="color-box"></div>
                <p>Available</p>
            </div>
            <div class="legend-item not-available">
                <div class="color-box"></div>
                <p>Not Available</p>
            </div>
        </div>

        <div class="bed-container">
    <?php
    if ($availability_result->num_rows > 0) {
        while ($row = $availability_result->fetch_assoc()) {
            echo "<div class='ward'>";
            echo "<h3>" . $row['wardName'] . "</h3>";

            for ($i = 0; $i < $row['totalBed']; $i++) {
                if ($i < $row['availableBed']) {
                    echo "<div class='bed available'></div>";
                } else {
                    echo "<div class='bed not-available'></div>";
                }
            }

            $wardID = $row['wardID'];
            if (array_key_exists($wardID, $equipment)) {
                echo "<p>Equipment:</p><ul>";
                foreach ($equipment[$wardID] as $item) {
                    echo "<li>" . $item . "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>No equipment information available.</p>";
            }
            echo "</div>";
        }
    } else {
        echo "<p>No bed information available.</p>";
    }            
    ?>
</div>
    </section>

    <section id="booking">
        <h2>Booking Form</h2>
        <form method="POST" action="">
            <label for="diseaseID">Disease Name:</label>
            <input type="text" id="diseaseID" name="diseaseID" placeholder="Enter disease name" required>

            <label for="checkInDate">Check-in Date:</label>
            <input type="date" id="checkInDate" name="checkInDate" required>

            <label for="dischargeDate">Discharge Date:</label>
            <input type="date" id="dischargeDate" name="dischargeDate" required>

            <label for="bookingTime">Booking Time:</label>
            <input type="time" id="bookingTime" name="bookingTime" required>

            <label for="wardID">Ward:</label>
            <select id="wardID" name="wardID" required>
                <?php
                if ($ward_result->num_rows > 0) {
                    while ($row = $ward_result->fetch_assoc()) {
                        echo '<option value="' . $row["wardID"] . '">' . $row["wardName"] . ' (Available Beds: ' . $row["availableBed"] . ')</option>';
                    }
                }
                ?>
            </select>

            <button type="submit">Submit Booking</button>
        </form>
    </section>

    <footer>
        <p>© 2024 Harmony Health Center. All rights reserved.</p>
    </footer>
</body>
</html>