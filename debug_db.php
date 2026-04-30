<?php
include 'Database.php';

$result = $conn->query('SHOW TABLES');
if (!$result) {
    echo 'ERROR: ' . $conn->error;
    exit(1);
}
while ($row = $result->fetch_array()) {
    echo $row[0] . "\n";
}
?>