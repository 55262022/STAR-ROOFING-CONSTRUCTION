<?php
include '../includes/auth.php';
require_once '../database/starroofing_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $category_id    = $_POST['category_id'];
    $name           = trim($_POST['name']);
    $description    = $_POST['description'];
    $price          = $_POST['price'];
    $stock_quantity = $_POST['stock_quantity'];
    $unit           = $_POST['unit'];
    $created_by     = $_SESSION['account_id'];

    $image_path = null;

    // Handle image upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "../uploads/products/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $filename    = time() . "_" . basename($_FILES['image_file']['name']);
        $target_file = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target_file)) {
            $image_path = "uploads/products/" . $filename;
        }
    }

    // ✅ Check if product already exists (same name + category)
    $check_sql = "SELECT product_id, stock_quantity FROM products WHERE name = ? AND category_id = ? AND is_archived = 0";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        die("SQL prepare failed: " . $conn->error);
    }

    $check_stmt->bind_param("si", $name, $category_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result && $result->num_rows > 0) {
        // ✅ Product exists — update quantity
        $existing_product = $result->fetch_assoc();
        $new_quantity = $existing_product['stock_quantity'] + $stock_quantity;

        $update_sql = "UPDATE products 
                       SET stock_quantity = ?, updated_at = NOW() 
                       WHERE product_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        if (!$update_stmt) {
            die("SQL prepare failed (update): " . $conn->error);
        }

        $update_stmt->bind_param("ii", $new_quantity, $existing_product['product_id']);

        if ($update_stmt->execute()) {
            header("Location: ../admin/inventory.php?success=Quantity updated for existing product");
            exit();
        } else {
            header("Location: ../admin/inventory.php?error=Failed to update product quantity");
            exit();
        }

    } else {
        // ✅ Product does not exist — insert new one
        $insert_sql = "INSERT INTO products 
            (category_id, name, description, price, stock_quantity, unit, image_path, created_by, created_at, updated_at, is_archived) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), 0)";
        
        $insert_stmt = $conn->prepare($insert_sql);
        if (!$insert_stmt) {
            die("SQL prepare failed (insert): " . $conn->error);
        }

        $insert_stmt->bind_param(
            "issdissi",
            $category_id,
            $name,
            $description,
            $price,
            $stock_quantity,
            $unit,
            $image_path,
            $created_by
        );

        if ($insert_stmt->execute()) {
            header("Location: ../admin/inventory.php?success=Product added successfully");
            exit();
        } else {
            header("Location: ../admin/inventory.php?error=Failed to add product");
            exit();
        }
    }
}
?>
