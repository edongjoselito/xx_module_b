<?php
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    header('Content-Type: application/json; charset=UTF-8');
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use GET.'], JSON_UNESCAPED_SLASHES);
    exit;
}

header('Content-Type: application/json; charset=UTF-8');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = $pdo->prepare(
        "SELECT
            id,
            company_name,
            company_address,
            company_telephone,
            company_email,
            owner_name,
            owner_mobile,
            owner_email,
            contact_name,
            contact_mobile,
            contact_email,
            is_deactivated,
            created_at,
            updated_at
         FROM companies
         WHERE id = ?
         LIMIT 1"
    );
    $stmt->execute([$id]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$company) {
        http_response_code(404);
        echo json_encode([
            'error' => 'Company not found.',
            'id' => $id,
        ], JSON_UNESCAPED_SLASHES);
        exit;
    }

    echo json_encode($company, JSON_UNESCAPED_SLASHES);
    exit;
}

$status = strtolower(trim((string)($_GET['status'] ?? 'active')));

$where = '';
if ($status === 'active') {
    $where = 'WHERE is_deactivated = 0';
} elseif ($status === 'deactivated') {
    $where = 'WHERE is_deactivated = 1';
}

$sql = "SELECT
            id,
            company_name,
            company_address,
            company_telephone,
            company_email,
            owner_name,
            owner_mobile,
            owner_email,
            contact_name,
            contact_mobile,
            contact_email,
            is_deactivated,
            created_at,
            updated_at
        FROM companies
        $where
        ORDER BY id DESC";

$companies = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'count' => count($companies),
    'status' => ($status === 'deactivated' || $status === 'all') ? $status : 'active',
    'items' => $companies,
], JSON_UNESCAPED_SLASHES);
