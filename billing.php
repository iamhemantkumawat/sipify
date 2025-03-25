<?php

session_start();

require_once "database/config.php";
require_once "includes/header.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: signin");
    exit;
}

$userId = $_SESSION['id'];

$sql = "SELECT sipusername, sippassword FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $userId);
$stmt->execute();
$stmt->bind_result($sipUsername, $sipPassword);
$stmt->fetch();
$stmt->close();

?>


<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sipify | Billing</title>
  <link rel="shortcut icon" type="image/png" href="assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="assets/css/styles.min.css" />
</head>

<body>

<div class="container-fluid">
    <div class="container-fluid">
        <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-3">Billing</h5>
            <form method="post" action="invoice">

                <div class="mb-3">
                    <label for="amount" class="form-label" style="font-weight: 700; text-transform: uppercase; font-size: 12px;">Enter the amount you want to deposit in USD:</label>
                    <input class="form-control" type="number" placeholder="0.00" name="amount" id="amount" min="5" max="500">
                    <div id="text" class="form-text">The minimum is 5 USD.</div>
                </div>

                <div class="mb-3">
                    <label for="cryptocurrency" class="form-label" style="font-weight: 700; text-transform: uppercase; font-size: 12px;">Select cryptocurrency:</label>
                    <select class="form-select" name="cryptocurrency" id="cryptocurrency">
                        <option value="bitcoin">Bitcoin</option>
                        <option value="litecoin">Litecoin</option>
                        <option value="ethereum">Ethereum</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Submit</button>

                </form>
        </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Invoices</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $conn->prepare("SELECT * FROM invoices WHERE username = ? ORDER BY id DESC");
                        $stmt->bind_param("s", $sipUsername);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($invoice = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $invoice['id'] . "</td>";
                                echo "<td>$" . $invoice['amount'] . ".00</td>";
                                echo "<td>" . $invoice['status'] . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3'>No invoices found.</td></tr>";
                        }

                        $stmt->close();
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