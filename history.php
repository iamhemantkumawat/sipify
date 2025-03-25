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

$magnusBilling = new \magnusbilling\api\MagnusBilling($apiKey, $apiSecret);
$magnusBilling->public_url = "http://$magnusIp/mbilling"; // Your MagnusBilling URL

$id_user = $magnusBilling->getId('user', 'username', $sipUsername);

// Set the filter to get calls from $id_user
$magnusBilling->setFilter('id_user', $id_user, 'eq', 'numeric');

// Read recent calls
$result = $magnusBilling->read('call');

?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sipify | CDR Report</title>
  <link rel="shortcut icon" type="image/png" href="assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="assets/css/styles.min.css" />
</head>

<body>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">CDR Report</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Number</th>
                            <th>CallerID</th>
                            <th>Duration</th>
                            <th>Call Costs</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Check if there are any rows
                        if (!empty($result['rows'])) {
                            // Loop through each call
                            foreach ($result['rows'] as $call) {
                                $sessionTime = gmdate("i:s", $call['sessiontime']);
                                echo "<tr>";
                                echo "<td>+" . $call['calledstation'] . "</td>";
                                echo "<td>+" . $call['callerid'] . "</td>";
                                echo "<td>" . $sessionTime . "</td>";
                                echo "<td>$" . $call['sessionbill'] . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            // No recent calls found
                            echo "<tr><td colspan='4'>No recent calls found.</td></tr>";
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