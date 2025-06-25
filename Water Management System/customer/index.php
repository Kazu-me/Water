<?php 
session_start();
// Check if user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$page = 'Customer';
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
    <title>Water Billing System - Customer</title>
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
                <a href="add.php" class="btn btn-primary">Add Customer</a>
                <!-- SEARCH FEATURE START -->
                <div class="search-area">
                    <form action="index.php" method="get" class="search-form">
                        <button
                            class="back-arrow-btn"
                            id="backToAll"
                            title="Back to all customers"
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
                        <th>ID</th>
                        <th>Customer Name</th>
                        <th>Purok/Sitio</th>
                        <th>Category</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                // Modified query to handle NULL del_status and 'deleted' status
                // SEARCH FEATURE START
                if (!empty($search)) {
                    $search_like = '%' . $search . '%';
                    $stmt = mysqli_prepare(
                        $conn,
                        "SELECT * FROM customer WHERE (del_status IS NULL OR del_status != 'deleted') AND (
                            id LIKE ? OR
                            first_name LIKE ? OR
                            middle_name LIKE ? OR
                            last_name LIKE ? OR
                            suffix LIKE ?
                        ) ORDER BY id DESC"
                    );
                    mysqli_stmt_bind_param($stmt, "sssss", $search_like, $search_like, $search_like, $search_like, $search_like);
                    mysqli_stmt_execute($stmt);
                    $squery = mysqli_stmt_get_result($stmt);
                } else {
                    $squery = mysqli_query($conn, "SELECT * FROM customer WHERE del_status IS NULL OR del_status != 'deleted' ORDER BY id DESC");
                }
                // SEARCH FEATURE END

                if ($squery) {
                    while ($row = mysqli_fetch_array($squery)) {
                        $customer_name = trim($row['first_name'] . ' ' . 
                                           ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . 
                                           $row['last_name'] . ' ' . 
                                           $row['suffix']);
                ?>
                    <tr>
                        <td>24-<?php echo htmlspecialchars($row['id']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($customer_name); ?>
                            <?php if (isset($row['disconnected']) && $row['disconnected']): ?>
                                <span class="badge bg-danger ms-1">Disconnected</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['purok']); ?></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-info">View</a>
                            <?php if (isset($row['disconnected']) && $row['disconnected']): ?>
                                <form action="undisconnect.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="customer_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-outline-success ms-1">Undisconnect</button>
                                </form>
                            <?php elseif (empty($row['disconnected']) && mysqli_num_rows(mysqli_query($conn, "SELECT id FROM billing WHERE customer_id = '" . $row['id'] . "' AND status = 'Pending' LIMIT 1"))): ?>
                                <a href="disconnect.php?customer_id=<?php echo $row['id']; ?>" class="btn btn-outline-danger ms-1">Disconnect</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='5'>Error loading customers: " . mysqli_error($conn) . "</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/table.js"></script>
</body>
</html>