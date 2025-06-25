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
include_once "../sidebar.php";

// SEARCH FEATURE START
$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}
$can_print = false;
// SEARCH FEATURE END

// Build query based on search
$filter_query = "SELECT * FROM billing WHERE 1";
$params = [];
$types = '';

if (!empty($search)) {
    $search_like = '%' . $search . '%';
    $filter_query .= " AND (customer_id LIKE ? OR customer_name LIKE ? OR DATE_FORMAT(due_date, '%Y-%m') LIKE ?)";
    $params = [$search_like, $search_like, $search_like];
    $types = 'sss';
}

$filter_query .= " ORDER BY id DESC";

// Use prepared statements for safety
if (!empty($search)) {
    $stmt = mysqli_prepare($conn, $filter_query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $query = mysqli_stmt_get_result($stmt);
    $can_print = mysqli_num_rows($query) > 0;
} else {
    $query = mysqli_query($conn, $filter_query);
    $can_print = mysqli_num_rows($query) > 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Water Billing System - Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
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
            max-width: 600px;
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
                <?php if ($page) {echo strtoupper($page);} ?>
            </h1>
        </div>

        <div class="content">
            <div class="add mb-3 d-flex justify-content-between align-items-center">
                <div></div>
                <!-- SEARCH FEATURE START -->
                <div class="search-area">
                    <form action="index.php" method="get" class="search-form" style="flex:1;">
                        <button
                            class="back-arrow-btn"
                            id="backToAll"
                            title="Back to all reports"
                            type="button"
                            <?php if (empty($search)) echo 'disabled'; ?>
                            onclick="window.location='index.php';"
                            style="margin-right: 4px;"
                        >
                            <i class="fas fa-arrow-left"></i>
                        </button>
                        <input class="form-control me-2" type="text" name="search" placeholder="Search by ID, Name, or YYYY-MM" value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-outline-secondary me-2" type="submit">Search</button>
                        <a href="print.php?search=<?php echo urlencode($search); ?>">
                            <button type="button" class="btn btn-success" <?php if(!$can_print) echo "disabled"; ?>>Print</button>
                        </a>
                    </form>
                </div>
                <!-- SEARCH FEATURE END -->
            </div>
             <table id="table" class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Purok/Sitio</th>
                            <th>Amount</th>
                            <th>Processed By</th>
                            <th>Status</th>
                            <th>Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                   while ($row = mysqli_fetch_array($query)) {
                      $customer_id =  $row['customer_id'];
                      $squery1 =  mysqli_query($conn, "SELECT * from customer where id = '$customer_id'");
                      $customer = mysqli_fetch_array($squery1);

                      // Fetch Processed By: get the latest payment for this billing, then fetch user info
                      $processed_by = 'N/A';
                      $billing_id = $row['id'];
                      $payment_result = mysqli_query($conn, "SELECT user FROM payment WHERE billing_id = '$billing_id' ORDER BY date_created DESC LIMIT 1");
                      if ($payment_row = mysqli_fetch_array($payment_result)) {
                          $user_field = $payment_row['user'];
                          if (!empty($user_field) && $user_field !== '0') {
                              // Always show the username from user table
                              if (is_numeric($user_field)) {
                                  $user_stmt = mysqli_prepare($conn, "SELECT username FROM user WHERE id = ? LIMIT 1");
                                  mysqli_stmt_bind_param($user_stmt, "i", $user_field);
                              } else {
                                  $user_stmt = mysqli_prepare($conn, "SELECT username FROM user WHERE username = ? LIMIT 1");
                                  mysqli_stmt_bind_param($user_stmt, "s", $user_field);
                              }
                              mysqli_stmt_execute($user_stmt);
                              $user_result = mysqli_stmt_get_result($user_stmt);
                              if ($user_row = mysqli_fetch_assoc($user_result)) {
                                  $processed_by = htmlspecialchars($user_row['username']);
                              } else {
                                  $processed_by = htmlspecialchars($user_field); // fallback to raw value
                              }
                              mysqli_stmt_close($user_stmt);
                          }
                      }
                    ?>
                        <tr>
                        <td>24-<?php echo $row['id'] ?></td>
                        <td><?php echo $row['customer_id'] ." - ". $row['customer_name']  ?></td>
                        <td><?php echo $customer['purok'] ?></td>
                        <td><?php echo $row['total'] ?></td>
                        <td><?php echo $processed_by; ?></td>
                        <td style="color:
                        <?php 
                        if(isset($customer['disconnected']) && $customer['disconnected']){
                            echo '#dc3545'; // red for disconnected
                        } else if($row['status'] == 'Paid'){
                            echo 'green';
                        } else if($row['status'] == 'Pending'){
                            echo 'red';
                        }
                        ?>;
                        font-weight:bold;">
                        <?php 
                        if(isset($customer['disconnected']) && $customer['disconnected']){
                            echo 'Disconnected';
                        } else {
                            echo $row['status'];
                        }
                        ?>
                        </td>
                        <td><?php echo $row['due_date'] ?></td>
                        </tr> <?php }?>
                    </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/table.js"></script>
</body>
</html>