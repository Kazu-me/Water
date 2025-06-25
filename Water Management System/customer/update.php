<?php 

include "../db_conn.php";
$first_name = ucwords($_POST['first_name']);
$middle_name = ucwords($_POST['middle_name']);
$last_name = ucwords($_POST['last_name']);
$suffix = $_POST['suffix'];
$gender = $_POST['gender'];
$date_of_birth = $_POST['date_of_birth'];
$purok = ucwords($_POST['purok']);
$place_of_birth = ucwords($_POST['place_of_birth']);
$civil_status = ucwords($_POST['civil_status']);
$phone_number = $_POST['phone_number'];
$category = ucwords($_POST['category']);
$water_reading = ucwords($_POST['water_reading']);
$latest_reading_date = $_POST['latest_reading_date'];
$id = $_GET['id'];

$sql ="UPDATE `customer` SET 
`first_name`='$first_name',
`middle_name`='$middle_name',
`last_name`='$last_name',
`suffix`='$suffix',
`gender`='$gender',
`date_of_birth`='$date_of_birth',
`purok`='$purok',

`place_of_birth`='$place_of_birth',
`civil_status`='$civil_status',
`phone_number`='$phone_number',
`category`='$category',
`water_reading`='$water_reading',
`latest_reading_date`='$latest_reading_date'
WHERE id = '$id'";
mysqli_query($conn, $sql);

header("location:edit.php?id=$id&message=Success! Changes has been saved successfully.");
?>
