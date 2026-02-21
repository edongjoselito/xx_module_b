<?php
// 01/index.php (PUBLIC) - Product Details
require_once __DIR__ . '/../db.php';

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// Get GTIN from the URL: /xx_module_b/01/{GTIN}
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts = explode('/', trim($uri, '/'));

// last part is the gtin
$gtin = end($parts);
$gtin = trim((string)$gtin);

$gtin = trim($_GET['gtin'] ?? '');

$gtin = trim($_GET['gtin'] ?? '');

if ($gtin === '') {
    header("Location: ../index.php");
    exit;
}
// Fetch product (visible only)
$stmt = $pdo->prepare("
    SELECT
        p.*,
        c.company_name
    FROM products p
    LEFT JOIN companies c ON c.id = p.company_id
    WHERE p.gtin = ?
      AND p.is_hidden = 0
      AND (c.is_deactivated = 0 OR c.is_deactivated IS NULL)
    LIMIT 1
");
$stmt->execute([$gtin]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    $notFound = true;
} else {
    $notFound = false;
    $img = trim((string)($product['image_path'] ?? ''));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $notFound ? 'Not Found' : h($product['name_en']) ?></title>
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">

  <style>
    body { background: #f6f7fb; }
    .hero-img {
      width: 100%;
      max-height: 380px;
      object-fit: cover;
      border-radius: 14px;
      background: #fff;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
  <div class="container d-flex justify-content-between">
    <a class="navbar-brand fw-bold" href="../index.php">Public Products</a>
    <a class="btn btn-outline-light btn-sm" href="../index.php">Back</a>
  </div>
</nav>

<div class="container py-4">

  <?php if ($notFound): ?>
    <div class="alert alert-danger">
      Product not found or hidden.
    </div>
  <?php else: ?>

    <div class="row g-3">
      <div class="col-12 col-lg-6">
        <?php if ($img !== ''): ?>
          <img src="../<?= h($img) ?>" class="hero-img" alt="Product Image">
        <?php else: ?>
          <div class="p-5 bg-white border rounded-4 text-center text-muted">
            <div class="fw-bold">No Image</div>
            <div class="small">Add image_path in admin</div>
          </div>
        <?php endif; ?>
      </div>

      <div class="col-12 col-lg-6">
        <div class="card shadow-sm border-0">
          <div class="card-body p-4">

            <h3 class="fw-bold mb-1"><?= h($product['name_en']) ?></h3>
            <div class="text-muted mb-3">
              Company: <span class="fw-semibold"><?= h($product['company_name'] ?? 'N/A') ?></span>
            </div>

            <div class="mb-3">
              <div class="small text-muted fw-semibold">GTIN</div>
              <div class="fs-5 fw-bold"><?= h($product['gtin']) ?></div>
            </div>

            <div class="mb-3">
              <div class="small text-muted fw-semibold">Description</div>
              <div><?= nl2br(h($product['description_en'])) ?></div>
            </div>

            <div class="row g-2">
              <div class="col-6">
                <div class="p-3 bg-light rounded-3">
                  <div class="small text-muted fw-semibold">Weight</div>
                  <div class="fw-bold">
                    <?= h($product['gross_weight']) ?> <?= h($product['weight_unit']) ?>
                  </div>
                </div>
              </div>
              <div class="col-6">
                <div class="p-3 bg-light rounded-3">
                  <div class="small text-muted fw-semibold">Net content</div>
                  <div class="fw-bold">
                    <?= h($product['net_weight']) ?> <?= h($product['weight_unit']) ?>
                  </div>
                </div>
              </div>
            </div>

            <hr>

            <div class="d-flex flex-wrap gap-2">
              <?php if (!empty($product['brand'])): ?>
                <span class="badge text-bg-secondary">Brand: <?= h($product['brand']) ?></span>
              <?php endif; ?>
              <?php if (!empty($product['country_of_origin'])): ?>
                <span class="badge text-bg-secondary">Origin: <?= h($product['country_of_origin']) ?></span>
              <?php endif; ?>
            </div>

          </div>
        </div>
      </div>
    </div>

  <?php endif; ?>

</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>