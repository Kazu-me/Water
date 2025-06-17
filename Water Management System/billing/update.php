<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

include "../db_conn.php";

try {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        throw new Exception("Invalid request");
    }

    // Validate required fields
    $required_fields = ['billing_id', 'customer_id', 'current_reading', 'total_reading', 
                       'reading_date', 'due_date', 'total'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Sanitize and validate inputs
    $billing_id = filter_var($_POST['billing_id'], FILTER_SANITIZE_NUMBER_INT);
    $customer_id = filter_var($_POST['customer_id'], FILTER_SANITIZE_NUMBER_INT);
    $current_reading = filter_var($_POST['current_reading'], FILTER_VALIDATE_FLOAT);
    $total_reading = filter_var($_POST['total_reading'], FILTER_VALIDATE_FLOAT);
    $reading_date = $_POST['reading_date'];
    $due_date = $_POST['due_date'];
    $total = filter_var($_POST['total'], FILTER_VALIDATE_FLOAT);

    // Additional validations
    if (strtotime($due_date) <= strtotime($reading_date)) {
        throw new Exception("Due date must be after the reading date");
    }

    // Begin transaction
    mysqli_begin_transaction($conn);

    // Update billing record
    $update_stmt = mysqli_prepare($conn, 
        "UPDATE billing 
         SET current_reading = ?, total_reading = ?, reading_date = ?, 
             due_date = ?, total = ? 
         WHERE id = ? AND status = 'Pending'"
    );

    mysqli_stmt_bind_param($update_stmt, "ddssdi", 
        $current_reading, $total_reading, $reading_date, 
        $due_date, $total, $billing_id
    );

    if (!mysqli_stmt_execute($update_stmt)) {
        throw new Exception("Failed to update billing record: " . mysqli_error($conn));
    }

    if (mysqli_affected_rows($conn) === 0) {
        throw new Exception("Billing record not found or already paid");
    }
    mysqli_stmt_close($update_stmt);

    // Update customer's latest reading
    $customer_update_stmt = mysqli_prepare($conn, 
        "UPDATE customer 
         SET water_reading = ?, latest_reading_date = ? 
         WHERE id = ?"
    );

    mysqli_stmt_bind_param($customer_update_stmt, "dsi", 
        $current_reading, $reading_date, $customer_id
    );

    if (!mysqli_stmt_execute($customer_update_stmt)) {
        throw new Exception("Failed to update customer reading: " . mysqli_error($conn));
    }
    mysqli_stmt_close($customer_update_stmt);

    // Commit transaction
    mysqli_commit($conn);

    header("Location: index.php?message=Success! Billing has been updated successfully.");

} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    header("Location: edit.php?id=" . $billing_id . "&message=Error: " . urlencode($e->getMessage()));
} finally {
    mysqli_close($conn);
}
exit();
?>
