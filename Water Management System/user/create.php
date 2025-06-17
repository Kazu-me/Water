<?php
session_start();
include "../db_conn.php";

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: ../dashboard/dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // Check if username already exists
    $stmt = mysqli_prepare($conn, "SELECT username FROM user WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        header("Location: add.php?message=Error! Username already exists.");
        exit();
    }

    // Insert new user
    $stmt = mysqli_prepare($conn, "INSERT INTO user (username, password, role) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sss", $username, $password, $role);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?message=Success! New user has been saved successfully.");
    } else {
        header("Location: add.php?message=Error! Something went wrong.");
    }
} else {
    header("Location: index.php");
}
exit();
