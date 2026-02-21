<?php
// login.php
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passphrase = trim($_POST['passphrase'] ?? '');

    if ($passphrase === 'admin') {
        // Optional: mark as logged in (use this to protect admin_dashboard.php)
        $_SESSION['admin_logged_in'] = true;

        header('Location: admin_dashboard.php');
        exit;
    } else {
        $error = 'Incorrect passphrase. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>

<body class="bg-light d-flex align-items-center" style="min-height:100vh;">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">
                <div class="card shadow border-0">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <h3 class="mb-1">Admin Login</h3>
                            <p class="text-muted mb-0">Enter passphrase to continue</p>
                        </div>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger py-2">
                                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" autocomplete="off">
                            <div class="mb-3">
                                <label class="form-label">Passphrase</label>
                                <input
                                    type="password"
                                    name="passphrase"
                                    class="form-control form-control-lg"
                                    placeholder="Enter passphrase"
                                    required
                                    autofocus
                                >
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                Login
                            </button>
                        </form>

                        <div class="text-center mt-3 small text-muted">
                            Tip: passphrase is case-sensitive.
                        </div>
                    </div>
                </div>

              
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>