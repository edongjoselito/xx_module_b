<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }

require_once 'db.php';
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$action = $_GET['action'] ?? ''; // create | edit | delete
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = "";

// ADD
if (isset($_POST['add_company'])) {
    $company_name      = trim($_POST['company_name'] ?? '');
    $company_address   = trim($_POST['company_address'] ?? '');
    $company_telephone = trim($_POST['company_telephone'] ?? '');
    $company_email     = trim($_POST['company_email'] ?? '');

    $owner_name        = trim($_POST['owner_name'] ?? '');
    $owner_mobile      = trim($_POST['owner_mobile'] ?? '');
    $owner_email       = trim($_POST['owner_email'] ?? '');

    $contact_name      = trim($_POST['contact_name'] ?? '');
    $contact_mobile    = trim($_POST['contact_mobile'] ?? '');
    $contact_email     = trim($_POST['contact_email'] ?? '');

    $is_deactivated    = isset($_POST['is_deactivated']) ? 1 : 0;

    if ($company_name === '' || $company_address === '' || $company_telephone === '') {
        $message = "Please fill required fields (Name, Address, Telephone).";
        $action = 'create';
    } else {
        $now = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("
            INSERT INTO companies
            (company_name, company_address, company_telephone, company_email,
             owner_name, owner_mobile, owner_email,
             contact_name, contact_mobile, contact_email,
             is_deactivated, created_at, updated_at)
            VALUES
            (?, ?, ?, ?,
             ?, ?, ?,
             ?, ?, ?,
             ?, ?, ?)
        ");
        $stmt->execute([
            $company_name, $company_address, $company_telephone, $company_email,
            $owner_name, $owner_mobile, $owner_email,
            $contact_name, $contact_mobile, $contact_email,
            $is_deactivated, $now, $now
        ]);
        header("Location: companies.php");
        exit;
    }
}

// UPDATE
if (isset($_POST['update_company'])) {
    $editId = (int)($_POST['id'] ?? 0);

    $company_name      = trim($_POST['company_name'] ?? '');
    $company_address   = trim($_POST['company_address'] ?? '');
    $company_telephone = trim($_POST['company_telephone'] ?? '');
    $company_email     = trim($_POST['company_email'] ?? '');

    $owner_name        = trim($_POST['owner_name'] ?? '');
    $owner_mobile      = trim($_POST['owner_mobile'] ?? '');
    $owner_email       = trim($_POST['owner_email'] ?? '');

    $contact_name      = trim($_POST['contact_name'] ?? '');
    $contact_mobile    = trim($_POST['contact_mobile'] ?? '');
    $contact_email     = trim($_POST['contact_email'] ?? '');

    $is_deactivated    = isset($_POST['is_deactivated']) ? 1 : 0;

    if ($company_name === '' || $company_address === '' || $company_telephone === '') {
        $message = "Please fill required fields (Name, Address, Telephone).";
        $action = 'edit';
        $id = $editId;
    } else {
        $now = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("
            UPDATE companies SET
                company_name=?, company_address=?, company_telephone=?, company_email=?,
                owner_name=?, owner_mobile=?, owner_email=?,
                contact_name=?, contact_mobile=?, contact_email=?,
                is_deactivated=?, updated_at=?
            WHERE id=?
        ");
        $stmt->execute([
            $company_name, $company_address, $company_telephone, $company_email,
            $owner_name, $owner_mobile, $owner_email,
            $contact_name, $contact_mobile, $contact_email,
            $is_deactivated, $now, $editId
        ]);
        header("Location: companies.php");
        exit;
    }
}

// DELETE
if ($action === 'delete' && $id > 0) {
    $stmt = $pdo->prepare("DELETE FROM companies WHERE id=?");
    $stmt->execute([$id]);
    header("Location: companies.php");
    exit;
}

// LIST
$companies = $pdo->query("SELECT * FROM companies ORDER BY id DESC")->fetchAll();

// EDIT ROW
$editRow = null;
if ($action === 'edit' && $id > 0) {
    $st = $pdo->prepare("SELECT * FROM companies WHERE id=?");
    $st->execute([$id]);
    $editRow = $st->fetch();
    if (!$editRow) { header("Location: companies.php"); exit; }
}

// Keep values on validation fail
$v = function($key) use ($editRow){
    if (isset($_POST[$key])) return trim((string)$_POST[$key]);
    return $editRow[$key] ?? '';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Companies</title>
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

  <?php if ($message): ?>
    <div class="alert alert-warning"><?= h($message) ?></div>
  <?php endif; ?>

  <div class="d-flex justify-content-end mb-3">
    <a href="companies.php?action=create" class="btn btn-success btn-sm">+ Add New Company</a>
  </div>

  <?php if ($action === 'create' || $action === 'edit'): ?>
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-white fw-bold d-flex justify-content-between">
        <span><?= $action==='edit' ? 'Edit Company' : 'Add Company' ?></span>
        <a href="companies.php" class="btn btn-outline-secondary btn-sm">Close</a>
      </div>
      <div class="card-body">
        <form method="POST" class="row g-3">
          <?php if ($action==='edit'): ?>
            <input type="hidden" name="id" value="<?= (int)$editRow['id'] ?>">
          <?php endif; ?>

          <div class="col-md-6">
            <label class="form-label">Company Name *</label>
            <input type="text" name="company_name" class="form-control" required value="<?= h($v('company_name')) ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label">Telephone *</label>
            <input type="text" name="company_telephone" class="form-control" required value="<?= h($v('company_telephone')) ?>">
          </div>

          <div class="col-12">
            <label class="form-label">Address *</label>
            <input type="text" name="company_address" class="form-control" required value="<?= h($v('company_address')) ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label">Company Email</label>
            <input type="email" name="company_email" class="form-control" value="<?= h($v('company_email')) ?>">
          </div>

          <div class="col-md-6 d-flex align-items-end">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="is_deactivated" value="1"
                <?= ((int)$v('is_deactivated') === 1) ? 'checked' : '' ?>>
              <label class="form-check-label">Deactivated</label>
            </div>
          </div>

          <hr class="mt-3">

          <div class="col-md-4">
            <label class="form-label">Owner Name</label>
            <input type="text" name="owner_name" class="form-control" value="<?= h($v('owner_name')) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Owner Mobile</label>
            <input type="text" name="owner_mobile" class="form-control" value="<?= h($v('owner_mobile')) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Owner Email</label>
            <input type="email" name="owner_email" class="form-control" value="<?= h($v('owner_email')) ?>">
          </div>

          <hr class="mt-3">

          <div class="col-md-4">
            <label class="form-label">Contact Name</label>
            <input type="text" name="contact_name" class="form-control" value="<?= h($v('contact_name')) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Contact Mobile</label>
            <input type="text" name="contact_mobile" class="form-control" value="<?= h($v('contact_mobile')) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Contact Email</label>
            <input type="email" name="contact_email" class="form-control" value="<?= h($v('contact_email')) ?>">
          </div>

          <div class="col-12 d-flex gap-2">
            <?php if ($action==='edit'): ?>
              <button class="btn btn-primary" name="update_company">Update</button>
            <?php else: ?>
              <button class="btn btn-success" name="add_company">Save</button>
              <button class="btn btn-outline-secondary" type="reset">Clear</button>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-header bg-white fw-bold">Company List</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th>Company</th>
              <th>Telephone</th>
              <th>Email</th>
              <th>Status</th>
              <th class="text-end" style="width:180px;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$companies): ?>
              <tr><td colspan="6" class="text-center text-muted py-4">No record found.</td></tr>
            <?php else: ?>
              <?php foreach ($companies as $c): ?>
                <tr>
                  <td>
                    <div class="fw-bold"><?= h($c['company_name']) ?></div>
                    <div class="small text-muted"><?= h($c['company_address']) ?></div>
                  </td>
                  <td><?= h($c['company_telephone']) ?></td>
                  <td><?= h($c['company_email']) ?></td>
                  <td>
                    <?= ((int)$c['is_deactivated'] === 1)
                      ? '<span class="badge bg-secondary">Deactivated</span>'
                      : '<span class="badge bg-success">Active</span>' ?>
                  </td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary"
                       href="companies.php?action=edit&id=<?= (int)$c['id'] ?>">Edit</a>
                    <a class="btn btn-sm btn-danger"
                       href="companies.php?action=delete&id=<?= (int)$c['id'] ?>"
                       onclick="return confirm('Delete this company?');">Delete</a>
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