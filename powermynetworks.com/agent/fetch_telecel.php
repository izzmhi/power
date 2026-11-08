<?php
declare(strict_types=1);
session_name("AGENT_PORTAL_SESSION");
session_start();
include '../database/dbconfig.php';
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/orders_debug.log');
/*
|--------------------------------------------------------------------------
| SESSION CHECK
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['user_id'])) {
    error_log('[SESSION] Session expired or user_id not set');
    echo "<tr><td colspan='9'>Session expired</td></tr>";
    exit;
}
$user_id = $_SESSION['user_id'];
error_log("[SESSION] User ID: {$user_id}");
$network = "TELECEL";
$stmt = $con->prepare(" SELECT reference_id, beneficiary, network, gig, price, order_status, real_date, order_time FROM orders_agent WHERE agent_id = ? AND network=? ORDER BY id DESC");

if (!$stmt) {
    error_log('[DB] Prepare failed: ' . $con->error);
    exit;
}

$stmt->bind_param("ss", $user_id,$network);
$stmt->execute();
$result = $stmt->get_result();

error_log('[DB] Orders fetched: ' . $result->num_rows);

$resolvedStatuses   = ['delivered', 'failed', 'completed'];
$unresolvedStatuses = ['pending', 'processing'];

$maxApiCalls = 50;
$apiCalls    = 0;

$orders = [];

/*
|--------------------------------------------------------------------------
| LOOP ORDERS
|--------------------------------------------------------------------------
*/
while ($row = $result->fetch_assoc()) {

    error_log("[ORDER] Processing reference_id={$row['reference_id']} | DB status={$row['order_status']}");

    $order = [
        'id'         => $row['reference_id'],
        'phone'      => $row['beneficiary'],
        'network'    => $row['network'],
        'size'       => $row['gig'],
        'price'      => $row['price'],
        'status'     => strtolower($row['order_status']),
        'created_at' => $row['real_date'],
        'order_time' => $row['order_time']
    ];

    /*
    |--------------------------------------------------------------------------
    | API CALL FOR UNRESOLVED STATUSES
    |--------------------------------------------------------------------------
    */
    if (
        in_array($order['status'], $unresolvedStatuses, true) &&
        $apiCalls < $maxApiCalls
    ) {

        $apiCalls++;
        error_log("[API] Calling API for {$order['id']} (call #{$apiCalls})");

        $agent_api = 'REDACTED';
        require_once __DIR__ . '/../configs/pmn_provider.php';
        $url = pmn_api_url('https://api.codecraftnetwork.com/api/response_regular.php');

        $payload = json_encode(['reference_id' => $order['id']]);
        error_log("[API] Payload: {$payload}");

$queryUrl = $url . '?reference_id=' . urlencode($order['id']);

$ch = curl_init($queryUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPGET        => true,
    CURLOPT_HTTPHEADER     => [
        'Accept: application/json',
        'x-api-key: ' . $agent_api,
    ],
    CURLOPT_TIMEOUT        => 3,
    CURLOPT_CONNECTTIMEOUT => 2
]);

$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    error_log('[API] cURL error: ' . curl_error($ch));
}

curl_close($ch);

error_log("[API] HTTP {$http_code} | Raw response: {$response}");

/*
|--------------------------------------------------------------------------
| JSON PARSE
|--------------------------------------------------------------------------
*/
$api = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('[API] JSON decode error: ' . json_last_error_msg());
}

/*
|--------------------------------------------------------------------------
| STATUS EXTRACTION (MATCHES API DOCS)
|--------------------------------------------------------------------------
*/
if (
isset($api['status'], $api['success'], $api['data']['order_status']) &&
(int)$api['status'] === 200 &&
$api['success'] === true
) {

$apiStatus = strtolower($api['data']['order_status']);
error_log("[API] API status={$apiStatus} | DB status={$order['status']}");
if ($apiStatus !== $order['status']) {
error_log("[DB] Updating status {$order['id']} → {$apiStatus}");

                $update = $con->prepare(
                    "UPDATE orders_agent SET order_status = ? WHERE reference_id = ?"
                );

                if ($update) {
                    $update->bind_param("ss", $apiStatus, $order['id']);
                    $update->execute();
                    $update->close();

                    $order['status'] = $apiStatus;
                } else {
                    error_log('[DB] Update prepare failed: ' . $con->error);
                }

            } else {
                error_log("[API] Status unchanged for {$order['id']}");
            }

        } else {
            error_log("[API] Invalid API structure for {$order['id']}");
        }

    } elseif ($apiCalls >= $maxApiCalls) {
        error_log('[API] Max API call limit reached');
    }

    $orders[] = $order;
}

/*
|--------------------------------------------------------------------------
| NO DATA
|--------------------------------------------------------------------------
*/
if (!$orders) {
    error_log('[DATA] No orders found');
    echo "<tr><td colspan='9'>No orders found</td></tr>";
    exit;
}

/*
|--------------------------------------------------------------------------
| RENDER TABLE
|--------------------------------------------------------------------------
*/
$sn = 1;

foreach ($orders as $o) {

    $dt = new DateTime($o['created_at']);

    $badge = match ($o['status']) {
        'delivered'              => 'success',
        'processing'             => 'info',
        'pending'                => 'warning',
        default                  => 'danger'
    };

    echo "
    <tr>
        <td>{$sn}</td>
        <td>{$o['id']}</td>
        <td>{$o['phone']}</td>
        <td>{$o['network']}</td>
        <td>{$o['size']}</td>
        <td>{$o['price']}</td> 
        <td>{$dt->format('Y-m-d')}</td>
        <td>{$o['order_time']}</td>
        <td><span class='badge bg-{$badge}'>" . ucfirst($o['status']) . "</span></td>
    </tr>
    ";

    $sn++;
}

error_log('[END] Script completed successfully');
?>
