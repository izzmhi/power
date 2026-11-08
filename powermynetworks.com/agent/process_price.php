<?php
session_name("AGENT_PORTAL_SESSION");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}
include'../database/dbconfig.php';
$user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
$selects = "select * from account_agents where user_id='$user_id' ";
$sqls=mysqli_query($con,$selects);
$checks=mysqli_num_rows($sqls)>0;
if($checks)
{ 
    while($rows=mysqli_fetch_array($sqls))
    {
       $_SESSION['first_name'] = $rows['first_name'];
       $_SESSION['last_name']= $rows['last_name'];
       $_SESSION['email'] = $rows['email'];
       $_SESSION['telephone'] = $rows['telephone'];
    }
}
if (isset($_POST['insert_package'])) {
    // Sanitize session inputs
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $agent_name = trim($first_name . " " . $last_name);
    $email = mysqli_real_escape_string($con, $_SESSION['email']);
    $agent_tel = mysqli_real_escape_string($con, $_SESSION['telephone']);

    // Get form data arrays
    $agent_prices = $_POST['agent_amount'];
    $amounts = $_POST['amount'];
    $networks = $_POST['network'];
    $originals = $_POST['original'];
    $packages = $_POST['package'];

    // Validate input
    foreach ($amounts as $index => $amount) {
        $amount_raw = trim($amount);
        if ($amount_raw === '' || !is_numeric($amount_raw)) {
            $error = "Invalid or missing amount at row " . ($index + 1);
            header("Location: shop.php?error=" . urlencode($error));
            exit();
        }
    }

    // Insert data
    foreach ($agent_prices as $index => $agent_price) {
        // Sanitize and format
        $network = mysqli_real_escape_string($con, $networks[$index]);
        $package = mysqli_real_escape_string($con, $packages[$index]);
        $amount = round((float)$amounts[$index], 2);
        $agent_price = round((float)$agent_prices[$index], 2);
        $original_price = round((float)$originals[$index], 2);

        // Insert with numeric fields as doubles (no quotes)
        $insertquery = "
            INSERT INTO agents_prices_packages 
                (agent_id, agent_name, email, network, package, amount, agent_price, original)
            VALUES 
                ('$user_id', '$agent_name', '$email', '$network', '$package', $amount, $agent_price, $original_price)
        ";

        $insert = mysqli_query($con, $insertquery);

        if (!$insert) {
            $error = "Failed to add package at row " . ($index + 1) . ": " . mysqli_error($con);
            header("Location: shop.php?error=" . urlencode($error));
            exit();
        }
    }

    // All inserts succeeded
    $success = "Packages added successfully.";
    header("Location: shop.php?success=" . urlencode($success));
    exit();
}



if (isset($_POST['update_package'])) {
    // Sanitize session inputs
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $agent_name = $first_name . " " . $last_name;
    $email = mysqli_real_escape_string($con, $_SESSION['email']);
    $agent_tel = mysqli_real_escape_string($con, $_SESSION['telephone']);

    $special_ids = $_POST['id'];
    $amounts = $_POST['amount'];

    foreach ($special_ids as $index => $special_id) {
        $special_id = mysqli_real_escape_string($con, $special_id);
        $amount_raw = trim($amounts[$index]);

        // Validate amount
        if ($amount_raw === '' || !is_numeric($amount_raw)) {
            $error = "Invalid amount at row " . ($index + 1);
            header("Location: shop.php?error=" . urlencode($error));
            exit();
        }

        // Format amount as double with 2 decimals
        $amount = round((float)$amount_raw, 2);

        // Update query for each row (amount as double)
        $updatequery = "
            UPDATE agents_prices_packages 
            SET amount = $amount
            WHERE id = '$special_id' 
              AND agent_id = '$user_id'
        ";

        $uquery = mysqli_query($con, $updatequery);

        if (!$uquery) {
            $error = "Failed to update package at row " . ($index + 1) . ": " . mysqli_error($con);
            header("Location: shop.php?error=" . urlencode($error));
            exit();
        }
    }

    // All updates succeeded
    $success = "Package updated successfully.";
    header("Location: shop.php?success=" . urlencode($success));
    exit();
}


