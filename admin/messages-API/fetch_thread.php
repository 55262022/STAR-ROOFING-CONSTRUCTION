<?php
include '../../authentication/auth.php';
require_once '../../database/starroofing_db.php';
header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { echo json_encode([]); exit; }

// fetch inquiry
$stmt = $conn->prepare("SELECT id, firstname, lastname, email, phone, inquiry_type, message, submitted_at FROM inquiries WHERE id = ?");
$stmt->bind_param('i',$id);
$stmt->execute();
$res = $stmt->get_result();
$inq = $res->fetch_assoc();
$stmt->close();

if (!$inq) { echo json_encode(['error'=>'Not found']); exit; }

// fetch replies
$replies = [];
$stmt = $conn->prepare("SELECT id, sender, message, sent_at FROM replies WHERE inquiry_id = ? ORDER BY sent_at ASC");
$stmt->bind_param('i',$id);
$stmt->execute();
$res = $stmt->get_result();
$replies = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['inquiry'=>$inq, 'replies'=>$replies]);
