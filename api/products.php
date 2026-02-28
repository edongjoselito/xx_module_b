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

$gtin = trim((string)($_GET['gtin'] ?? ''));

if ($gtin !== '') {
    $stmt = $pdo->prepare(
        "SELECT
            p.gtin,
            p.name_en,
            p.name_fr,
            p.description_en,
            p.description_fr,
            p.brand,
            p.country_of_origin,
            p.gross_weight,
            p.net_weight,
            p.weight_unit,
            p.image_path,
            c.company_name
         FROM products p
         LEFT JOIN companies c ON c.id = p.company_id
         WHERE p.gtin = ?
           AND p.is_hidden = 0
           AND (c.is_deactivated = 0 OR c.is_deactivated IS NULL)
         LIMIT 1"
    );

    $stmt->execute([$gtin]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        http_response_code(404);
        echo json_encode([
            'error' => 'Product not found.',
            'gtin' => $gtin,
        ], JSON_UNESCAPED_SLASHES);
        exit;
    }

    echo json_encode($product, JSON_UNESCAPED_SLASHES);
    exit;
}

$sql = "SELECT
            p.gtin,
            p.name_en,
            p.name_fr,
            p.description_en,
            p.description_fr,
            p.brand,
            p.country_of_origin,
            p.gross_weight,
            p.net_weight,
            p.weight_unit,
            p.image_path,
            c.company_name
        FROM products p
        LEFT JOIN companies c ON c.id = p.company_id
        WHERE p.is_hidden = 0
          AND (c.is_deactivated = 0 OR c.is_deactivated IS NULL)
        ORDER BY p.id DESC";

$products = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'count' => count($products),
    'items' => $products,
], JSON_UNESCAPED_SLASHES);
