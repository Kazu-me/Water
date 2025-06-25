<?php
session_start();
include_once "db_conn.php";
date_default_timezone_set('Asia/Manila');
$customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;
$customer = null;
$billing_list = [];
$total_due = 0;
$earliest_due = null;
if ($customer_id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM customer WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $customer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $customer = mysqli_fetch_assoc($result);
    $bill_stmt = mysqli_prepare($conn, "SELECT * FROM billing WHERE customer_id=? AND status='Pending' ORDER BY due_date ASC");
    mysqli_stmt_bind_param($bill_stmt, "i", $customer_id);
    mysqli_stmt_execute($bill_stmt);
    $bill_result = mysqli_stmt_get_result($bill_stmt);
    while ($bill = mysqli_fetch_assoc($bill_result)) {
        $amount_due = isset($bill['balance']) && $bill['balance'] > 0 ? $bill['balance'] : $bill['total'];
        $billing_list[] = array_merge($bill, ['amount_due' => $amount_due]);
        $total_due += $amount_due;
        if (!$earliest_due || strtotime($bill['due_date']) < strtotime($earliest_due)) {
            $earliest_due = $bill['due_date'];
        }
    }
}
$date_today = date('F d, Y h:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Disconnection Notice</title>
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
            display: flex;
            align-items: center;
            background: #f4fcfd;
            padding: 24px 10px 10px 10px;
            border-bottom: 2px solid #20b9c1;
            position: relative;
        }
        .company-header .logo-col {
            flex: 0 0 140px;
            text-align: center;
        }
        .company-header .logo-col img {
            width: 130px;
            height: 130px;
            object-fit: contain;
            background: #fff;
            border-radius: 8px;
        }
        .company-header .details-col {
            flex: 1 1 0%;
            padding-left: 18px;
            text-align: left;
        }
        .company-header .company-details {
            font-size: 1.18em;
            color: #222;
            margin-bottom: 10px;
            margin-top: 2px;
        }
        .company-header .company-title {
            margin-top: 10px;
        }
        .company-header .company-title h2 {
            color:rgb(204, 37, 56);
            font-size: 2.5em;
            margin: 0 0 3px 0;
            font-weight: bold;
            letter-spacing: .5px;
            line-height: 1;
        }
        .company-header .company-title h4 {
            color: #20b9c1;
            font-size: 1.28em;
            margin: 0;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .section-title {
            color: #20b9c1;
            font-weight: bold;
            font-size: 1em;
            margin: 16px 0 5px 0;
            letter-spacing: 1px;
            border-bottom: 1px dashed #20b9c1;
        }
        table.receipt-table, .info-table {
            width: 98%;
            margin: 0 auto 10px auto;
            font-size: 0.96em;
        }
        table.receipt-table td,
        table.receipt-table th,
        .info-table td,
        .info-table th {
            padding: 3px 4px;
        }
        table.receipt-table th,
        .info-table th {
            color: #20b9c1;
            background: #f4fcfd;
            font-weight: bold;
            border-bottom: 1px solid #c9f3f7;
        }
        .info-table .label {
            white-space: nowrap;
            font-weight: bold;
            color: #222;
        }
        .info-table .result {
            color: #2c3e50;
            font-weight: normal;
            min-width: 520px;
            display: inline-block;
            font-size: .79em;
            letter-spacing: 0.5px;
            
        }
        .letter-body {
            font-size: 0.98em;
            margin-bottom: 18px;
        }
        .notice-box {
            background: #fff3cd;
            border-left: 4px;
            border-radius: 5px;
            padding: 10px 14px;
            margin-bottom: 16px;
            font-size: 0.97em;
            color: #856404;
        }
        .letter-footer {
            margin-top: 18px;
            font-size: 0.95em;
            color: #555;
        }
        .signature {
            margin-top: 32px;
            text-align: right;
        }
        .signature-line {
            display: inline-block;
            border-top: 1px solid #000;
            width: 160px;
            margin-top: 18px;
        }
        .table {
            font-size: 0.97em;
            background: #fff;
        }
        .table th {
            background: #e3f7fa;
            color: #117a8b;
        }
        .table-warning {
            background: #fff8ee !important;
        }
        .no-print {
            text-align: center;
            margin-top: 12px;
        }
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
            .company-header .logo-col img {
                height: 85px !important;
                width: 85px !important;
            }
            .company-header .company-title h2 { font-size: 1.3em !important; }
            .company-header .company-title h4 { font-size: 1.05em !important; }
            .section-title { font-size: 0.95em !important; }
            .no-print { display: none !important; }
        }
        @media screen and (max-width: 900px) {
            .receipt-container {
                width: 100vw;
                max-width: unset;
                padding: 0 2vw 20px 2vw;
            }
            .company-header .logo-col img { height:60px; width:60px; }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <img src="asset/img/logo.jpg" class="watermark" alt="Watermark">
        <div class="company-header">
            <div class="logo-col">
                <img src="asset/img/logo.jpg" alt="Company Logo">
            </div>
            <div class="details-col">
                <div class="company-details">
                    BANATE WATER COMPANY, INC.<br>
                    Purok 1, Malungon, Sarangani, Philippines<br>
                    TIN 005-038-428-000 VAT
                </div>
                <div class="company-title">
                    <h2>DISCONNECTION NOTICE</h2>
                    <h4>Water Billing Management System</h4>
                </div>
            </div>
        </div>
        <table class="info-table mb-3">
            <tr>
                <td class="label">Date:</td>
                <td class="result"><?php echo $date_today; ?></td>
            </tr>
            <tr>
                <td class="label">Account Name:</td>
                <td class="result"><?php echo $customer ? htmlspecialchars(trim($customer['first_name'].' '.$customer['middle_name'].' '.$customer['last_name'].' '.$customer['suffix'])) : 'N/A'; ?></td>
            </tr>
            <tr>
                <td class="label">Account #:</td>
                <td class="result"><?php echo $customer ? htmlspecialchars($customer['id']) : 'N/A'; ?></td>
            </tr>
            <tr>
                <td class="label">Address:</td>
                <td class="result"><?php echo $customer ? htmlspecialchars($customer['purok']) : 'N/A'; ?></td>
            </tr>
        </table>
        <div class="letter-body">
            <?php if (count($billing_list) > 0): ?>
            <div class="notice-box">
                <strong>Dear Customer,</strong><br>
                Our records show that you have the following unpaid water bill(s):<br>
                <table class="table table-bordered table-sm mt-3 mb-2" style="background:#fff;">
                    <thead>
                        <tr>
                            <th>Bill #</th>
                            <th>Due Date</th>
                            <th>Amount Due (₱)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($billing_list as $bill): ?>
                        <tr>
                            <td><?php echo '24-' . htmlspecialchars($bill['id']); ?></td>
                            <td><?php echo date('F d, Y', strtotime($bill['due_date'])); ?></td>
                            <td><?php echo number_format($bill['amount_due'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="table-warning">
                            <td colspan="2" class="text-end"><strong>Total Amount Due:</strong></td>
                            <td><strong>₱<?php echo number_format($total_due, 2); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
                <br>
                Please be advised that unless payment is made within <strong>3 days</strong> from the date of this notice, your water service will be <span style="color:#dc3545;font-weight:bold;">disconnected</span> without further notice.<br>
                To avoid disconnection and additional charges, kindly settle your account promptly at our office.
            </div>
            <?php else: ?>
            <div class="notice-box">
                <strong>Dear Customer,</strong><br>
                This is a formal notice regarding your account. Please contact our office for more information.
            </div>
            <?php endif; ?>
        </div>
        <div class="letter-footer">
            <p>For inquiries or clarifications, please contact our office during business hours.</p>
        </div>
        <div class="signature">
            <div class="signature-line"></div><br>
            <span>Authorized Officer</span>
        </div>
        <div class="no-print text-center mt-4">
            <button onclick="window.print()" class="btn btn-danger">Print Disconnection Notice</button>
            <button onclick="window.location.href='billing/index.php'" class="btn btn-secondary ms-2">Back</button>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
if (isset($stmt)) mysqli_stmt_close($stmt);
if (isset($bill_stmt)) mysqli_stmt_close($bill_stmt);
?>