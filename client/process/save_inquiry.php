<?php
session_start();
require_once '../../database/starroofing_db.php';

header('Content-Type: application/json; charset=utf-8');

// Prevent any output before JSON
ob_clean();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname    = trim($_POST['firstname'] ?? '');
    $lastname     = trim($_POST['lastname'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $message      = trim($_POST['message'] ?? '');
    $product_id   = intval($_POST['product_id'] ?? 0);
    
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
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields.']);
        exit();
    }

    try {
        $stmt = $conn->prepare("
            INSERT INTO inquiries 
            (firstname, lastname, email, phone, message, product_id,
             region_code, region_name, province_code, province_name, city_code, city_name, barangay_code, barangay_name, street) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param(
            "sssssisssssssss", 
            $firstname, $lastname, $email, $phone, $message, $product_id,
            $region_code, $region_name, $province_code, $province_name, $city_code, $city_name, $barangay_code, $barangay_name, $street
        );

        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Inquiry saved successfully!', 'inquiry_id' => $stmt->insert_id]);
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>