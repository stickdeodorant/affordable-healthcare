<?php
require_once __DIR__ . '/../../inc/env.php';

$conn = get_db_connection();

$email      = $_POST['email'];
$ipaddress  = $_POST['ipaddress'];
$referrer   = $_POST['referrer'];
$city       = $_POST['city'];
$state      = $_POST['state'];
$zip        = $_POST['zip'];

$stmt = $conn->prepare("INSERT INTO bademail (email, timestamp, ip_address, referrer, city, state, zip) VALUES (?, CURRENT_TIMESTAMP, ?, ?, ?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo 'Error preparing statement';
    $conn->close();
    exit;
}

$stmt->bind_param("ssssss", $email, $ipaddress, $referrer, $city, $state, $zip);

if ($stmt->execute()) {
    echo $email." successfully inserted";
} else {
    echo "Error creating table: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>