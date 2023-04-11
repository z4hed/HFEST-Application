<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>vaccination</title>
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
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process the submitted form

            $sql = "SELECT max(ID) as max FROM vaccination";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            $id = 1 + $row["max"];

            $name = $_POST['name'];
            $type = $_POST['type'];
            $date_of_vaccine = $_POST['date_of_vaccine'];
            $location = $_POST['location'];
            $dose = $_POST['dose'];
            $employee_ID = $_POST['employee_ID'];
            $facility_ID = $_POST['facility_ID'];

            $stmt = $conn->prepare("INSERT INTO vaccination (ID, name, type, date_of_vaccine, location, dose, employee_ID, facility_ID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssiii", $id, $name, $type, $date_of_vaccine, $location, $dose, $employee_ID, $facility_ID);

            if ($stmt->execute()) {
                echo "New vaccination record created successfully";
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
                <title>Create Vaccination Record</title>
            </head>
            <body>
                <h1>Create Vaccination Record</h1>
                <form action="?action=create" method="post">
                    <label for="name">Name: </label>
                    <input type="text" id="name" name="name" required><br>

                    <label for="type">Type:</label>
                    <input type="text" id="type" name="type" required><br>

                    <label for="date_of_vaccine">Date of Vaccine:</label>
                    <input type="date" id="date_of_vaccine" name="date_of_vaccine" required><br>

                    <label for="location">Location:</label>
                    <input type="text" id="location" name="location" required><br>

                    <label for="dose">Dose:</label>
                    <input type="number" id="dose" name="dose" required><br>

                    <label for="employee_ID">Employee ID:</label>
                    <input type="number" id="employee_ID" name="employee_ID" required><br>

                    <label for="facility_ID">Facility ID:</label>
                    <input type="number" id="facility_ID" name="facility_ID" required><br>

                    <button type="submit">Create Vaccination Record</button>
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
        
                $name = $_POST['name'];
                $type = $_POST['type'];
                $date_of_vaccine = $_POST['date_of_vaccine'];
                $location = $_POST['location'];
                $dose = $_POST['dose'];
                $employee_id = $_POST['employee_id'];
                $facility_id = $_POST['facility_id'];
        
                $stmt = $conn->prepare("UPDATE vaccination SET name=?, type=?, date_of_vaccine=?, location=?, dose=?, employee_id=?, facility_id=? WHERE ID=?");
                $stmt->bind_param("ssssiisi", $name, $type, $date_of_vaccine, $location, $dose, $employee_id, $facility_id, $id);
        
                if ($stmt->execute()) {
                    echo "Vaccination updated successfully";
                } else {
                    echo "Error: " . $stmt->error;
                }
        
                $stmt->close();
            } else {
                // Fetch the vaccination details
                $stmt = $conn->prepare("SELECT * FROM vaccination WHERE ID=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $vaccination = $result->fetch_assoc();
        
                // Display the form with fetched details
                ?>
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Edit Vaccination</title>
                </head>
                <body>
                    <h1>Edit Vaccination</h1>
                    <form action="?action=edit&id=<?php echo $id; ?>" method="post">
                        <!-- Your form fields with fetched data here -->
        
                        <label for="name">Name: </label>
                        <input type="text" id="name" name="name" value="<?php echo $vaccination['name']; ?>" required><br>
        
                        <label for="type">Type:</label>
                        <input type="text" id="type" name="type" value="<?php echo $vaccination['type']; ?>" required><br>
        
                        <label for="date_of_vaccine">Date of Vaccine:</label>
                        <input type="date" id="date_of_vaccine" name="date_of_vaccine" value="<?php echo $vaccination['date_of_vaccine']; ?>" required><br>
        
                        <label for="location">Location:</label>
                        <input type="text" id="location" name="location" value="<?php echo $vaccination['location']; ?>" required><br>
        
                        <label for="dose">Dose:</label>
                        <input type="number" id="dose" name="dose" value="<?php echo $vaccination['dose']; ?>" required><br>
        
                        <label for="employee_id">Employee ID:</label>
                        <input type="number" id="employee_id" name="employee_id" value="<?php echo $vaccination['employee_ID']; ?>"  readonly required><br>
        
                        <label for="facility_id">Facility ID:</label>
                        <input type="number" id="facility_id" name="facility_id" value="<?php echo $vaccination['facility_ID']; ?>" readonly required><br>
        
                        <button type="submit">Update Vaccination</button>
                    </form>
                    <?php
            }
        }
        else{
            echo "ID not found";
        }

        break;
    case 'delete':
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
        
            // Delete the vaccination record
            $stmt = $conn->prepare("DELETE FROM vaccination WHERE ID=?");
            $stmt->bind_param("i", $id);
        
            if ($stmt->execute()) {
                echo "Vaccination record deleted successfully";
            } else {
                echo "Error: " . $stmt->error;
            }
        
            $stmt->close();
        } else {
            echo "Vaccination ID is missing";
        }
        break;
    case 'list':
    default:
        $tablename = 'vaccination';
        $query = 'SHOW COLUMNS FROM '. $tablename;
        $column_names = mysqli_query($conn, $query);
        
        echo "<a href='?action=create' class='edit-button'>Create</a>";
        
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
        
            echo "<td><a href='?action=edit&id=" . $row["ID"] . "' class='edit-button'>Edit</a></td>";
            echo "<td><a href='?action=delete&id=" . $row["ID"] . "' class='delete-button'>Delete</a></td>";
        
            echo "</tr>";
        }
        
        echo "</table>";
        break;
}



?>
</body>
</html>

