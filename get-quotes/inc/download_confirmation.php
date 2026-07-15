<?php
require_once __DIR__ . '/../../inc/env.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	exit('Method not allowed');
}

$exportType = $_POST['export_type'] ?? '';
$allowed = ['leads', 'bademail'];
if (!in_array($exportType, $allowed, true)) {
	http_response_code(400);
	exit('Invalid export type');
}

$conn = get_db_connection();
$today = date('d-m-Y');
$filename = 'healthcare-ins-' . $exportType . '-' . $today . '.csv';

header('Content-Description: File Transfer');
header('Content-Disposition: attachment; filename=' . $filename);
header('Content-Type: text/csv; charset=UTF-8');

$output = fopen('php://output', 'w');

if ($exportType === 'leads') {
	fputcsv($output, ['ID', 'Email', 'IP Address', 'Date', 'Time', 'Error']);
	$query = "SELECT ID, email, ip_address, timestamp, error FROM leads ORDER BY ID DESC LIMIT 1000";
} else {
	fputcsv($output, ['ID', 'Email', 'IP Address', 'Date', 'Time', 'Referrer']);
	$query = "SELECT ID, email, ip_address, timestamp, referrer FROM bademail ORDER BY ID DESC LIMIT 1000";
}

$result = $conn->query($query);
if ($result) {
	while ($row = $result->fetch_assoc()) {
		$date = date('d/m/Y', strtotime($row['timestamp']));
		$time = date('h:s:i A', strtotime($row['timestamp']));

		if ($exportType === 'leads') {
			fputcsv($output, [$row['ID'], $row['email'], $row['ip_address'], $date, $time, $row['error']]);
		} else {
			fputcsv($output, [$row['ID'], $row['email'], $row['ip_address'], $date, $time, $row['referrer']]);
		}
	}
}

fclose($output);
$conn->close();
exit();