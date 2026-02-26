<?php
require_once 'db.php';

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$sql = "
    SELECT
        p.gtin,
        p.name_en,
        p.description_en,
        p.image_path,
        p.gross_weight,
        p.net_weight,
        p.weight_unit,
        c.company_name
    FROM products p
    LEFT JOIN companies c ON c.id = p.company_id
    WHERE p.is_hidden = 0
      AND (c.is_deactivated = 0 OR c.is_deactivated IS NULL)
    ORDER BY p.id DESC
";

$products = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Products</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">

  <style>
    body { background: #f6f7fb; }

    .img-wrap{
      height: 220px;
      width: 100%;
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      border-bottom: 1px solid rgba(0,0,0,.06);
    }
    .img-wrap img{
      max-height: 100%;
      max-width: 100%;
      width: auto;
      height: auto;
      object-fit: contain;    
      object-position: center;
      display: block;
    }

    .badge-soft {
      background: rgba(13,110,253,.08);
      color: #0d6efd;
      border: 1px solid rgba(13,110,253,.15);
    }
    .muted-line {
      color: #6c757d;
      font-size: .9rem;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
  <div class="container d-flex justify-content-between align-items-center">
    <a class="navbar-brand fw-bold" href="./">Public Products</a>

    <div class="d-flex gap-2">
      <a href="gtin-bulk-verify.php" class="btn btn-outline-light btn-sm">
        Bulk GTIN Verification
      </a>

      <a href="login" class="btn btn-outline-light btn-sm">
        Admin Login
      </a>
    </div>
  </div>
</nav>

<div class="container py-4">

  <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-3">
    <div>
      <h3 class="mb-0 fw-bold">All Products</h3>
      <div class="text-muted">Browse products</div>
    </div>
  </div>

  <?php if (!$products): ?>
    <div class="alert alert-info">No products available.</div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($products as $p): ?>
        <?php
          $gtin = trim((string)$p['gtin']);
          $img  = trim((string)($p['image_path'] ?? ''));

     
          $placeholder = 'assets/images/no-image.avif';

    
          $imgSrc = $placeholder;
          if ($img !== '' && file_exists(__DIR__ . '/' . $img)) {
              $imgSrc = $img;
          }
        ?>
        <div class="col-12 col-sm-6 col-lg-4">
          <div class="card shadow-sm border-0 h-100">

         
            <div class="img-wrap">
              <img src="<?= h($imgSrc) ?>" alt="Product Image">
            </div>

            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start gap-2">
                <div class="fw-bold"><?= h($p['name_en']) ?></div>
                <span class="badge badge-soft">GTIN: <?= h($gtin) ?></span>
              </div>

              <div class="muted-line mt-1">
                Company: <span class="fw-semibold"><?= h($p['company_name'] ?? 'N/A') ?></span>
              </div>

              <div class="mt-2">
                <div class="small text-muted fw-semibold">Description</div>
                <div class="small">
                  <?= h($p['description_en']) ?>
                </div>
              </div>

              <div class="mt-3 d-flex flex-wrap gap-2">
                <span class="badge text-bg-light">
                  Weight: <?= h($p['gross_weight']) ?> <?= h($p['weight_unit']) ?>
                </span>
                <span class="badge text-bg-light">
                  Net content: <?= h($p['net_weight']) ?> <?= h($p['weight_unit']) ?>
                </span>
              </div>
            </div>

            <div class="card-footer bg-white border-0">
              <a href="01/<?= rawurlencode($gtin) ?>" class="btn btn-primary w-100">
                View Details
              </a>
            </div>

          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>

<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>