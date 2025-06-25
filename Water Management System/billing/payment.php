<?php 
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}
echo "Username: " . htmlspecialchars($_SESSION['username']); // Debugging line

$page = 'Payment';
if(isset($_GET['message'])){
    $message = htmlspecialchars($_GET['message']);
    echo "<script type='text/javascript'>alert('$message');</script>";
}

include_once "../sidebar.php";
include_once "../db_conn.php";

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validate and sanitize id
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT) : 0;
if (!$id) {
    header("Location: index.php?message=Invalid billing ID");
    exit();
}

// Get billing data
$stmt = mysqli_prepare($conn, 
    "SELECT * FROM billing WHERE id = ? AND status = 'Pending'"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_array($result)) {
    // Fetch total amount due for the current bill
    $total_amount_due = $row['total'];

    // Calculate total payments made
    $total_payments = 0;
    $payment_query = mysqli_prepare($conn, "SELECT amount FROM payment WHERE billing_id = ?");
    mysqli_stmt_bind_param($payment_query, "i", $id);
    mysqli_stmt_execute($payment_query);
    $payment_result = mysqli_stmt_get_result($payment_query);

    while ($payment = mysqli_fetch_assoc($payment_result)) {
        $total_payments += $payment['amount'];
    }
    mysqli_stmt_close($payment_query);

    // Calculate outstanding balance
    $total_due = $total_amount_due - $total_payments;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Water Billing System - Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="main-content">
        <div class="header">
            <h1 class="title-page text-center py-3 mb-4" style="background: #007bff; color: #fff; border-radius: 8px; letter-spacing:2px; font-weight:700; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                PAYMENT
            </h1>
            <h2 class="text-center py-2 mb-4" style="background: #28a745; color: #fff; border-radius: 8px; letter-spacing:1.5px; font-weight:700; text-transform:uppercase; box-shadow:0 2px 8px rgba(40,167,69,0.10);">Payment Information</h2>
        </div>
        <div class="content">
            <form class="row g-3" method="POST" action="../payment/create.php" id="paymentForm">
                <!-- CSRF Token and Hidden Fields -->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="billing_id" value="<?php echo $row['id']; ?>">
                <input type="hidden" name="customer_id" value="<?php echo $row['customer_id']; ?>">
                <input type="hidden" name="customer_name" value="<?php echo htmlspecialchars($row['customer_name']); ?>">
                <input type="hidden" name="user" value="<?php echo htmlspecialchars($_SESSION['username']); ?>">
                
                <!-- Billing Details -->
                <div class="col-md-4">
                    <label class="form-label">Bill #</label>
                    <input readonly class="form-control" value="<?php echo sprintf('24-%04d', $row['id']); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Customer</label>
                    <input readonly class="form-control" value="<?php echo htmlspecialchars($row['customer_name']); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Total Due (₱)</label>
                    <input readonly type="number" step="0.01" class="form-control" id="total_due" 
                           value="<?php echo $total_due; ?>">
                </div>

                <!-- Payment Details -->
                <div class="col-md-4">
                    <label class="form-label">Amount Paid (₱)<span style="color: red;">*</span></label>
                    <input required type="number" step="0.01" min="0" class="form-control" 
                           name="amount" id="amount" oninput="calculateChangeAndBalance()">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Change (₱)</label>
                    <input readonly type="number" step="0.01" class="form-control" 
                           name="change" id="change">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Balance (₱)</label>
                    <input readonly type="number" step="0.01" class="form-control" 
                           name="balance" id="balance">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Payment Date<span style="color: red;">*</span></label>
                    <input required type="date" class="form-control" name="payment_date" 
                           value="<?php echo date('Y-m-d'); ?>">
                </div>
                <input type="hidden" name="status" value="Pending">

                <!-- Form Buttons -->
                <div class="col-12 buttons">
                    <button type="submit" class="btn btn-primary">Process Payment</button>
                    <a href="index.php" class="btn btn-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function calculateChangeAndBalance() {
        const totalBill = parseFloat(document.getElementById('total_due').value) || 0;
        const amountPaid = parseFloat(document.getElementById('amount').value) || 0;
        const change = amountPaid - totalBill;
        const balance = totalBill - amountPaid;

        document.getElementById('change').value = change > 0 ? change.toFixed(2) : '0.00';
        document.getElementById('balance').value = balance > 0 ? balance.toFixed(2) : '0.00';
    }

    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        const totalBill = parseFloat(document.getElementById('total_due').value);
        const amountPaid = parseFloat(document.getElementById('amount').value);

        // Allow partial payments, so no need to prevent submission
        if (amountPaid <= 0) {
            alert('Amount paid must be greater than zero.');
            e.preventDefault(); // Prevent form submission
        }
         // Check if the balance is zero and update the status to "Paid"
         const balance = totalBill - amountPaid;
        if (balance <= 0) {
            document.querySelector('input[name="status"]').value = 'Paid';
        }
    });
    </script>
</body>
</html>
<?php 
} else {
    header("Location: index.php?message=Bill not found or already paid.");
    exit();
}
mysqli_stmt_close($stmt);
?>