<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../../database/dbconfig.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 405, 'message' => 'Method Not Allowed']);
    exit();
}
$json = file_get_contents('php://input');
$data = json_decode($json, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(402);
    echo json_encode(['status' => 402, 'message' => 'Invalid JSON']);
    exit();
}

// Extract data safely
$recipient_number = $data['recipient_number'] ?? null;
$network          = "AT";
$gig              = $data['gig'] ?? null;
$reference_id     = $data['reference_id'] ?? uniqid('ref_');
$agent_name       = $data['agent_name'] ?? "Unknown Agent";
$agent_tel        = $data['agent_tel'] ?? "N/A";
$agent_email      = "None";
$price            = $data['price'] ?? 0.00;

$user_id = "None"; // Use session agent_id if available

// Date and time
date_default_timezone_set('GMT');
$order_time = date('H:i:s A');
$order_date = date('l, F d, Y');
$real_date  = date('Y-m-d');
$mode       = "RealestPlug";
$origin     = "RealestPlug";
$profit     = "0.00";


    $sqls = mysqli_query($con, "SELECT * FROM special_all_activation");
    if ($sqls && mysqli_num_rows($sqls) > 0) {
        $row = mysqli_fetch_assoc($sqls);
        if ($row['status'] === "ACTIVATED") {
            $sqll = mysqli_query($con, "SELECT * FROM api_switch");
            if ($sqll && mysqli_num_rows($sqll) > 0) {
                while ($row_switch = mysqli_fetch_assoc($sqll)) {
                    if ($row_switch['at_big_time'] === "API") {
                        $medium = "API";
                        $status     = "Pending";
                        // API Processing
   $agent_api = "REDACTED";           
// API data preparation
$data = [
    'agent_api' => $agent_api,
    'recipient_number' => $recipient_number,
    'network' => "AT",
    'gig' => $gig,
    'reference_id' => $reference_id,
    'client_email' => $agent_email,
];
$jsonData = json_encode($data);
require_once __DIR__ . '/../../configs/pmn_provider.php';
$url = pmn_api_url('https://api.codecraftnetwork.com/api/special.php', 'at_big_time');

                        $ch = curl_init($url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

                        $response = curl_exec($ch);
                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);

                        $decoded = json_decode($response, true);
                        $response_message = $decoded['message'] ?? 'Unknown response';

                        if ($http_code == 200) {
                                $stmt = $con->prepare("INSERT INTO special_offers_orders(reference_id,agent_id,agent_name,agent_tel,agent_email,customer_email,beneficiary,network,gig,price,order_time,order_date,real_date,order_status,mode,original,customer_price,profit,medium,origin) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                            if ($stmt) {
                                $stmt->bind_param("sssssssssdsssssdssss", $reference_id,$user_id,$agent_name,$agent_tel,$agent_email,$agent_email,$recipient_number,$network,$gig,$price,$order_time,$order_date,$real_date,$status,$mode,$price,$price,$profit,$medium,$origin);
                                if ($stmt->execute()) {
                                    http_response_code(200);
                                    echo json_encode(['status' => 200, 'message' => 'Successful']);
                                } else {
                                    http_response_code(500);
                                    echo json_encode(['status' => 500, 'message' => 'Failed to insert order']);
                                }
                                $stmt->close();
                            } else {
                                http_response_code(501);
                                echo json_encode(['status' => 501, 'message' => 'Failed to prepare insert order']);
                            }
                        } else {
                            http_response_code(502);
                            echo json_encode(['status' => 502, 'message' => "API Error: {$response_message}"]);
                        }

                    } elseif ($row_switch['at_big_time'] === "EXCEL") {
                        $medium = "EXCEL";
                        $status     = "Pending";
                        // Excel processing
                            $stmt = $con->prepare("INSERT INTO special_offers_orders(reference_id,agent_id,agent_name,agent_tel,agent_email,customer_email,beneficiary,network,gig,price,order_time,order_date,real_date,order_status,mode,original,customer_price,profit,medium,origin) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                            if ($stmt) {
                                $stmt->bind_param("sssssssssdsssssdssss", $reference_id,$user_id,$agent_name,$agent_tel,$agent_email,$agent_email,$recipient_number,$network,$gig,$price,$order_time,$order_date,$real_date,$status,$mode,$price,$price,$profit,$medium,$origin);
                                if ($stmt->execute()) {
                                http_response_code(200);
                                echo json_encode(['status' => 200, 'message' => 'Successful']);
                            } else {
                                http_response_code(500);
                                echo json_encode(['status' => 500, 'message' => 'Failed to insert order']);
                            }
                            $stmt->close();
                        } else {
                            http_response_code(501);
                            echo json_encode(['status' => 501, 'message' => 'Failed to prepare insert order']);
                        }
                    }
                }
            }
        } else {
            http_response_code(400);
            echo json_encode(['status' => 400, 'message' => 'Account is out of stock']);
        }
    }

$con->close();
?>
