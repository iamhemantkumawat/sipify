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

$magnusBilling = new \magnusbilling\api\MagnusBilling($apiKey, $apiSecret);
$magnusBilling->public_url = "http://$magnusIp/mbilling"; // Your MagnusBilling URL  

$userId = $_SESSION['id'];

$sql = "SELECT sipusername, sippassword FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $userId);
$stmt->execute();
$stmt->bind_result($sipUsername, $sipPassword);
$stmt->fetch();
$stmt->close();

$magnusBilling->setFilter('username', $sipUsername);

$result = $magnusBilling->read('user');

function findCreditByUsername($userDataArray, $username) {
    foreach ($userDataArray['rows'] as $userData) {
        if ($userData['username'] === $username) {
            return $userData['credit'];
        }
    }
    return null;
}

$credit = findCreditByUsername($result, $sipUsername);
?>


<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sipify | Dashboard</title>
  <link rel="shortcut icon" type="image/png" href="assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="assets/css/styles.min.css" />
</head>

<body>

      <div class="container-fluid">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Dashboard</h5>
            <p class="mb-0">Welcome to Sipify!</p>
            <span class="navbar-text mr-2">
              Your Balance: $<?php echo $credit; ?>
            </span>
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