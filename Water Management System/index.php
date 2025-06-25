<!DOCTYPE html>
<html>
<head>
    <title>Water Billing System - Login</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #007bff 0%, #6dd5ed 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            max-width: 400px;
            margin: auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 6px 32px rgba(0,0,0,0.13);
            padding: 36px 32px 28px 32px;
        }
        .login-title {
            font-weight: 700;
            color: #007bff;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        .login-logo {
            width: 60px;
            margin-bottom: 10px;
        }
        .form-label {
            font-weight: 500;
        }
        .show-password {
            font-size: 0.95em;
        }
        .btn-primary {
            font-size: 1.1em;
            padding: 0.7rem;
            border-radius: 8px;
            font-weight: 600;
        }
        .alert {
            font-size: 0.95rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-3">
            <img src="asset/img/logo.jpg" alt="Logo" class="login-logo">
            <h2 class="login-title">Water Billing System</h2>
            <div class="mb-2 text-muted">Sign in to your account</div>
        </div>
        <?php if (isset($_GET['error'])) { ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php } ?>
        <form action="login.php" method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" name="username" id="username" placeholder="Enter username" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" name="password" id="password" placeholder="Enter password" required>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="showPasswordCheck" onclick="togglePassword()">
                    <label class="form-check-label show-password" for="showPasswordCheck">Show Password</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function togglePassword() {
        var pw = document.getElementById('password');
        pw.type = pw.type === 'password' ? 'text' : 'password';
    }
    </script>
</body>
</html>