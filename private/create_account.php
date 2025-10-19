<?php
session_start();

// Enable detailed error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Database & Composer autoload
require_once __DIR__ . '/../database/starroofing_db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load environment variables
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to load .env file: ' . $e->getMessage()]);
    exit;
}

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['email'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email format']);
    exit;
}

// Verify database connection
if (!isset($conn) || $conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM accounts WHERE email = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
    exit;
}
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Email is already registered.']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Use default password from .env
$default_password = $_ENV['DEFAULT_PASSWORD'] ?? null;
if (!$default_password) {
    echo json_encode(['success' => false, 'error' => 'DEFAULT_PASSWORD missing in .env']);
    exit;
}

$hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

// Prepare insert statement (matches table columns exactly)
$stmt = $conn->prepare("
    INSERT INTO accounts (email, password, role_id, account_status, last_login, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Prepare insert failed: ' . $conn->error]);
    exit;
}

// Default values
$role_id = 1;
$account_status = 'active';
$last_login = null;
$created_at = date('Y-m-d H:i:s');
$updated_at = date('Y-m-d H:i:s');

$stmt->bind_param(
    "ssissss",
    $email,
    $hashed_password,
    $role_id,
    $account_status,
    $last_login,
    $created_at,
    $updated_at
);

// Execute and check
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'error' => 'Database insert error: ' . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Send welcome email
$email_sent = sendWelcomeEmail($email, $default_password);

$conn->close();

if ($email_sent) {
    echo json_encode(['success' => true, 'message' => 'Account created and email sent successfully.']);
} else {
    echo json_encode(['success' => true, 'message' => 'Account created, but email could not be sent.']);
}

// Function: Send welcome email
function sendWelcomeEmail($email, $password) {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($_ENV['EMAIL_USER'], 'Star Roofing & Construction');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Admin Account Created - Star Roofing & Construction';
        $mail->Body = "
            <h2>Admin Account Created</h2>
            <p>You have been added as an admin to Star Roofing & Construction.</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Temporary Password:</strong> $password</p>
            <p>Please change your password after logging in for security purposes.</p>
            <br>
            <p>Best regards,<br>Star Roofing & Construction Developer</p>
        ";

        return $mail->send();
    } catch (Exception $e) {
        error_log('Email error: ' . $mail->ErrorInfo);
        return false;
    }
}
?>
