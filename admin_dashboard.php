<?php
session_start();

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
    <div class="container d-flex justify-content-between">
        <a class="navbar-brand fw-bold" href="admin_dashboard.php">Admin Dashboard</a>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</nav>

<div class="container py-5">

    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">

            <div class="text-center mb-4">
                <h2 class="fw-bold mb-1">Welcome, Admin</h2>
                <p class="text-muted mb-0">Choose what you want to manage</p>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-2">Companies</h5>
                            <p class="text-muted small mb-3">
                                Add, edit, and delete company records.
                            </p>
                            <a href="companies.php" class="btn btn-primary w-100">
                                Manage Companies
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-2">Products</h5>
                            <p class="text-muted small mb-3">
                                Add, edit, and delete products under companies.
                            </p>
                            <a href="products.php" class="btn btn-success w-100">
                                Manage Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4 small text-muted">
                Tip: Add companies first, then you can add products.
            </div>

        </div>
    </div>

</div>

<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>