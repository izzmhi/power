// receive_order.php on the Receiving Website

<?php
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        // Extract data
        $order_id = $data['order_id'];
        $reference_id = $data['reference_id'];
        $agent_id = $data['agent_id'];
        $agent_name = $data['agent_name'];
        $agent_tel = $data['agent_tel'];
        $agent_email = $data['agent_email'];
        $beneficiary = $data['beneficiary'];
        $network = $data['network'];
        $gig = $data['gig'];
        $price = $data['price'];
        $order_time = $data['order_time'];
        $order_date = $data['order_date'];
        $real_date = $data['real_date'];
        $status = $data['order_status'];
        $mode = $data['mode'];
        $purpose = $data['purpose'];

        // Save data to the database
        if ($stmt = $con->prepare("INSERT INTO api_orders (order_id, reference_id, agent_id, agent_name, agent_tel, agent_email, beneficiary, network, gig, price, order_time, order_date, real_date, order_status, mode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
            $stmt->bind_param("sssssssssssssss", $order_id, $reference_id, $agent_id, $agent_name, $agent_tel, $agent_email, $beneficiary, $network, $gig, $price, $order_time, $order_date, $real_date, $status, $mode);

            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(['message' => 'Order saved successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'Failed to save order: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Database error: ' . $con->error]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid JSON']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
}

// Close the database connection
$con->close();
