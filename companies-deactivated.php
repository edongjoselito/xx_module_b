<?php
// companies-deactivated.php (ADMIN) - List of Deactivated Companies
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// ✅ Fetch only deactivated companies
$companies = $pdo->query("
    SELECT *
    FROM companies
    WHERE is_deactivated = 1
    ORDER BY id DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Deactivated Companies</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
  <div class="container d-flex justify-content-between">
    <a class="navbar-brand fw-bold" href="companies.php">Companies</a>

    <div class="d-flex gap-2">
      <a class="btn btn-outline-light btn-sm" href="admin_dashboard.php">Dashboard</a>
      <a class="btn btn-outline-light btn-sm" href="products.php">Products</a>
      <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="mb-0 fw-bold">Deactivated Companies</h3>
      <div class="text-muted small">These companies are hidden from public product listing.</div>
    </div>
    <a href="companies.php" class="btn btn-outline-secondary btn-sm">← Back to Companies</a>
  </div>

  <div class="card shadow-sm">
    <div class="card-header bg-white fw-bold">List</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th>Company</th>
              <th>Telephone</th>
              <th>Email</th>
              <th class="text-end" style="width:220px;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$companies): ?>
              <tr>
                <td colspan="4" class="text-center text-muted py-4">
                  No deactivated companies found.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($companies as $c): ?>
                <tr>
                  <td>
                    <div class="fw-bold"><?= h($c['company_name']) ?></div>
                    <div class="small text-muted"><?= h($c['company_address']) ?></div>
                  </td>
                  <td><?= h($c['company_telephone']) ?></td>
                  <td><?= h($c['company_email']) ?></td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary"
                       href="companies.php?action=edit&id=<?= (int)$c['id'] ?>">
                      Edit / Reactivate
                    </a>

                   
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