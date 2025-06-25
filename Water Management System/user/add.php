<?php 
session_start();
$page = 'User';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: ../dashboard/dashboard.php");
    exit();
}

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
    <title>Add User</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                <h2 class="text-center py-2 mb-3" style="background: #007bff; color: #fff; border-radius: 8px; letter-spacing:1.5px; font-weight:700; text-transform:uppercase;">User Information</h2>

                <div class="col-md-4">
                    <label for="username" class="form-label">Username</label>
                    <input required type="text" class="form-control" name="username" id="username" placeholder="Enter username">
                </div>

                <div class="col-md-4">
                    <label for="password" class="form-label">Password</label>
                    <input required type="password" class="form-control" name="password" id="password" placeholder="Enter password">
                </div>

                <div class="col-md-4">
                    <label for="role" class="form-label">Role</label>
                    <select required class="form-select" name="role" id="role">
                        <option value="" selected disabled>Choose role...</option>
                        <option value="Administrator">Administrator</option>
                        <option value="Staff">Staff</option>
                        <option value="Financial Analyst">Financial Analyst</option>
                    </select>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
