<?php
session_start();
include_once "db_conn.php";
// Set timezone to your local timezone (e.g., Asia/Manila)
date_default_timezone_set('Asia/Manila');
// Get customer and billing info by GET id
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
    // Get all unpaid bills
    $bill_stmt = mysqli_prepare($conn, "SELECT * FROM billing WHERE customer_id=? AND status='Pending' ORDER BY due_date ASC");
    mysqli_stmt_bind_param($bill_stmt, "i", $customer_id);
    mysqli_stmt_execute($bill_stmt);
    $bill_result = mysqli_stmt_get_result($bill_stmt);
    while ($bill = mysqli_fetch_assoc($bill_result)) {
        // Use balance if available, otherwise fallback to total
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
        body { font-family: 'Arial', sans-serif; background: #f8f9fa; }
        .letter-container {
            width: 100%;
            max-width: 794px; /* A4 width at 96dpi */
            min-height: 1123px; /* A4 height at 96dpi */
            margin: 24px auto;
            background: #fff url('assets/img/logo.png') no-repeat center 70%;
            background-size: 180px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
            padding: 24px 28px 20px 28px;
            border: 1.5px solid #dc3545;
            position: relative;
        }
        .letter-header {
            text-align: left;
            border-bottom: 1.5px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .letter-header img {
            height: 48px;
        }
        .company-details {
            flex: 1;
        }
        .company-title {
            color: #007bff;
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .company-address {
            font-size: 0.98em;
            color: #333;
        }
        .letter-title {
            color: #dc3545;
            font-size: 1.4em;
            font-weight: bold;
            text-align: center;
            letter-spacing: 1.5px;
            margin-bottom: 12px;
            text-shadow: 1px 2px 8px #f8d7da;
        }
        .customer-info {
            background: #f7f7f7;
            border: 1px solid #007bff;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 14px;
            font-size: 0.98em;
        }
        .customer-info strong {
            min-width: 90px;
            display: inline-block;
        }
        .letter-body {
            font-size: 0.98em;
            margin-bottom: 18px;
        }
        .notice-box {
            background: #fff3cd;
            border-left: 4px solid #dc3545;
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
        }
        @media print {
            @page { size: A4; margin: 14mm; }
            body { background: #fff; }
            .no-print { display: none; }
            .letter-container {
                box-shadow: none;
                border: 0;
                width: 100%;
                max-width: 100%;
                min-height: auto;
                margin: 0;
                padding: 0 6mm;
            }
        }
    </style>
</head>
<body>
    <div class="letter-container">
        <div class="letter-header">
            <img src="asset/img/logo.jpg" alt="Company Logo">
            <div class="company-details">
                <div class="company-title">WATER BILLING MANAGEMENT SYSTEM</div>
                <div class="company-address">Barangay Banate, Malungon, Sarangani Province</div>
            </div>
        </div>
        <div class="letter-title">DISCONNECTION NOTICE</div>
        <div class="customer-info mb-3">
            <div><strong>Date:</strong> <?php echo $date_today; ?></div>
            <div><strong>Account Name:</strong> <?php echo $customer ? htmlspecialchars(trim($customer['first_name'].' '.$customer['middle_name'].' '.$customer['last_name'].' '.$customer['suffix'])) : 'N/A'; ?></div>
            <div><strong>Account #:</strong> <?php echo $customer ? htmlspecialchars($customer['id']) : 'N/A'; ?></div>
            <div><strong>Address:</strong> <?php echo $customer ? htmlspecialchars($customer['purok']) : 'N/A'; ?></div>
        </div>
        <div class="letter-body">
            <?php if (count($billing_list) > 0): ?>
            <div class="notice-box">
                <strong>Dear Customer,</strong><br>
                Our records show that you have the following unpaid water bill(s):<br>
                <table class="table table-bordered table-sm mt-3 mb-2" style="background:#fff;">
                    <thead class="table-light">
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