if (isset($_POST['insert_special'])) {
    // Sanitize agent session data
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $agent_name = trim($first_name . " " . $last_name);
    $email = mysqli_real_escape_string($con, $_SESSION['email']);
    $agent_tel = mysqli_real_escape_string($con, $_SESSION['telephone']);

    // Get posted arrays
    $agent_prices = $_POST['agent_amount'];
    $amounts = $_POST['amount'];
    $originals = $_POST['original'];
    $packages = $_POST['package'];
    $networks = $_POST['network'];

    // Validate input
    foreach ($amounts as $index => $amount) {
        $amount_raw = trim($amount);
        if ($amount_raw === '' || !is_numeric($amount_raw)) {
            $error = "Invalid or missing amount at row " . ($index + 1);
            header("Location: shop.php?error=" . urlencode($error));
            exit();
        }
    }

    // Insert data
    foreach ($amounts as $index => $amount) {
        $network = mysqli_real_escape_string($con, $networks[$index]);
        $package = mysqli_real_escape_string($con, $packages[$index]);

        // Convert to double and round
        $amount = round((float)$amounts[$index], 2);
        $agent_price = round((float)$agent_prices[$index], 2);
        $original_price = round((float)$originals[$index], 2);

        // Insert with numeric fields (no quotes)
        $insertquery = "
            INSERT INTO agents_prices_special 
                (agent_id, agent_name, email, network, package, amount, agent_price, original)
            VALUES 
                ('$user_id', '$agent_name', '$email', '$network', '$package', $amount, $agent_price, $original_price)
        ";

        $insert = mysqli_query($con, $insertquery);

        if (!$insert) {
            $error = "Failed to add special package at row " . ($index + 1) . ": " . mysqli_error($con);
            header("Location: shop.php?error=" . urlencode($error));
            exit();
        }
    }

    // All inserts succeeded
    $success = "Special packages added successfully.";
    header("Location: shop.php?success=" . urlencode($success));
    exit();
}



if (isset($_POST['update_special'])) {
    // Sanitize session inputs
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $agent_name = $first_name . " " . $last_name;
    $email = mysqli_real_escape_string($con, $_SESSION['email']);
    $agent_tel = mysqli_real_escape_string($con, $_SESSION['telephone']);

    // Get posted arrays
    $networks = $_POST['network'];
    $packages = $_POST['package'];
    $amounts  = $_POST['amount'];

    foreach ($networks as $index => $network) {
        $network = mysqli_real_escape_string($con, $network);
        $package = mysqli_real_escape_string($con, $packages[$index]);
        $amount = round((float)$amounts[$index], 2);
    
        if (empty($amount)) {
            $error = "Amount is required for row " . ($index + 1);
            header("Location: shop.php?error=" . urlencode($error));
            exit();
        }

        // Update based on network, package, and agent_id
        $updatequery = "
            UPDATE agents_prices_special 
            SET amount = '$amount'
            WHERE network = '$network' 
              AND package = '$package' 
              AND agent_id = '$user_id'
        ";

        $uquery = mysqli_query($con, $updatequery);

        if (!$uquery) {
            $error = "Failed to update package at row " . ($index + 1);
            header("Location: shop.php?error=" . urlencode($error));
            exit();
        }
    }

    // All updates succeeded
    $success = "Special package updated successfully.";
    header("Location: shop.php?success=" . urlencode($success));
    exit();
}




if (isset($_POST['insert_afa'])) {
    // Get agent session data and sanitize
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $agent_name = $first_name . " " . $last_name;
    $email = mysqli_real_escape_string($con, $_SESSION['email']);
    $agent_tel = mysqli_real_escape_string($con, $_SESSION['telephone']);

    // Get form data arrays
    $agent_prices = $_POST['agent_amount'];
    $amounts = $_POST['amount'];
    $originals = $_POST['original'];

    // Validate input
    foreach ($amounts as $index => $amount) {
        if (empty($amount)) {
            $error = "Amount is required at row " . ($index + 1);
            header("Location: shop.php?error=" . urlencode($error));
            exit();
        }
    }

    // Insert data
    foreach ($agent_prices as $index => $agent_price) {
        $agent_price = mysqli_real_escape_string($con, $agent_prices[$index]);
        $amount = mysqli_real_escape_string($con, $amounts[$index]);
        $original_price = mysqli_real_escape_string($con, $originals[$index]);

        $insertquery = "INSERT INTO agents_prices_afa (agent_id, agent_name, email, amount, agent_price, original_price)
                        VALUES ('$user_id', '$agent_name', '$email', '$amount', '$agent_price', '$original_price')";
        $insert = mysqli_query($con, $insertquery);

        if (!$insert) {
            $error = "Failed to add package at row " . ($index + 1);
            header("Location: shop.php?error=" . urlencode($error));
            exit();
        }
    }

    // If all inserts succeed
    $success = "Afa packages added successfully.";
    header("Location: shop.php?success=" . urlencode($success));
    exit();
}



