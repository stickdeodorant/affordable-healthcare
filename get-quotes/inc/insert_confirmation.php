<?php
require_once __DIR__ . '/../../inc/env.php';

$conn = get_db_connection();

$email      = $_POST['email'];
$ipaddress  = $_POST['ipaddress'];
$error      = $_POST['error'];


$sql = "INSERT INTO leads VALUES (null, '$email', CURRENT_TIMESTAMP, '$ipaddress', '$error')";

if ($conn->query($sql) === TRUE) {
    echo $email." successfully inserted";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>