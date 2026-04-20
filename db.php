<?php

// Connect to MySQL server, select database
        $conn = new mysqli('localhost', 'user_name', 'password', 'database_name');
        if ($conn ->connect_error)
               die('Could not connect: ' . $conn->connect_error);
        echo 'sucess';

// Send SQL query
        $query = 'SELECT * FROM CRUISE';
        $result = $conn -> query($query);

// Print results in HTML
        echo "<table>\n";
        while ($line = $result->fetch_assoc()) {
                echo "\t<tr>\n";
                foreach ($line as $col_value) {
                        echo "\t\t<td>$col_value</td>\n";
                }
                echo "\t</tr>\n";
        }
        echo "</table>\n";

//      echo "Number of fields: ".mysql_num_fields($result)."<br>";
//      echo "Number of records: ".mysql_num_rows($result)."<br>";


// Close connection
        $conn->close();
?>