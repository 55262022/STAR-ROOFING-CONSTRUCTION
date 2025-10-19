<?php
require_once '../../database/starroofing_db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $inquiry_type = trim($_POST['inquiry_type'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($firstname) || empty($lastname) || empty($email) || empty($phone) || empty($inquiry_type) || empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill all fields.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO inquiries (firstname, lastname, email, phone, inquiry_type, message) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $firstname, $lastname, $email, $phone, $inquiry_type, $message);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Inquiry saved successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database insertion failed.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>
