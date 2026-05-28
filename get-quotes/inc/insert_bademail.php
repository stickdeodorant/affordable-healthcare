<?php
require_once __DIR__ . '/../../inc/env.php';

$conn = get_db_connection();

$email      = $_POST['email'];
$ipaddress  = $_POST['ipaddress'];
$referrer   = $_POST['referrer'];
$city       = $_POST['city'];
$state      = $_POST['state'];
$zip        = $_POST['zip'];


$sql = "INSERT INTO bademail VALUES (null, '$email', CURRENT_TIMESTAMP, '$ipaddress', '$referrer', '$city', '$state', '$zip')";

if ($conn->query($sql) === TRUE) {
    echo $email." successfully inserted";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>