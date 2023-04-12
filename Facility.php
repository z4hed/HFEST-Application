
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facility</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.html'; ?>
    <?php

include 'ConnectToDB.php'
?>

<?php

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

switch ($action) {
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process the submitted form

            $sql = "SELECT max(ID) as max FROM facility";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            $id = 1+$row["max"];

            
            
            $name = $_POST['name'];
            $address = $_POST['address'];
            $city = $_POST['city'];
            $province = $_POST['province'];
            $postal_code = $_POST['postal_code'];
            $phone = $_POST['phone'];
            $web_address = $_POST['web_address'];
            $type = $_POST['type'];
            $capacity = $_POST['capacity'];
            $manager = $_POST['manager'];
            $current_employee_count = $_POST['current_employee_count'];

            $stmt = $conn->prepare("INSERT INTO employee (id,name, address, city, province, postal_code, phone, web_address, type, capacity, manager, current_employee_count) VALUES (?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssissisi",$id,$name, $address, $city, $province, $postal_code, $phone, $web_address, $type, $capacity, $manager, $current_employee_count);


            if ($stmt->execute()) {
                echo "New employee created successfully";
            } else {
                echo "Error: " . $stmt->error;
            }


            echo "ID after incrementing: " . $id;


            $stmt->close();
        } else {
            // Display the form
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <title>Create Facility</title>
            </head>
            <body>
                <h1>Create Facility</h1>
                
                <!-- Your form fields with fetched data here -->
                    
                <form action="?action=create" method="post">
                    <label for="name">Name: </label>
                    <input type="text" id="name" name="name" value="<?php echo $employee['name']; ?>" required><br>

                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" value="<?php echo $employee['address']; ?>" required><br>

                    <label for="city">City:</label>
                    <input type="text" id="city" name="city" value="<?php echo $employee['city']; ?>" required><br>

                    <label for="province">Province:</label>
                    <input type="text" id="province" name="province" value="<?php echo $employee['province']; ?>" required><br>

                    <label for="postal_code">Postal Code:</label>
                    <input type="text" id="postal_code" name="postal_code" value="<?php echo $employee['postal_code']; ?>" required><br>

                    <label for="phone">Phone:</label>
                    <input type="text" id="phone" name="phone" value="<?php echo $employee['phone']; ?>" required><br>

                    <label for="web_address">Web Address:</label>
                    <input type="text" id="web_address" name="web_address" value="<?php echo $employee['web_address']; ?>" required><br>

                    <label for="type">Type:</label>
                    <input type="text" id="type" name="type" value="<?php echo $employee['type']; ?>" required><br>

                    <label for="capacity">Capacity:</label>
                    <input type="text" id="capacity" name="capacity" value="<?php echo $employee['capacity']; ?>" required><br>

                    <label for="manager">Manager:</label>
                    <input type="text" id="manager" name="manager" value="<?php echo $employee['manager']; ?>" required><br>

                    <label for="current_employee_count">Current Employee Count:</label>
                    <input type="text" id="current_employee_count" name="current_employee_count" value="<?php echo $employee['current_employee_count']; ?>" required><br>

                    <button type="submit">Create Facility</button>
                </form>
                        


                   
            </body>
            </html>
            <?php
        }
        break;

    case 'edit':
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process the submitted form

            $first_name = $_POST['first_name'];
            $last_name = $_POST['last_name'];
            $date_of_birth = $_POST['date_of_birth'];
            $phone = $_POST['phone'];
            $address = $_POST['address'];
            $city = $_POST['city'];
            $province = $_POST['province'];
            $postal_code = $_POST['postal_code'];
            $citizenship = $_POST['citizenship'];
            $email = $_POST['email'];
            $medicare = $_POST['medicare'];
            $role = $_POST['role'];
            
            $stmt = $conn->prepare("UPDATE employee SET first_name=?, last_name=?, date_of_birth=?, phone=?, address=?, city=?, province=?, postal_code=?, citizenship=?, email=?, medicare=?, role=? WHERE ID=?");
            $stmt->bind_param("sssiisssssisi", $first_name, $last_name, $date_of_birth, $phone, $address, $city, $province, $postal_code, $citizenship, $email, $medicare, $role, $id);


            if ($stmt->execute()) {
                echo "Facility updated successfully";
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            // Fetch the facility details
            $stmt = $conn->prepare("SELECT * FROM facility WHERE ID=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $facility = $result->fetch_assoc();

            // Display the form with fetched details
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <title>Edit Facility</title>
            </head>
            <body>
                <h1>Edit Facility</h1>
                <form action="?action=edit&id=<?php echo $id; ?>" method="post">
                    <!-- Your form fields with fetched data here -->

                    <label for="name">Name: </label>
                    <input type="text" id="name" name="name" value="<?php echo $facility['name']; ?>" required><br>

                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" value="<?php echo $facility['address']; ?>" required><br>

                    <label for="city">City:</label>
                    <input type="text" id="city" name="city" value="<?php echo $facility['city']; ?>" required><br>

                    <label for="province">Province:</label>
                    <input type="text" id="province" name="province" value="<?php echo $facility['province']; ?>" required><br>

                    <label for="postal_code">Postal Code:</label>
                    <input type="text" id="postal_code" name="postal_code" value="<?php echo $facility['postal_code']; ?>" required><br>

                    <label for="phone">Phone:</label>
                    <input type="text" id="phone" name="phone" value="<?php echo $facility['phone']; ?>" required><br>

                    <label for="web_address">Web Address:</label>
                    <input type="text" id="web_address" name="web_address" value="<?php echo $facility['web_address']; ?>" required><br>

                    <label for="type">Type:</label>
                    <input type="text" id="type" name="type" value="<?php echo $facility['type']; ?>" required><br>

                    <label for="capacity">Capacity:</label>
                    <input type="text" id="capacity" name="capacity" value="<?php echo $facility['capacity']; ?>" required><br>

                    <label for="manager">Manager:</label>
                    <input type="text" id="manager" name="manager" value="<?php echo $facility['manager']; ?>" required><br>

                    <label for="current_employee_count">Current Employee Count:</label>
                    <input type="text" id="current_employee_count" name="current_employee_count" value="<?php echo $facility['current_employee_count']; ?>" required><br>

                    <button type="submit">Create Facility</button>
                </form>
                        
            </body>
            </html>
            <?php
        }
    } else {
        echo "Employee ID is missing";
    }
    break;

    case 'delete':
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);

        // Delete the Facility record

        $stmt = $conn->prepare("DELETE FROM facility WHERE ID=?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "Employee deleted successfully";
        } else {
            echo "but Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Employee ID is missing";
    }
    break;

    case 'list':
    default:
    
    $tablename = 'facility';
    $query = 'SHOW COLUMNS FROM '. $tablename; 
    
    echo "<a href='?action=create' class='edit-button'>create</a>";

    $column_names = mysqli_query($conn, $query);
    echo "<table class =\"table\">";
    echo "<tr>";
    
    
    while($row = mysqli_fetch_assoc($column_names)) {
        echo '<th>' . $row['Field'] . '</th>';
    }
    
    echo '<th> Edit </th>';
    echo '<th> Delete</th>';
    
     $query = 'SELECT * FROM '. $tablename;
     $table_data = mysqli_query($conn, $query);
     while ($row = mysqli_fetch_assoc($table_data)) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . $value . "</td>";
        }
    
    
        echo "<td><a href='?action=edit&id=" . $row["ID"] . "' class='edit-button'>Edit</a> </td><td> <a href='?action=delete&id=" . $row["ID"] . "' class='delete-button'>Delete</a></td></tr>";
    
        echo "</tr>";
    }
    echo "</table>";

}
//=======================================================


