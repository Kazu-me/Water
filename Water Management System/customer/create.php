<?php 
session_start();
// Check if user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

include "../db_conn.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add.php");
    exit();
}

// Validate and sanitize inputs
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

try {
    // Get and sanitize POST data
    $first_name = ucwords(sanitize($_POST['first_name']));
    $middle_name = ucwords(sanitize($_POST['middle_name']));
    $last_name = ucwords(sanitize($_POST['last_name']));
    $suffix = sanitize($_POST['suffix']);
    $gender = sanitize($_POST['gender']);
    $date_of_birth = sanitize($_POST['date_of_birth']);
    $purok = ucwords(sanitize($_POST['purok']));
    $place_of_birth = ucwords(sanitize($_POST['place_of_birth']));
    $civil_status = ucwords(sanitize($_POST['civil_status']));
    $phone_number = sanitize($_POST['phone_number']);
    $category = sanitize($_POST['category']);
    $water_reading = sanitize($_POST['water_reading']);
    $latest_reading_date = sanitize($_POST['latest_reading_date']);

    // Check if customer already exists
    $check_stmt = mysqli_prepare($conn, 
        "SELECT id FROM customer 
        WHERE first_name = ? AND middle_name = ? AND last_name = ? 
        AND suffix = ? AND del_status IS NULL"
    );
    mysqli_stmt_bind_param($check_stmt, "ssss", 
        $first_name, $middle_name, $last_name, $suffix
    );
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        header("Location: add.php?message=Error! Customer already exists.");
        exit();
    }
    mysqli_stmt_close($check_stmt);

    // Insert new customer
    $insert_stmt = mysqli_prepare($conn, 
        "INSERT INTO customer (
            first_name, middle_name, last_name, suffix,
            gender, date_of_birth, purok, place_of_birth,
            civil_status, phone_number, category, water_reading,
            latest_reading_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param($insert_stmt, "sssssssssssss",
        $first_name, $middle_name, $last_name, $suffix,
        $gender, $date_of_birth, $purok, $place_of_birth,
        $civil_status, $phone_number, $category, $water_reading,
        $latest_reading_date
    );

    if (mysqli_stmt_execute($insert_stmt)) {
        header("Location: index.php?message=Success! New customer has been saved successfully.");
    } else {
        throw new Exception(mysqli_error($conn));
    }
    mysqli_stmt_close($insert_stmt);

} catch (Exception $e) {
    header("Location: add.php?message=Error! " . urlencode($e->getMessage()));
} finally {
    mysqli_close($conn);
}
exit();
?>
