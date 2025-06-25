<?php
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}
?>

<link rel="stylesheet" href="../assets/css/style.css">

<div class="sidebar">
    <div class="logo-details">
        <i class="fas fa-water"></i>
        <span class="logo_name">Water Billing</span>
    </div>
    <ul class="nav-links">
        <li>
            <a href="../dashboard/dashboard.php" class="<?php echo ($page == 'Dashboard') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span class="link_name">Dashboard</span>
            </a>
        </li>

        <?php if ($_SESSION['role'] === 'Administrator'): ?>
        <li>
            <a href="../user" class="<?php echo ($page == 'User') ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span class="link_name">Users</span>
            </a>
        </li>
        <?php endif; ?>

        <li>
            <a href="../customer/index.php" class="<?php echo ($page == 'Customer') ? 'active' : ''; ?>">
                <i class="fas fa-user-check"></i>
                <span class="link_name">Customer</span>
            </a>
        </li>

        <li>
            <a href="../category" class="<?php echo ($page == 'Category') ? 'active' : ''; ?>">
                <i class="fas fa-list"></i>
                <span class="link_name">Category</span>
            </a>
        </li>

        <li>
            <a href="../billing" class="<?php echo ($page == 'Billing') ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice"></i>
                <span class="link_name">Billing</span>
            </a>
        </li>

        <li>
            <a href="../payment/index.php" class="<?php echo ($page == 'Payment') ? 'active' : ''; ?>">
                <i class="fas fa-money-bill-wave"></i>
                <span class="link_name">Payment</span>
            </a>
        </li>

         <li>
        <a href="../report/index.php" class="<?php echo ($page == 'Report') ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i>
            <span class="link_name">Report</span>
        </a>
        </li>

       
        <li class="profile">
            <div class="profile-details">
                <i class="fas fa-user-circle profile_pic"></i>
                <div class="name_job">
                    <div class="name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                    <div class="job"><?php echo htmlspecialchars($_SESSION['role']); ?></div>
                </div>
            </div>
            <a href="../logout.php" class="logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </li>
    </ul>
</div>