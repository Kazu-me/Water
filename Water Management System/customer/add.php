<?php 
session_start();
// Check if user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$page = 'Customer';
if(isset($_GET['message'])){
    $message = htmlspecialchars($_GET['message']);
    echo "<script type='text/javascript'>alert('$message');</script>";
}
include_once "../sidebar.php";
include_once "../db_conn.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Water Billing System - Add Customer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="main-content">
        <div class="header">
            <h1 class="title-page text-center py-3 mb-4" style="background: #007bff; color: #fff; border-radius: 8px; letter-spacing:2px; font-weight:700; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                ADD <?php if ($page) {echo strtoupper(htmlspecialchars($page));} ?>
            </h1>
        </div>
        <div class="content">
            <a href="index.php" class="btn btn-secondary mb-3">Back</a>
            <form class="row g-3" method="POST" action="create.php">
                <h2 class="text-center py-2 mb-3" style="background: #007bff; color: #fff; border-radius: 8px; letter-spacing:1.5px; font-weight:700; text-transform:uppercase;">Customer Information</h2>

                <div class="col-md-3">
                    <label for="first_name" class="form-label">First Name<span style="color: red;">*</span></label>
                    <input required type="text" class="form-control" name="first_name" id="first_name">
                </div>

                <div class="col-md-3">
                    <label for="middle_name" class="form-label">Middle Name</label>
                    <input type="text" class="form-control" name="middle_name" id="middle_name">
                </div>

                <div class="col-md-3">
                    <label for="last_name" class="form-label">Last Name<span style="color: red;">*</span></label>
                    <input required type="text" class="form-control" name="last_name" id="last_name">
                </div>

                <div class="col-md-3">
                    <label for="suffix" class="form-label">Suffix</label>
                    <input type="text" class="form-control" name="suffix" id="suffix">
                </div>

                <div class="col-md-3">
                    <label for="gender" class="form-label">Gender<span style="color: red;">*</span></label>
                    <select required id="gender" class="form-select" name="gender">
                        <option value="" selected hidden>Choose...</option>
                        <option value="Female">Female</option>
                        <option value="Male">Male</option>
                        <option value="Male">Rather not say</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="date_of_birth" class="form-label">Date of Birth<span style="color: red;">*</span></label>
                    <input required type="date" class="form-control" name="date_of_birth" max="<?php echo date('Y-m-d'); ?>" id="date_of_birth">
                </div>

                <div class="col-md-3">
                    <label for="purok" class="form-label">Purok<span style="color: red;">*</span></label>
                    <select required class="form-select" name="purok" id="purok">
                        <option value="" selected hidden>Choose...</option>
                        <?php
                        $puroks = [
                            "Purok 1", "Purok 2", "Purok 3", "Purok 4",
                            "Purok 5", "Purok 6", "Purok 7", "Purok Tapcan",
                            "Sitio Pananim", "Sitio Ambang", "Sitio Malinis", "Sitio Balitangan",
                            "Sitio Lamlinol", "Purok Abcalag", "Sitio Kablala", "Purok Sufa Mlanub",
                            "Sitio Lamlangil", "Sitio Kalbangan", "Sitio Nian", "Purok Lumagok",
                            "Sitio Kadengen", "Purok Tinago", "Purok Binenli", "Sitio Pikong",
                            "Purok Mahayag", "Purok Mati", "Sitio Kanyugan", "Purok Kidalgan",
                            "Purok Kiturok", "Sitio Lambugad"
                        ];
                        foreach ($puroks as $purok) {
                            echo "<option value=\"" . htmlspecialchars($purok) . "\">" . htmlspecialchars($purok) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="place_of_birth" class="form-label">Place of Birth<span style="color: red;">*</span></label>
                    <input required type="text" class="form-control" name="place_of_birth" id="place_of_birth">
                </div>

                <div class="col-md-3">
                    <label for="civil_status" class="form-label">Civil Status<span style="color: red;">*</span></label>
                    <select required class="form-select" name="civil_status" id="civil_status">
                        <option value="" selected hidden>Choose...</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Widowed">Widowed</option>
                        <option value="Legally Separated">Legally Separated</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="phone_number" class="form-label">Phone Number<span style="color: red;">*</span></label>
                    <input required type="text" maxlength="11" pattern="[0-9]{11}" placeholder="09123456789" class="form-control" name="phone_number" id="phone_number">
                </div>

                <h2 class="text-center py-2 mb-3" style="background: #007bff; color: #fff; border-radius: 8px; letter-spacing:1.5px; font-weight:700; text-transform:uppercase;">Billing Information</h2>

                <div class="col-md-4">
                    <label for="category" class="form-label">Category<span style="color: red;">*</span></label>
                    <select required class="form-select" name="category" id="category" onchange="updateRate()">
                        <option value="" selected hidden>Choose...</option>
                        <?php
                        $squery = mysqli_query($conn, "SELECT * FROM category ORDER BY category_name ASC");
                        while ($row = mysqli_fetch_array($squery)) {
                            echo "<option value=\"" . htmlspecialchars($row['category_name']) . "\" 
                                  data-rate=\"" . htmlspecialchars($row['rate']) . "\">
                                  " . htmlspecialchars($row['category_name']) . " - ₱" . 
                                  number_format($row['rate'], 2) . "/cu.m
                                  </option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="rate" class="form-label">Rate per cu.m</label>
                    <input type="number" step="0.01" class="form-control" name="rate" id="rate" readonly>
                </div>

                <div class="col-md-4">
                    <label for="latest_reading_date" class="form-label">Date of First Reading<span style="color: red;">*</span></label>
                    <input required type="date" class="form-control" name="latest_reading_date" id="latest_reading_date" value="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="col-md-4">
                    <label for="water_reading" class="form-label">First Water Reading<span style="color: red;">*</span></label>
                    <input required type="number" step="0.0001" class="form-control" name="water_reading" id="water_reading">
                </div>

                <div class="col-12 buttons">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateRate() {
            const categorySelect = document.getElementById('category');
            const rateInput = document.getElementById('rate');
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            
            if (selectedOption.value !== "") {
                const rate = selectedOption.getAttribute('data-rate');
                rateInput.value = rate;
            } else {
                rateInput.value = "";
            }
        }
    </script>
</body>
</html>
