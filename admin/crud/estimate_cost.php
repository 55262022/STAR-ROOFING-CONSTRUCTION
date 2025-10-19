<?php
require_once '../../database/starroofing_db.php';

header('Content-Type: application/json');

$material_id = $_POST['material_id'] ?? null;
$area = $_POST['area'] ?? 0;

if (!$material_id || !$area) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT price, unit, name FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $material_id);
    $stmt->execute();
    $stmt->bind_result($price, $unit, $name);
    $stmt->fetch();

    $stmt->close();

    $total_cost = $area * $price;

    echo json_encode([
        'status' => 'success',
        'data' => [
            'material' => $name,
            'unit' => $unit,
            'area' => $area,
            'price' => $price,
            'total_cost' => $total_cost
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
