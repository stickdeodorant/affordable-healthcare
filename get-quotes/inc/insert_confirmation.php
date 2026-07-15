<?php
require_once __DIR__ . '/../../inc/env.php';

$conn = get_db_connection();

$email      = $_POST['email'];
$ipaddress  = $_POST['ipaddress'];
$error      = $_POST['error'];

$stmt = $conn->prepare("INSERT INTO leads (email, timestamp, ip_address, error) VALUES (?, CURRENT_TIMESTAMP, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo 'Error preparing statement';
    $conn->close();
    exit;
}

$stmt->bind_param("sss", $email, $ipaddress, $error);

if ($stmt->execute()) {
    echo $email." successfully inserted";
} else {
    echo "Error creating table: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>