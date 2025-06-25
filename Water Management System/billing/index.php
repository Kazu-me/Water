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

include_once "../db_conn.php";
include_once "../sidebar.php";

// SEARCH FEATURE START
$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}
// SEARCH FEATURE END
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Water Billing System - Billing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Small arrow back button styling */
        .back-arrow-btn {
            background: none;
            border: none;
            padding: 0 8px 0 0;
            color: #007bff;
            font-size: 1.25rem;
            cursor: pointer;
            outline: none;
            transition: color 0.2s;
            display: flex;
            align-items: center;
            height: 100%;
        }
        .back-arrow-btn:disabled {
            color: #ccc;
            cursor: not-allowed;
        }
        .search-area {
            max-width: 400px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .search-form {
            display: flex;
            align-items: center;
            width: 100%;
        }
        .search-form input[type="text"] {
            flex: 1 1 auto;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="header">
            <h1 class="title-page text-center py-3 mb-4" style="background: #007bff; color: #fff; border-radius: 8px; letter-spacing:2px; font-weight:700; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                <?php echo strtoupper(htmlspecialchars($page)); ?>
            </h1>
        </div>

        <div class="content">
            <div class="add mb-3 d-flex justify-content-between align-items-center">
                <a href="select_customer.php" class="btn btn-primary">Add Billing</a>
                <!-- SEARCH FEATURE START -->
                <div class="search-area">
                    <form action="index.php" method="get" class="search-form">
                        <button
                            class="back-arrow-btn"
                            id="backToAll"
                            title="Back to all billing"
                            type="button"
                            <?php if (empty($search)) echo 'disabled'; ?>
                            onclick="window.location='index.php';"
                            style="margin-right: 4px;"
                        >
                            <i class="fas fa-arrow-left"></i>
                        </button>
                        <input class="form-control me-2" type="text" name="search" placeholder="Search by ID or Name" value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-outline-secondary" type="submit">Search</button>
                    </form>
                </div>
                <!-- SEARCH FEATURE END -->
            </div>
            <table id="table" class="table table-striped">
                <thead>
                    <tr>
                        <th>Bill #</th>
                        <th>Customer</th>
                        <th>Purok/Sitio</th>
                        <th>Total (₱)</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                // SEARCH FEATURE START
                $query = "SELECT b.*, c.purok, c.disconnected, c.first_name, c.middle_name, c.last_name, c.suffix, (b.total + b.balance) AS total_due 
                    FROM billing b 
                    LEFT JOIN customer c ON b.customer_id = c.id";
                if (!empty($search)) {
                    $search_like = '%' . $search . '%';
                    $query .= " WHERE b.customer_id LIKE ? OR c.first_name LIKE ? OR c.middle_name LIKE ? OR c.last_name LIKE ? OR c.suffix LIKE ?";
                }
                $query .= " ORDER BY b.id DESC";

                $stmt = mysqli_prepare($conn, $query);

                if (!$stmt) {
                    die("Prepare failed: " . mysqli_error($conn));
                }

                if (!empty($search)) {
                    mysqli_stmt_bind_param($stmt, "sssss", $search_like, $search_like, $search_like, $search_like, $search_like);
                }

                if (!mysqli_stmt_execute($stmt)) {
                    die("Execute failed: " . mysqli_error($conn));
                }

                $result = mysqli_stmt_get_result($stmt);

                if (!$result) {
                    die("Get result failed: " . mysqli_error($conn));
                }
                // SEARCH FEATURE END

                while ($row = mysqli_fetch_array($result)) {
                    $status_color = ($row['status'] == 'Paid') ? 'text-success' : 'text-danger';
                    $bill_number = sprintf("24-%04d", $row['id']);
                    $is_disconnected = isset($row['disconnected']) && $row['disconnected'];
                    $customer_name = trim(
                        ($row['first_name'] ?? '') . ' ' .
                        (!empty($row['middle_name']) ? $row['middle_name'] . ' ' : '') .
                        ($row['last_name'] ?? '') . ' ' .
                        ($row['suffix'] ?? '')
                    );
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($bill_number); ?></td>
                    <td><?php echo htmlspecialchars($row['customer_id'] . " - " . $customer_name); ?></td>
                    <td><?php echo htmlspecialchars($row['purok']); ?></td>
                    <td><?php echo htmlspecialchars($row['total']); ?></td>
                    <td class="fw-bold">
                        <?php if ($is_disconnected): ?>
                            <span class="badge bg-danger">Disconnected</span>
                        <?php else: ?>
                            <span class="<?php echo $status_color; ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($row['due_date'])); ?></td>
                    <td>
                        <?php if ($is_disconnected): ?>
                            <form action="undisconnect.php" method="POST" style="display:inline;">
                                <input type="hidden" name="customer_id" value="<?php echo $row['customer_id']; ?>">
                                <button type="submit" class="btn btn-outline-success btn-sm">Undisconnect</button>
                            </form>
                            <a href="print_disconnected.php?customer_id=<?php echo $row['customer_id']; ?>" class="btn btn-outline-primary btn-sm">Print</a>
                            <form action="delete_billing.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm" 
                                        onclick="return confirm('Are you sure you want to delete this billing record?');">
                                    Delete
                                </button>
                            </form>
                        <?php elseif($row['status'] == 'Pending'): ?>
                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-info btn-sm">Edit</a>
                            <a href="payment.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-success btn-sm">Payment</a>
                            <a href="../disconnection_letter.php?customer_id=<?php echo $row['customer_id']; ?>" class="btn btn-outline-danger btn-sm">Disconnection Notice</a>
                            <a href="print_billing.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-primary btn-sm">Print</a>
                            <form action="delete_billing.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm" 
                                        onclick="return confirm('Are you sure you want to delete this billing record?');">
                                    Delete
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="print_billing.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-primary btn-sm">Print</a>
                            <form action="delete_billing.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm" 
                                        onclick="return confirm('Are you sure you want to delete this billing record?');">
                                    Delete
                                </button>
                            </form>
                        <?php endif; ?>
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