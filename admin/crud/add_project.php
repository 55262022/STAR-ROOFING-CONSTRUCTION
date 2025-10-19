<?php
include '../../includes/auth.php';
require_once '../../database/starroofing_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $project_code = trim($_POST['project_code']);
    $project_name = trim($_POST['project_name']);
    $description = $_POST['description'] ?? '';
    $client_name = trim($_POST['client_name']);
    $client_email = $_POST['client_email'] ?? '';
    $client_phone = $_POST['client_phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $budget = floatval($_POST['budget'] ?? 0);
    $actual_cost = floatval($_POST['actual_cost'] ?? 0);
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    $status = $_POST['status'] ?? 'planning';
    $progress = floatval($_POST['progress'] ?? 0);
    $project_manager_id = !empty($_POST['project_manager_id']) ? intval($_POST['project_manager_id']) : null;
    $created_by = $_SESSION['account_id'] ?? null;
    $created_at = date('Y-m-d H:i:s');
    $updated_at = $created_at;

    if (empty($project_code) || empty($project_name) || empty($client_name)) {
        echo "Required fields are missing.";
        exit;
    }

    // Ensure project_manager_id exists in accounts if provided
    if ($project_manager_id) {
        $check_manager = $conn->prepare("SELECT id FROM accounts WHERE id = ?");
        $check_manager->bind_param("i", $project_manager_id);
        $check_manager->execute();
        $result = $check_manager->get_result();
        if ($result->num_rows === 0) {
            echo "Invalid project manager selected.";
            exit;
        }
        $check_manager->close();
    }

    // Prepare SQL
    $sql = "INSERT INTO projects (
        project_code, project_name, description, client_name, client_email, client_phone,
        address, budget, actual_cost, start_date, end_date, status, progress,
        project_manager_id, created_by, created_at, updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo "Database error: " . $conn->error;
        exit;
    }

    $stmt->bind_param(
        'sssssssdsssdiisss',
        $project_code,
        $project_name,
        $description,
        $client_name,
        $client_email,
        $client_phone,
        $address,
        $budget,
        $actual_cost,
        $start_date,
        $end_date,
        $status,
        $progress,
        $project_manager_id,
        $created_by,
        $created_at,
        $updated_at
    );

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
