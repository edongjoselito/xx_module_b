<?php
// gtin-bulk-verify.php (PUBLIC) - Bulk GTIN Validation
require_once 'db.php';

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$inputText = '';
$results = [];   // each item: ['gtin' => '...', 'valid' => bool]
$allValid = false;
$hasSubmitted = ($_SERVER['REQUEST_METHOD'] === 'POST');

if ($hasSubmitted) {
    $inputText = trim((string)($_POST['gtins'] ?? ''));

    // Split by line breaks (supports Windows/Mac/Linux)
    $lines = preg_split("/\r\n|\n|\r/", $inputText);

    // Keep order, skip empty lines
    $gtins = [];
    foreach ($lines as $ln) {
        $g = trim($ln);
        if ($g === '') continue;
        $gtins[] = $g;
    }

    if (!empty($gtins)) {
        // Build placeholders for IN query
        $placeholders = implode(',', array_fill(0, count($gtins), '?'));

        // Valid when exists in products and not hidden (is_hidden = 0)
        $sql = "SELECT gtin FROM products WHERE is_hidden = 0 AND gtin IN ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($gtins);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Convert to set for fast lookup
        $validSet = [];
        foreach ($rows as $v) {
            $validSet[(string)$v] = true;
        }

        $allOk = true;
        foreach ($gtins as $g) {
            $ok = isset($validSet[$g]);
            $results[] = ['gtin' => $g, 'valid' => $ok];
            if (!$ok) $allOk = false;
        }

        $allValid = $allOk && count($results) > 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bulk GTIN Verification</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <style>
    body { background: #f6f7fb; }
    .card { border: 0; }
    .tick {
      display:inline-flex; align-items:center; justify-content:center;
      width: 28px; height: 28px; border-radius: 50%;
      background: rgba(25,135,84,.12); color: #198754; font-weight: 800;
      border: 1px solid rgba(25,135,84,.25);
    }
    .cross {
      display:inline-flex; align-items:center; justify-content:center;
      width: 28px; height: 28px; border-radius: 50%;
      background: rgba(220,53,69,.12); color: #dc3545; font-weight: 800;
      border: 1px solid rgba(220,53,69,.25);
    }
    .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
    textarea { min-height: 220px; }
  </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
  <div class="container d-flex justify-content-between align-items-center">
    <a class="navbar-brand fw-bold" href="./">Public Products</a>

    <div class="d-flex gap-2">
      <a href="./" class="btn btn-outline-light btn-sm">Back to Products</a>
      <a href="login" class="btn btn-outline-light btn-sm">Admin Login</a>
    </div>
  </div>
</nav>

<div class="container py-4">

  <div class="mb-3">
    <h3 class="mb-0 fw-bold">Public GTIN Bulk Verification</h3>
    <div class="text-muted">Paste multiple GTIN numbers (one per line) to check if they are registered and visible.</div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="POST" action="">
        <div class="mb-2 fw-semibold">GTIN Numbers (one per line)</div>
        <textarea name="gtins" class="form-control mono" placeholder="e.g.&#10;0123456789012&#10;0123456789013"><?= h($inputText) ?></textarea>

        <div class="d-flex gap-2 mt-3">
          <button type="submit" class="btn btn-primary">Validate</button>
          <a href="gtin-bulk-verify.php" class="btn btn-light">Clear</a>
        </div>
      </form>
    </div>
  </div>

  <?php if ($hasSubmitted): ?>
    <div class="mt-4">

      <?php if (empty($results)): ?>
        <div class="alert alert-warning mb-0">
          No GTINs detected. Please enter at least one GTIN (one per line).
        </div>
      <?php else: ?>

        <?php if ($allValid): ?>
          <div class="alert alert-success d-flex align-items-center gap-2">
            <span class="tick">✓</span>
            <div class="fw-semibold">All valid</div>
          </div>
        <?php endif; ?>

        <div class="card shadow-sm">
          <div class="card-header bg-white fw-bold">Validation Results</div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped mb-0 align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width: 70px;">Status</th>
                    <th>GTIN</th>
                    <th style="width: 140px;">Result</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($results as $r): ?>
                    <tr>
                      <td class="text-center">
                        <?php if ($r['valid']): ?>
                          <span class="tick">✓</span>
                        <?php else: ?>
                          <span class="cross">×</span>
                        <?php endif; ?>
                      </td>
                      <td class="mono"><?= h($r['gtin']) ?></td>
                      <td>
                        <?php if ($r['valid']): ?>
                          <span class="badge text-bg-success">Valid</span>
                        <?php else: ?>
                          <span class="badge text-bg-danger">Invalid</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="text-muted small mt-2">
          Note: A GTIN is considered valid when it exists in the database and is not hidden.
        </div>

      <?php endif; ?>
    </div>
  <?php endif; ?>

</div>

<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>