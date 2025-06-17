<?php 
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

include "../db_conn.php";

try {
    // Validate and sanitize id
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id === 0) {
        throw new Exception("Invalid category ID");
    }

    // Check if category is in use
    $check_stmt = mysqli_prepare($conn, "SELECT id FROM customer WHERE category = (SELECT category_name FROM category WHERE id = ?)");
    mysqli_stmt_bind_param($check_stmt, "i", $id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        throw new Exception("Cannot delete category that is in use by customers");
    }
    mysqli_stmt_close($check_stmt);

    // Delete category
    $delete_stmt = mysqli_prepare($conn, "DELETE FROM category WHERE id = ?");
    mysqli_stmt_bind_param($delete_stmt, "i", $id);

    if (!mysqli_stmt_execute($delete_stmt)) {
        throw new Exception(mysqli_error($conn));
    }
    mysqli_stmt_close($delete_stmt);

    header("Location: index.php?message=Success! Category deleted successfully.");

} catch (Exception $e) {
    header("Location: index.php?message=Error: " . urlencode($e->getMessage()));
} finally {
    mysqli_close($conn);
}
exit();
?>
