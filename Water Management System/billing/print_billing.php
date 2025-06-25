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
            background: #fff;
            font-family: Arial, Helvetica, sans-serif;
        }
        .receipt-container {
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            border: 4px solid #20b9c1;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 0 18px #e3f7fa;
            padding: 0 0 20px 0;
            position: relative;
        }
        .watermark {
            position: absolute;
            top: 25%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.06;
            z-index: 0;
            pointer-events: none;
            width: 90%;
        }
        .company-header {
            text-align: center;
            background: #f4fcfd;
            padding: 24px 10px 10px 10px;
            border-bottom: 2px solid #20b9c1;
            position: relative;
        }
        .company-header img {
            height: 100px;
            float: left;
            margin-right: 16px;
        }
        .company-header h2 {
            color: #20b9c1;
            font-size: 2.1em;
            margin-bottom: 0;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .company-header h4 {
            color: #444;
            font-size: 1.1em;
            margin-top: 0;
            font-weight: 600;
        }
        .company-details {
            font-size: 0.98em;
            color: #222;
            margin-bottom: 2px;
        }
        .section-title {
            color: #20b9c1;
            font-weight: bold;
            font-size: 1em;
            margin: 16px 0 5px 0;
            letter-spacing: 1px;
            border-bottom: 1px dashed #20b9c1;
        }
        table.receipt-table {
            width: 98%;
            margin: 0 auto 10px auto;
            font-size: 0.96em;
        }
        table.receipt-table td,
        table.receipt-table th {
            padding: 3px 4px;
        }
        table.receipt-table th {
            color: #20b9c1;
            background: #f4fcfd;
            font-weight: bold;
            border-bottom: 1px solid #c9f3f7;
        }
        .summary-table td.label,
        .details-table td.label {
            font-weight: bold;
            color: #20b9c1;
            width: 170px;
        }
        .summary-table td.amount,
        .details-table td.amount {
            text-align: right;
            font-weight: bold;
        }
        .details-table tr.total-row td {
            font-size: 1em;
            background: #e4fcfa;
            color: #20b9c1;
        }
        .meter-table td {
            width: 120px;
        }
        .outstanding-balance {
            color: #20b9c1;
            font-weight: bold;
            font-size: 1em;
            margin: 10px 32px 7px 0;
            text-align: right;
        }
        .footer-note {
            margin-top: 15px;
            font-size: 0.94em;
            color: #555;
            text-align: center;
        }
        .no-print {
            text-align: center;
            margin-top: 12px;
        }
        /* Print Styles */
        @media print {
            @page {
                size: A4 portrait;
                margin: 16mm 8mm 16mm 8mm;
            }
            body {
                background: #fff !important;
                font-size: 12px;
            }

            
            .receipt-container {
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0 !important;
                border: 2px solid #20b9c1 !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 0 10px 0 !important;
                page-break-after: auto;
            }
            
            .company-header img {
                height: 70px !important;
            }
            
            .section-title { font-size: 0.95em !important; }
            .outstanding-balance { font-size: 0.95em !important; }
            table.receipt-table { font-size: 0.93em !important; }
            .no-print { display: none !important; }
        }
        /* Responsive for screen only, not for print */
        @media screen and (max-width: 900px) {
            .receipt-container {
                width: 100vw;
                max-width: unset;
                padding: 0 2vw 20px 2vw;
            }
            table.receipt-table { font-size: 0.94em; }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <img src="../asset/img/logo.jpg" class="watermark" alt="Watermark">
        <div class="company-header">
            <img src="../asset/img/logo.jpg" alt="Company Logo">
            <div style="display:inline-block;text-align:left;">
                <div class="company-details">
                    BANATE WATER COMPANY, INC.<br>
                    Purok 1, Malungon, Sarangani, Philippines<br>
                    TIN 005-038-428-000 VAT
                </div>
                <h2>WATER BILLING RECEIPT</h2>
                <h4>Statement of Account</h4>
            </div>
        </div>

        <div class="section-title">SERVICE INFORMATION</div>
        <table class="receipt-table summary-table">
            <tr>
                <td class="label">Contract Account No.</td>
                <td><?php echo htmlspecialchars($row['customer_id']); ?></td>
            </tr>
            <tr>
                <td class="label">Account Name</td>
                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
            </tr>
            <tr>
                <td class="label">Service Address</td>
                <td><?php echo htmlspecialchars($row['purok']); ?></td>
            </tr>
            <tr>
                <td class="label">Contact #</td>
                <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
            </tr>
        </table>
        
        <div class="section-title">BILLING SUMMARY</div>
        <table class="receipt-table summary-table">
            <tr>
                <td class="label">Bill Date</td>
                <td><?php echo date('d F Y', strtotime($row['reading_date'])); ?></td>
            </tr>
            <tr>
                <td class="label">Consumption</td>
                <td><?php echo number_format($row['total_reading'], 4); ?> cubic meters</td>
            </tr>
            <tr>
                <td class="label">Total Amount Due</td>
                <td style="font-weight:bold;">₱<?php echo number_format($row['total'], 2); ?></td>
            </tr>
            <tr>
                <td class="label">Due Date</td>
                <td><?php echo date('d F Y', strtotime($row['due_date'])); ?></td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td><span class="<?php echo $status_color; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
            </tr>
        </table>

        <div class="section-title">BILLING DETAILS</div>
        <table class="receipt-table details-table">
            <tr>
                <th>Description</th>
                <th class="amount">Amount (₱)</th>
            </tr>
            <tr>
                <td>Previous Reading</td>
                <td class="amount"><?php echo number_format($row['previous_reading'], 4); ?></td>
            </tr>
            <tr>
                <td>Current Reading</td>
                <td class="amount"><?php echo number_format($row['current_reading'], 4); ?></td>
            </tr>
            <tr>
                <td>Rate</td>
                <td class="amount"><?php echo number_format($row['rate'], 2); ?></td>
            </tr>
            <tr>
                <td>Consumption</td>
                <td class="amount"><?php echo number_format($row['total_reading'], 4); ?></td>
            </tr>
            <tr class="total-row">
                <td>Total Bill</td>
                <td class="amount">₱<?php echo number_format($row['total'], 2); ?></td>
            </tr>
        </table>

        <?php if (count($payments) > 0): ?>
        <div class="section-title">PAYMENT HISTORY</div>
        <table class="receipt-table">
            <tr>
                <th>Payment Date</th>
                <th>Amount Paid (₱)</th>
            </tr>
            <?php foreach ($payments as $payment): ?>
            <tr>
                <td><?php echo date('d F Y', strtotime($payment['payment_date'])); ?></td>
                <td><?php echo number_format($payment['amount'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>

        <div class="section-title">METER READING INFORMATION</div>
        <table class="receipt-table meter-table">
            <tr>
                <td class="label">Previous Reading</td>
                <td><?php echo number_format($row['previous_reading'], 4); ?></td>
                <td class="label">Current Reading</td>
                <td><?php echo number_format($row['current_reading'], 4); ?></td>
                <td class="label">Consumption</td>
                <td><?php echo number_format($row['total_reading'], 4); ?></td>
            </tr>
        </table>

        <div class="outstanding-balance">
            Outstanding Balance: ₱<?php echo number_format($outstanding_balance, 2); ?>
        </div>
        <div class="no-print">
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