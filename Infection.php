
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Infection</title>
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
        
            $sql = "SELECT max(ID) as max FROM infection";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            $id = 1+$row["max"];
        
            $employee_id = $_POST['employee_id'];
            $facility_id = $_POST['facility_id'];
            $type = $_POST['type'];
            $date_of_infection = $_POST['date_of_infection'];
        
            $stmt = $conn->prepare("INSERT INTO infection (ID, employee_id, facility_id, type, date_of_infection) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiiis", $id, $employee_id, $facility_id, $type, $date_of_infection);
        
            if ($stmt->execute()) {
                echo "New infection created successfully";
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
                <title>Create Infection</title>
            </head>
            <body>
                <h1>Create Infection</h1>
        
                <form action="?action=create" method="post">
                    <label for="employee_id">Employee ID:</label>
                    <input type="text" id="employee_id" name="employee_id" value="" required><br>
        
                    <label for="facility_id">Facility ID:</label>
                    <input type="text" id="facility_id" name="facility_id" value="" required><br>
        
                    <label for="type">Type:</label>
                    <input type="text" id="type" name="type" value="" required><br>
        
                    <label for="date_of_infection">Date of Infection:</label>
                    <input type="date" id="date_of_infection" name="date_of_infection" value="" required><br>
        
                    <button type="submit">Create Infection</button>
                </form>
        
               
                       
                            <?php
                        }
                    
                    break;
    case 'edit':
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
        
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // Process the submitted form
        
                $employee_id = $_POST['employee_id'];
                $facility_id = $_POST['facility_id'];
                $type = $_POST['type'];
                $date_of_infection = $_POST['date_of_infection'];
        
                $stmt = $conn->prepare("UPDATE infection SET employee_ID=?, facility_ID=?, type=?, date_of_infection=? WHERE ID=?");
                $stmt->bind_param("iisss", $employee_id, $facility_id, $type, $date_of_infection, $id);
        
                if ($stmt->execute()) {
                    echo "Infection updated successfully";
                } else {
                    echo "Error: " . $stmt->error;
                }
        
                $stmt->close();
            } else {
                // Fetch the infection details
                $stmt = $conn->prepare("SELECT * FROM infection WHERE ID=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $infection = $result->fetch_assoc();
        
                // Display the form with fetched details
                ?>
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Edit Infection</title>
                </head>
                <body>
                    <h1>Edit Infection</h1>
                    <form action="?action=edit&id=<?php echo $id; ?>" method="post">
                        <!-- Your form fields with fetched data here -->
        
                        <label for="employee_id">Employee ID: </label>
                        <input type="number" id="employee_id" name="employee_id" value="<?php echo $infection['employee_ID']; ?>" required><br>
        
                        <label for="facility_id">Facility ID:</label>
                        <input type="number" id="facility_id" name="facility_id" value="<?php echo $infection['facility_ID']; ?>" required><br>
        
                        <label for="type">Type:</label>
                        <input type="text" id="type" name="type" value="<?php echo $infection['type']; ?>" required><br>
        
                        <label for="date_of_infection">Date of Infection:</label>
                        <input type="date" id="date_of_infection" name="date_of_infection" value="<?php echo $infection['date_of_infection']; ?>" required><br>
        
                        <button type="submit">Update Infection</button>
                    </form>
        
                    <form action="?action=delete&id=<?php echo $id; ?>" method="post">
                        <button type="submit">Delete Infection</button>
                    </form>
                </body>
                </html>
                <?php
            }
        } else {
            echo "Infection ID is missing";
        }
        break;
    case 'delete':
        // Delete the selected infection record
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
           

            $sql = "DELETE FROM infection WHERE ID = $id";
            $result = $conn->query($sql);

            if ($result) {
                echo "Infection $id record deleted successfully";
            } else {
                echo "Error deleting infection record: " . $conn->error;
            }
        }
        else {
            echo "ID is missing";
        }
        break;
    case 'list':
    default:
        echo "<a href='?action=create' class='edit-button'>create</a>";

            $tablename = 'infection';
            $query = 'SHOW COLUMNS FROM '. $tablename; 
            $column_names = mysqli_query($conn, $query);
            echo "<table class =\"table\">";
            echo "<tr>";

            while($row = mysqli_fetch_assoc($column_names)) {
                echo '<th>' . $row['Field'] . '</th>';
            }

            echo '<th> Edit </th>';
            echo '<th> Delete </th>';


            $query = 'SELECT * FROM '. $tablename;
            $table_data = mysqli_query($conn, $query);
            while ($row = mysqli_fetch_assoc($table_data)) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . $value . "</td>";
                }

                echo "<td><a href='?action=edit&id=" . $row["ID"] . "' class='edit-button'>Edit</a> </td>";
                echo "<td> <a class=\"delete-button\" type ='submit' name ='delete' href='?action=delete&id=" . $row["ID"] . "' value ='". $row["ID"] ."'> Delete </a> </td>";

                echo "</tr>";
            }
            echo "</table>";
            }


?>


</body>
</html>
