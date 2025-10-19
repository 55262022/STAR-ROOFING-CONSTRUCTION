<?php
// database/starroofing_db.php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env from project root
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$host     = $_ENV['DB_HOST'];
$dbname   = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASSWORD'];

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

/**
 * Verify user credentials
 */
function verify_credentials($email, $password, $conn) {
    $sql = "SELECT a.id, a.email, a.password, a.role_id, a.account_status, 
                   up.first_name, up.last_name
            FROM accounts a 
            LEFT JOIN user_profiles up ON a.id = up.account_id 
            WHERE a.email = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $update_sql = "UPDATE accounts SET last_login = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $user['id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            $stmt->close();
            return $user;
        }
    }
    
    $stmt->close();
    return false;
}

/**
 * Check if email exists
 */
function email_exists($email, $conn) {
    $sql = "SELECT id FROM accounts WHERE email = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    
    return $exists;
}

/**
 * Get dashboard statistics
 */
function getDashboardStats($conn) {
    $stats = [];
    
    // Total projects
    $sql = "SELECT COUNT(*) as total_projects FROM projects WHERE status != 'cancelled'";
    $result = $conn->query($sql);
    $stats['total_projects'] = $result->fetch_assoc()['total_projects'];
    
    // Active projects
    $sql = "SELECT COUNT(*) as active_projects FROM projects WHERE status = 'active'";
    $result = $conn->query($sql);
    $stats['active_projects'] = $result->fetch_assoc()['active_projects'];
    
    // Overall progress
    $sql = "SELECT AVG(progress) as overall_progress FROM projects WHERE status = 'active'";
    $result = $conn->query($sql);
    $stats['overall_progress'] = round($result->fetch_assoc()['overall_progress'], 2);
    
    // Active issues
    $sql = "SELECT COUNT(*) as active_issues FROM project_issues WHERE status IN ('open', 'in_progress')";
    $result = $conn->query($sql);
    $stats['active_issues'] = $result->fetch_assoc()['active_issues'];
    
    return $stats;
}

/**
 * Get project details
 */
function getProjectDetails($project_id, $conn) {
    $sql = "SELECT p.*, 
                   a.email as pm_email,
                   up.first_name as pm_first_name, 
                   up.last_name as pm_last_name
            FROM projects p 
            LEFT JOIN accounts a ON p.project_manager_id = a.id
            LEFT JOIN user_profiles up ON a.id = up.account_id
            WHERE p.project_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Get project phases
 */
function getProjectPhases($project_id, $conn) {
    $sql = "SELECT * FROM project_phases WHERE project_id = ? ORDER BY sequence_order";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $phases = [];
    while ($row = $result->fetch_assoc()) {
        $phases[] = $row;
    }
    
    return $phases;
}

/**
 * Get project materials
 */
function getProjectMaterials($project_id, $conn) {
    $sql = "SELECT pm.*, 
                   p.name as product_name,
                   p.image_path as product_image,
                   cm.name as custom_material_name,
                   cm.texture_path as custom_texture
            FROM project_materials pm
            LEFT JOIN products p ON pm.product_id = p.product_id
            LEFT JOIN custom_materials cm ON pm.custom_material_id = cm.id
            WHERE pm.project_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $materials = [];
    while ($row = $result->fetch_assoc()) {
        $materials[] = $row;
    }
    
    return $materials;
}

/**
 * Get all projects
 */
function getAllProjects($conn) {
    $sql = "SELECT p.*, 
                   a.email as pm_email,
                   up.first_name as pm_first_name, 
                   up.last_name as pm_last_name
            FROM projects p 
            LEFT JOIN accounts a ON p.project_manager_id = a.id
            LEFT JOIN user_profiles up ON a.id = up.account_id
            ORDER BY p.created_at DESC";
    
    $result = $conn->query($sql);
    
    $projects = [];
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
    
    return $projects;
}

/**
 * Create new project
 */
function createProject($data, $user_id, $conn) {
    $sql = "INSERT INTO projects (project_code, project_name, description, client_name, client_email, client_phone, address, budget, start_date, end_date, project_manager_id, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssssdssii",
        $data['project_code'],
        $data['project_name'],
        $data['description'],
        $data['client_name'],
        $data['client_email'],
        $data['client_phone'],
        $data['address'],
        $data['budget'],
        $data['start_date'],
        $data['end_date'],
        $data['project_manager_id'],
        $user_id
    );
    
    if ($stmt->execute()) {
        $project_id = $conn->insert_id;
        
        // Create default phases
        createDefaultPhases($project_id, $conn);
        
        return $project_id;
    }
    
    return false;
}

