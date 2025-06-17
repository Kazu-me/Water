<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}
include_once "../db_conn.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['customer_id'])) {
    $customer_id = intval($_POST['customer_id']);
    $update = mysqli_query($conn, "UPDATE customer SET disconnected=0 WHERE id='$customer_id'");
    if ($update) {
        header("Location: index.php?message=Customer successfully undisconnected.");
        exit();
    } else {
        header("Location: index.php?message=Failed to undisconnect customer.");
        exit();
    }
} else {
    header("Location: index.php?message=Invalid request.");
    exit();
}
