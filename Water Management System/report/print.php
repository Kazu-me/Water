<?php 
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$page = 'Report';
if(isset($_GET['message'])){
    $message = htmlspecialchars($_GET['message']);
    echo "<script type='text/javascript'>alert('$message');</script>";
}
include_once "../db_conn.php";

$status = $_GET['status'] ?? 'All';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

// Join with customer table to get full customer details
$query = "SELECT b.*, 
          c.first_name, c.middle_name, c.last_name, c.suffix, c.purok 
          FROM billing b 
          LEFT JOIN customer c ON b.customer_id = c.id 
          WHERE 1";

if ($status != 'All') {
    $query .= " AND b.status = '" . mysqli_real_escape_string($conn, $status) . "'";
}
if (!empty($from) && !empty($to)) {
    $query .= " AND b.due_date BETWEEN '" . mysqli_real_escape_string($conn, $from) . "' 
                AND '" . mysqli_real_escape_string($conn, $to) . "'";
}
$query .= " ORDER BY b.due_date ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Print Report</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <style>
        body { font-family: 'Arial', sans-serif; }
        .logo1, .logo2 { height: 70px; }
        .report-header { margin-bottom: 30px; }
        .table th { background-color: #f8f9fa; }
        .status-paid { color: green; font-weight: bold; }
        .status-pending { color: red; font-weight: bold; }
        @media print {
            .table { border-collapse: collapse; width: 100%; }
            .table th, .table td { border: 1px solid #ddd; padding: 8px; }
            .report-info { margin: 20px 0; }
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="report-header text-center">
            <div class="d-flex justify-content-center align-items-center gap-4">
                <img class="logo1" src="../assets/img/logo.png" alt="Logo 1">
                <div>
                    <h4 class="mb-0">Water Billing Management System</h4>
                    <p class="mb-0">Barangay Banate Malungon Sarangani Province</p>
                </div>
                <img class="logo2" src="../assets/img/logo2.png" alt="Logo 2">
            </div>
        </div>

        <div class="report-info">
            <p><strong>Period:</strong> <?php echo date("F d, Y", strtotime($from)) ?> to <?php echo date("F d, Y", strtotime($to)) ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($status) ?></p>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Bill No.</th>
                    <th>Customer Name</th>
                    <th>Purok/Sitio</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Due Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_amount = 0;
                while ($row = mysqli_fetch_array($result)) {
                    $total_amount += $row['total'];
                    // Construct full name
                    $full_name = $row['first_name'];
                    if (!empty($row['middle_name'])) {
                        $full_name .= ' ' . substr($row['middle_name'], 0, 1) . '.';
                    }
                    $full_name .= ' ' . $row['last_name'];
                    if (!empty($row['suffix'])) {
                        $full_name .= ' ' . $row['suffix'];
                    }
                ?>
                    <tr>
                        <td><?php echo sprintf("BILL-%05d", $row['id']); ?></td>
                        <td><?php echo htmlspecialchars($full_name); ?></td>
                        <td><?php echo htmlspecialchars($row['purok']); ?></td>
                        <td>₱<?php echo number_format($row['total'], 2); ?></td>
                        <td class="<?php echo ($row['status'] == 'Paid') ? 'status-paid' : 'status-pending'; ?>">
                            <?php echo htmlspecialchars($row['status']); ?>
                        </td>
                        <td><?php echo date("M d, Y", strtotime($row['due_date'])); ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                    <td colspan="3"><strong>₱<?php echo number_format($total_amount, 2); ?></strong></td>
                </tr>
            </tbody>
        </table>

        <div class="mt-4">
            <p><strong>Printed by:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
            <p><strong>Date Printed:</strong> <?php echo date("F d, Y h:i A"); ?></p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
        window.onafterprint = function() {
            window.location.href = 'index.php';
        }
    </script>
</body>
</html>
