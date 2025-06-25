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
        html, body { height: 100%; }
        body { font-family: 'Arial', sans-serif; background: #f8f9fa; font-size: 1.12em; }
        .letter-container {
            max-width: 700px;
            margin: 28px auto 0 auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 0 16px rgba(0,0,0,0.10);
            padding: 36px 44px 32px 44px;
            border-top: 8px solid #dc3545;
            position: relative;
            page-break-after: avoid;
            page-break-before: avoid;
            page-break-inside: avoid;
        }
        .header-area {
            margin-bottom: 22px;
            margin-top: 0px;
        }
        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0 10px;
        }
        .logo-img {
            height: 70px;
            width: 70px;
            object-fit: contain;
            background: #fff;
            border-radius: 100%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.09);
            border: 2px solid #007bff;
            padding: 3px;
        }
        .letter-header {
            flex: 1 1 0%;
            text-align: center;
            margin-bottom: 0;
        }
        .letter-header h3 {
            margin-bottom: 0;
            font-weight: 800;
            color: #007bff;
            letter-spacing: 1.1px;
            font-size: 1.25em;
        }
        .letter-header p {
            margin-bottom: 0;
            color: #333;
            font-size: 1.07em;
        }
        .letter-title {
            color: #dc3545;
            font-size: 1.4em;
            font-weight: bold;
            margin-bottom: 14px;
            letter-spacing: 1.3px;
            margin-top: 0;
        }
        .letter-body {
            font-size: .9em;
            margin-bottom: 60px;
            color: #222;
            margin-top: 70px; /* ADDED FOR SPACE BELOW HEADER */
        }
        .notice-box {
            background: #fff3cd;
            border-left: 5px solid #dc3545;
            border-radius: 5px;
            padding: 14px 18px;
            margin-bottom: 50px;
            font-size: 1.06em;
            color: #856404;
        }
        .signature {
            margin-top: 44px;
            text-align: right;
        }
        .signature-line {
            display: inline-block;
            border-top: 1.5px solid #000;
            width: 120px;
            margin-top: 10px;
        }
        .signature-label {
            display: inline-block;
            text-align: right;
            font-size: .8em;
            font-weight: 500;
            width: 190px;
            margin-top: .1px;
        }
        @media print {
            html, body { height: auto; }
            .no-print { display: none; }
            .letter-container { 
                box-shadow: none; 
                border-top: 0; 
                page-break-after: avoid !important;
                page-break-before: avoid !important;
                page-break-inside: avoid !important;
                max-width: 100%;
                margin: 0 auto;
            }
            .logo-img { box-shadow: none; border: 1px solid #007bff; }
            body { font-size: 1.08em; }
            .letter-title { font-size: 1.23em; margin-bottom: 10px; }
        }
        @media print {
            body, html {
                overflow: hidden !important;
            }
            .letter-container {
                max-height: 98vh !important;
                overflow: hidden !important;
            }
        }
    </style>
</head>
<body>
    <div class="letter-container">
        <div class="header-area">
            <div class="header-flex">
                <img src="../asset/img/logo.jpg" class="logo-img" alt="Logo 1">
                <div class="letter-header">
                    <h3>WATER BILLING MANAGEMENT SYSTEM</h3>
                    <p>Barangay Banate, Malungon, Sarangani Province</p>
                </div>
                <img src="../asset/img/logo2.png" class="logo-img" alt="Logo 2">
            </div>
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
            <span class="signature-line"></span><br>
            <span class="signature-label">Authorized Officer</span>
        </div>
        <div class="no-print text-center mt-4">
            <button onclick="window.print()" class="btn btn-danger">Print Notice</button>
            <a href="index.php" class="btn btn-secondary ms-2">Back</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>