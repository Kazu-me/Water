<?php 
session_start();
// Check if user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$page = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Billing System - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Add gap between summary cards */
        .dashboard-cards .card {
            margin-bottom: 16px;
        }
        @media (min-width: 992px) {
            .dashboard-cards > .col-lg-2 {
                margin-right: 24px;
            }
            .dashboard-cards > .col-lg-2:last-child {
                margin-right: 0;
            }
        }
        @media (max-width: 991.98px) {
            .dashboard-cards > [class^="col-"] {
                margin-bottom: 20px;
            }
        }
        /* Make summary numbers larger and centered */
        .dashboard-stat-number {
            font-size: 2.6rem !important;
            line-height: 1.1;
            text-align: center;
            width: 100%;
        }
        @media (max-width: 575.98px) {
            .dashboard-stat-number {
                font-size: 2rem !important;
            }
        }
    </style>
</head>
<body class="dashboard-page">

<?php
include "../db_conn.php";
include "../sidebar.php";

if(isset($_GET['message'])){
    $message = htmlspecialchars($_GET['message']);
    echo "<script type='text/javascript'>alert('$message');</script>";
}

// Fetch counts for dashboard cards with error handling
try {
    // Active Customers Count
    $customer_query = "SELECT COUNT(id) as count FROM customer WHERE del_status IS NULL OR del_status != 'deleted'";
    $squery1 = mysqli_query($conn, $customer_query);
    if (!$squery1) throw new Exception(mysqli_error($conn));
    $customer = mysqli_fetch_array($squery1);

    // Disconnected Customers Count
    $disconnected_query = "SELECT COUNT(id) as disconnected_count FROM customer WHERE disconnected = 1 AND (del_status IS NULL OR del_status != 'deleted')";
    $squery_disc = mysqli_query($conn, $disconnected_query);
    if (!$squery_disc) throw new Exception(mysqli_error($conn));
    $disconnected = mysqli_fetch_array($squery_disc);

    // Total Billings Count
    $billing_query = "SELECT 
        COUNT(id) as total_count,
        SUM(CASE WHEN status = 'Paid' THEN 1 ELSE 0 END) as paid_count,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_count,
        SUM(total) as total_amount
        FROM billing";
    $squery2 = mysqli_query($conn, $billing_query);
    if (!$squery2) throw new Exception(mysqli_error($conn));
    $billing = mysqli_fetch_array($squery2);

    // Fix: Get the real total paid amount from payment table (sum all payments)
    $paid_amount_query = "SELECT SUM(amount) as paid_amount FROM payment";
    $paid_amount_result = mysqli_query($conn, $paid_amount_query);
    if (!$paid_amount_result) throw new Exception(mysqli_error($conn));
    $paid_amount_row = mysqli_fetch_array($paid_amount_result);
    $billing['paid_amount'] = $paid_amount_row['paid_amount'] ?? 0;

    // Get current month's data
    $current_month_query = "SELECT 
        COUNT(id) as bill_count,
        SUM(total) as total_amount
        FROM billing 
        WHERE MONTH(reading_date) = MONTH(CURRENT_DATE())
        AND YEAR(reading_date) = YEAR(CURRENT_DATE())";
    $current_month_result = mysqli_query($conn, $current_month_query);
    if (!$current_month_result) throw new Exception(mysqli_error($conn));
    $current_month = mysqli_fetch_array($current_month_result);

} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error loading dashboard data: " . htmlspecialchars($e->getMessage()) . "</div>";
    $customer = ['count' => 0];
    $disconnected = ['disconnected_count' => 0];
    $billing = ['total_count' => 0, 'paid_count' => 0, 'pending_count' => 0, 'total_amount' => 0, 'paid_amount' => 0];
    $current_month = ['bill_count' => 0, 'total_amount' => 0];
}
?>

