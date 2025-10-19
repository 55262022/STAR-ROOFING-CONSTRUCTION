<?php
include '../../includes/auth.php';
require_once '../../database/starroofing_db.php';
header('Content-Type: application/json');

if (!empty($_POST['id'])) {
    $id = intval($_POST['id']);

    // Get the image path before deleting
    $query = $conn->prepare("SELECT image_path FROM roof_types WHERE id = ?");
    $query->bind_param("i", $id);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();
    $query->close();

    // Delete record
    $stmt = $conn->prepare("DELETE FROM roof_types WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Delete image file if exists
        if (!empty($row['image_path']) && file_exists("../" . $row['image_path'])) {
            unlink("../" . $row['image_path']);
        }

        echo json_encode(['status' => 'success', 'message' => 'Roof type deleted successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete roof type.']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}

$conn->close();
?>
