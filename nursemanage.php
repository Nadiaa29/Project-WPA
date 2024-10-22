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
    $wardID = $_POST['wardID'];
    $availableBed = $_POST['availableBed'];

    $stmt = $conn->prepare("UPDATE wards SET availableBed = ? WHERE wardID = ?");
    $stmt->bind_param("ii", $availableBed, $wardID);

    if ($stmt->execute()) {
        echo "Ward availability updated successfully.";
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $stmt->close();
}

$sql = "SELECT wardID, wardName, totalBed, availableBed FROM wards";
$result = $conn->query($sql);
$sqlBookings = "
    SELECT p.patientName, b.bookingDate, b.bookingTime, w.wardName
    FROM patient p
    INNER JOIN booking b ON p.patientID = b.patientID
    INNER JOIN wards w ON b.wardID = w.wardID
";
$resultBookings = $conn->query($sqlBookings);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Ward Availability</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #698ddb; 
            color: black;
            padding: 20px;
            text-align: center;
        }

        header h1 {
            margin: 0;
            font-size: 2em;
            animation: fadeIn 2s;
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
            background:#3266d9;
            border-radius: 25px;
            transition: background-color 0.3s ease;
        }

        nav ul li a:hover {
            background-color: #4572d6;
        }

        #manage-wards {
            margin: 50px auto;
            width: 50%;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        #manage-wards h2 {
            text-align: center;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-top: 10px;
            font-weight: bold;
        }

        select, input[type="number"] {
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            padding: 10px;
            margin-top: 15px;
            border: none;
            background-color: #3266d9; 
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #4978de; 
        }

        #manage-wards {
            margin: 50px auto;
            width: 50%;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 100px; /* Add this to give space between the container and footer */
        }

        footer {
            text-align: center;
            padding: 20px;
            background-color: #698ddb; 
            color: black;
            position: fixed;
            bottom: 0;
            width: 100%;
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
        <h1>Ward Availability Management</h1>
        <nav>
            <ul>
                <li><a href="index.php">Log Out</a></li>
            </ul>
        </nav>
    </header>

    <section id="booking-list">
    <h2>Logged-in Users and Their Bookings</h2>
    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; margin-top: 20px;">
        <thead>
            <tr>
                <th>Patient Name</th>
                <th>Booking Date</th>
                <th>Booking Time</th>
                <th>Ward Name</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($resultBookings->num_rows > 0) {
                while ($row = $resultBookings->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['patientName'] . "</td>";
                    echo "<td>" . $row['bookingDate'] . "</td>";
                    echo "<td>" . $row['bookingTime'] . "</td>";
                    echo "<td>" . $row['wardName'] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No bookings found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</section>


    <section id="manage-wards">
        <h2>Update Ward Availability</h2>

        <form method="POST" action="">
            <label for="wardID">Select Ward:</label>
            <select id="wardID" name="wardID" required onchange="updateAvailableBeds()">
                <option value="">Select Ward</option>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $wardID = $row['wardID'];
                        $wardName = $row['wardName'];
                        $availableBed = $row['availableBed'];
                        $totalBed = $row['totalBed'];
                        echo "<option value='$wardID' data-available='$availableBed'>$wardName (Total: $totalBed, Available: $availableBed)</option>";
                    }
                } else {
                    echo "<option value=''>No wards available</option>";
                }
                ?>
            </select>

            <label for="availableBed">Available Beds:</label>
            <input type="number" id="availableBed" name="availableBed" min="0" required>

            <button type="submit">Update</button>
        </form>
    </section>

    <script>
        function updateAvailableBeds() {
            const selectElement = document.getElementById("wardID");
            const availableBedInput = document.getElementById("availableBed");
            const selectedOption = selectElement.options[selectElement.selectedIndex];

            const availableBeds = selectedOption.getAttribute("data-available");
            availableBedInput.value = availableBeds; 
        }
    </script>
     <footer>
        <p>&copy; 2024 Harmony Health Center. All rights reserved.</p>
    </footer>
</body>
</html>