<div class="main-content">
    <div class="header">
        <h1 class="title-page text-center py-4 mb-3 fs-2" style="background: linear-gradient(90deg, #007bff 60%, #6dd5ed 100%); color: #fff; border-radius: 16px; letter-spacing:2px; font-weight:800; box-shadow:0 4px 16px rgba(0,0,0,0.10); font-size:2.2rem; padding:1.2rem 0; margin-bottom:1.2rem;">
            <?php echo strtoupper(htmlspecialchars($page)); ?>
        </h1>
        <div class="text-end pe-2 pb-2">
            <span class="badge bg-secondary" style="font-size:0.9rem;">Last Updated: <?php echo date('M d, Y h:i A'); ?></span>
        </div>
    </div>
    <div class="content">
        <!-- Summary Cards -->
        <div class="row my-2 dashboard-cards g-0">
            <div class="col-12 col-sm-6 col-lg-2 mb-3">
                <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #007bff 70%, #6dd5ed 100%); color: #fff; border-radius: 12px;">
                    <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <h2 class="mb-1 fw-bold dashboard-stat-number"><?php echo number_format($customer['count']); ?></h2>
                        <h6 class="card-title mb-0 fs-6 text-center" style="font-size:1rem;">Active Customers</h6>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-2 mb-3">
                <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #dc3545 70%, #ffb3b3 100%); color: #fff; border-radius: 12px;">
                    <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center">
                        <i class="fas fa-user-slash fa-2x mb-2"></i>
                        <h2 class="mb-1 fw-bold dashboard-stat-number"><?php echo number_format($disconnected['disconnected_count']); ?></h2>
                        <h6 class="card-title mb-0 fs-6 text-center" style="font-size:1rem;">Disconnected Customers</h6>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-2 mb-3">
                <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #28a745 70%, #a8e063 100%); color: #fff; border-radius: 12px;">
                    <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <h2 class="mb-1 fw-bold dashboard-stat-number"><?php echo number_format($billing['paid_count']); ?></h2>
                        <h6 class="card-title mb-0 fs-6 text-center" style="font-size:1rem;">Paid Bills</h6>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-2 mb-3">
                <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #ffc107 70%, #fff6a3 100%); color: #333; border-radius: 12px;">
                    <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center">
                        <i class="fas fa-clock fa-2x mb-2"></i>
                        <h2 class="mb-1 fw-bold dashboard-stat-number"><?php echo number_format($billing['pending_count']); ?></h2>
                        <h6 class="card-title mb-0 fs-6 text-center" style="font-size:1rem;">Pending Bills</h6>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-2 mb-3">
                <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #17a2b8 70%, #6dd5ed 100%); color: #fff; border-radius: 12px;">
                    <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center">
                        <i class="fas fa-peso-sign fa-2x mb-2"></i>
                        <h2 class="mb-1 fw-bold dashboard-stat-number">₱<?php echo number_format($billing['paid_amount'], 2); ?></h2>
                        <h6 class="card-title mb-0 fs-6 text-center" style="font-size:1rem;">Total Collections</h6>
                    </div>
                </div>
            </div>
        </div>
        <!-- Monthly Summary -->
        <div class="row mb-2">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 10px;">
                    <div class="card-header bg-primary text-white" style="border-radius: 10px 10px 0 0; padding:0.2rem 0.5rem;">
                        <h5 class="card-title mb-0 fs-6" style="font-size:0.8rem;">Current Month Summary</h5>
                    </div>
                    <div class="card-body p-2" style="padding:0.5rem 0.7rem !important;">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1" style="font-size:0.8rem;">Total Bills: <span class="fw-bold"><?php echo number_format($current_month['bill_count']); ?></span></p>
                                <p class="mb-1" style="font-size:0.8rem;">Total Amount: <span class="fw-bold">₱<?php echo number_format($current_month['total_amount'], 2); ?></span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1" style="font-size:0.8rem;">Collection Rate: 
                                    <span class="fw-bold">
                                    <?php 
                                    $collection_rate = $billing['total_count'] > 0 
                                        ? ($billing['paid_count'] / $billing['total_count']) * 100 
                                        : 0;
                                    echo number_format($collection_rate, 1) . '%';
                                    ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Charts -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-header bg-info text-white" style="border-radius: 12px 12px 0 0; padding:0.5rem 1rem;">
                        <h5 class="card-title mb-0 fs-5" style="font-size:1.1rem;">Monthly Collections</h5>
                    </div>
                    <div class="card-body p-3">
                        <canvas id="collectionsChart" style="max-height:160px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-header bg-success text-white" style="border-radius: 12px 12px 0 0; padding:0.5rem 1rem;">
                        <h5 class="card-title mb-0 fs-5" style="font-size:1.1rem;">Payment Status</h5>
                    </div>
                    <div class="card-body p-3">
                        <canvas id="statusChart" style="max-height:160px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php
// Prepare monthly collections data
$collections_query = "SELECT 
    DATE_FORMAT(date_created, '%Y-%m') as month,
    SUM(amount) as amount
    FROM payment
    GROUP BY DATE_FORMAT(date_created, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12";
$collections_result = mysqli_query($conn, $collections_query);
$months = [];
$amounts = [];

while ($row = mysqli_fetch_assoc($collections_result)) {
    $months[] = date('M Y', strtotime($row['month']));
    $amounts[] = $row['amount'];
}
?>

<script>
// Monthly Collections Chart
new Chart(document.getElementById('collectionsChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_reverse($months)); ?>,
        datasets: [{
            label: 'Monthly Collections',
            data: <?php echo json_encode(array_reverse($amounts)); ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₱' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Payment Status Chart
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Paid', 'Pending', 'Disconnected'],
        datasets: [{
            data: [
                <?php echo $billing['paid_count']; ?>,
                <?php echo $billing['pending_count']; ?>,
                <?php echo $disconnected['disconnected_count']; ?>
            ],
            backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

</body>
</html>