<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}
include_once "../db_conn.php";
include_once "../sidebar.php";

$customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;
$customer = null;
$billing_list = [];
$total_due = 0;
$disconnected = false;

// Handle disconnect action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['disconnect_id'])) {
    $disconnect_id = intval($_POST['disconnect_id']);
    $update = mysqli_query($conn, "UPDATE customer SET disconnected=1 WHERE id='$disconnect_id'");
    if ($update) {
        $disconnected = true;
    }
    // Reload customer info after update
    $customer_id = $disconnect_id;
}

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
        $amount_due = isset($bill['balance']) && $bill['balance'] > 0 ? $bill['balance'] : $bill['total'];
        $billing_list[] = array_merge($bill, ['amount_due' => $amount_due]);
        $total_due += $amount_due;
    }
}
if (!$customer || count($billing_list) === 0) {
    header("Location: index.php?message=No unpaid bills for this customer.");
    exit();
}
$page = 'Customer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Disconnect Customer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="main-content">
    <div class="header">
        <h1 class="title-page text-center py-3 mb-4" style="background: #dc3545; color: #fff; border-radius: 8px; letter-spacing:2px; font-weight:700; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
            DISCONNECT CUSTOMER
        </h1>
    </div>
    <div class="content">
        <?php if ($disconnected): ?>
            <div class="alert alert-success">Customer has been disconnected.</div>
        <?php endif; ?>
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-2">Customer: <?php echo htmlspecialchars(trim($customer['first_name'].' '.$customer['middle_name'].' '.$customer['last_name'].' '.$customer['suffix'])); ?></h5>
                <p class="card-text mb-1">Purok/Sitio: <?php echo htmlspecialchars($customer['purok']); ?></p>
                <p class="card-text mb-1">Category: <?php echo htmlspecialchars($customer['category']); ?></p>
                <p class="card-text mb-1">Phone: <?php echo htmlspecialchars($customer['phone_number']); ?></p>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <strong>Unpaid Bills</strong>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-sm">
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
            </div>
        </div>
        <div class="mb-4">
            <a href="index.php" class="btn btn-secondary">Back</a>
            <?php if (!$disconnected): ?>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="disconnect_id" value="<?php echo $customer['id']; ?>">
                <button type="submit" class="btn btn-danger ms-2">Disconnect</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