// 7. Get details of all the employees currently working in a specific facility. Details include employee’s first-name, last-name, start date of work, date of birth, Medicare card number, telephone-number, address, city, province, postal-code, citizenship, and email address. Results should be displayed sorted in ascending order by role, then by first name, then by last name.

// $facility_id = 1; // Replace this with the specific facility ID you want to fetch employees for

// $stmt = $conn->prepare("SELECT employee.*, workHistory.start_date FROM employee JOIN workHistory ON employee.ID = workHistory.employee_ID WHERE workHistory.facility_ID = ? AND workHistory.end_date IS NULL ORDER BY employee.role ASC, employee.first_name ASC, employee.last_name ASC");
// $stmt->bind_param("i", $facility_id);
// $stmt->execute();
// $result = $stmt->get_result();

// if ($result->num_rows > 0) {
//     echo "<table>";
//     echo "<tr><th>First Name</th><th>Last Name</th><th>Start Date</th><th>Date of Birth</th><th>Medicare</th><th>Phone</th><th>Address</th><th>City</th><th>Province</th><th>Postal Code</th><th>Citizenship</th><th>Email</th></tr>";
//     while ($row = $result->fetch_assoc()) {
//         echo "<tr><td>" . htmlspecialchars($row["first_name"]) . "</td><td>" . htmlspecialchars($row["last_name"]) . "</td><td>" . htmlspecialchars($row["start_date"]) . "</td><td>" . htmlspecialchars($row["date_of_birth"]) . "</td><td>" . htmlspecialchars($row["medicare"]) . "</td><td>" . htmlspecialchars($row["phone"]) . "</td><td>" . htmlspecialchars($row["address"]) . "</td><td>" . htmlspecialchars($row["city"]) . "</td><td>" . htmlspecialchars($row["province"]) . "</td><td>" . htmlspecialchars($row["postal_code"]) . "</td><td>" . htmlspecialchars($row["citizenship"]) . "</td><td>" . htmlspecialchars($row["email"]) . "</td></tr>";
//     }
//     echo "</table>";
// } else {
//     echo "0 results";
// }

// 10. List the emails generated by a given facility. The results should be displayed in ascending order by the date of the emails.

// Replace this with the actual facility ID you want to query for
// $facility_id = 1;

// // Prepare and execute the query
// $stmt = $conn->prepare("SELECT employee.email, workHistory.start_date FROM employee INNER JOIN workHistory ON employee.ID = workHistory.employee_id WHERE workHistory.facility_id = ? AND workHistory.end_date IS NULL ORDER BY workHistory.start_date ASC");
// $stmt->bind_param("i", $facility_id);
// $stmt->execute();
// $result = $stmt->get_result();

