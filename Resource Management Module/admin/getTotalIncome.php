<?php

// Node.js API URL
$url = 'http://localhost:5000/admin/overview-stats';

// Initialize cURL session
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the API call
$response = curl_exec($ch);

// Check for cURL errors
if(curl_errno($ch)){
    echo 'Error fetching data: ' .curl_error($ch);
    exit;
}

// Close cURL session
curl_close($ch);

// Decode the JSON response
$data = json_decode($response, true);

// Check if decoding was successful and 'totalRevenue' exits
if (isset($data['totalRevenue'])) {
    // Format the number 
    $formattedRevenue = number_format($data['totalRevenue']);

    echo "₦" . $formattedRevenue;
} else {
    echo "₦0"; 
}
?>