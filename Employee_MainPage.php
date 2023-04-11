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
            $stmt = $conn->prepare("UPDATE employee SET first_name=?, last_name=?, date_of_birth=?, phone=?, address=?, city=?, province=?, postal_code=?, citizenship=?, email=?, medicare=?, role=? WHERE ID=?");
            $stmt->bind_param("sssiissssiisi", $first_name, $last_name, $date_of_birth, $phone, $address, $city, $province, $postal_code, $citizenship, $email, $medicare, $role, $id);

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
                    <button type="submit">Update Employee</button>
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

$conn->close();
?>

</body>
</html>

