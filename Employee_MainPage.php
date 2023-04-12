
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee</title>
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

            $sql = "SELECT max(ID) as max FROM employee";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            $id = 1+$row["max"];

            
            
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


            $stmt = $conn->prepare("INSERT INTO employee (ID, first_name, last_name, date_of_birth, phone, address, city, province, postal_code, citizenship, email, medicare, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssiisssssis",$id, $first_name, $last_name, $date_of_birth, $phone, $address, $city, $province, $postal_code, $citizenship, $email, $medicare, $role);


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
                <title>Create Employee</title>
            </head>
            <body>
                <h1>Create Employee</h1>
                <form action="?action=create" method="post">

                    <!-- Your form fields with fetched data here -->
                    
                        <label for="first_name">First Name: </label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo $employee['first_name']; ?>" required><br>

                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo $employee['last_name']; ?>" required><br>

                        <label for="date_of_birth">Date of Birth:</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo $employee['date_of_birth']; ?>" required><br>

                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo $employee['email']; ?>" required><br>

                        <label for="phone">Phone:</label>
                        <input type="text" id="phone" name="phone" value="<?php echo $employee['phone']; ?>" required><br>

                        <label for="address">Address:</label>
                        <input type="text" id="address" name="address" value="<?php echo $employee['address']; ?>" required><br>

                        <label for="city">City:</label>
                        <input type="text" id="city" name="city" value="<?php echo $employee['city']; ?>" required><br>

                        <label for="province">Province:</label>
                        <input type="text" id="province" name="province" value="<?php echo $employee['province']; ?>" required><br>

                        <label for="postal_code">Postal Code:</label>
                        <input type="text" id="postal_code" name="postal_code" value="<?php echo $employee['postal_code']; ?>" required><br>

                        <label for="citizenship">Citizenship:</label>
                        <input type="text" id="citizenship" name="citizenship" value="<?php echo $employee['citizenship']; ?>" required><br>

                        <label for="medicare">Medicare:</label>
                        <input type="text" id="medicare" name="medicare" value="<?php echo $employee['medicare']; ?>" required><br>

                        <label for="role">Role:</label>
                        <input type="text" id="role" name="role" value="<?php echo $employee['role']; ?>" required><br>

                        


                    <button type="submit">Create Employee</button>

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
                echo "Employee updated successfully";
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            // Fetch the employee details
            $stmt = $conn->prepare("SELECT * FROM employee WHERE ID=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $employee = $result->fetch_assoc();

            // Display the form with fetched details
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <title>Edit Employee</title>
            </head>
            <body>
                <h1>Edit Employee</h1>
                <form action="?action=edit&id=<?php echo $id; ?>" method="post">
                    <!-- Your form fields with fetched data here -->

                    
                        <label for="first_name">First Name: </label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo $employee['first_name']; ?>" required><br>

                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo $employee['last_name']; ?>" required><br>

                        <label for="date_of_birth">Date of Birth:</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo $employee['date_of_birth']; ?>" required><br>

                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo $employee['email']; ?>" required><br>

                        <label for="phone">Phone:</label>
                        <input type="text" id="phone" name="phone" value="<?php echo $employee['phone']; ?>" required><br>

                        <label for="address">Address:</label>
                        <input type="text" id="address" name="address" value="<?php echo $employee['address']; ?>" required><br>

                        <label for="city">City:</label>
                        <input type="text" id="city" name="city" value="<?php echo $employee['city']; ?>" required><br>

                        <label for="province">Province:</label>
                        <input type="text" id="province" name="province" value="<?php echo $employee['province']; ?>" required><br>

                        <label for="postal_code">Postal Code:</label>
                        <input type="text" id="postal_code" name="postal_code" value="<?php echo $employee['postal_code']; ?>" required><br>

                        <label for="citizenship">Citizenship:</label>
                        <input type="text" id="citizenship" name="citizenship" value="<?php echo $employee['citizenship']; ?>" required><br>

                        <label for="medicare">Medicare:</label>
                        <input type="text" id="medicare" name="medicare" value="<?php echo $employee['medicare']; ?>" required><br>

                        <label for="role">Role:</label>
                        <input type="text" id="role" name="role" value="<?php echo $employee['role']; ?>" required><br>

                        

                    <button type="submit">Edit Employee</button>
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

        // Delete the employee record

        $sql = "DELETE FROM infection WHERE employee_ID = $id";
        $result = $conn->query($sql);
        $sql = "DELETE FROM vaccination WHERE employee_ID = $id";
        $result = $conn->query($sql);
        $sql = "DELETE FROM workHistory WHERE employee_ID = $id";
        $result = $conn->query($sql);

        $stmt = $conn->prepare("DELETE FROM employee WHERE ID=?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "Employee deleted successfully";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Employee ID is missing";
    }
    break;

    case 'list':
    default:
        $sql = "SELECT * FROM employee";
        $result = $conn->query($sql);

        echo "<a href='?action=create' class='edit-button'>Create</a>";


        if ($result->num_rows > 0) {
            echo "<table class=\"table\"><tr><th>ID</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Role</th><th>Action</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row["ID"] . "</td><td>" . $row["first_name"] . "</td><td>" . $row["last_name"] . "</td><td>" . $row["email"] . "</td><td>" . $row["role"] . "</td><td><a href='?action=edit&id=" . $row["ID"] . "' class='edit-button'>Edit</a>  <a href='?action=delete&id=" . $row["ID"] . "' class='delete-button'>Delete</a></td></tr>";
            }
            echo "</table>";
        } else {
            echo "0 results";
        }
        break;

}

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

$conn->close();
?>


</body>
</html>


