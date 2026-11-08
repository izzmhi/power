<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
include '../../database/dbconfig.php';

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $agent_email = "azumahnbalino1@gmail.com";
    $agent_api   = "REDACTED";

    if (isset($input['reference_id'], $input['network'])) {
        $reference = mysqli_real_escape_string($con, $input['reference_id']);
        $network   = mysqli_real_escape_string($con, $input['network']);

        $url = null;
        $data = [];

        // Handle MTN
        if ($network === "MTN") {
            require_once __DIR__ . '/../../configs/pmn_provider.php';
            $url = pmn_api_url('https://api.dataworksgh.com/api/customer_response.php');
            $data = [
                'reference_id' => $reference,
                'agent_email'  => $agent_email
            ];

        // Handle AT
        } elseif ($network === "AT") {
            $url = pmn_api_url('https://api.codecraftnetwork.com/api/response_console.php');
            $data = [
                'reference_id' => $reference,
                'agent_api'    => $agent_api
            ];

        // Handle TELECEL
        } elseif ($network === "TELECEL") {
            $url = pmn_api_url('https://api.codecraftnetwork.com/api/response_telecel.php');
            $data = [
                'reference_id' => $reference,
                'agent_api'    => $agent_api
            ];

        // Handle AT BIG
        } elseif ($network === "ATBIG") {
            $url = pmn_api_url('https://api.codecraftnetwork.com/api/response_big_time.php');
            $data = [
                'reference_id' => $reference,
                'agent_api'    => $agent_api
            ];

        // Handle MTN BIG (DB lookup instead of API)
        } elseif ($network === "MTNBIG") {
            $select = "SELECT * FROM special_offers_orders WHERE reference_id='$reference' AND network='MTN'";
            $sql    = mysqli_query($con, $select);

            if ($sql && mysqli_num_rows($sql) > 0) {
                $row = mysqli_fetch_assoc($sql);
                $response = [
                    'status'       => 'success',
                    'code'         => 200,
                    'message'      => 'Successful',
                    'order_status' => $row['order_status'],
                    'beneficiary' => $row['beneficiary']
                ];
            } else {
                $response = [
                    'status'     => 'error',
                    'code'       => 404,
                    'message'    => 'No MTNBIG order found for this reference ID.'
                ];
            }

            echo json_encode($response);
            mysqli_close($con);
            exit;
        }

        // Run API request (all except MTNBIG)
        if (!empty($url)) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            $apiResponse = curl_exec($ch);

            // Capture curl errors
            if ($apiResponse === false) {
                $response = [
                    'status'     => 'error',
                    'code'       => 500,
                    'message'    => 'Curl error: ' . curl_error($ch)
                ];
                curl_close($ch);
                echo json_encode($response);
                mysqli_close($con);
                exit;
            }

            curl_close($ch);
            $result = json_decode($apiResponse, true);

            // Handle API response
            if (!empty($result) && isset($result['status']) && $result['status'] === 'success') {
                $response = [
                    'status'       => 'success',
                    'code'         => 200,
                    'message'      => 'Successful',
                    'order_status' => $result['order_details']['order_status'] ?? null,
                    'beneficiary'  => $result['order_details']['beneficiary'] ?? null
                ];
            } else {
                $response = [
                    'status'     => 'error',
                    'code'       => 502, // Bad gateway - upstream API failure
                    'message'    => $result['message'] ?? 'Failed to fetch order details.'
                ];
            }
        }
    } else {
        $response = [
            'status'     => 'error',
            'code'       => 400,
            'message'    => 'Reference ID and network are required.'
        ];
    }
} else {
    $response = [
        'status'     => 'error',
        'code'       => 405,
        'message'    => 'Invalid request method.'
    ];
}
mysqli_close($con);
echo json_encode($response);
exit;
?>
