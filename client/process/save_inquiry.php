<?php
require_once '../../database/starroofing_db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname    = trim($_POST['firstname'] ?? '');
    $lastname     = trim($_POST['lastname'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $message      = trim($_POST['message'] ?? '');
    
    // Address fields
    $region_code  = trim($_POST['region_code'] ?? '');
    $region_name  = trim($_POST['region_name'] ?? '');
    $province_code= trim($_POST['province_code'] ?? '');
    $province_name= trim($_POST['province_name'] ?? '');
    $city_code    = trim($_POST['city_code'] ?? '');
    $city_name    = trim($_POST['city_name'] ?? '');
    $barangay_code= trim($_POST['barangay_code'] ?? '');
    $barangay_name= trim($_POST['barangay_name'] ?? '');
    $street       = trim($_POST['street'] ?? '');

    // Required field check
    if (empty($firstname) || empty($lastname) || empty($email) || empty($phone) ||
        empty($region_code) || empty($province_code) || empty($city_code) || empty($barangay_code) || empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields.']);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO inquiries 
        (firstname, lastname, email, phone, message, 
         region_code, region_name, province_code, province_name, city_code, city_name, barangay_code, barangay_name, street) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param(
        "ssssssssssssss", 
        $firstname, $lastname, $email, $phone, $message,
        $region_code, $region_name, $province_code, $province_name, $city_code, $city_name, $barangay_code, $barangay_name, $street
    );

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
