<?php
include 'ConnectToDB.php'
?>

<?php

        $sql = "WITH nurse_schedule AS (
            SELECT e.ID, e.first_name, e.last_name, e.date_of_birth, e.email, 
                   MIN(wh.start_date) AS first_day_of_work_as_nurse, 
                   SUM(TIMESTAMPDIFF(HOUR, s.start_time, s.end_time)) AS total_hours_scheduled
            FROM employee e
            JOIN workHistory wh ON e.ID = wh.employee_ID AND e.role = 'nurse'
            JOIN schedule s ON e.ID = s.employee_ID
            GROUP BY e.ID
        ),
        max_hours AS (
            SELECT MAX(total_hours_scheduled) AS max_hours_scheduled
            FROM nurse_schedule
        )
        SELECT ns.first_name AS fs, ns.last_name AS ls, ns.first_day_of_work_as_nurse AS first_day, ns.date_of_birth AS DOB, ns.email AS email, ns.total_hours_scheduled as THS
        FROM nurse_schedule ns
        JOIN max_hours mh ON ns.total_hours_scheduled = mh.max_hours_scheduled;";

        $result = $conn->query($sql);
        echo "<h1> List of Nurses who have the highest number of hours scheduled since they started working. </h1>";
        if ($result->num_rows > 0) {
            echo "<table><tr><th>First Name</th><th>Last Name</th><th>First Day of work as Nurse</th><th>DOB</th><th>Email</th><th>Total hours Scheduled</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row["fs"] . "</td><td>" . $row["ls"] . "</td><td>" . $row["first_day"] . "</td><td>" . $row["DOB"] . "</td><td>" . $row["email"] . "</td><td>" . $row["THS"] . "</td><tr>";
            }
            echo "</table>";
        } else {
            echo "0 results";
        }


$conn->close();
?>