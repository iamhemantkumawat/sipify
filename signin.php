<?php

session_start();

require_once "database/config.php";


if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip_address = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip_address = $_SERVER['REMOTE_ADDR'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE username = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $param_username);
        $param_username = $username;
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                if (password_verify($password, $row['password'])) {
                    $_SESSION["loggedin"] = true;
                    $_SESSION["username"] = $username;
                    $_SESSION['id'] = $row['id'];

                    $insertQuery = "INSERT INTO login_history (username, ip_address, login_time) VALUES (?, ?, NOW())";
                    if ($stmt = $conn->prepare($insertQuery)) {
                        $stmt->bind_param("ss", $username, $ip_address);
                        $stmt->execute();
                    }
                    
                    header("location: dashboard");

                } else {
                    $login_err = "Invalid username or password.";
                }
            } else {
                $login_err = "Invalid username or password.";
            }
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        $stmt->close();
    }
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sipify | SignIn</title>
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

                <p class="text-center">Sign in to your account.</p>

                <form action="signin" method="POST" class="form">
                  <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username">
                  </div>
                  <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password">
                  </div>
                  <?php if(isset($login_err)) { ?>
                    <p style="color: red;"><?php echo $login_err; ?></p>
                <?php } ?>

                  <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                      <input class="form-check-input primary" type="checkbox" value="" id="flexCheckChecked" checked>
                      <label class="form-check-label text-dark" for="flexCheckChecked">
                        Remeber this Device
                      </label>
                    </div>
                    <a class="text-primary fw-bold">Forgot Password ?</a>
                  </div>
                  <button class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2">Sign In</button>
                  <div class="d-flex align-items-center justify-content-center">
                    <a class="text-primary fw-bold ms-2" href="signup">Create an account</a>
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