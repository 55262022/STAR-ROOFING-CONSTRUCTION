<?php
include '../../authentication/auth.php';
require_once '../../database/starroofing_db.php';
header('Content-Type: application/json');

// Get inquiry ID
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Missing inquiry ID']);
    exit;
}

// Update inquiry to accepted
$stmt = $conn->prepare("UPDATE inquiries SET is_accepted = 1 WHERE id = ?");
$stmt->bind_param('i', $id);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    // Return success in a consistent format
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to accept inquiry']);
}
?>