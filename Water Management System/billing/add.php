<?php 
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

include_once "../db_conn.php";

// Initialize variables
$page = 'Billing';
$user = htmlspecialchars($_SESSION['username']);

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if(isset($_GET['message'])){
    $message = htmlspecialchars($_GET['message']);
    echo "<script type='text/javascript'>alert('$message');</script>";
}

include_once "../sidebar.php";

// Validate and sanitize customer ID
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT) : 0;

// Get customer data with proper error handling
$stmt = mysqli_prepare($conn, 
    "SELECT c.*, cat.rate, 
            (SELECT SUM(balance) FROM billing WHERE customer_id = c.id AND status = 'Pending') AS outstanding_balance 
     FROM customer c 
     LEFT JOIN category cat ON c.category = cat.category_name 
     WHERE c.id = ? 
     AND (c.del_status IS NULL OR c.del_status != 'deleted')"
);

if (!$stmt) {
    die("Prepare failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);

if (!mysqli_stmt_execute($stmt)) {
    die("Execute failed: " . mysqli_error($conn));
}

$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_array($result)) {
    $customer_name = trim($row['first_name'] . ' ' . 
                         ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . 
                         $row['last_name'] . ' ' . 
                         $row['suffix']);
    $outstanding_balance = $row['outstanding_balance'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Water Billing System - Add Billing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="main-content">
        <div class="header">
            <h1 class="title-page text-center py-3 mb-4" style="background: #007bff; color: #fff; border-radius: 8px; letter-spacing:2px; font-weight:700; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                ADD <?php echo strtoupper(htmlspecialchars($page)); ?>
            </h1>
        </div>
        <div class="content">
            <form class="row g-3" method="POST" action="create.php" id="billingForm">
                <!-- CSRF Token and Hidden Fields -->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="customer_id" value="<?php echo $row['id']; ?>">
                <input type="hidden" name="customer" value="<?php echo htmlspecialchars($customer_name); ?>">
                <input type="hidden" name="user" value="<?php echo $user; ?>">
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($row['category']); ?>">
                <h2 class="text-center py-2 mb-3" style="background: #28a745; color: #fff; border-radius: 8px; letter-spacing:1.5px; font-weight:700; text-transform:uppercase; box-shadow:0 2px 8px rgba(40,167,69,0.10);">Billing Information</h2>

                <!-- Customer Information -->
                <div class="col-md-4">
                    <label class="form-label">Customer</label>
                    <input readonly class="form-control" value="<?php echo htmlspecialchars($customer_name); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Category</label>
                    <input readonly class="form-control" value="<?php echo htmlspecialchars($row['category']); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Rate (₱)</label>
                    <input readonly type="number" step="0.01" class="form-control" name="rate" id="rate" 
                           value="<?php echo htmlspecialchars($row['rate']); ?>">
                </div>

                <!-- Reading Information -->
                <div class="col-md-4">
                    <label class="form-label">Previous Reading</label>
                    <input readonly type="number" step="0.0001" class="form-control" name="previous_reading" 
                           id="previous_reading" value="<?php echo htmlspecialchars($row['water_reading']); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Current Reading<span style="color: red;">*</span></label>
                    <input required type="number" step="0.0001" class="form-control" name="current_reading" 
                           id="current_reading" oninput="calculate()">
                    <span class="error-msg text-danger"></span>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Total Reading</label>
                    <input readonly type="number" step="0.0001" class="form-control" name="total_reading" 
                           id="total_reading">
                </div>

                <!-- Dates -->
                <div class="col-md-4">
                    <label class="form-label">Previous Reading Date</label>
                    <input readonly type="date" class="form-control" name="latest_reading_date" 
                           value="<?php echo htmlspecialchars($row['latest_reading_date']); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Current Reading Date<span style="color: red;">*</span></label>
                    <input required type="date" class="form-control" name="current_reading_date" 
                           id="current_reading_date">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Due Date<span style="color: red;">*</span></label>
                    <input required type="date" class="form-control" name="due_date" id="due_date">
                </div>

                

                <!-- Total -->
                <div class="col-md-4">
                    <label class="form-label">Total Price (₱)</label>
                    <input readonly type="number" step="0.01" class="form-control" name="total_price" 
                           id="total_price">
                </div>

                <!-- Status -->
                <input type="hidden" name="status" value="Pending">

                <!-- Form Buttons -->
                <div class="col-12 buttons">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="select_customer.php" class="btn btn-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function calculate() {
        const previousReading = parseFloat(document.getElementById("previous_reading").value) || 0;
        const currentReading = parseFloat(document.getElementById("current_reading").value) || 0;
        const rate = parseFloat(document.getElementById("rate").value) || 0;
        const errorMsg = document.querySelector(".error-msg");
        
        errorMsg.innerHTML = "";
        
        if (currentReading < 0) {
            errorMsg.innerHTML = "Current reading must be greater than or equal to 0";
            clearCalculations();
            return;
        }
        
        if (currentReading <= previousReading) {
            errorMsg.innerHTML = "Current reading must be greater than previous reading";
            clearCalculations();
            return;
        }
        
        const totalReading = (currentReading - previousReading).toFixed(4);
        const totalPrice = (totalReading * rate).toFixed(2);
        
        document.getElementById("total_reading").value = totalReading;
        document.getElementById("total_price").value = totalPrice;
    }

    function clearCalculations() {
        document.getElementById("total_reading").value = "";
        document.getElementById("total_price").value = "";
    }

    document.getElementById('billingForm').addEventListener('submit', function(e) {
        const currentReading = parseFloat(document.getElementById('current_reading').value);
        const previousReading = parseFloat(document.getElementById('previous_reading').value);
        
        if (currentReading <= previousReading) {
            e.preventDefault();
            alert('Current reading must be greater than previous reading');
        }
    });
    </script>
</body>
</html>
<?php

