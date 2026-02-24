<?php
// company-products.php (ADMIN) - View Products per Company
session_start();
if (empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }

require_once 'db.php';

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// ✅ Get company_id from URL: company-products.php?company_id=1
$company_id = isset($_GET['company_id']) ? (int)$_GET['company_id'] : 0;
if ($company_id <= 0) {
    header("Location: companies.php");
    exit;
}

// ✅ Get Company Info
$stCo = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
$stCo->execute([$company_id]);
$company = $stCo->fetch();

if (!$company) {
    header("Location: companies.php");
    exit;
}

// ✅ Get Products that belong to this Company only
$st = $pdo->prepare("
    SELECT p.*
    FROM products p
    WHERE p.company_id = ?
    ORDER BY p.id DESC
");
$st->execute([$company_id]);
$products = $st->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Company Products</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <style>
    body{ background:#f6f7fb; }
    .img-mini{
      width: 54px;
      height: 54px;
      object-fit: contain;
      background:#fff;
      border:1px solid rgba(0,0,0,.08);
      border-radius:10px;
      padding:4px;
    }
  </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
  <div class="container d-flex justify-content-between">
    <a class="navbar-brand fw-bold" href="companies.php">Companies</a>

    <div class="d-flex gap-2">
      <a class="btn btn-outline-light btn-sm" href="admin_dashboard.php">Dashboard</a>
      <a class="btn btn-outline-light btn-sm" href="products.php">All Products</a>
      <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">

  <!-- ✅ Company header -->
  <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-3">
    <div>
      <h3 class="mb-0 fw-bold"><?= h($company['company_name']) ?></h3>
      <div class="text-muted small">
        <?= h($company['company_address']) ?> • <?= h($company['company_telephone']) ?>
      </div>
    </div>
    <div class="d-flex gap-2">
      <a href="companies.php" class="btn btn-outline-secondary btn-sm">← Back to Companies</a>
      <a href="products.php?action=create" class="btn btn-success btn-sm">+ Add New Product</a>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header bg-white fw-bold">
      Products Owned by this Company
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th style="width:70px;">Image</th>
              <th>GTIN</th>
              <th>Name (EN)</th>
              <th>Brand</th>
              <th>Status</th>
              <th class="text-end" style="width:180px;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$products): ?>
              <tr>
                <td colspan="6" class="text-center text-muted py-4">
                  No products found for this company.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($products as $p): ?>
                <?php
                  $img = trim((string)($p['image_path'] ?? ''));
                  $placeholder = 'assets/images/no-image.avif';
                  $imgSrc = $placeholder;

                  // If image exists in filesystem, use it
                  if ($img !== '' && file_exists(__DIR__ . '/' . $img)) {
                      $imgSrc = $img;
                  }
                ?>
                <tr>
                  <td>
                    <img src="<?= h($imgSrc) ?>" class="img-mini" alt="Img">
                  </td>
                  <td><?= h($p['gtin']) ?></td>
                  <td class="fw-semibold"><?= h($p['name_en']) ?></td>
                  <td><?= h($p['brand']) ?></td>
                  <td>
                    <?= ((int)$p['is_hidden'] === 1)
                      ? '<span class="badge bg-secondary">Hidden</span>'
                      : '<span class="badge bg-success">Visible</span>' ?>
                  </td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary"
                       href="products.php?action=edit&id=<?= (int)$p['id'] ?>">Edit</a>

                  
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

</div>

<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>