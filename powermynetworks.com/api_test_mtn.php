<?php
        $super_agent_email = "azumahnbalino1@gmail.com";
        require_once __DIR__ . '/configs/pmn_provider.php';
        $api_url = pmn_api_url('https://api.dataworksgh.com/api/power.php');
        $data = [
            'super_agent_email' => $super_agent_email,
            'agent_email' => "sclastechnologies@gmail.com",
            'recipient_number' => "0554226398",
            'network' => "MTN",
            'gig' => 1,
            'reference_id' => "jkdfsahanmvbnsj",
        ];
        $jsonData = json_encode($data);
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = "cURL Error: " . curl_error($ch);
            curl_close($ch);
            echo "error=" . urlencode($error);
            exit();
        }
        $response = json_decode($response, true);
        curl_close($ch);
        if (!isset($response['message'])) {
            $error = "API response missing message.";
            echo "error=" . urlencode($error);
            exit();
        }
        $success_message = $response['message'];
        if ($http_code == 200) {
            echo "Connected successfully: [$http_code] $response_message";
        } else {
                echo "Order Failed: [$http_code] $response_message";
        }
?>