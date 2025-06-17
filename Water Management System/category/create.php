<?php 
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

include "../db_conn.php";

try {
    // Validate and sanitize inputs
    if (!isset($_POST['category']) || !isset($_POST['rate'])) {
        throw new Exception("Missing required fields");
    }

    $category_name = trim(ucwords(htmlspecialchars($_POST['category'])));
    $rate = filter_var($_POST['rate'], FILTER_VALIDATE_FLOAT);

    if ($rate === false || $rate < 0) {
        throw new Exception("Invalid rate value");
    }

    // Check if category exists using prepared statement
    $check_stmt = mysqli_prepare($conn, "SELECT category_name FROM category WHERE category_name = ?");
    mysqli_stmt_bind_param($check_stmt, "s", $category_name);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        header("Location: add.php?message=Error! Category already exists.");
        exit();
    }
    mysqli_stmt_close($check_stmt);

    // Insert new category
    $insert_stmt = mysqli_prepare($conn, "INSERT INTO category (category_name, rate) VALUES (?, ?)");
    mysqli_stmt_bind_param($insert_stmt, "sd", $category_name, $rate);

    if (!mysqli_stmt_execute($insert_stmt)) {
        throw new Exception(mysqli_error($conn));
    }
    mysqli_stmt_close($insert_stmt);

    header("Location: index.php?message=Success! New category has been saved successfully.");

} catch (Exception $e) {
    header("Location: add.php?message=Error: " . urlencode($e->getMessage()));
} finally {
    mysqli_close($conn);
}
exit();
 ?>
