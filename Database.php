<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "jobportal_db"; //  actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);  // acts like a continue if this part of code block executes, it will stop the rest of the code.
} else {

    echo "Connection successful! <br>"; 
}
?>