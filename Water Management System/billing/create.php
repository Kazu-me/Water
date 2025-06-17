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
    $required = ['customer_id', 'customer', 'user', 'previous_reading', 'rate', 
                 'latest_reading_date', 'current_reading', 'current_reading_date', 'due_date'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Sanitize and validate inputs
    $customer_id = filter_var($_POST['customer_id'], FILTER_SANITIZE_NUMBER_INT);
    $customer_name = ucwords(trim($_POST['customer']));
    $user = $_SESSION['username']; // Use session username instead of GET

    $previous_reading = filter_var($_POST['previous_reading'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $rate = filter_var($_POST['rate'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $previous_reading_date = $_POST['latest_reading_date'];
    $current_reading = filter_var($_POST['current_reading'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $current_reading_date = $_POST['current_reading_date'];
    $due_date = $_POST['due_date'];

    // Calculate derived values server-side
    $total_reading = $current_reading - $previous_reading;
    $total = $total_reading * $rate;

    // Default status for new bills
    $status = 'Pending'; // Not user-controlled
    $category = ucwords(trim($_POST['category'] ?? 'Residential'));

    // Start transaction
    mysqli_begin_transaction($conn);

    // Insert billing record (prepared statement)
    $stmt = mysqli_prepare($conn, 
        "INSERT INTO billing (
            customer_id, customer_name, user, reading_date, previous_reading_date,
            previous_reading, current_reading, rate, total_reading, total, 
            due_date, status, category
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt, 
        "isssssddddsss",
        $customer_id, $customer_name, $user, $current_reading_date, $previous_reading_date,
        $previous_reading, $current_reading, $rate, $total_reading, $total,
        $due_date, $status, $category
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error inserting billing record: " . mysqli_error($conn));
    }

    // Update customer's latest reading (prepared statement)
    $stmt2 = mysqli_prepare($conn, 
        "UPDATE customer SET latest_reading_date = ?, water_reading = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt2, "sdi", $current_reading_date, $current_reading, $customer_id);

    if (!mysqli_stmt_execute($stmt2)) {
        throw new Exception("Error updating customer record: " . mysqli_error($conn));
    }

    mysqli_commit($conn);
    header("Location: index.php?message=Success! New billing saved.");
    exit();

} catch (Exception $e) {
    mysqli_rollback($conn);
    header("Location: select_customer.php?message=" . urlencode($e->getMessage()));
    exit();
}
?>