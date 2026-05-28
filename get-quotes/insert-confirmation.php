<?php

// Database Credentials
$dbHost = "localhost";
$dbUser = "healthcareins_ajax_form";
$dbPass = "rV!jftyyW90L$16RJUEC";
$dbName = "healthcareins_form_submissions";

try {
    // Create connection
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Retrieve input data
    $postData = $_POST;
    $responseData = $postData['apiResponse']['response'] ?? null;

    // Extract values with defaults to prevent null errors
    $primaryPhone = $postData['requestData']['Primary_Phone'] ?? null;
    $email = $postData['requestData']['Email'] ?? null;
    $city = $postData['requestData']['City'] ?? null;
    $state = $postData['requestData']['State'] ?? null;
    $zip = $postData['requestData']['Zip'] ?? null;
    $householdIncome = $postData['requestData']['Household_Income'] ?? 0;
    $householdSize = $postData['requestData']['Household'] ?? 0;
    $currentlyInsured = $postData['requestData']['Currently_Insured'] ?? null;
    $age = $postData['requestData']['Age'] ?? 0;

    $dob = $postData['requestData']['DOB'] ?? null;
    // Convert DOB to the correct format (YYYY-MM-DD) if it's not null
    if ($dob) {
        $dobParts = explode('/', $dob); // Split by `/`
        if (count($dobParts) === 3) {
            $dob = $dobParts[2] . '-' . $dobParts[0] . '-' . $dobParts[1]; // Reformat to YYYY-MM-DD
        } else {
            $dob = null; // If the format is invalid, set DOB to null
        }
    }

    $leadId = $postData['requestData']['Lead_ID'] ?? null;
    $landingPage = $postData['requestData']['Landing_Page'] ?? null;
    $trustedformToken = $postData['requestData']['TrustedFormToken'] ?? null;
    $ipAddress = isset($postData['requestData']['IP_Address']) ? inet_pton($postData['requestData']['IP_Address']) : null;
    $subId = $postData['requestData']['Sub_ID'] ?? null;
    $sellerCompanyName = $postData['requestData']['seller_company_name'] ?? null;

    // Process API response
    $status = 'Unknown';
    $apiResponseDetails = null;

    if ($responseData) {
        $apiStatus = $responseData['status'] ?? null;

        if ($apiStatus === 'Matched') {
            $status = 'Matched';
            $apiResponseDetails = [
                'seller_lead_bid' => $responseData['bids']['bid'][0]['seller_lead_bid'] ?? null
            ];
        } elseif ($apiStatus === 'Error') {
            $status = 'Error';
            $apiResponseDetails = [
                'error_details' => $responseData['errors'] ?? null
            ];
        } elseif ($apiStatus === 'Unmatched') {
            $status = 'Unmatched';
            $apiResponseDetails = $responseData; // Store the full response for unmatched cases
        }
    }

    // Convert `api_response_details` to JSON
    $apiResponseDetailsJson = $apiResponseDetails ? json_encode($apiResponseDetails, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '{}';

    // Insert into database
    $stmt = $conn->prepare("
        INSERT INTO individual_entries (
            primary_phone, email, city, state, zip, household_income, household_size, 
            currently_insured, age, dob, lead_id, landing_page, trustedform_token, 
            ip_address, sub_id, seller_company_name, status, api_response_details
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        throw new Exception("Statement preparation failed: " . $conn->error);
    }

    $stmt->bind_param(
        "ssssiiisisssssssss",
        $primaryPhone, $email, $city, $state, $zip, $householdIncome, $householdSize,
        $currentlyInsured, $age, $dob, $leadId, $landingPage, $trustedformToken,
        $ipAddress, $subId, $sellerCompanyName, $status, $apiResponseDetailsJson
    );

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Form submission saved successfully."]);
    } else {
        throw new Exception("Error inserting record: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage(),
        "debug" => [
            "postData" => $postData,
            "primaryPhone" => $primaryPhone,
            "email" => $email,
            "city" => $city,
            "state" => $state,
            "zip" => $zip,
            "householdIncome" => $householdIncome,
            "householdSize" => $householdSize,
            "currentlyInsured" => $currentlyInsured,
            "age" => $age,
            "dob" => $dob,
            "leadId" => $leadId,
            "landingPage" => $landingPage,
            "trustedformToken" => $trustedformToken,
            "ipAddress" => $postData['requestData']['IP_Address'] ?? null,
            "subId" => $subId,
            "sellerCompanyName" => $sellerCompanyName,
            "apiResponseDetailsJson" => $apiResponseDetailsJson
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}