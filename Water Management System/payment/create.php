<?php
session_start();

// Check if session variables are set
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

include "../db_conn.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $billing_id = filter_var($_POST['billing_id'], FILTER_SANITIZE_NUMBER_INT);
    $customer_id = filter_var($_POST['customer_id'], FILTER_SANITIZE_NUMBER_INT);
    $amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
    $user = $_SESSION['username'];

    if (empty($user)) {
        die("Error: User is not set in session.");
    }

    try {
        // Start transaction
        mysqli_begin_transaction($conn);

        // Insert payment record FIRST
        $payment_stmt = mysqli_prepare($conn, 
            "INSERT INTO payment (billing_id, customer_id, customer_name, user, amount) 
             VALUES (?, ?, (SELECT customer_name FROM billing WHERE id = ?), ?, ?)"
        );
        mysqli_stmt_bind_param($payment_stmt, "iisis", $billing_id, $customer_id, $billing_id, $user, $amount);
        mysqli_stmt_execute($payment_stmt);
        mysqli_stmt_close($payment_stmt);

        // Get the original total from billing
        $billing_query2 = mysqli_prepare($conn, "SELECT total FROM billing WHERE id = ?");
        mysqli_stmt_bind_param($billing_query2, "i", $billing_id);
        mysqli_stmt_execute($billing_query2);
        $billing_result2 = mysqli_stmt_get_result($billing_query2);
        $billing_data2 = mysqli_fetch_assoc($billing_result2);
        $total_due2 = $billing_data2['total'];
        mysqli_stmt_close($billing_query2);

        // Calculate total payments for this billing
        $sum_stmt = mysqli_prepare($conn, "SELECT SUM(amount) as total_paid FROM payment WHERE billing_id = ?");
        mysqli_stmt_bind_param($sum_stmt, "i", $billing_id);
        mysqli_stmt_execute($sum_stmt);
        $sum_result = mysqli_stmt_get_result($sum_stmt);
        $sum_row = mysqli_fetch_assoc($sum_result);
        $total_paid = $sum_row['total_paid'] ?? 0;
        mysqli_stmt_close($sum_stmt);

        // Calculate new balance
        $new_balance = $total_due2 - $total_paid;
        $new_balance = max(0, $new_balance);
        $status = $new_balance == 0 ? 'Paid' : 'Pending';

        // Update billing status and balance
        $update_stmt = mysqli_prepare($conn, "UPDATE billing SET status = ?, balance = ? WHERE id = ?");
        mysqli_stmt_bind_param($update_stmt, "sdi", $status, $new_balance, $billing_id);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);

        // Commit transaction
        mysqli_commit($conn);

        header("Location: ../billing/index.php?message=Payment successful");
    } catch (Exception $e) {
        mysqli_rollback($conn);
        header("Location: ../billing/payment.php?id=" . $billing_id . "&message=Error: " . urlencode($e->getMessage()));
    } finally {
        mysqli_close($conn);
    }
}
?>
