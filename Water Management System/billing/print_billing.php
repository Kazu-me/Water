<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

include "../db_conn.php";

// Validate and sanitize id
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT) : 0;
if (!$id) {
    header("Location: index.php?message=Invalid billing ID");
    exit();
}

// Get billing data with customer details
$stmt = mysqli_prepare($conn, 
    "SELECT b.*, c.purok, c.phone_number 
     FROM billing b 
     LEFT JOIN customer c ON b.customer_id = c.id 
     WHERE b.id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_array($result)) {
    $bill_number = sprintf("24-%04d", $row['id']);
    $status_color = ($row['status'] == 'Paid') ? 'text-success' : 'text-danger';
    // Fetch payment details
    $payment_stmt = mysqli_prepare($conn, 
        "SELECT p.amount, p.date_created AS payment_date 
         FROM payment p 
         WHERE p.billing_id = ?"
    );
    mysqli_stmt_bind_param($payment_stmt, "i", $row['id']);
    mysqli_stmt_execute($payment_stmt);
    $payment_result = mysqli_stmt_get_result($payment_stmt);
    $payments = [];
    while ($payment = mysqli_fetch_array($payment_result)) {
        $payments[] = $payment;
    }
    mysqli_stmt_close($payment_stmt);

    $total_payments = 0;
    foreach ($payments as $payment) {
        $total_payments += $payment['amount'];
    }
    $outstanding_balance = $row['total'] - $total_payments;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Water Bill - <?php echo $bill_number; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }
        .receipt-container {
            max-width: 600px;
            margin: 30px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
            padding: 32px 32px 24px 32px;
            border-top: 8px solid #007bff;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 18px;
        }
        .receipt-header h2 {
            margin: 0;
            color: #007bff;
            font-size: 2em;
            font-weight: bold;
        }
        .receipt-header h4 {
            margin: 0;
            color: #333;
            font-size: 1.1em;
            font-weight: 600;
        }
        .info-box {
            border: 1px solid #007bff;
            border-radius: 6px;
            padding: 16px;
            margin-bottom: 18px;
            background: #f4faff;
        }
        .info-box .row > div {
            margin-bottom: 6px;
        }
        .summary-box {
            border: 1px solid #6c757d;
            border-radius: 6px;
            padding: 16px;
            margin-bottom: 18px;
            background: #f8f9fa;
        }
        .summary-box table {
            width: 100%;
        }
        .summary-box th, .summary-box td {
            padding: 6px 0;
            font-size: 1em;
        }
        .summary-box .total-row td {
            font-weight: bold;
            font-size: 1.1em;
        }
        .payment-history {
            margin-bottom: 18px;
        }
        .payment-history h5 {
            font-size: 1.1em;
            margin-bottom: 8px;
        }
        .payment-history table {
            width: 100%;
            border-collapse: collapse;
        }
        .payment-history th, .payment-history td {
            border: 1px solid #dee2e6;
            padding: 7px 6px;
            font-size: 0.98em;
        }
        .outstanding-box {
            background: #e9f7ef;
            border: 2px solid #28a745;
            border-radius: 6px;
            padding: 14px 18px;
            margin-bottom: 18px;
            text-align: right;
        }
        .outstanding-box strong {
            color: #28a745;
            font-size: 1.2em;
        }
        .footer-note {
            margin-top: 30px;
            font-size: 0.95em;
            color: #555;
            text-align: center;
        }
        @media print {
            .no-print { display: none; }
            .receipt-container { box-shadow: none; border-top: 0; }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <h2>WATER BILLING RECEIPT</h2>
            <h4>Statement of Account</h4>
        </div>
        <div class="info-box">
            <div class="row">
                <div class="col-6">
                    <strong>Bill #:</strong> <?php echo htmlspecialchars($bill_number); ?><br>
                    <strong>Customer Name:</strong> <?php echo htmlspecialchars($row['customer_name']); ?><br>
                    <strong>Address:</strong> <?php echo htmlspecialchars($row['purok']); ?><br>
                    <strong>Contact #:</strong> <?php echo htmlspecialchars($row['phone_number']); ?><br>
                </div>
                <div class="col-6">
                    <strong>Category:</strong> <?php echo htmlspecialchars($row['category']); ?><br>
                    <strong>Reading Date:</strong> <?php echo date('F d, Y', strtotime($row['reading_date'])); ?><br>
                    <strong>Due Date:</strong> <?php echo date('F d, Y', strtotime($row['due_date'])); ?><br>
                    <strong>Status:</strong> <span class="<?php echo $status_color; ?>"><?php echo htmlspecialchars($row['status']); ?></span><br>
                </div>
            </div>
        </div>
        <div class="summary-box">
            <table>
                <tr>
                    <th>Previous Reading</th>
                    <th>Current Reading</th>
                    <th>Consumption</th>
                    <th>Rate (₱)</th>
                </tr>
                <tr>
                    <td><?php echo number_format($row['previous_reading'], 4); ?></td>
                    <td><?php echo number_format($row['current_reading'], 4); ?></td>
                    <td><?php echo number_format($row['total_reading'], 4); ?></td>
                    <td><?php echo number_format($row['rate'], 2); ?></td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" style="text-align:right;">Total Bill:</td>
                    <td>₱<?php echo number_format($row['total'], 2); ?></td>
                </tr>
            </table>
        </div>
        <div class="payment-history">
            <h5>Payment History</h5>
            <table>
                <tr>
                    <th>Payment Date</th>
                    <th>Amount Paid (₱)</th>
                </tr>
                <?php if (count($payments) > 0): ?>
                    <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?php echo date('F d, Y', strtotime($payment['payment_date'])); ?></td>
                        <td><?php echo number_format($payment['amount'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="2" style="text-align:center;">No payments yet</td></tr>
                <?php endif; ?>
            </table>
        </div>
        <div class="outstanding-box">
            Outstanding Balance: <strong>₱<?php echo number_format($outstanding_balance, 2); ?></strong>
        </div>
        <div class="no-print text-center mt-4">
            <button onclick="window.print()" class="btn btn-primary">Print Bill</button>
            <a href="index.php" class="btn btn-secondary">Back</a>
        </div>
        <?php if ($outstanding_balance > 0): ?>
        <div class="footer-note">
            Please pay your bill on or before the due date to avoid penalties and service interruption.<br>
            For inquiries, contact our office.
        </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
} else {
    header("Location: index.php?message=Billing record not found");
    exit();
}
mysqli_stmt_close($stmt);
?>