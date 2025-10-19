<?php
include '../../includes/auth.php';
require_once '../../database/starroofing_db.php';
header('Content-Type: application/json');

// 🚫 Prevent direct access (redirect to main page)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>
      window.location.href = '../admin/roof_type.php';
    </script>";
    exit;
}

// Validate required fields
if (
    !empty($_POST['type_name']) &&
    !empty($_POST['description']) &&
    isset($_POST['steel_per_sqm']) &&
    isset($_POST['screw_per_sqm']) &&
    isset($_POST['paint_per_sqm']) &&
    isset($_POST['slope_percentage'])
) {
    $type_name = trim($_POST['type_name']);
    $description = trim($_POST['description']);
    $steel_per_sqm = floatval($_POST['steel_per_sqm']);
    $screw_per_sqm = floatval($_POST['screw_per_sqm']);
    $paint_per_sqm = floatval($_POST['paint_per_sqm']);
    $slope_percentage = floatval($_POST['slope_percentage']);

    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image_path']) && $_FILES['image_path']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/roof_types/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $file_tmp = $_FILES['image_path']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['image_path']['name']);
        $target_path = $upload_dir . $file_name;

        if (move_uploaded_file($file_tmp, $target_path)) {
            $image_path = 'uploads/roof_types/' . $file_name;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Image upload failed.']);
            exit;
        }
    }

    // Insert into DB
    $stmt = $conn->prepare("
        INSERT INTO roof_types 
        (type_name, description, steel_per_sqm, screw_per_sqm, paint_per_sqm, slope_percentage, image_path)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssdddds", $type_name, $description, $steel_per_sqm, $screw_per_sqm, $paint_per_sqm, $slope_percentage, $image_path);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Roof type added successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database insertion failed.']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
}

$conn->close();
?>
