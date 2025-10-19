<?php
session_start();
include '../db/connectdb.php'; 
require '../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $error_message = 'Please enter a valid email.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM admin WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error_message = 'Email does not exist.';
        } else {
            $code = rand(100000, 999999);
            $expiry = date("Y-m-d H:i:s", strtotime('+5 minutes'));

            $update = $conn->prepare("UPDATE admin SET reset_code = ?, reset_code_expires = ? WHERE email = ?");
            $update->bind_param('sss', $code, $expiry, $email);
            $update->execute();

            $mail = new PHPMailer(true);
            
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = $_ENV['EMAIL_USER'];
                $mail->Password   = $_ENV['EMAIL_PASSWORD'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom($_ENV['EMAIL_USER'], 'Your code to reset password');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Code';
                $mail->Body = "
                    <p>Hi,</p>
                    <p>You requested a password reset for your admin account.</p>
                    <p>Your reset code is:</p>
                    <h2 style='color: #4CAF50;'>$code</h2>
                    <p><strong>Note:</strong> This code will expire in 5 minutes.</p>
                    <br>
                    <p>If you did not request this, please ignore this email.</p>
                    <p>Regards,<br>Star Roofing & Construction Admin Officer</p>
                ";
                $mail->AltBody = "You requested a password reset.\nYour code is $code\nThis code will expire in 5 minutes.\n\nIf you did not request this, please ignore this email.\n\nStar Roofing & Construction Admin Officer";

                $mail->send();

                $_SESSION['reset_email'] = $email;
                $success_message = 'Code sent successfully! Please check your email.';
            } catch (Exception $e) {
                $error_message = 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enter Email</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
         * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar {
            width: 100%;
            background-color: #059669;
            padding: 15px 20px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);

            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .navbar a {
            color: #fff;
            text-decoration: none;
            font-size: 1rem;
            background-color: #3498db;
            padding: 8px 15px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }
        .navbar a:hover {
            background-color: #2980b9;
        }
        .navbar-title {
            flex: 1;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: #fff;
            margin-right: 30px; 
        }

        .content {
            margin-top: 90px; 
            display: flex;
            justify-content: center;
            align-items: center;
            flex-grow: 1;
            padding: 20px;
        }
        form {
            background-color: #fff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        form h2 {
            margin-bottom: 20px;
            font-size: 1.5rem;
            color: #333;
            text-align: center;
        }
        input[type="email"], 
        input[type="submit"] {
            width: 100%;
            padding: 12px 15px;
            margin: 10px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
        }
        input[type="email"] {
            background-color: #fafafa;
        }
        input[type="submit"] {
            background-color: #3498db;
            color: white;
            border: none;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

<header class="navbar">
    <a href="../login.php">Back</a>
    <div class="navbar-title">Star Roofing & Construction</div>
</header>

<div class="content">
    <form method="POST" action="enter_email.php">
        <h2>Enter Your Email Address</h2>
        <input type="email" name="email" placeholder="Enter your Email" required>
        <input type="submit" value="Send Code">
    </form>
</div>

<?php if (!empty($error_message)): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: <?php echo json_encode($error_message); ?>,
            confirmButtonColor: '#3498db'
        }).then(() => {
            window.location.href = 'enter_email.php';
        });
    });
</script>
<?php elseif (!empty($success_message)): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: <?php echo json_encode($success_message); ?>,
            confirmButtonColor: '#3498db'
        }).then(() => {
            window.location.href = 'enter_code.php';
        });
    });
</script>
<?php endif; ?>

</body>
</html>
