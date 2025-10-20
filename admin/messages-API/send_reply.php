<?php
include '../../authentication/auth.php';
require_once '../../database/starroofing_db.php';
header('Content-Type: application/json');

$inquiry_id = isset($_POST['inquiry_id']) ? (int)$_POST['inquiry_id'] : 0;
$message = trim($_POST['message'] ?? '');

if (!$inquiry_id || $message === '') {
    echo json_encode(['success'=>false,'message'=>'Missing fields']);
    exit;
}

// ensure replies table exists
$conn->query("
CREATE TABLE IF NOT EXISTS replies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  inquiry_id INT NOT NULL,
  sender ENUM('admin','client') NOT NULL,
  message TEXT NOT NULL,
  sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (inquiry_id) REFERENCES inquiries(id) ON DELETE CASCADE
) ENGINE=InnoDB;
");

// insert
$stmt = $conn->prepare("INSERT INTO replies (inquiry_id, sender, message) VALUES (?, 'admin', ?)");
if (!$stmt) {
    echo json_encode(['success'=>false,'message'=>$conn->error]);
    exit;
}
$stmt->bind_param('is', $inquiry_id, $message);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'message'=>'Insert failed']);
}
