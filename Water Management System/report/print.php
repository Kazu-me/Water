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

// GET FILTER PARAMETERS
$status = $_GET['status'] ?? 'All';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$search = trim($_GET['search'] ?? '');

// Join with customer table to get full customer details
$query = "SELECT b.*, 
          c.first_name, c.middle_name, c.last_name, c.suffix, c.purok 
          FROM billing b 
          LEFT JOIN customer c ON b.customer_id = c.id 
          WHERE 1";

// Apply status filter
if ($status != 'All') {
    $query .= " AND b.status = '" . mysqli_real_escape_string($conn, $status) . "'";
}

// Apply date filter
if (!empty($from) && !empty($to)) {
    $query .= " AND b.due_date BETWEEN '" . mysqli_real_escape_string($conn, $from) . "' 
                AND '" . mysqli_real_escape_string($conn, $to) . "'";
}

// Apply search filter (by customer_id, customer_name, or due_date YYYY-MM)
if (!empty($search)) {
    $search_like = '%' . mysqli_real_escape_string($conn, $search) . '%';
    $query .= " AND (b.customer_id LIKE '$search_like' 
                OR b.customer_name LIKE '$search_like' 
                OR DATE_FORMAT(b.due_date, '%Y-%m') LIKE '$search_like')";
}

$query .= " ORDER BY b.due_date ASC";
$result = mysqli_query($conn, $query);

// Calculate the actual period based on the data in $result
$min_due_date = null;
$max_due_date = null;
$total_amount = 0;
$rows = [];
while ($row = mysqli_fetch_array($result)) {
    $rows[] = $row;
    $total_amount += $row['total'];
    $due_date = $row['due_date'];
    if ($min_due_date === null || $due_date < $min_due_date) {
        $min_due_date = $due_date;
    }
    if ($max_due_date === null || $due_date > $max_due_date) {
        $max_due_date = $due_date;
    }
}

// Set PH timezone for date printed
date_default_timezone_set('Asia/Manila');
$ph_date_printed = date("F d, Y h:i A");
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
        .header-area {
            margin-bottom: 32px;
            margin-top: 24px;
        }
        .header-grid {
            display: grid;
            grid-template-columns: 120px 1fr 120px;
            align-items: center;
            justify-items: center;
            gap: 0 24px;
        }
        .logo1, .logo2 {
            height: 90px;
            width: 90px;
            object-fit: contain;
            background: #fff;
            border-radius: 100%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.10);
            padding: 6px;
            border: 2px solid #007bff;
        }
        .header-main {
            text-align: center;
        }
        .header-highlight {
            font-weight: bold;
            font-size: 2rem;
            color: #007bff;
            letter-spacing: 1.5px;
            margin-bottom: 0.2rem;
        }
        .header-location {
            font-size: 1.08rem;
            color: #343a40;
            font-weight: 600;
            margin-bottom: 0;
        }
        .table th { background-color: #f8f9fa; }
        .status-paid { color: green; font-weight: bold; }
        .status-pending { color: red; font-weight: bold; }
        @media print {
            .header-area { margin-top: 0; }
            .logo1, .logo2 {
                box-shadow: none;
                border: 1px solid #007bff;
            }
            .table { border-collapse: collapse; width: 100%; }
            .table th, .table td { border: 1px solid #ddd; padding: 8px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-area">
            <div class="header-grid">
                <img class="logo1" src="../asset/img/logo.jpg" alt="Logo 1">
                <div class="header-main">
                    <div class="header-highlight">Water Billing Management System</div>
                    <div class="header-location">Barangay Banate</div>
                    <div class="header-location">Malungon, Sarangani Province</div>
                </div>
                <img class="logo2" src="../asset/img/logo2.png" alt="Logo 2">
            </div>
        </div>

        <div class="report-info mb-3">
            <p>
                <strong>Period:</strong>
                <?php
                if ($min_due_date !== null && $max_due_date !== null) {
                    echo date("F d, Y", strtotime($min_due_date)) . " to " . date("F d, Y", strtotime($max_due_date));
                } else {
                    echo "---";
                }
                ?>
            </p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($status) ?></p>
            <?php if(!empty($search)): ?>
                <p><strong>Search:</strong> <?php echo htmlspecialchars($search); ?></p>
            <?php endif; ?>
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
                <?php if(count($rows) > 0): ?>
                    <?php foreach ($rows as $row): ?>
                        <?php
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
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                        <td colspan="3"><strong>₱<?php echo number_format($total_amount, 2); ?></strong></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="mt-4">
            <p><strong>Printed by:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
            <p><strong>Date Printed:</strong> <?php echo $ph_date_printed; ?></p>
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