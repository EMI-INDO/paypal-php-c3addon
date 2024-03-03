<?php
/*
it is recommended not to change the php code,
all php variables and responses are handled by c3addon.
*/
/**
 * Created by EMI INDO So on 03/03/2024
 */
$cors = $_POST["cors"];

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: " . $cors);
    header("Access-Control-Allow-Methods: GET, POST");
    header("Access-Control-Allow-Headers: Authorization, Content-Type");
    header("Content-Type: application/json");
    http_response_code(200);
    exit;
}

// Handle CORS for the main request
header("Access-Control-Allow-Origin: " . $cors);
header("Access-Control-Allow-Methods: GET, POST");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $environment = $_POST['environment'];
    $amount = $_POST["amount"];
    $currency = $_POST["currency"];
    $return_url = $_POST["return_url"];
    $cancel_url = $_POST["cancel_url"];

    $address_line_1 = $_POST["address_line_1"];
    $address_line_2 = $_POST["address_line_2"];
    $admin_area_2 = $_POST["admin_area_2"];
    $admin_area_1 = $_POST["admin_area_1"];
    $postal_code = $_POST["postal_code"];
    $country_code = $_POST["country_code"];

    $payment_method = $_POST["payment_method"];
    $brand_name = $_POST["brand_name"];
    $locale = $_POST["locale"];
    $landing_page = $_POST["landing_page"];
    $shipping = $_POST["shipping"];
    $user_action = $_POST["user_action"];


    $clientId = $_POST["clientId"];
    $clientSecret = $_POST["clientSecret"];


    $accessToken = getPayPalAccessToken($environment, $clientId, $clientSecret);

   
    if ($accessToken) {
    
        $paypal_data = '{
            "intent": "CAPTURE",
            "purchase_units": [
                {
                    "amount": {
                        "currency_code": "'.$currency.'",
                        "value": "'.$amount.'"
                    },
                    "shipping": {
                        "address": {
                            "address_line_1": "'.$address_line_1.'",
                            "address_line_2": "'.$address_line_2.'",
                            "admin_area_2": "'.$admin_area_2.'",
                            "admin_area_1": "'.$admin_area_1.'",
                            "postal_code": "'.$postal_code.'",
                            "country_code": "'.$country_code.'"
                        }
                    }
                }
            ],
            "payment_source": {
                "paypal": {
                    "experience_context": {
                        "payment_method_preference": "'.$payment_method.'",
                        "brand_name": "'.$brand_name.'",
                        "locale": "'.$locale.'",
                        "landing_page": "'.$landing_page.'",
                        "shipping_preference": "'.$shipping.'",
                        "user_action": "'.$user_action.'",
                        "return_url": "'.$return_url.'",
                        "cancel_url": "'.$cancel_url.'"
                    }
                }
            }
        }';

        
        $paymentResponse = sendPayPalRequest($environment, $paypal_data, $accessToken);

     
        $paymentResponse['accessToken'] = $accessToken;

        
        echo json_encode($paymentResponse);
    } else {
     
        echo json_encode([
            "success" => false,
            "error" => "Gagal memperoleh akses token",
            "accessToken" => null
        ]);
    }
} else {

    echo json_encode([
        "success" => false,
        "error" => "Permintaan tidak valid",
        "accessToken" => null
    ]);
}

function getPayPalAccessToken($environment, $clientId, $clientSecret) {

    $url = ($environment == "sandbox") ? "https://api-m.sandbox.paypal.com/v1/oauth2/token" : "https://api-m.paypal.com/v1/oauth2/token";
    $data = 'grant_type=client_credentials';

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_USERPWD, $clientId . ':' . $clientSecret);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/x-www-form-urlencoded',
    ));

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        return false; 
    }

    curl_close($ch);

    $responseArray = json_decode($response, true);

    return isset($responseArray['access_token']) ? $responseArray['access_token'] : false;
}

function sendPayPalRequest($environment, $data, $accessToken) {

     $url = ($environment == "sandbox") ? "https://api-m.sandbox.paypal.com/v2/checkout/orders" : "https://api-m.paypal.com/v2/checkout/orders";

    $headers = array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken,
    );

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        return [
            'success' => false,
            'error' => 'Kesalahan Curl: ' . curl_error($ch),
            'accessToken' => $accessToken,
        ];
    }

    curl_close($ch);

    
    return json_decode($response, true);
}
?>
