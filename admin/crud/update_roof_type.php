<?php
include '../../includes/auth.php';
require_once '../../database/starroofing_db.php';
header('Content-Type: application/json');

// Check if all required fields exist
if (!empty($_POST['id']) && !empty($_POST['type_name']) && !empty($_POST['description']) && 
    isset($_POST['steel_per_sqm']) && isset($_POST['screw_per_sqm']) && 
    isset($_POST['paint_per_sqm']) && isset($_POST['slope_percentage'])) {

    $id = intval($_POST['id']);
    $type_name = trim($_POST['type_name']);
    $description = trim($_POST['description']);
    $steel_per_sqm = floatval($_POST['steel_per_sqm']);
    $screw_per_sqm = floatval($_POST['screw_per_sqm']);
    $paint_per_sqm = floatval($_POST['paint_per_sqm']);
    $slope_percentage = floatval($_POST['slope_percentage']);

    // Image upload handling (optional)
    $image_path = $_POST['existing_image'] ?? ''; // keep old image if not replaced
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = '../../uploads/roof_types/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $image_name = basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $image_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $image_path = 'uploads/roof_types/' . $image_name;
        }
    }

    // Update query
    $stmt = $conn->prepare("UPDATE roof_types 
        SET type_name = ?, description = ?, steel_per_sqm = ?, screw_per_sqm = ?, 
            paint_per_sqm = ?, slope_percentage = ?, image_path = ?
        WHERE id = ?");
    $stmt->bind_param("ssddddsi", $type_name, $description, $steel_per_sqm, 
        $screw_per_sqm, $paint_per_sqm, $slope_percentage, $image_path, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Roof type updated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database update failed.']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
}

$conn->close();
?>
