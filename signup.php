<?php

session_start();

require_once 'database/config.php';
require_once "api/magnusBilling.php";

$sql = "SELECT apikey, apisecret, poof_key, poof_shared_secret, magnus_ip, plan_id, start_credit FROM settings WHERE id = ?";
$stmt = $conn->prepare($sql);
$id = 1;
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($apiKey, $apiSecret, $poofKey, $poofSharedSecret, $magnusIp, $planId, $startCredit); // Binding result variables
$stmt->fetch();
$stmt->close();

$magnusBilling = new \magnusbilling\api\MagnusBilling($apiKey, $apiSecret);
$magnusBilling->public_url = "http://$magnusIp/mbilling";


if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip_address = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip_address = $_SERVER['REMOTE_ADDR'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate and sanitize user inputs
    $username = mysqli_real_escape_string($conn, $username);

    // Check if the username is already taken
    $checkQuery = "SELECT * FROM users WHERE username = ?";
    if ($stmt = $conn->prepare($checkQuery)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $checkResult = $stmt->get_result();

        if ($checkResult->num_rows > 0) {
            $registration_error = 'Username already in use. Please choose a different username.';
        } else {
            // Proceed with registration
            if (strlen($username) < 4 || strlen($username) > 16 || !ctype_alpha($username)) {
                $registration_error = 'Username must be between 4 and 16 characters long and contain alphabetic letters.';
            } elseif (empty($password) || strlen($password) < 6) {
                $registration_error = 'Password must be at least 6 characters long.';
            } else {
                // Insert user data into the database
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $length = 10; // Define the length of the random string
                $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; // Define the character pool
                $generatedPassword = '';
                
                // Generate random string
                for ($i = 0; $i < $length; $i++) {
                    $generatedPassword .= $characters[rand(0, strlen($characters) - 1)];
                }

                $generatedUsername = rand(1000000, 9999999);
                
                $insertQuery = "INSERT INTO users (username, password, sipusername, sippassword, ip_address, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
                if ($stmt = $conn->prepare($insertQuery)) {
                    $stmt->bind_param("sssss", $username, $hashed_password, $generatedUsername, $generatedPassword, $ip_address);
                    if ($stmt->execute()) {
                        // All parameters are optional.
                        $result = $magnusBilling->createUser([
                            'username' => $generatedUsername,
                            'password' => $generatedPassword,
                            'active' => '1',
                            'firstname' => $username, 
                            'email' => $username . '@gmail.com',
                            'id_group' => 3, // DEFAULT: GROUP IS USER
                            'id_plan' => $planId, // DEFAULT: GET THE FIRST PLAN THAT YOU SET TO USE IN SIGNUP      
                            'credit' => $startCredit, // DEFAULT: GET THE CREDIT FROM THE PLAN
                        ]);

                        $registration_success = 'Registration successful. Please <a style="color: green;" href="signin"><u>login</u></a> to access your account.';
                    } else {
                        $registration_error = 'Something went wrong. Please try again later. ' . $conn->error . ' ';
                    }
                } else {
                    $registration_error = 'Something went wrong. Please try again later.';
                }
            }
        }
        $stmt->close();
    } else {
        $registration_error = 'Something went wrong. Please try again later.';
    }
}

?>


<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sipify | SignUp</title>
  <link rel="shortcut icon" type="image/png" href="assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="assets/css/styles.min.css" />
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <div
      class="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">
      <div class="d-flex align-items-center justify-content-center w-100">
        <div class="row justify-content-center w-100">
          <div class="col-md-8 col-lg-6 col-xxl-3">
            <div class="card mb-0">
              <div class="card-body">
              <div class="logo">
                <img src="assets/images/second.png" alt="Logo" class="logo-image">
                <span class="logo-text">Sipify</span>
              </div>
              <div class="small-space"></div>
                <p class="text-center">Create your account now.</p>

                <form action="signup" method="POST" class="form">
                  <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username">
                  </div>
                  <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password">
                  </div>
                  <?php if(isset($registration_error)) { ?>
                        <p style="color: red;"><?php echo $registration_error; ?></p>
                    <?php } elseif(isset($registration_success)) { ?>
                        <p style="color: green;"><?php echo $registration_success; ?></p>
                    <?php } ?>

                  <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                      <input class="form-check-input primary" type="checkbox" value="" id="flexCheckChecked" checked>
                      <label class="form-check-label text-dark" for="flexCheckChecked">
                        Remeber this Device
                      </label>
                    </div>
                  </div>
                  <button class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2">Create Account</button>
                  <div class="d-flex align-items-center justify-content-center">
                    <a class="text-primary fw-bold ms-2" href="signin">Already have an Account?</a>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>

<style>
  @font-face {
  font-family: 'GalebBold';
  src: url('fonts/Galeb W01 Bold.ttf') format('truetype');
  font-weight: bold;
  font-style: normal;
}

.logo-image {
  width: 50px; /* Adjust size as needed */
  margin-right: 0px; /* Adjust spacing between logo and text */
}

.logo {
  font-family: 'GalebBold', Arial, sans-serif;
  font-size: 36px;
  color: #333; /* Adjust color as needed */
  text-align: center; /* Center align the text */
}

.small-space {
    margin-bottom: 10px; /* Adjust the value as needed */
}
</style>

</html>