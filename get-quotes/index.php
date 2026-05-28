<?php
// Check if there are any query string parameters
$queryString = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';

// Define the new URL to which you want to redirect the user
$newURL = "https://affordable-healthcare.com/multi-quote" . $queryString;

// Perform the redirect
header("Location: " . $newURL);
exit; // Make sure no further code is executed
