
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
$tablename = 'infection';
$query = 'SHOW COLUMNS FROM '. $tablename; 
$column_names = mysqli_query($conn, $query);
echo "<table class =\"table\">";
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


    echo "<td> <button class=\"delete-button\"  type ='submit' name ='delete' value =''> Delete </button> </td>";

    echo "</tr>";
}
echo "</table>";
?>


</body>
</html>
