<?php
include 'ConnectToDB.php'
?>

<?php

        $sql = "SELECT
        e.first_name as fs,
        e.last_name as ls,
        MIN(wh.start_date) AS first_day,
        e.role as E_role,
        e.date_of_birth as dob,
        e.email,
        COUNT(DISTINCT i.ID) as infection_count
    FROM
        employee e
    JOIN workHistory wh ON e.ID = wh.employee_ID
    JOIN infection i ON e.ID = i.employee_ID
    WHERE
        e.role IN ('nurse', 'doctor') AND
        wh.end_date IS NULL AND
        i.type = 'COVID-19'
    GROUP BY
        e.ID
    HAVING
        COUNT(DISTINCT i.ID) >= 3
    ORDER BY
        e.role ASC,
        e.first_name ASC,
        e.last_name ASC;";

        $result = $conn->query($sql);
        echo "<h1> Nurses and Doctors infected more than 3 times by COVID 19 </h1>";
        if ($result->num_rows > 0) {
            echo "<table><tr><th>First Name</th><th>Last Name</th><th>First Day of work as Nurse</th><th>Role</th><th>Date of Birth</th><th>Email</th><th>Infection Count</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row["fs"] . "</td><td>" . $row["ls"] . "</td><td>" . $row["first_day"] . "</td><td>" . $row["E_role"] . "</td><td>" . $row["dob"] . "</td><td>" . $row["email"] . "</td><td>" . $row["infection_count"] . "</td><tr>";
            }
            echo "</table>";
        } else {
            echo "0 results";
        }


$conn->close();
?>