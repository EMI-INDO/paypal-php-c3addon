
<?php
/*
it is recommended not to change the php code,
all php variables and responses are handled by c3addon.
*/
/**
 * Created by EMI INDO So on 03/03/2024
 */
$cors = $_GET["cors"];
header("Access-Control-Allow-Origin: " . $cors);
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$environment = $_GET['environment'];
$orderID = $_GET['orderID'];
$accessToken = $_GET['accessToken'];

$api_url = ($environment == "sandbox") ? "https://api-m.sandbox.paypal.com/v2/checkout/orders/{$orderID}/capture" : "https://api-m.paypal.com/v2/checkout/orders/{$orderID}/capture";

$headers = array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accessToken
);

$data = array(
    'note_to_payer' => 'Optional note to payer',
);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(array('error' => 'Curl error: ' . curl_error($ch)));
} else {
    $decoded_result = json_decode($result, true);
    echo json_encode($decoded_result);
}

curl_close($ch);

?>
