<?php
include '../../authentication/auth.php';
require_once '../../database/starroofing_db.php';
header('Content-Type: application/json');

$search = $_GET['search'] ?? '';
$limit = 50;

$sql = "SELECT id, firstname, lastname, email, phone, message, submitted_at
        FROM inquiries
        WHERE COALESCE(is_accepted,0) = 0";

$params = [];
$types = '';
if (strlen($search) > 0) {
    $sql .= " AND (firstname LIKE ? OR lastname LIKE ? OR email LIKE ? OR message LIKE ?)";
    $like = "%" . $search . "%";
    $params = [$like,$like,$like,$like];
    $types='ssss';
}

$sql .= " ORDER BY submitted_at DESC LIMIT ?";
$params[] = $limit; 
$types .= 'i';

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Wrap the response in a standard structure
$response = [
    'success' => true,
    'inquiries' => $data
];

echo json_encode($response);
?>