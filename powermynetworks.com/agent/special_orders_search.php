<?php
declare(strict_types=1);
session_name("AGENT_PORTAL_SESSION");
session_start();
include '../database/dbconfig.php';
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/orders_debug.log');
if (!isset($_SESSION['user_id'])) {
    echo "<tr><td colspan='9'>Session expired</td></tr>";
    exit;
}
$user_id = $_SESSION['user_id'];
$network = 'AT';
$stmt = $con->prepare("SELECT reference_id, beneficiary, network, gig, price, order_status, real_date FROM special_offers_orders WHERE agent_id = ? AND network = ? ORDER BY id DESC");
$stmt->bind_param("ss", $user_id, $network);
$stmt->execute();
$result = $stmt->get_result();
$resolvedStatuses   = ['delivered', 'failed', 'completed'];
$unresolvedStatuses = ['pending', 'processing'];
$maxApiCalls = 10;   // HARD LIMIT PER PAGE
$apiCalls    = 0;
$orders = [];
while ($row = $result->fetch_assoc()) {
    $order = [
        'id'         => $row['reference_id'],
        'phone'      => $row['beneficiary'],
        'network'    => $row['network'],
        'size'       => $row['gig'],
        'price'      => $row['price'],
        'status'     => strtolower($row['order_status']),
        'created_at' => $row['real_date']
    ];
    if (
        in_array($order['status'], $unresolvedStatuses, true) &&
        $apiCalls < $maxApiCalls
    ) {
        $apiCalls++;
        $agent_api = 'REDACTED';
        require_once __DIR__ . '/../configs/pmn_provider.php';
        $url = pmn_api_url('https://api.dataworksgh.com/api/response_big_time.php');
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
if (!$orders) {
    echo "<tr><td colspan='9'>No orders found</td></tr>";
    exit;
}
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
        <td>{$dt->format('Y-m-d')}</td>
        <td><span class='badge bg-{$badge}'>" . ucfirst($o['status']) . "</span></td>
    </tr>
    ";
    $sn++;
}

?>