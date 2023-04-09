
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin-left: 0;
            padding: 20px 40px;
            background-color: #f5f7fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        th, td {
            border: 1px solid #e3e8ee;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #e3e8ee;
            color: #3c4b64;
        }

        tr:nth-child(even) {
            background-color: #f5f7fa;
        }

        h1 {
            font-size: 28px;
            color: #3c4b64;
            margin-bottom: 20px;
        }

        .delete-button {
            background-color: #f06c61;
            color: #ffffff;
            border: none;
            border-radius: 5px;
            padding: 8px 12px;
            cursor: pointer;
            font-size: 14px;
        }
    </style>
<body>


<?php
        // Your PHP code with the added classes
        include 'ConnectToDB.php';

        $tablename = 'employee';
        $query = 'SHOW COLUMNS FROM '. $tablename; 
        $column_names = mysqli_query($conn, $query);
        echo "<table class=\"table\">";
        echo "<tr>";

        while($row = mysqli_fetch_assoc($column_names)) {
            echo '<th>' . $row['Field'] . '</th>';
        }

        echo '<th> Delete </th>';

        $query = 'SELECT * FROM '. $tablename;
        $table_data = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($table_data)) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . $value . "</td>";
            }

            echo "<td> <button class=\"delete-button\" type=\"submit\" name=\"delete\" value=\"\"> Delete </button> </td>";
            echo "</tr>";
        }
        echo "</table>";
        // 8. For a given employee, get the details of all the schedules she/he has been scheduled during a specific period of time. Details include facility name, day of the year, start time and end time. Results should be displayed sorted in ascending order by facility name, then by day of the year, the by start time.

// $employee_id = 1; // Replace this with the specific employee ID you want to fetch schedules for
// $start_date = '2020-01-01'; // Replace with the start date of the specific period
// $end_date = '2023-01-31'; // Replace with the end date of the specific period

// $stmt = $conn->prepare("SELECT facility.name AS facility_name, schedule.date AS day_of_year, schedule.start_time, schedule.end_time FROM schedule JOIN facility ON schedule.facility_ID = facility.ID WHERE schedule.employee_ID = ? AND schedule.date BETWEEN ? AND ? ORDER BY facility.name ASC, schedule.date ASC, schedule.start_time ASC");
// $stmt->bind_param("iss", $employee_id, $start_date, $end_date);
// $stmt->execute();
// $result = $stmt->get_result();

// if ($result->num_rows > 0) {
//     echo "<table>";
//     echo "<tr><th>Facility Name</th><th>Day of the Year</th><th>Start Time</th><th>End Time</th></tr>";
//     while ($row = $result->fetch_assoc()) {
//         echo "<tr><td>" . htmlspecialchars($row["facility_name"]) . "</td><td>" . htmlspecialchars($row["day_of_year"]) . "</td><td>" . htmlspecialchars($row["start_time"]) . "</td><td>" . htmlspecialchars($row["end_time"]) . "</td></tr>";
//     }
//     echo "</table>";
// } else {
//     echo "0 results";
// }

// // Close the prepared statement and the connection
// $stmt->close();
?>



</body>
</html>