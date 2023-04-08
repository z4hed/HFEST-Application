<?php
include 'ConnectToDB.php'
?>

<?php

        $sql = "SELECT e.first_name AS first_name, e.last_name AS last_name, i.date_of_infection AS date_of_infection, f.name AS facility_name
        FROM employee e
        JOIN infection i ON e.ID = i.employee_ID
        JOIN workHistory wh ON e.ID = wh.employee_ID
        JOIN facility f ON wh.facility_ID = f.ID
        WHERE e.role = 'doctor'
            AND i.type = 'COVID-19'
            AND i.date_of_infection BETWEEN DATE_SUB(CURDATE(), INTERVAL 2 WEEK) AND CURDATE()
            AND (wh.end_date IS NULL OR wh.end_date > CURDATE())
        ORDER BY f.name ASC, e.first_name ASC;";

        $result = $conn->query($sql);
        echo "<h1> List of Doctors who have been infected by COVID-19 in the past two weeks. </h1>";
        if ($result->num_rows > 0) {
            echo "<table><tr><th>First Name</th><th>Last Name</th><th>Date of Infection</th><th>Facility Name</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row["first_name"] . "</td><td>" . $row["last_name"] . "</td><td>" . $row["date_of_infection"] . "</td><td>" . $row["facility_name"] . "</td><tr>";
            }
            echo "</table>";
        } else {
            echo "0 results";
        }


$conn->close();
?>