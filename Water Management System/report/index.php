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

    $status = 'All';

    if(isset($_POST['status']) && isset($_POST['from']) && isset($_POST['to'])){
        $status = $_POST['status'];

        $from = $_POST['from'];
        $to = $_POST['to'];
    }
    $query = "SELECT * from billing WHERE 1";

    if($status != 'All'){
        $query .= " and status = '$status'";
    }
    if(!empty($from) && !empty($to)){
        $query .= " AND due_date BETWEEN '$from' AND '$to'";
    }
   
 $query = mysqli_query($conn, $query);

 ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Water Billing System - Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="main-content">
        <div class="header">
            <h1 class="title-page text-center py-3 mb-4" style="background: #007bff; color: #fff; border-radius: 8px; letter-spacing:2px; font-weight:700; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                <?php if ($page) {echo strtoupper($page);} ?>
            </h1>
        </div>

        <div class="content">
            <div class="add">
                <form class="row" method="POST" action="index.php">

                    <div class="col-2">
                        <label for="statusSelect">Status</label>
                        <select class="form-select" id="statusSelect" name="status">
                            <option selected hidden value="<?php echo $status ?>" selected><?php echo $status ?></option>
                            <option value="All">All</option>
                            <option value="Pending">Pending</option>
                            <option value="Paid">Paid</option>
                        </select>

                    </div>
                    
                    <!-- <div class="col-2">
                        <label for="monthSelect">Month</label>
                        <select class="form-select" id="monthSelect" name="month">
                            <option selected hidden value="<?php echo $month ?>" selected><?php echo $month ?></option>
                            <option value="All">All</option>
                            <option value="January">January</option>
                            <option value="February">February</option>
                            <option value="March">March</option>
                            <option value="April">April</option>
                            
    <option value="May">May</option>
                            <option value="June">June</option>
                            <option value="July">July</option>
                            <option value="August">August</option>
                            <option value="September">September</option>
                            <option value="October">October</option>
                            <option value="November">November</option>
                            <option value="December">December</option>
                        </select>
                    </div> -->

                    <div class="col-2">
                        <label for="monthSelect">From</label>
                        <input required type="date" class="form-control" name="from" value="<?php echo $from ?>">
                    </div>

                    <div class="col-2">
                        <label for="monthSelect">To</label>

                        <input required type="date" class="form-control" name="to" value="<?php echo $to ?>">
                    </div>

                    <div class="col-1">
                        <label for="filterButton"> </label>
                        <button type="submit" class="form-control btn btn-danger" id="filterButton">Filter</button>
                    </div>
                    <div class="col-1">
                </form>

                <label for="autoSizingInput"> </label>
                  

              <div class="button-container">
                  <a href="print.php?status=<?php echo $status ?>&from=<?php echo $from ?>&to=<?php echo $to ?>">
                      <button type="button" class="btn btn-success">Print</button>
                  </a>
              </div>
                </div>
            </div>
             <table id="table" class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Purok/Sitio</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
    //    $squery =  mysqli_query($conn, "SELECT * from billing");

                   while ($row = mysqli_fetch_array($query)) {
                      $customer_id =  $row['customer_id'];
                  $squery1 =  mysqli_query($conn, "SELECT * from customer where id = '$customer_id'");
                  $customer = mysqli_fetch_array($squery1);
                    ?>
                        <tr>
                        <td>24-<?php echo $row['id'] ?></td>
                        <td><?php echo $row['customer_id'] ." - ". $row['customer_name']  ?></td>
                        <td><?php echo $customer['purok'] ?></td>
                        <td><?php echo $row['total'] ?></td>
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
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/table.js"></script>
</body>
</html>
