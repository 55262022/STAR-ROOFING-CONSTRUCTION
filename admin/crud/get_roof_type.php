<?php
include '../../includes/auth.php';
require_once '../../database/starroofing_db.php';
header('Content-Type: application/json');

// Fetch all roof types from database
try {
    $query = "SELECT id, type_name, description, steel_per_sqm, screw_per_sqm, paint_per_sqm, slope_percentage, image_path 
              FROM roof_types
              ORDER BY id DESC";
    $result = $conn->query($query);

    $roof_types = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $roof_types[] = $row;
        }
        echo json_encode([
            'status' => 'success',
            'data' => $roof_types
        ]);
    } else {
        echo json_encode([
            'status' => 'empty',
            'message' => 'No roof types found.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
