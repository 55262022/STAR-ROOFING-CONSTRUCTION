<?php 
session_start();
require_once __DIR__ . '/../database/starroofing_db.php';

$error_message = '';
$error_type = '';

if (!isset($_SESSION['reset_email'])) {
    header('Location: enter_email.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_SESSION['reset_email'];
    $code = implode('', $_POST['code']);

    // Fetch OTP from password_resets
    $stmt = $conn->prepare("SELECT token, expires_at, used FROM password_resets WHERE email = ? ORDER BY reset_id DESC LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row || $row['token'] != $code) {
        $error_type = 'incorrect';
        $error_message = 'Incorrect verification code.';
    } elseif (strtotime($row['expires_at']) < time()) {
        $error_type = 'expired';
        $error_message = 'Verification code has expired.';
    } elseif ($row['used'] == 1) {
        $error_message = 'This code has already been used.';
    } else {
        $update = $conn->prepare("UPDATE password_resets SET used = 1 WHERE email = ?");
        $update->bind_param('s', $email);
        $update->execute();

        header('Location: new_password.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code - Star Roofing & Construction</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/otp.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="otp-container">
        <div class="otp-box">
            <div class="otp-header">
                <i class="fas fa-shield-alt"></i>
                <h1>Verification Code</h1>
                <p>Enter the code sent to your email</p>
            </div>
            
            <div class="otp-body">
                <form id="otpForm" method="POST" action="">
                    <div class="form-group">
                        <label for="code">Verification Code</label>
                        <div class="code-inputs">
                            <input type="text" maxlength="1" class="code-box" name="code[]" required>
                            <input type="text" maxlength="1" class="code-box" name="code[]" required>
                            <input type="text" maxlength="1" class="code-box" name="code[]" required>
                            <input type="text" maxlength="1" class="code-box" name="code[]" required>
                            <input type="text" maxlength="1" class="code-box" name="code[]" required>
                            <input type="text" maxlength="1" class="code-box" name="code[]" required>
                        </div>
                    </div>
                    <button type="submit" class="otp-button">Verify Code</button>
                </form>
                
                <div class="back-link">
                    <a href="forgot_password.php"><i class="fas fa-arrow-left"></i> Back to Email Entry</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.code-box');

            inputs.forEach((input, index) => {
                input.addEventListener('input', (e) => {
                    e.target.value = e.target.value.replace(/[^0-9]/g, '');
                    if (e.target.value && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                });

                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && !e.target.value && index > 0) {
                        inputs[index - 1].focus();
                    }
                });
            });

            document.getElementById('otpForm').addEventListener('submit', function(e) {
                const values = Array.from(inputs).map(input => input.value).join('');
                if (values.length !== 6) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Please enter a valid 6-digit numeric code',
                        confirmButtonColor: '#e9b949'
                    });
                }
            });

            <?php if ($error_type === 'incorrect'): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Incorrect verification code',
                    confirmButtonColor: '#e9b949'
                });
            <?php elseif ($error_type === 'expired'): ?>
                Swal.fire({
                    icon: 'warning',
                    title: 'Verification code has expired',
                    confirmButtonColor: '#e9b949'
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>
