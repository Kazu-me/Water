<?php 
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

include "../db_conn.php";

try {
    // Validate and sanitize inputs
    if (!isset($_POST['category']) || !isset($_POST['rate']) || !isset($_GET['id'])) {
        throw new Exception("Missing required fields");
    }

    $id = intval($_GET['id']);
    $category_name = trim(ucwords(htmlspecialchars($_POST['category'])));
    $rate = filter_var($_POST['rate'], FILTER_VALIDATE_FLOAT);

    if ($rate === false || $rate < 0) {
        throw new Exception("Invalid rate value");
    }

    // Check if category exists (excluding current category)
    $check_stmt = mysqli_prepare($conn, "SELECT category_name FROM category WHERE category_name = ? AND id != ?");
    mysqli_stmt_bind_param($check_stmt, "si", $category_name, $id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        header("Location: edit.php?id=$id&message=Error! Category name already exists.");
        exit();
    }
    mysqli_stmt_close($check_stmt);

    // Update category
    $update_stmt = mysqli_prepare($conn, "UPDATE category SET category_name = ?, rate = ? WHERE id = ?");
    mysqli_stmt_bind_param($update_stmt, "sdi", $category_name, $rate, $id);

    if (!mysqli_stmt_execute($update_stmt)) {
        throw new Exception(mysqli_error($conn));
    }
    mysqli_stmt_close($update_stmt);

    header("Location: edit.php?id=$id&message=Success! Changes have been saved successfully.");

} catch (Exception $e) {
    header("Location: edit.php?id=$id&message=Error: " . urlencode($e->getMessage()));
} finally {
    mysqli_close($conn);
}
exit();
?>
