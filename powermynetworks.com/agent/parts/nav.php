<style>
    /* Make button responsive */
    .wallet-btn {
        width: auto;
        height:40px;
        max-width: 250px;
        font-size: 16px;
        font-weight: 600;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease-in-out;
    }

    .wallet-btn i {
        font-size: 18px;
    }

    /* Hover effect */
    .wallet-btn:hover {
        background-color: #117a8b; /* Darker shade of info color */
        color: #fff;
    }

    /* Make button full width on small screens */
    @media (max-width: 576px) {
        .wallet-btn {
            width: 100% !important;
            max-width: 100% !important;
        }
    }
</style>

<!-- Button -->
<button type="button" class="btn btn-info wallet-btn float-right" data-toggle="modal" data-target="#exampleModal">
    <i class="fa fa-money" aria-hidden="true"></i>
    <span>&#x20B5; 
        <?php
        include '../database/dbconfig.php';
        $user_id = mysqli_real_escape_string($con, $_SESSION['user_id']);
        $select = "SELECT wallet FROM agents WHERE user_id='$user_id' LIMIT 1";
        $sql = mysqli_query($con, $select);
        if ($sql && mysqli_num_rows($sql) > 0) {
            $row = mysqli_fetch_assoc($sql);
            echo number_format($row['wallet'], 2);
        } else {
            echo "0.00";
        }
        mysqli_close($con);
        ?>
    </span>
    | Top up
</button>
