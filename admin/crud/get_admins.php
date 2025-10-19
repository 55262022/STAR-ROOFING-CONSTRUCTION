<?php
require_once '../database/starroofing_db.php';

$sql = "SELECT id, email FROM accounts WHERE role_id = 1 AND account_status = 'active'";
$result = $conn->query($sql);

$admins = [];
while ($row = $result->fetch_assoc()) {
    $admins[] = $row;
}

echo json_encode(['success' => true, 'data' => $admins]);
$conn->close();
?>
