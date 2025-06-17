<?php 
session_start();
$page = 'User';

// Check if user is admin
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['Administrator', 'Staff', 'Financial Analyst'])) {
    header("Location: ../dashboard/dashboard.php");
    exit();
}

if(isset($_GET['message'])){
    $message = htmlspecialchars($_GET['message']);
    echo "<script type='text/javascript'>alert('$message');</script>";
}

include_once "../sidebar.php";
include_once "../db_conn.php";

// Validate and sanitize id
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id === 0) {
    header("Location: index.php?message=Invalid user ID");
    exit();
}

// Get user data
$stmt = mysqli_prepare($conn, "SELECT * FROM user WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_array($result)) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="main-content">
        <div class="header">
            <h1>Edit <?php echo htmlspecialchars($page); ?></h1>
        </div>
        <div class="content">
            <a href="index.php" class="btn btn-secondary mb-3">Back</a>
            <form class="row g-3" method="POST" action="update.php?id=<?php echo $row['id']; ?>">
                <h2>User Information</h2>

                <div class="col-md-4">
                    <label for="username" class="form-label">Username</label>
                    <input required type="text" class="form-control" name="username" id="username" 
                           value="<?php echo htmlspecialchars($row['username']); ?>">
                </div>

                <div class="col-md-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" id="password" 
                           placeholder="Leave blank to keep current password">
                    <small class="text-muted">Only fill this if you want to change the password</small>
                </div>

                <div class="col-md-4">
                    <label for="role" class="form-label">Role</label>
                    <select required class="form-select" name="role" id="role">
                        <option value="Administrator" <?php echo ($row['role'] == 'Administrator') ? 'selected' : ''; ?>>
                            Administrator
                        </option>
                        <option value="Staff" <?php echo ($row['role'] == 'Staff') ? 'selected' : ''; ?>>
                            Staff
                        </option>
                        <option value="Financial Analyst" <?php echo ($row['role'] == 'Financial Analyst') ? 'selected' : ''; ?>>
                            Financial Analyst
                        </option>
                    </select>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <?php if ($row['id'] != $_SESSION['id']) { ?>
                        <button type="button" class="btn btn-danger" onclick="confirmDelete(<?php echo $row['id']; ?>)">
                            Delete
                        </button>
                    <?php } ?>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this user?")) {
                window.location.href = `delete.php?id=${id}`;
            }
        }
    </script>
</body>
</html>
<?php 
} else {
    header("Location: index.php?message=User not found");
    exit();
}
?>