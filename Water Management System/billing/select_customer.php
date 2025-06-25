<?php 
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$page = 'Billing';
if(isset($_GET['message'])){
    $message = htmlspecialchars($_GET['message']);
    echo "<script type='text/javascript'>alert('$message');</script>";
}

include_once "../sidebar.php";
include_once "../db_conn.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Water Billing System - Select Customer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="main-content">
        <div class="header">
            <h1 class="title-page text-center py-3 mb-4" style="background: #007bff; color: #fff; border-radius: 8px; letter-spacing:2px; font-weight:700; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                SELECT CUSTOMER FOR BILLING
            </h1>
        </div>
        <div class="content">
            <a href="index.php" class="btn btn-secondary mb-3">Back</a>
            <table id="table" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer Name</th>
                        <th>Purok/Sitio</th>
                        <th>Category</th>
                        <th>Last Reading</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $stmt = mysqli_prepare($conn, 
                    "SELECT c.*, cat.rate 
                     FROM customer c 
                     LEFT JOIN category cat ON c.category = cat.category_name 
                     WHERE c.del_status IS NULL OR c.del_status != 'deleted'
                     ORDER BY c.id DESC"
                );

                if (!mysqli_stmt_execute($stmt)) {
                    die("Query failed: " . mysqli_error($conn));
                }
                $result = mysqli_stmt_get_result($stmt);

                while ($row = mysqli_fetch_array($result)) {
                    $customer_name = trim($row['first_name'] . ' ' . 
                                       ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . 
                                       $row['last_name'] . ' ' . 
                                       $row['suffix']);
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($customer_name); ?></td>
                        <td><?php echo htmlspecialchars($row['purok']); ?></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td>
                            Reading: <?php echo number_format($row['water_reading'], 4); ?><br>
                            Date: <?php echo $row['latest_reading_date'] ? date('M d, Y', strtotime($row['latest_reading_date'])) : 'No reading'; ?>
                        </td>
                        <td>
                            <a href="add.php?id=<?php echo $row['id']; ?>" 
                               class="btn btn-outline-primary btn-sm">Select</a>
                        </td>
                    </tr>
                <?php } 
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