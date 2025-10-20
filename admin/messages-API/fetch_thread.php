<?php
include '../../authentication/auth.php';
require_once '../../database/starroofing_db.php';
header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing inquiry ID'
    ]);
    exit;
}

// fetch inquiry
$stmt = $conn->prepare("SELECT id, firstname, lastname, email, phone, message, submitted_at FROM inquiries WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$inq = $res->fetch_assoc();
$stmt->close();

if (!$inq) {
    echo json_encode([
        'success' => false,
        'message' => 'Inquiry not found'
    ]);
    exit;
}

// fetch replies
$stmt = $conn->prepare("SELECT id, sender, message, sent_at FROM replies WHERE inquiry_id = ? ORDER BY sent_at ASC");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$replies = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// return consistent JSON structure
echo json_encode([
    'success' => true,
    'inquiry' => $inq,
    'replies' => $replies
]);
?>