// // Display the emails
// if ($result->num_rows > 0) {
//     echo "<table><tr><th>Email</th><th>Start Date</th></tr>";
//     while ($row = $result->fetch_assoc()) {
//         echo "<tr><td>" . $row["email"] . "</td><td>" . $row["start_date"] . "</td></tr>";
//     }
//     echo "</table>";
// } else {
//     echo "No emails found for the given facility.";
// }

// $stmt->close();
// $conn->close();

// 11. For a given facility, generate a list of all the doctors and nurses who have been on schedule to work in the last two weeks. The list should include first-name, last-name, and role. Results should be displayed in ascending order by role, then by first name.

// // Replace this with the actual facility ID you want to query for
// $facility_id = 1;

// // Calculate the start date (two weeks ago from today)
// $two_weeks_ago = date('Y-m-d', strtotime('-2 weeks'));

// // Prepare and execute the query
// $stmt = $conn->prepare("SELECT employee.first_name, employee.last_name, employee.role FROM employee INNER JOIN workHistory ON employee.ID = workHistory.employee_id WHERE workHistory.facility_id = ? AND workHistory.start_date <= ? AND (workHistory.end_date >= ? OR workHistory.end_date IS NULL) AND (employee.role = 'Doctor' OR employee.role = 'Nurse') ORDER BY employee.role ASC, employee.first_name ASC");
// $stmt->bind_param("iss", $facility_id, $two_weeks_ago, $two_weeks_ago);
// $stmt->execute();
// $result = $stmt->get_result();

// // Display the result
// if ($result->num_rows > 0) {
//     echo "<table><tr><th>First Name</th><th>Last Name</th><th>Role</th></tr>";
//     while ($row = $result->fetch_assoc()) {
//         echo "<tr><td>" . $row["first_name"] . "</td><td>" . $row["last_name"] . "</td><td>" . $row["role"] . "</td></tr>";
//     }
//     echo "</table>";
// } else {
//     echo "No doctors or nurses found for the given facility within the last two weeks.";
// }

// $stmt->close();
// $conn->close();

// 12. For a given facility, give the total hours scheduled for every role during a specific period. Results should be displayed in ascending order by role.

// Replace these with the actual facility ID, start_date, and end_date you want to query for
// $facility_id = 1;
// $start_date = '2023-02-01';
// $end_date = '2023-02-28';

// // Prepare and execute the query
// $stmt = $conn->prepare("SELECT employee.role, SUM(TIMESTAMPDIFF(HOUR, schedule.start_time, schedule.end_time)) as total_hours FROM employee INNER JOIN schedule ON employee.ID = schedule.employee_id INNER JOIN workHistory ON employee.ID = workHistory.employee_id AND workHistory.facility_id = schedule.facility_id WHERE schedule.facility_id = ? AND schedule.date >= ? AND schedule.date <= ? AND workHistory.facility_id = ? GROUP BY employee.role ORDER BY employee.role ASC");
// $stmt->bind_param("isss", $facility_id, $start_date, $end_date, $facility_id);
// $stmt->execute();
// $result = $stmt->get_result();

// // Display the result
// if ($result->num_rows > 0) {
//     echo "<table><tr><th>Role</th><th>Total Hours</th></tr>";
//     while ($row = $result->fetch_assoc()) {
//         echo "<tr><td>" . $row["role"] . "</td><td>" . $row["total_hours"] . "</td></tr>";
//     }
//     echo "</table>";
// } else {
//     echo "No data found for the given facility and period.";
// }

// $stmt->close();
// $conn->close();


// 13. For every facility, provide the province where the facility is located, the facility name, the capacity of the facility, and the total number of employees in the facility who have been infected by COVID-19 in the past two weeks. The results should be displayed in ascending order by province, then by the total number of employees infected.


// Calculate the date two weeks ago
// $two_weeks_ago = date('Y-m-d', strtotime('-2 weeks'));

// // Prepare and execute the query
// $stmt = $conn->prepare("SELECT facility.province, facility.name, facility.capacity, COUNT(employee.id) as infected_count FROM facility LEFT JOIN employee ON facility.id = employee.facility_id AND employee.infection_date >= ? GROUP BY facility.id ORDER BY facility.province ASC, infected_count ASC");
// $stmt->bind_param("s", $two_weeks_ago);
// $stmt->execute();
// $result = $stmt->get_result();

// // Display the result
// if ($result->num_rows > 0) {
//     echo "<table><tr><th>Province</th><th>Facility Name</th><th>Capacity</th><th>Infected Employees</th></tr>";
//     while ($row = $result->fetch_assoc()) {
//         echo "<tr><td>" . $row["province"] . "</td><td>" . $row["name"] . "</td><td>" . $row["capacity"] . "</td><td>" . $row["infected_count"] . "</td></tr>";
//     }
//     echo "</table>";
// } else {
//     echo "No data found.";
// }

// $stmt->close();
// $conn->close();

?>

</body>
</html>



