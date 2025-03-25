<?php

session_start();

require_once "database/config.php";
require_once "includes/header.php";

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

$depositAmount = isset($_POST['amount']) ? $_POST['amount'] : '';
$cryptoType = isset($_POST['cryptocurrency']) ? $_POST['cryptocurrency'] : '';

if (!empty($depositAmount) && is_numeric($depositAmount)) {
  $depositAmount = (int)$depositAmount;
  if ($depositAmount >= 5 && $depositAmount <= 500) {
    
  } else {
    header("Location: billing");
    exit();
  }
} else {
  header("Location: billing");
  exit();
}

if (empty($depositAmount) || empty($userId) || empty($cryptoType)) {
  header("Location: billing");
  exit();
}

$invoiceid = rand(10000000, 99999999);

$capitalizedCrypto = ucfirst($cryptoType);

$url = "https://www.poof.io/api/v2/create_invoice";
$payload = array(
    "metadata" => array("combo" => $sipUsername . '.' . $depositAmount),
    "amount" => $depositAmount,
    "crypto" => $cryptoType,
);
$headers = array(
    "Authorization: $poofKey",
    "Content-Type: application/json"
);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$responseData = json_decode($response, true);

if ($responseData && isset($responseData['address'])) {
    $address = $responseData['address'];
    $charge = $responseData['charge'];
    $crypto = $responseData['crypto'];
    $currency = $responseData['currency'];

    $qrCodeURI = "$crypto:$address?amount=$charge";
    $encodedQRCodeURI = htmlspecialchars($qrCodeURI);

    $username = $sipUsername;
    $amount = $depositAmount;
    $status = "Pending";

    // Prepare and execute the statement
    $stmt = $conn->prepare("INSERT INTO invoices (username, amount, status) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $amount, $status);
    $result = $stmt->execute();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sipify | Invoice</title>
  <link rel="shortcut icon" type="image/png" href="assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="assets/css/styles.min.css" />
</head>
<body>
<div class="container-fluid">
        <div class="container-fluid">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title fw-semibold mb-4">Invoice</h5>
              <div class="card">
                <div class="card-body">
                  <form>
                  <div class="mb-3">
                    <img src="https://chart.googleapis.com/chart?chs=220x200&cht=qr&chl=<?php echo $encodedQRCodeURI; ?>" alt="QR Code">
                  </div>

                    <div class="mb-3">
                      <label for="exampleInputEmail1" class="form-label">Amount</label>
                      <input type="text" class="form-control" id="exampleInputEmail1" readonly value="<?php echo $charge; ?>">
                      <div id="text" class="form-text">Please make sure to send the exact amount.</div>
                    </div>

                    <div class="mb-3">
                      <label for="exampleInputEmail1" class="form-label">Address</label>
                      <input type="text" class="form-control" id="exampleInputEmail1" readonly value="<?php echo $address; ?>">
                      <div id="text" class="form-text">Send the funds to this address.</div>
                    </div>

                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    
</body>
  <script src="assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/sidebarmenu.js"></script>
  <script src="assets/js/app.min.js"></script>
  <script src="assets/libs/simplebar/dist/simplebar.js"></script>
</html>