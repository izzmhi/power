<?php
session_start();
if (!isset($_SESSION['package'])) {
    header('Location: logout.php');
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

        $network = $_SESSION['network']; 
		$gig = $_SESSION['gig'];
		$prices = $_SESSION['price'];
		$email = $_SESSION['email'];
		$beneficiary = $_SESSION['beneficiary'];
		$price	   = $_SESSION['actual_price'];
		
		$_SESSION['networks']=$network; 
		$_SESSION['gigs']=$gig;
		$_SESSION['prices']=$price;
		$_SESSION['emails']=$email;
		$_SESSION['beneficiarys']=$beneficiary;
		
		echo $network;
		echo $gig;
		echo $prices;
		echo $beneficiary;
?>

<script>
    // Define the data object
    const data = {
        API_Key: "REDACTED",
        Receiver: "0554226398",
        Volume: "1",
        Reference: "7iKB9ycGZCZ724mPjP19", 
        Package_Type: "AirtelTigo"
    };

    // Check if the server is reachable before making the POST request
    fetch('https://www.value4moni.com/api/v1/initiate_transaction', {
        method: 'HEAD', // Use HEAD to check for server reachability without fetching content
    })
    .then(response => {
        console.log('Server is reachable:', response);

        if (response.ok) {
            // Server is reachable, proceed with the transaction
            return fetch('https://www.value4moni.com/api/v1/initiate_transaction', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
        } else {
            throw new Error('Server responded, but not OK: ' + response.statusText);
        }
    })
    .then(response => {
        console.log('Raw Response:', response);

        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.statusText);
        }

        return response.json();
    })
    .then(result => {
        console.log('Success:', result);
        alert('Transaction successful: ' + JSON.stringify(result));
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Transaction failed: ' + error.message);
    });
</script>

