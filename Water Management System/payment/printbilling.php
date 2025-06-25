<?php 
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$page = 'Payment';
if(isset($_GET['message'])){
    $message = $_GET['message'];
    echo "<script type='text/javascript'>alert('$message');</script>";
}
include_once "../db_conn.php";

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

// Set PHP timezone to Philippines
date_default_timezone_set('Asia/Manila');

// Get payment details with customer information
$stmt = mysqli_prepare($conn, 
    "SELECT p.*, c.first_name, c.middle_name, c.last_name, c.suffix, c.purok 
     FROM payment p 
     LEFT JOIN customer c ON p.customer_id = c.id 
     WHERE p.id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_array($result)) {
    // Construct full name
    $customer_name = $row['first_name'];
    if (!empty($row['middle_name'])) {
        $customer_name .= ' ' . substr($row['middle_name'], 0, 1) . '.';
    }
    $customer_name .= ' ' . $row['last_name'];
    if (!empty($row['suffix'])) {
        $customer_name .= ' ' . $row['suffix'];
    }
?>

<script>
window.onload = function() {
    window.print();
}
window.onafterprint = function() {
    window.location.href = 'index.php';
}
</script>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: 'Arial', monospace, sans-serif;
            background: #fff;
            margin: 0;
            padding: 0;
            font-size: 13px;
        }
        .receipt {
            width: 420px;
            margin: 24px auto 0 auto;
            background: #fff;
            border: 1.5px dashed #bbb;
            border-radius: 8px;
            padding: 18px 18px 10px 18px;
            box-shadow: 0 0 8px rgba(0,0,0,0.09);
        }
        .receipts-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
        }
        .receipt-header {
            margin-bottom: 7px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 12px;
        }
        .receipt-header .logo-img {
            width: 55px;
            height: 55px;
            object-fit: contain;
            border-radius: 50%;
            border: 1.5px solid #007bff;
            background: #fff;
            box-shadow: 0 1px 5px rgba(0,0,0,0.10);
            margin-bottom: 0;
            flex-shrink: 0;
        }
        .receipt-header-text {
            flex: 1;
        }
        .receipt-header-text h3 {
            margin: 0 0 2px 0;
            font-size: 1.23em;
            letter-spacing: 1px;
            color: #007bff;
        }
        .receipt-header-text p {
            margin: 0;
            font-size: 1.02em;
            color: #222;
        }
        .receipt-header-text .copy {
            font-weight: bold;
            font-size: 1em;
            margin-top: 2px;
        }
        .receipt-details {
            margin: 11px 0 10px 0;
            font-size: 1.13em;
        }
        .receipt-details strong {
            display: inline-block;
            width: 110px;
        }
        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .receipt-table th, .receipt-table td {
            padding: 5px 3px;
            font-size: 1.08em;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        .receipt-table th {
            background: #f7f7f7;
            font-weight: bold;
        }
        .receipt-total {
            text-align: right;
            font-size: 1.13em;
            font-weight: bold;
            margin-top: 9px;
        }
        .signature {
            margin-top: 18px;
            text-align: right;
            font-size: 1em;
        }
        .signature-line {
            display: inline-block;
            border-top: 1px solid #000;
            width: 154px;
            margin-top: 8px;
        }
        .footer {
            text-align: center;
            font-size: 0.90em;
            color: #555;
            margin-top: 10px;
        }
        .cut-line-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            margin: 0;
        }
        .cut-line {
            border-top: 1.5px dashed #888;
            margin: 16px 0 16px 0;
            text-align: center;
            font-size: 0.85em;
            color: #888;
            width: 100%;
            position: relative;
        }
        .cut-line span {
            background: #fff;
            position: absolute;
            left: 50%;
            transform: translate(-50%, -50%);
            top: 0;
            padding: 0 12px;
        }
        @media print {
            @page { size: A4; margin: 18mm; }
            body { background: #fff; }
            .receipts-wrapper { page-break-inside: avoid; }
            .receipt, .cut-line-wrapper { page-break-inside: avoid; }
            .cut-line { page-break-after: avoid; }
        }
    </style>
</head>
<body>
<div class="receipts-wrapper">
<?php for ($copy = 0; $copy < 2; $copy++): ?>
    <div class="receipt">
        <div class="receipt-header">
            <img src="../asset/img/logo.jpg" class="logo-img" alt="Logo">
            <div class="receipt-header-text">
                <h3>Water Billing Management System</h3>
                <p>Barangay Banate, Malungon, Sarangani Province</p>
                <div class="copy"><?php echo $copy == 0 ? "Administration's Copy" : "Customer's Copy"; ?></div>
            </div>
        </div>
        <div class="receipt-details">
            <div><strong>Account Name:</strong> <?php echo htmlspecialchars($customer_name); ?></div>
            <div><strong>Account #:</strong> <?php echo htmlspecialchars($row['customer_id']); ?></div>
            <div><strong>Address:</strong> <?php echo htmlspecialchars($row['purok']); ?></div>
            <div><strong>Receipt #:</strong> <?php echo htmlspecialchars($row['id']); ?></div>
            <div><strong>Date:</strong> <?php echo date("F d, Y", strtotime($row['date_created'])); ?></div>
        </div>
        <table class="receipt-table">
            <tr>
                <th>Description</th>
                <th>Amount (₱)</th>
            </tr>
            <tr>
                <td>Payment Received</td>
                <td><?php echo number_format($row['amount'], 2); ?></td>
            </tr>
        </table>
        <div class="receipt-total">
            Total: ₱<?php echo number_format($row['amount'], 2); ?>
        </div>
        <div class="signature">
            <div class="signature-line"></div><br>
            <span>Cashier / Authorized Rep.</span>
        </div>
        <div class="footer">
            Thank you for your payment!
        </div>
    </div>
    <?php if ($copy == 0): ?>
        <div class="cut-line-wrapper">
            <div class="cut-line"><span>------------------- Cut Here -------------------</span></div>
        </div>
    <?php endif; ?>
<?php endfor; ?>
</div>
</body>
</html>
<?php } ?>