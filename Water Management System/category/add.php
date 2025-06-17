<?php 
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

include_once "../db_conn.php";

$page = 'Category';
if(isset($_GET['message'])){
    $message = htmlspecialchars($_GET['message']);
    echo "<script type='text/javascript'>alert('$message');</script>";
}
include_once "../sidebar.php";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Water Billing System - Add Category</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="main-content">
        <div class="header">
            <h1 class="title-page text-center py-3 mb-4" style="background: #007bff; color: #fff; border-radius: 8px; letter-spacing:2px; font-weight:700; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                ADD <?php echo strtoupper(htmlspecialchars($page)); ?>
            </h1>
        </div>
        <div class="content">
            <a href="index.php" class="btn btn-secondary mb-3">Back</a>
            <form class="row g-3" method="POST" action="create.php">
                <h2 class="text-center py-2 mb-3" style="background: #007bff; color: #fff; border-radius: 8px; letter-spacing:1.5px; font-weight:700; text-transform:uppercase;">Category Information</h2>

                <div class="col-md-6">
                    <label for="category" class="form-label">Category Name<span style="color: red;">*</span></label>
                    <input required type="text" class="form-control" name="category" id="category" placeholder="Enter category name">
                </div>

                <div class="col-md-6">
                    <label for="rate" class="form-label">Rate (₱)<span style="color: red;">*</span></label>
                    <input required type="number" step="0.01" min="0" class="form-control" name="rate" id="rate" placeholder="0.00">
                </div>

                <div class="col-12 buttons">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
