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

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validate and sanitize id
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT) : 0;
if (!$id) {
    header("Location: index.php?message=Invalid billing ID");
    exit();
}

// Get billing data
$stmt = mysqli_prepare($conn, 
    "SELECT b.*, c.category as customer_category, cat.rate 
     FROM billing b 
     LEFT JOIN customer c ON b.customer_id = c.id 
     LEFT JOIN category cat ON c.category = cat.category_name 
     WHERE b.id = ? AND b.status = 'Pending'"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_array($result)) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Water Billing System - Edit Billing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="main-content">
        <div class="header">
            <h1>Edit <?php echo htmlspecialchars($page); ?></h1>
        </div>
        <div class="content">
            <form class="row g-3" method="POST" action="update.php" id="billingForm">
                <!-- CSRF Token and Hidden Fields -->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="billing_id" value="<?php echo $row['id']; ?>">
                <input type="hidden" name="customer_id" value="<?php echo $row['customer_id']; ?>">
                
                <h1>Billing Information</h1>

                <!-- Customer Information -->
                <div class="col-md-4">
                    <label class="form-label">Customer</label>
                    <input readonly class="form-control" value="<?php echo htmlspecialchars($row['customer_name']); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Category</label>
                    <input readonly class="form-control" value="<?php echo htmlspecialchars($row['category']); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Rate (₱)</label>
                    <input readonly type="number" step="0.01" class="form-control" id="rate" 
                           value="<?php echo htmlspecialchars($row['rate']); ?>">
                </div>

                <!-- Reading Information -->
                <div class="col-md-4">
                    <label class="form-label">Previous Reading</label>
                    <input readonly type="number" step="0.0001" class="form-control" id="previous_reading" 
                           value="<?php echo htmlspecialchars($row['previous_reading']); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Current Reading<span style="color: red;">*</span></label>
                    <input required type="number" step="0.0001" class="form-control" name="current_reading" 
                           id="current_reading" value="<?php echo htmlspecialchars($row['current_reading']); ?>" 
                           oninput="calculate()">
                    <span class="error-msg text-danger"></span>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Total Reading</label>
                    <input readonly type="number" step="0.0001" class="form-control" name="total_reading" 
                           id="total_reading" value="<?php echo htmlspecialchars($row['total_reading']); ?>">
                </div>

                <!-- Dates and Total -->
                <div class="col-md-4">
                    <label class="form-label">Reading Date<span style="color: red;">*</span></label>
                    <input required type="date" class="form-control" name="reading_date" 
                           value="<?php echo htmlspecialchars($row['reading_date']); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Due Date<span style="color: red;">*</span></label>
                    <input required type="date" class="form-control" name="due_date" 
                           value="<?php echo htmlspecialchars($row['due_date']); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Total Bill (₱)</label>
                    <input readonly type="number" step="0.01" class="form-control" name="total" 
                           id="total" value="<?php echo htmlspecialchars($row['total']); ?>">
                </div>

                <!-- Form Buttons -->
                <div class="col-12 buttons">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="index.php" class="btn btn-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function calculate() {
        const previousReading = parseFloat(document.getElementById("previous_reading").value) || 0;
        const currentReading = parseFloat(document.getElementById("current_reading").value) || 0;
        const rate = parseFloat(document.getElementById("rate").value) || 0;
        const errorMsg = document.querySelector(".error-msg");
        
        errorMsg.innerHTML = "";
        
        if (currentReading < 0) {
            errorMsg.innerHTML = "Current reading must be greater than or equal to 0";
            clearCalculations();
            return;
        }
        
        if (currentReading <= previousReading) {
            errorMsg.innerHTML = "Current reading must be greater than previous reading";
            clearCalculations();
            return;
        }
        
        const totalReading = (currentReading - previousReading).toFixed(4);
        const totalPrice = (totalReading * rate).toFixed(2);
        
        document.getElementById("total_reading").value = totalReading;
        document.getElementById("total").value = totalPrice;
    }

    function clearCalculations() {
        document.getElementById("total_reading").value = "";
        document.getElementById("total").value = "";
    }

    document.getElementById('billingForm').addEventListener('submit', function(e) {
        const currentReading = parseFloat(document.getElementById('current_reading').value);
        const previousReading = parseFloat(document.getElementById('previous_reading').value);
        
        if (currentReading <= previousReading) {
            e.preventDefault();
            alert('Current reading must be greater than previous reading');
        }
    });
    </script>
</body>
</html>
<?php 
} else {
    header("Location: index.php?message=Billing record not found or already paid");
    exit();
}
mysqli_stmt_close($stmt);
?>
