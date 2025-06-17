<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

include_once "../db_conn.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id'])) {
        $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);

        if ($id) {
            $stmt = mysqli_prepare($conn, "DELETE FROM payment WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: index.php?message=Payment deleted successfully");
            } else {
                header("Location: index.php?message=Error deleting payment");
            }

            mysqli_stmt_close($stmt);
        } else {
            header("Location: index.php?message=Invalid payment ID");
        }
    } else {
        header("Location: index.php?message=No payment ID provided");
    }
}

mysqli_close($conn);
exit();
?>