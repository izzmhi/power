<?php
declare(strict_types=1);

session_name("AGENT_PORTAL_SESSION");
session_start();
include '../database/dbconfig.php';

/*
|--------------------------------------------------------------------------
| ERROR LOGGING (errors only)
|--------------------------------------------------------------------------
*/
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
    echo "<tr><td colspan='9'>Session expired</td></tr>";
    exit;
}

$user_id = $_SESSION['user_id'];
$network = 'MTN';

/*
|--------------------------------------------------------------------------
| FETCH ORDERS FROM DB
|--------------------------------------------------------------------------
*/
$stmt = $con->prepare("SELECT reference_id, beneficiary, network, gig, price, order_status, real_date, order_time FROM orders_agent WHERE agent_id = ? AND network = ? ORDER BY id DESC");
$stmt->bind_param("ss", $user_id, $network);
$stmt->execute();
$result = $stmt->get_result();

/*
|--------------------------------------------------------------------------
| CONFIG
|--------------------------------------------------------------------------
*/
$resolvedStatuses   = ['delivered', 'failed', 'completed'];
$unresolvedStatuses = ['pending', 'processing'];

$maxApiCalls = 50;   // HARD LIMIT PER PAGE
$apiCalls    = 0;

$orders = [];

/*
|--------------------------------------------------------------------------
| LOOP ORDERS
|--------------------------------------------------------------------------
*/
while ($row = $result->fetch_assoc()) {

    // 1️⃣ SHOW DB STATUS IMMEDIATELY
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
    | 2️⃣ CALL API ONLY FOR UNRESOLVED STATUSES
    |--------------------------------------------------------------------------
    */
    if (
        in_array($order['status'], $unresolvedStatuses, true) &&
        $apiCalls < $maxApiCalls
    ) {

        $apiCalls++;

        $agent_api = 'REDACTED';
        require_once __DIR__ . '/../configs/pmn_provider.php';
        $url = pmn_api_url('https://api.dataworksgh.com/api/response_regular.php');

        $payload = json_encode([
            'reference_id' => $order['id']
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Content-Type: application/json',
                'x-api-key: ' . $agent_api,
            ],
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 3,   // FAST FAIL
            CURLOPT_CONNECTTIMEOUT => 2
        ]);

        $response  = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200 && $response !== false) {

            $api = json_decode($response, true);

            if (
                isset($api['code']) &&
                (int)$api['code'] === 200 &&
                isset($api['order_details']['order_status'])
            ) {
                $order['status'] = strtolower($api['order_details']['order_status']);
            }
        }
    }

    $orders[] = $order;
}

/*
|--------------------------------------------------------------------------
| NO DATA
|--------------------------------------------------------------------------
*/
if (!$orders) {
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
        'delivered', 'completed' => 'success',
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

?>