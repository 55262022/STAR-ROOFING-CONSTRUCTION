<?php
include '../../authentication/auth.php';
require_once '../../database/starroofing_db.php';
header('Content-Type: application/json');

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if (!$id) {
    echo json_encode(['success'=>false,'message'=>'Missing id']);
    exit;
}

// ensure column exists
$colExists = $conn->query("SHOW COLUMNS FROM inquiries LIKE 'is_accepted'")->fetch_assoc();
if (!$colExists) {
    @$conn->query("ALTER TABLE inquiries ADD COLUMN is_accepted TINYINT(1) DEFAULT 0");
}

// update
$stmt = $conn->prepare("UPDATE inquiries SET is_accepted = 1 WHERE id = ?");
if (!$stmt) {
    echo json_encode(['success'=>false,'message'=>$conn->error]);
    exit;
}
$stmt->bind_param('i',$id);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'message'=>'Could not update']);
}
