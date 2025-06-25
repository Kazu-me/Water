<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

include_once "../db_conn.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);

    $stmt = mysqli_prepare($conn, "DELETE FROM billing WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?message=Billing record deleted successfully");
    } else {
        header("Location: index.php?message=Error deleting billing record");
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    exit();
}
?>