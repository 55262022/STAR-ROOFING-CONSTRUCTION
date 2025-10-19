<?php
require_once '../../database/starroofing_db.php';

header('Content-Type: application/json');

try {
    $query = "SELECT product_id, name, price, unit FROM products WHERE is_archived = 0";
    $result = $conn->query($query);

    $materials = [];
    while ($row = $result->fetch_assoc()) {
        $materials[] = $row;
    }

    echo json_encode(['status' => 'success', 'data' => $materials]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
