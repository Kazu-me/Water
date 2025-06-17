<?php 
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$page = 'Category';
if(isset($_GET['message'])){
    $message = htmlspecialchars($_GET['message']);
    echo "<script type='text/javascript'>alert('$message');</script>";
}

include_once "../sidebar.php";
include_once "../db_conn.php";

// Validate and sanitize id
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id === 0) {
    header("Location: index.php?message=Invalid category ID");
    exit();
}

$stmt = mysqli_prepare($conn, "SELECT * FROM category WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_array($result)) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Water Billing System - Edit Category</title>
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
            <a href="index.php" class="btn btn-secondary">Back</a>
            <form class="row g-3" method="POST" action="update.php?id=<?php echo $row['id']; ?>">
                <h1>Category Information</h1>

                <div class="col-md-6">
                    <label for="category" class="form-label">Category Name<span style="color: red;">*</span></label>
                    <input required type="text" class="form-control" name="category" id="category" value="<?php echo htmlspecialchars($row['category_name']); ?>">
                </div>

                <div class="col-md-6">
                    <label for="rate" class="form-label">Rate (₱)<span style="color: red;">*</span></label>
                    <input required type="number" step="0.01" min="0" class="form-control" name="rate" id="rate" value="<?php echo htmlspecialchars($row['rate']); ?>">
                </div>

                <div class="col-12 buttons">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete(<?php echo $row['id']; ?>)">Delete</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function confirmDelete(id) {
        if (confirm("Are you sure you want to delete this category? This action cannot be undone.")) {
            window.location.href = `delete.php?id=${id}`;
        }
    }
    </script>
</body>
</html>
<?php 
} else {
    header("Location: index.php?message=Category not found");
    exit();
}
?>
