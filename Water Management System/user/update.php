<?php
session_start();
include "../db_conn.php";

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: ../dashboard/dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // Check if username exists for other users
    $stmt = mysqli_prepare($conn, "SELECT username FROM user WHERE username = ? AND id != ?");
    mysqli_stmt_bind_param($stmt, "si", $username, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        header("Location: edit.php?id=$id&message=Error! Username already exists.");
        exit();
    }

    // Update user
    if (!empty($_POST['password'])) {
        // If password is provided, update it too
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "UPDATE user SET username=?, password=?, role=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "sssi", $username, $password, $role, $id);
    } else {
        // If no password provided, only update username and role
        $stmt = mysqli_prepare($conn, "UPDATE user SET username=?, role=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ssi", $username, $role, $id);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?message=Success! Changes have been saved successfully.");
    } else {
        header("Location: edit.php?id=$id&message=Error! Something went wrong.");
    }
} else {
    header("Location: index.php");
}
exit();
?>
