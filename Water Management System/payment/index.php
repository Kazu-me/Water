<?php 
session_start();
// Check if user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$page = 'Payment';
if(isset($_GET['message'])){
    $message = htmlspecialchars($_GET['message']);
    echo "<script type='text/javascript'>alert('$message');</script>";
}
include_once "../db_conn.php";
include_once "../sidebar.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Water Billing System - Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="main-content">
        <div class="header">
            <h1 class="title-page text-center py-3 mb-4" style="background: #007bff; color: #fff; border-radius: 8px; letter-spacing:2px; font-weight:700; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                <?php echo strtoupper($page === 'Payment' ? 'PAYMENT HISTORY' : htmlspecialchars($page)); ?>
            </h1>
        </div>

        <div class="content">
            <div class="add mb-3">
                <a href="select_billing.php" class="btn btn-primary">Add Payment</a>
            </div>
            <table id="table" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer Name</th>
                        <th>Amount</th>
                        <th>Payment Date</th>
                        <th>Processed By</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $stmt = mysqli_prepare($conn, 
                    "SELECT * FROM payment ORDER BY date_created DESC"
                );
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                while ($row = mysqli_fetch_array($result)) {
                    $processed_by = '';
                    $role = '';
                    $user_field = $row['user'];
                    if (!empty($user_field) && $user_field !== '0') {
                        if (is_numeric($user_field)) {
                            $user_query = mysqli_query($conn, "SELECT username, role FROM user WHERE id='" . intval($user_field) . "' LIMIT 1");
                        } else {
                            $user_query = mysqli_query($conn, "SELECT username, role FROM user WHERE username='" . mysqli_real_escape_string($conn, $user_field) . "' LIMIT 1");
                        }
                        if ($user_query && $user_row = mysqli_fetch_assoc($user_query)) {
                            $role = $user_row['role'];
                            $processed_by = ($role === 'Administrator') ? 'Administration' : htmlspecialchars($user_row['username']);
                        } else {
                            $processed_by = htmlspecialchars($user_field); // fallback to raw value
                        }
                    } else {
                        $processed_by = 'N/A';
                    }
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td>₱<?php echo number_format($row['amount'], 2); ?></td>
                        <td><?php echo date('M d, Y', strtotime($row['date_created'])); ?></td>
                        <td><?php echo $processed_by; ?></td>
                        <td>
                            <a href="printbilling.php?id=<?php echo $row['id']; ?>" 
                               class="btn btn-outline-primary btn-sm">Print</a>
                            <form action="delete_payment.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm" 
                                        onclick="return confirm('Are you sure you want to delete this payment?');">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php 
                }
                mysqli_stmt_close($stmt);
                ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/table.js"></script>
</body>
</html>
