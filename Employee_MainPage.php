<?php
include 'ConnectToDB.php'
?>

<?php

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

switch ($action) {
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process the submitted form
            $stmt = $conn->prepare("INSERT INTO employee (first_name, last_name, date_of_birth, phone, address, city, province, postal_code, citizenship, email, medicare, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssiissssiis", $first_name, $last_name, $date_of_birth, $phone, $address, $city, $province, $postal_code, $citizenship, $email, $medicare, $role);

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

            if ($stmt->execute()) {
                echo "New employee created successfully";
            } else {
                echo "Error: " . $stmt->error;
            }

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
                    <!-- Your form fields here -->
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
                 <style>
                    body {
                        background-color:#C0C0C0;
                    }
                </style>
            </head>
            <body>
                <div style="text-align:center;">
                <h1>Edit Employee</h1>
                <form action="?action=edit&id=<?php echo $id; ?>" method="post">
                    <!-- Your form fields with fetched data here -->
                    
            <label for="first_name" style="display: inline-block; width: 100px; text-align:left;background-color: powderblue;border-style: solid;">First Name: </label>
            <input type="text" id="first_name" name="first_name" value="<?php echo $employee['first_name']; ?>" style="display: inline-block; width: 200px;" required><br>
                    
      
            <label for="last_name" style="display: inline-block; width: 100px; text-align:left;background-color: powderblue;border-style: solid;">Last Name:</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo $employee['last_name']; ?>" style="display: inline-block; width: 200px; " required><br>

            <label for="date_of_birth" style="display: inline-block; width: 100px; text-align:left;background-color: powderblue;border-style: solid;">Date of Birth:</label>
            <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo $employee['date_of_birth']; ?>" style="display: inline-block; width: 200px;" required><br>

            <label for="email" style="display: inline-block; width: 100px; text-align:left;background-color: powderblue;border-style: solid;">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo $employee['email']; ?>" style="display: inline-block; width: 200px;" required><br>

            <label for="phone" style="display: inline-block; width: 100px; text-align:left;background-color: powderblue;border-style: solid;">Phone:</label>
            <input type="text" id="phone" name="phone" value="<?php echo $employee['phone']; ?>" style="display: inline-block; width: 200px;" required><br>

            <label for="address" style="display: inline-block; width: 100px; text-align: left;background-color: powderblue;border-style: solid;">Address:</label>
            <input type="text" id="address" name="address" value="<?php echo $employee['address']; ?>" style="display: inline-block; width: 200px;" required><br>

            <label for="city" style="display: inline-block; width: 100px; text-align: left;background-color: powderblue;border-style: solid;">City:</label>
            <input type="text" id="city" name="city" value="<?php echo $employee['city']; ?>" style="display: inline-block; width: 200px;" required><br>

            <label for="province" style="display: inline-block; width: 100px; text-align: left;background-color: powderblue;border-style: solid;">Province:</label>
            <input type="text" id="province" name="province" value="<?php echo $employee['province']; ?>" style="display: inline-block; width: 200px;" required><br>

            <label for="postal_code" style="display: inline-block; width: 100px; text-align:left;background-color: powderblue;border-style: solid;">Postal Code:</label>
            <input type="text" id="postal_code" name="postal_code" value="<?php echo $employee['postal_code']; ?>" style="display: inline-block; width: 200px;" required><br>

            <label for="citizenship" style="display: inline-block; width: 100px; text-align:left;background-color: powderblue;border-style: solid;">Citizenship:</label>
            <input type="text" id="citizenship" name="citizenship" value="<?php echo $employee['citizenship']; ?>" style="display: inline-block; width: 200px;" required><br>

            <label for="medicare" style="display: inline-block; width: 100px; text-align: left;background-color: powderblue;border-style: solid;">Medicare:</label>
            <input type="text" id="medicare" name="medicare" value="<?php echo $employee['medicare']; ?>" style="display: inline-block; width: 200px;" required><br>





             <button type="submit" style="background-color: powderblue; color: black; padding: 15px 25px; border: solid; border-radius: 5px; margin-top: 25px;">Update Employee</button>


                    
                </form>
                 </div>
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

        if ($result->num_rows > 0) {
            echo "<table><tr><th>ID</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Medicare</th><th>Role</th><th>Action</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row["ID"] . "</td><td>" . $row["first_name"] . "</td><td>" . $row["last_name"] . "</td><td>" . $row["email"] . "</td><td>" . $row["medicare"] . "</td><td>" . $row["role"] . "</td><td><a href='?action=edit&id=" . $row["ID"] . "'>Edit</a> | <a href='?action=delete&id=" . $row["ID"] . "'>Delete</a></td></tr>";
            }
            echo "</table>";
        } else {
            echo "0 results";
        }
        break;

}

$conn->close();
?>
