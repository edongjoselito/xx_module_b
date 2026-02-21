<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }

require_once 'db.php';
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$action  = $_GET['action'] ?? ''; // create | edit | delete
$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = "";

// Companies for dropdown
$companies = $pdo->query("SELECT id, company_name FROM companies ORDER BY company_name ASC")->fetchAll();

/**
 * Upload helper (optional)
 * - Accept jpg/jpeg/png/webp
 * - Save to uploads/products/
 * - Return saved relative path or null
 */
function upload_product_image(string $fieldName = 'product_image'): ?string
{
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // no upload
    }

    if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return null; // upload error
    }

    $tmp  = $_FILES[$fieldName]['tmp_name'];
    $name = $_FILES[$fieldName]['name'] ?? '';
    $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));

    $allowed = ['jpg','jpeg','png','webp'];
    if (!in_array($ext, $allowed, true)) {
        return '__INVALID_TYPE__';
    }

    // Basic file size guard (2MB)
    $size = (int)($_FILES[$fieldName]['size'] ?? 0);
    if ($size > 2 * 1024 * 1024) {
        return '__TOO_LARGE__';
    }

    $dirFs  = __DIR__ . '/uploads/products';
    $dirWeb = 'uploads/products';

    if (!is_dir($dirFs)) {
        @mkdir($dirFs, 0777, true);
    }

    $newName = 'prod_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destFs  = $dirFs . '/' . $newName;

    if (!move_uploaded_file($tmp, $destFs)) {
        return null;
    }

    return $dirWeb . '/' . $newName;
}

/**
 * GTIN validation: required, 13 or 14 digits only
 */
function is_valid_gtin(string $gtin): bool
{
    return (bool)preg_match('/^\d{13,14}$/', $gtin);
}

