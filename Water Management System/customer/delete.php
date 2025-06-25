<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

include "../db_conn.php";

// Validate and sanitize id
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id === 0) {
    header("Location: index.php?message=Invalid customer ID");
    exit();
}

try {
    // Begin transaction
    mysqli_begin_transaction($conn);

    // Check if customer exists and is not already deleted
    $check_stmt = mysqli_prepare($conn, "SELECT id FROM customer WHERE id = ? AND (del_status IS NULL OR del_status != 'deleted')");
    mysqli_stmt_bind_param($check_stmt, "i", $id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) === 0) {
        throw new Exception("Customer not found or already deleted");
    }
    mysqli_stmt_close($check_stmt);

    // Update customer status to deleted
    $update_stmt = mysqli_prepare($conn, "UPDATE customer SET del_status = 'deleted' WHERE id = ?");
    mysqli_stmt_bind_param($update_stmt, "i", $id);
    
    if (!mysqli_stmt_execute($update_stmt)) {
        throw new Exception("Failed to delete customer");
    }
    mysqli_stmt_close($update_stmt);

    // Commit transaction
    mysqli_commit($conn);
    header("Location: index.php?message=Customer deleted successfully");

} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    header("Location: index.php?message=Error: " . urlencode($e->getMessage()));
} finally {
    mysqli_close($conn);
}
exit();
?>
