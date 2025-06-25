<?php 
session_start(); // Add this at the top
// Check session
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
    <title>Water Billing System - Select Billing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="main-content">
        <div class="header">
            <h1 class="title-page text-center py-3 mb-4" style="background: #007bff; color: #fff; border-radius: 8px; letter-spacing:2px; font-weight:700; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                SELECT BILLING
            </h1>
        </div>

        <div class="content">
            <div class="mb-3">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

            <table id="table" class="table table-striped">
                <thead>
                    <tr>
                        <th>Billing ID</th>
                        <th>Customer</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $stmt = mysqli_prepare($conn, 
                    "SELECT b.*, c.purok 
                     FROM billing b 
                     LEFT JOIN customer c ON b.customer_id = c.id 
                     WHERE b.status = 'Pending' 
                     ORDER BY b.due_date ASC"
                );
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                while ($row = mysqli_fetch_array($result)) {
                    $due_date = date('M d, Y', strtotime($row['due_date']));
                    $status_class = ($row['status'] == 'Pending') ? 'text-danger' : 'text-success';
                ?>
                    <tr>
                        <td><?php echo sprintf("BILL-%05d", $row['id']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($row['customer_name']); ?>
                            <br>
                            <small class="text-muted">Purok: <?php echo htmlspecialchars($row['purok']); ?></small>
                        </td>
                        <td>₱<?php echo number_format($row['total'], 2); ?></td>
                        <td>
                            <span class="fw-bold <?php echo $status_class; ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </td>
                        <td><?php echo $due_date; ?></td>
                        <td>
                            <a href="../billing/payment.php?id=<?php echo $row['id']; ?>" 
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-check"></i> Select
                            </a>
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
