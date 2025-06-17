<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}
include_once "../db_conn.php";
$customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;
$customer = null;
if ($customer_id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM customer WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $customer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $customer = mysqli_fetch_assoc($result);
}
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
            max-width: 700px;
            margin: 40px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 0 16px rgba(0,0,0,0.10);
            padding: 40px 48px 32px 48px;
            border-top: 8px solid #dc3545;
        }
        .letter-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .letter-header h3 {
            margin-bottom: 0;
            font-weight: 800;
            color: #007bff;
            letter-spacing: 1.5px;
        }
        .letter-header p {
            margin-bottom: 0;
            color: #333;
        }
        .letter-title {
            color: #dc3545;
            font-size: 2.1em;
            font-weight: bold;
            margin-bottom: 18px;
            letter-spacing: 2px;
        }
        .letter-body {
            font-size: 1.13em;
            margin-bottom: 36px;
            color: #222;
        }
        .notice-box {
            background: #fff3cd;
            border-left: 6px solid #dc3545;
            border-radius: 6px;
            padding: 18px 22px;
            margin-bottom: 28px;
            font-size: 1.08em;
            color: #856404;
        }
        .signature {
            margin-top: 60px;
            text-align: right;
        }
        .signature-line {
            display: inline-block;
            border-top: 1.5px solid #000;
            width: 220px;
            margin-top: 30px;
        }
        @media print {
            .no-print { display: none; }
            .letter-container { box-shadow: none; border-top: 0; }
        }
    </style>
</head>
<body>
    <div class="letter-container">
        <div class="letter-header">
            <h3>WATER BILLING MANAGEMENT SYSTEM</h3>
            <p>Barangay Banate, Malungon, Sarangani Province</p>
        </div>
        <div class="letter-title text-center">DISCONNECTION NOTICE</div>
        <div class="letter-body">
            <p>Dear <?php echo $customer ? htmlspecialchars(trim($customer['first_name'].' '.$customer['middle_name'].' '.$customer['last_name'].' '.$customer['suffix'])) : 'Customer'; ?>,</p>
            <div class="notice-box">
                <strong>Subject: Disconnection of Water Service Due to Outstanding Balance</strong><br><br>
                We regret to inform you that your water service has been <span style="color:#dc3545;font-weight:bold;">disconnected</span> due to non-payment of your outstanding balance. To restore your water service, please settle your account in full at our office.<br><br>
                If you have already made payment, kindly disregard this notice. For any questions or clarifications, please contact our office during business hours.
            </div>
            <p>Thank you for your prompt attention to this matter.</p>
        </div>
        <div class="signature">
            <div class="signature-line"></div><br>
            <span>Administration / Staff Signature</span>
        </div>
        <div class="no-print text-center mt-4">
            <button onclick="window.print()" class="btn btn-danger">Print Notice</button>
            <a href="index.php" class="btn btn-secondary ms-2">Back</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
