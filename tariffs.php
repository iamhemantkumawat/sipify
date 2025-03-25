<?php

session_start();

require_once "database/config.php";
require_once "includes/header.php";
require_once "api/magnusBilling.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: signin");
    exit;
}

$sql = "SELECT apikey, apisecret, poof_key, poof_shared_secret, magnus_ip FROM settings WHERE id = ?";
$stmt = $conn->prepare($sql);
$id = 1;
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($apiKey, $apiSecret, $poofKey, $poofSharedSecret, $magnusIp); // Binding result variables
$stmt->fetch();
$stmt->close();

$userId = $_SESSION['id'];

$sql = "SELECT sipusername, sippassword FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $userId);
$stmt->execute();
$stmt->bind_result($sipUsername, $sipPassword);
$stmt->fetch();
$stmt->close();


// Instantiate the MagnusBilling class
$magnusBilling = new \magnusbilling\api\MagnusBilling($apiKey, $apiSecret);
$magnusBilling->public_url = "http://$magnusIp/mbilling"; // Your MagnusBilling URL 

$result = $magnusBilling->read('rate');
?>


<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sipify | Rates</title>
  <link rel="shortcut icon" type="image/png" href="assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="assets/css/styles.min.css" />
</head>

<body>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Tariffs</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Prefix</th>
                            <th>Country</th>
                            <th>Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            foreach ($result['rows'] as $row) {
                                // Extract relevant information
                                $prefix = '+' . $row['idPrefixprefix'];
                                $country = $row['idPrefixdestination'];
                                $rate = number_format($row['rateinitial'], 4);

                                // Print the row in the table
                                echo "<tr>";
                                echo "<td>$prefix</td>";
                                echo "<td>$country</td>";
                                echo "<td>$$rate</td>";
                                echo "</tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



    </div>
  </div>
  <script src="assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/sidebarmenu.js"></script>
  <script src="assets/js/app.min.js"></script>
  <script src="assets/libs/simplebar/dist/simplebar.js"></script>
</body>

</html>