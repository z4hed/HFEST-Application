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

    echo "<td> <button type ='submit' name ='delete' value =''> Delete </button> </td>";
    echo "</tr>";
}
echo "</table>";
?>