/**
 * Create default phases for a project
 */
function createDefaultPhases($project_id, $conn) {
    $phases = [
        ['Site Preparation', 1],
        ['Foundation Work', 2],
        ['Structural Framework', 3],
        ['Roofing System', 4],
        ['Exterior Finishing', 5],
        ['Interior Finishing', 6]
    ];
    
    $sql = "INSERT INTO project_phases (project_id, phase_name, sequence_order) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    foreach ($phases as $phase) {
        $stmt->bind_param("isi", $project_id, $phase[0], $phase[1]);
        $stmt->execute();
    }
}

/**
 * Add material to project
 */
function addProjectMaterial($data, $user_id, $conn) {
    $product_id = !empty($data['product_id']) ? $data['product_id'] : null;
    $custom_material_id = !empty($data['custom_material_id']) ? $data['custom_material_id'] : null;
    
    $sql = "INSERT INTO project_materials (project_id, product_id, custom_material_id, quantity, unit_price, added_by) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "iiiddi",
        $data['project_id'],
        $product_id,
        $custom_material_id,
        $data['quantity'],
        $data['unit_price'],
        $user_id
    );
    
    return $stmt->execute();
}

/**
 * Remove material from project
 */
function removeProjectMaterial($material_id, $project_id, $conn) {
    $sql = "DELETE FROM project_materials WHERE id = ? AND project_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $material_id, $project_id);
    
    return $stmt->execute();
}

/**
 * Get available materials (products + custom materials)
 */
function getAvailableMaterials($conn) {
    // Get products
    $sql = "SELECT product_id as id, name, price as cost, image_path as texture_url, 'product' as type 
            FROM products WHERE is_archived = 0";
    $result = $conn->query($sql);
    
    $materials = [];
    while ($row = $result->fetch_assoc()) {
        $materials[] = $row;
    }
    
    // Get custom materials
    $sql = "SELECT id, name, cost, texture_path as texture_url, 'custom' as type 
            FROM custom_materials";
    $result = $conn->query($sql);
    
    while ($row = $result->fetch_assoc()) {
        $materials[] = $row;
    }
    
    return $materials;
}

/**
 * Update project progress
 */
function updateProjectProgress($project_id, $progress, $conn) {
    $sql = "UPDATE projects SET progress = ?, updated_at = NOW() WHERE project_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $progress, $project_id);
    
    return $stmt->execute();
}

/**
 * Save project configuration
 */
function saveProjectConfiguration($data, $user_id, $conn) {
    $sql = "INSERT INTO project_configurations (project_id, roof_type, roof_pitch, roof_width, roof_length, primary_material_color, configuration_data, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            roof_type = VALUES(roof_type),
            roof_pitch = VALUES(roof_pitch),
            roof_width = VALUES(roof_width),
            roof_length = VALUES(roof_length),
            primary_material_color = VALUES(primary_material_color),
            configuration_data = VALUES(configuration_data),
            updated_at = NOW()";
    
    $stmt = $conn->prepare($sql);
    $config_json = json_encode($data['configuration_data']);
    $stmt->bind_param(
        "isddsssi",
        $data['project_id'],
        $data['roof_type'],
        $data['roof_pitch'],
        $data['roof_width'],
        $data['roof_length'],
        $data['primary_material_color'],
        $config_json,
        $user_id
    );
    
    return $stmt->execute();
}
?>