if (isset($_POST['update_afa'])) {
    // Sanitize session inputs
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $agent_name = $first_name . " " . $last_name;
    $email = mysqli_real_escape_string($con, $_SESSION['email']);
    $agent_tel = mysqli_real_escape_string($con, $_SESSION['telephone']);
    // Get form data
    $agent_prices = $_POST['agent_amount'];
    $afa_ids = $_POST['id'];
    $amounts = $_POST['amount'];
    $originals = $_POST['original'];
    foreach ($afa_ids as $index => $afa_id) {
        if (empty($amounts[$index])) {
            $error = "Amount is required for row " . ($index + 1);
            header("Location: shop.php?error=" . urlencode($error));
            exit();
        }
        // Sanitize each field
        $afa_id = mysqli_real_escape_string($con, $afa_id);
        $agent_price = mysqli_real_escape_string($con, $agent_prices[$index]);
        $amount = mysqli_real_escape_string($con, $amounts[$index]);
        $original_price = mysqli_real_escape_string($con, $originals[$index]);
        // Update query for each row
        $updatequery = "UPDATE agents_prices_afa SET amount='$amount' WHERE id='$afa_id' AND agent_id='$user_id'";
        $uquery = mysqli_query($con, $updatequery);
        if (!$uquery) {
            $error = "Failed to update package at row " . ($index + 1);
            header("Location: shop.php?error=" . urlencode($error));
            exit();
        }
    }
    // All updates succeeded
    $success = "Afa package updated successfully.";
    header("Location: shop.php?success=" . urlencode($success));
    exit();
}


if(isset($_POST['submit_prices'])){
    $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
    $first_name = mysqli_real_escape_string($con, $_SESSION['first_name']);
    $last_name = mysqli_real_escape_string($con, $_SESSION['last_name']);
    $agent_name = $first_name . " " . $last_name;
    $agent_email = mysqli_real_escape_string($con, $_SESSION['email']);
    $agent_tel = mysqli_real_escape_string($con, $_SESSION['telephone']);
    $network = mysqli_real_escape_string($con, $_POST['network']);
    $packages = $_POST['package']; // Array of packages
    $real_packages = $_POST['real_package'];
    $amounts = $_POST['amount'];   // Array of amounts
    $originals = $_POST['original'];
    if (empty($network)) {
        $errors['network'] = "Select a network";
    }
    // Check if any package or amount fields are empty
    foreach($packages as $index => $package) {
        if (empty($package)) {
            $errors["package_$index"] = "Package " . ($index + 1) . " is required";
        }
          if (empty($real_packages[$index])) {
            $errors["real_package$index"] = "Volume " . ($index + 1) . " is required";
        }
        if (empty($amounts[$index])) {
            $errors["amount_$index"] = "Amount " . ($index + 1) . " is required";
        }
        if (empty($originals[$index])) {
            $errors["original_$index"] = "Original" . ($index + 1) . " is required";
        }
    }
    
    $selectquery = "select * from prices_agents where network = '$network' AND package = '$package' AND agent_id = '$user_id' ";
    $query = mysqli_query($con,$selectquery);
    $selectcount = mysqli_num_rows($query);
    if($selectcount>0){
        // Update query for each row based on the unique id
        $updatequery = "UPDATE prices_agents SET amount='$amount' WHERE id='$id'";
            $uquery = mysqli_query($con, $updatequery);
            if ($uquery) {
                $success["sucess"] = "Package is updated successfully";
            } else {
                $errors["error"] = "Failed to update Package ";
            }
    }
    else{

    if (empty($errors)) {
        foreach($packages as $index => $package) {
            $package = mysqli_real_escape_string($con, $package);
            $real_package = mysqli_real_escape_string($con, $real_packages[$index]);
            $amount = mysqli_real_escape_string($con, $amounts[$index]);
            $original = mysqli_real_escape_string($con, $originals[$index]);
            $insertquery = "INSERT INTO prices_agents (agent_id, agent_name, email, network, package,amount, agent_price, original_price) VALUES 
            ('$agent_id', '$agent_name', '$email', '$network', '$package','$amount', '$agent_price', '$original_price')";
            $insert = mysqli_query($con, $insertquery);
            if (!$insert) {
                $errors["insert_$index"] = "Failed to insert Package '" . $package . "'";
            }
        }
        if (empty($errors)) {
            $success = "All packages added successfully.";
            header("Location: shop.php?success=" . urlencode($success));
            exit();
        } else {
            $errors['general'] = "Some packages could not be added.";
        }
    }
  }
}



?>