// ADD PRODUCT
if (isset($_POST['add_product'])) {

    $company_id        = (int)($_POST['company_id'] ?? 0);
    $gtin              = trim($_POST['gtin'] ?? '');
    $name_en           = trim($_POST['name_en'] ?? '');
    $name_fr           = trim($_POST['name_fr'] ?? '');
    $description_en    = trim($_POST['description_en'] ?? '');
    $description_fr    = trim($_POST['description_fr'] ?? '');
    $brand             = trim($_POST['brand'] ?? '');
    $country_of_origin = trim($_POST['country_of_origin'] ?? '');
    $gross_weight      = trim($_POST['gross_weight'] ?? '0.000');
    $net_weight        = trim($_POST['net_weight'] ?? '0.000');
    $weight_unit       = trim($_POST['weight_unit'] ?? 'g');
    $is_hidden         = isset($_POST['is_hidden']) ? 1 : 0;

    // ✅ Required + GTIN rule
    if ($company_id <= 0 || $gtin === '' || $name_en === '' || $name_fr === '') {
        $message = "Please fill required fields (Company, GTIN, Name EN, Name FR).";
        $action = 'create';
    } elseif (!is_valid_gtin($gtin)) {
        $message = "GTIN must be 13 or 14 digits only.";
        $action = 'create';
    } else {

        // ✅ Optional upload
        $uploadedPath = upload_product_image('product_image');
        if ($uploadedPath === '__INVALID_TYPE__') {
            $message = "Invalid image type. Allowed: JPG, JPEG, PNG, WEBP.";
            $action = 'create';
        } elseif ($uploadedPath === '__TOO_LARGE__') {
            $message = "Image too large. Max 2MB.";
            $action = 'create';
        } else {
            $now = date('Y-m-d H:i:s');

            $stmt = $pdo->prepare("
                INSERT INTO products
                (company_id, gtin, name_en, name_fr, description_en, description_fr,
                 brand, country_of_origin, gross_weight, net_weight, weight_unit,
                 image_path, is_hidden, created_at, updated_at)
                VALUES
                (?, ?, ?, ?, ?, ?,
                 ?, ?, ?, ?, ?,
                 ?, ?, ?, ?)
            ");

            $stmt->execute([
                $company_id, $gtin, $name_en, $name_fr, $description_en, $description_fr,
                $brand, $country_of_origin, $gross_weight, $net_weight, $weight_unit,
                $uploadedPath, // can be null
                $is_hidden, $now, $now
            ]);

            header("Location: products.php");
            exit;
        }
    }
}

// UPDATE PRODUCT
if (isset($_POST['update_product'])) {

    $editId            = (int)($_POST['id'] ?? 0);
    $company_id        = (int)($_POST['company_id'] ?? 0);
    $gtin              = trim($_POST['gtin'] ?? '');
    $name_en           = trim($_POST['name_en'] ?? '');
    $name_fr           = trim($_POST['name_fr'] ?? '');
    $description_en    = trim($_POST['description_en'] ?? '');
    $description_fr    = trim($_POST['description_fr'] ?? '');
    $brand             = trim($_POST['brand'] ?? '');
    $country_of_origin = trim($_POST['country_of_origin'] ?? '');
    $gross_weight      = trim($_POST['gross_weight'] ?? '0.000');
    $net_weight        = trim($_POST['net_weight'] ?? '0.000');
    $weight_unit       = trim($_POST['weight_unit'] ?? 'g');
    $is_hidden         = isset($_POST['is_hidden']) ? 1 : 0;

    if ($company_id <= 0 || $gtin === '' || $name_en === '' || $name_fr === '') {
        $message = "Please fill required fields (Company, GTIN, Name EN, Name FR).";
        $action = 'edit'; $id = $editId;
    } elseif (!is_valid_gtin($gtin)) {
        $message = "GTIN must be 13 or 14 digits only.";
        $action = 'edit'; $id = $editId;
    } else {

        // Fetch old row to keep existing image if no new upload
        $stOld = $pdo->prepare("SELECT image_path FROM products WHERE id=?");
        $stOld->execute([$editId]);
        $oldRow = $stOld->fetch();
        $oldImage = $oldRow['image_path'] ?? null;

        // Optional new upload
        $uploadedPath = upload_product_image('product_image');
        if ($uploadedPath === '__INVALID_TYPE__') {
            $message = "Invalid image type. Allowed: JPG, JPEG, PNG, WEBP.";
            $action = 'edit'; $id = $editId;
        } elseif ($uploadedPath === '__TOO_LARGE__') {
            $message = "Image too large. Max 2MB.";
            $action = 'edit'; $id = $editId;
        } else {
            $finalImage = $uploadedPath ?: $oldImage;

            $now = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare("
                UPDATE products SET
                    company_id=?, gtin=?, name_en=?, name_fr=?,
                    description_en=?, description_fr=?,
                    brand=?, country_of_origin=?,
                    gross_weight=?, net_weight=?, weight_unit=?,
                    image_path=?, is_hidden=?, updated_at=?
                WHERE id=?
            ");
            $stmt->execute([
                $company_id, $gtin, $name_en, $name_fr,
                $description_en, $description_fr,
                $brand, $country_of_origin,
                $gross_weight, $net_weight, $weight_unit,
                $finalImage,
                $is_hidden, $now, $editId
            ]);

            header("Location: products.php");
            exit;
        }
    }
}

// DELETE PRODUCT
if ($action === 'delete' && $id > 0) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
    $stmt->execute([$id]);
    header("Location: products.php");
    exit;
}

// LIST PRODUCTS (with company name)
$products = $pdo->query("
    SELECT p.*, c.company_name
    FROM products p
    LEFT JOIN companies c ON c.id = p.company_id
    ORDER BY p.id DESC
")->fetchAll();

// EDIT ROW
$editRow = null;
if ($action === 'edit' && $id > 0) {
    $st = $pdo->prepare("SELECT * FROM products WHERE id=?");
    $st->execute([$id]);
    $editRow = $st->fetch();
    if (!$editRow) { header("Location: products.php"); exit; }
}

// Keep values on validation fail
$v = function($key, $fallback='') use ($editRow){
    if (isset($_POST[$key])) return trim((string)$_POST[$key]);
    if ($editRow && isset($editRow[$key])) return (string)$editRow[$key];
    return $fallback;
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Products</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
  <div class="container d-flex justify-content-between">
    <a class="navbar-brand fw-bold" href="products.php">Products</a>

    <div class="d-flex gap-2">
      <a class="btn btn-outline-light btn-sm" href="admin_dashboard.php">Dashboard</a>
      <a class="btn btn-outline-light btn-sm" href="companies.php">Companies</a>
      <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">

  <?php if ($message): ?>
    <div class="alert alert-warning"><?= h($message) ?></div>
  <?php endif; ?>

  <div class="d-flex justify-content-end mb-3">
    <a href="products.php?action=create" class="btn btn-success btn-sm">+ Add New Product</a>
  </div>

  <?php if (!$companies): ?>
    <div class="alert alert-info">
      You must add at least 1 company first before adding products.
      <a href="companies.php" class="alert-link">Go to Companies</a>
    </div>
  <?php endif; ?>

  <?php if (($action === 'create' || $action === 'edit') && $companies): ?>
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-white fw-bold d-flex justify-content-between">
        <span><?= $action==='edit' ? 'Edit Product' : 'Add Product' ?></span>
        <a href="products.php" class="btn btn-outline-secondary btn-sm">Close</a>
      </div>

      <div class="card-body">
        <!-- ✅ enctype needed for file upload -->
        <form method="POST" class="row g-3" enctype="multipart/form-data">

          <?php if ($action==='edit'): ?>
            <input type="hidden" name="id" value="<?= (int)$editRow['id'] ?>">
          <?php endif; ?>

          <div class="col-md-6">
            <label class="form-label">Company *</label>
            <select name="company_id" class="form-select" required>
              <option value="">-- Select Company --</option>
              <?php foreach ($companies as $co): ?>
                <option value="<?= (int)$co['id'] ?>"
                  <?= ((int)$v('company_id', 0) === (int)$co['id']) ? 'selected' : '' ?>>
                  <?= h($co['company_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">GTIN (13 or 14 digits) *</label>
            <input
              type="text"
              name="gtin"
              class="form-control"
              required
              inputmode="numeric"
              pattern="\d{13,14}"
              maxlength="14"
              placeholder="e.g. 03000123456789"
              value="<?= h($v('gtin')) ?>"
            >
            <div class="form-text">Only numbers allowed (13–14 digits).</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Name (EN) *</label>
            <input type="text" name="name_en" class="form-control" required value="<?= h($v('name_en')) ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label">Name (FR) *</label>
            <input type="text" name="name_fr" class="form-control" required value="<?= h($v('name_fr')) ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label">Description (EN)</label>
            <textarea name="description_en" class="form-control" rows="3"><?= h($v('description_en')) ?></textarea>
          </div>

          <div class="col-md-6">
            <label class="form-label">Description (FR)</label>
            <textarea name="description_fr" class="form-control" rows="3"><?= h($v('description_fr')) ?></textarea>
          </div>

          <div class="col-md-4">
            <label class="form-label">Brand</label>
            <input type="text" name="brand" class="form-control" value="<?= h($v('brand')) ?>">
          </div>

          <div class="col-md-4">
            <label class="form-label">Country of Origin</label>
            <input type="text" name="country_of_origin" class="form-control" value="<?= h($v('country_of_origin')) ?>">
          </div>

          <div class="col-md-4">
            <label class="form-label">Product Image (optional)</label>
            <input type="file" name="product_image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
            <div class="form-text">Allowed: JPG, JPEG, PNG, WEBP (Max 2MB).</div>

            <?php if ($action==='edit' && !empty($editRow['image_path'])): ?>
              <div class="mt-2">
                <div class="small text-muted">Current Image:</div>
                <img src="<?= h($editRow['image_path']) ?>" alt="Current" style="max-width:140px; height:auto;" class="rounded border">
              </div>
            <?php endif; ?>
          </div>

          <div class="col-md-4">
            <label class="form-label">Gross Weight</label>
            <input type="number" step="0.001" name="gross_weight" class="form-control" value="<?= h($v('gross_weight', '0.000')) ?>">
          </div>

          <div class="col-md-4">
            <label class="form-label">Net Weight</label>
            <input type="number" step="0.001" name="net_weight" class="form-control" value="<?= h($v('net_weight', '0.000')) ?>">
          </div>

          <div class="col-md-4">
            <label class="form-label">Weight Unit</label>
            <input type="text" name="weight_unit" class="form-control" value="<?= h($v('weight_unit', 'g')) ?>">
          </div>

          <div class="col-md-6 d-flex align-items-end">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="is_hidden" value="1"
                <?= ((int)$v('is_hidden', 0) === 1) ? 'checked' : '' ?>>
              <label class="form-check-label">Hidden</label>
            </div>
          </div>

          <div class="col-12 d-flex gap-2">
            <?php if ($action==='edit'): ?>
              <button class="btn btn-primary" name="update_product">Update</button>
            <?php else: ?>
              <button class="btn btn-success" name="add_product">Save</button>
              <button class="btn btn-outline-secondary" type="reset">Clear</button>
            <?php endif; ?>
          </div>

        </form>
      </div>
    </div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-header bg-white fw-bold">Product List</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th>Company</th>
              <th>GTIN</th>
              <th>Name (EN)</th>
              <th>Brand</th>
              <th>Status</th>
              <th class="text-end" style="width:180px;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$products): ?>
              <tr><td colspan="6" class="text-center text-muted py-4">No record found.</td></tr>
            <?php else: ?>
              <?php foreach ($products as $p): ?>
                <tr>
                  <td><?= h($p['company_name'] ?? '') ?></td>
                  <td><?= h($p['gtin']) ?></td>
                  <td><?= h($p['name_en']) ?></td>
                  <td><?= h($p['brand']) ?></td>
                  <td>
                    <?= ((int)$p['is_hidden'] === 1)
                      ? '<span class="badge bg-secondary">Hidden</span>'
                      : '<span class="badge bg-success">Visible</span>' ?>
                  </td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary"
                       href="products.php?action=edit&id=<?= (int)$p['id'] ?>">Edit</a>
                    <a class="btn btn-sm btn-danger"
                       href="products.php?action=delete&id=<?= (int)$p['id'] ?>"
                       onclick="return confirm('Delete this product?');">Delete</a>
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