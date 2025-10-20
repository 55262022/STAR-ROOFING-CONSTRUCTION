<?php
session_start();
include '../database/starroofing_db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $middle_name = trim($_POST['middle_name']);
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $contact_number = $_POST['contact_number'];
    $region_code = $_POST['region_code'];
    $region_name = $_POST['region_name'];
    $province_code = $_POST['province_code'];
    $province_name = $_POST['province_name'];
    $city_code = $_POST['city_code'];
    $city_name = $_POST['city_name'];
    $barangay_code = $_POST['barangay_code'];
    $barangay_name = $_POST['barangay_name'];
    $street = $_POST['street'];
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $terms = isset($_POST['terms']) ? $_POST['terms'] : '';

    if (empty($email) || empty($password) || empty($confirm_password) || empty($first_name) || empty($last_name)) {
        $error = "Please fill out all required fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif ($terms !== 'on') {
        $error = "Please accept the Terms and Conditions.";
    } else {
        $check_sql = "SELECT id FROM accounts WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $error = "Email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $conn->begin_transaction();
            try {
                $insert_account = "INSERT INTO accounts (email, password, role_id, account_status, created_at) 
                                   VALUES (?, ?, 2, 'active', NOW())";
                $stmt = $conn->prepare($insert_account);
                $stmt->bind_param("ss", $email, $hashed_password);
                $stmt->execute();
                $account_id = $stmt->insert_id;

                $insert_profile = "INSERT INTO user_profiles 
                    (account_id, first_name, middle_name, last_name, birthdate, gender, contact_number, 
                     region_code, region, province_code, province, city_code, city, barangay_code, barangay, street)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt2 = $conn->prepare($insert_profile);
                $stmt2->bind_param("isssssssssssssss", $account_id, $first_name, $middle_name, $last_name, $birthdate, $gender, 
                    $contact_number, $region_code, $region_name, $province_code, $province_name, $city_code, $city_name, $barangay_code, $barangay_name, $street);
                $stmt2->execute();

                $conn->commit();
                $success = "Registration successful! You can now log in.";
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Star Roofing & Construction</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../css/register.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <i class="fas fa-user-plus"></i>
                <h1>Create Account</h1>
                <p>Join Star Roofing & Construction</p>
            </div>

            <div class="login-body">
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="registrationForm">
                    <!-- Personal Information -->
                    <div class="form-section">
                        <h3 class="section-title">Personal Information</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">First Name *</label>
                                <input type="text" id="firstName" name="first_name" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name *</label>
                                <input type="text" id="lastName" name="last_name" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="middleName">Middle Initial</label>
                            <input type="text" id="middleName" name="middle_name" maxlength="4" placeholder="e.g., A." value="<?php echo isset($_POST['middle_name']) ? htmlspecialchars($_POST['middle_name']) : ''; ?>">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="birthdate">Date of Birth *</label>
                                <input type="date" id="birthdate" name="birthdate" value="<?php echo isset($_POST['birthdate']) ? htmlspecialchars($_POST['birthdate']) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="gender">Gender *</label>
                                <select id="gender" name="gender" required>
                                    <option value="" disabled selected>Select Gender</option>
                                    <option value="male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="contactNumber">Contact Number</label>
                            <input type="tel" id="contactNumber" name="contact_number" maxlength="11" placeholder="09XXXXXXXXX" value="<?php echo isset($_POST['contact_number']) ? htmlspecialchars($_POST['contact_number']) : ''; ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="form-section">
                        <h3 class="section-title">Address Information</h3>
                        
                        <div class="address-group">
                            <div class="form-group">
                                <label for="region">Region *</label>
                                <select id="region" name="region_code" required>
                                    <option value="">Select Region</option>
                                </select>
                                <input type="hidden" id="region_name" name="region_name" value="<?php echo isset($_POST['region_name']) ? htmlspecialchars($_POST['region_name']) : ''; ?>">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="province">Province *</label>
                                    <select id="province" name="province_code" required disabled>
                                        <option value="">Select Province</option>
                                    </select>
                                    <input type="hidden" id="province_name" name="province_name" value="<?php echo isset($_POST['province_name']) ? htmlspecialchars($_POST['province_name']) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="city">City *</label>
                                    <select id="city" name="city_code" required disabled>
                                        <option value="">Select City</option>
                                    </select>
                                    <input type="hidden" id="city_name" name="city_name" value="<?php echo isset($_POST['city_name']) ? htmlspecialchars($_POST['city_name']) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="barangay">Barangay *</label>
                                    <select id="barangay" name="barangay_code" required disabled>
                                        <option value="">Select Barangay</option>
                                    </select>
                                    <input type="hidden" id="barangay_name" name="barangay_name" value="<?php echo isset($_POST['barangay_name']) ? htmlspecialchars($_POST['barangay_name']) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="street">Street Address *</label>
                                    <textarea id="street" name="street" placeholder="House No., Street Name, Subdivision, etc." required><?php echo isset($_POST['street']) ? htmlspecialchars($_POST['street']) : ''; ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="form-section">
                        <h3 class="section-title">Account Information</h3>
                        
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope envelope-icon"></i>
                                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>
                            <div id="emailFeedback" class="input-feedback"></div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Password *</label>
                                <div class="password-container">
                                    <i class="fas fa-key password-icon"></i>
                                    <input type="password" id="password" name="password" required minlength="6">
                                    <span class="toggle-password">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                                <div class="password-strength">
                                    <div class="password-strength-bar"></div>
                                </div>
                                <div class="password-feedback"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password *</label>
                                <div class="password-container">
                                    <i class="fas fa-key password-icon"></i>
                                    <input type="password" id="confirm_password" name="confirm_password" required>
                                    <span class="toggle-password">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                                <div class="password-match-feedback"></div>
                            </div>
                        </div>
                        
                        <div class="terms">
                            <input type="checkbox" id="terms" name="terms" required <?php echo (isset($_POST['terms']) && $_POST['terms'] == 'on') ? 'checked' : ''; ?>>
                            <label for="terms">
                                I agree to the <a href="../terms.php" target="_blank">Terms and Conditions</a> and <a href="../privacy.php" target="_blank">Privacy Policy</a> *
                            </label>
                        </div>
                        
                        <button type="submit" class="login-button">
                            Create Account <i class="fas fa-check"></i>
                        </button>
                    </div>
                </form>

                <div class="separator">Already have an account?</div>
                <div class="register-link">
                    <a href="login.php">Log In Here</a>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert + Address Logic -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../javascript/register-address-selector.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password strength indicator
            const passwordInput = document.getElementById('password');
            const strengthBar = document.querySelector('.password-strength-bar');
            const strengthFeedback = document.querySelector('.password-feedback');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const confirmFeedback = document.querySelector('.password-match-feedback');
            
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                let feedback = '';
                
                if (password.length >= 6) strength += 1;
                if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 1;
                if (password.match(/\d/)) strength += 1;
                if (password.match(/[^a-zA-Z\d]/)) strength += 1;
                
                const container = this.closest('.form-group');
                container.classList.remove('password-weak', 'password-medium', 'password-strong');
                
                if (password.length > 0) {
                    if (strength <= 2) {
                        container.classList.add('password-weak');
                        feedback = 'Weak password';
                    } else if (strength === 3) {
                        container.classList.add('password-medium');
                        feedback = 'Medium strength password';
                    } else {
                        container.classList.add('password-strong');
                        feedback = 'Strong password';
                    }
                }
                
                strengthFeedback.textContent = feedback;
            });
            
            // Password confirmation check
            confirmPasswordInput.addEventListener('input', function() {
                if (this.value !== passwordInput.value) {
                    this.style.borderColor = '#e53e3e';
                    confirmFeedback.textContent = 'Passwords do not match';
                    confirmFeedback.style.color = '#e53e3e';
                } else {
                    this.style.borderColor = '#38a169';
                    confirmFeedback.textContent = 'Passwords match';
                    confirmFeedback.style.color = '#38a169';
                }
            });
            
            // Toggle password visibility
            document.querySelectorAll('.toggle-password').forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const passwordInput = this.parentElement.querySelector('input');
                    const icon = this.querySelector('i');
                    
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });
            
            // Email availability check
            document.getElementById('email').addEventListener('blur', function() {
                const email = this.value.trim();
                const feedback = document.getElementById('emailFeedback');
                
                if (email.length > 0) {
                    feedback.innerHTML = '<span style="color:#3498db;">Checking email availability...</span>';
                    
                    fetch('../process/check_email.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'email=' + encodeURIComponent(email)
                    })
                    .then(response => response.text())
                    .then(data => {
                        if (data === 'exists') {
                            feedback.innerHTML = '<span style="color:#e74c3c;">Email already exists</span>';
                        } else {
                            feedback.innerHTML = '<span style="color:#27ae60;">Email is available</span>';
                        }
                    })
                    .catch(error => {
                        feedback.innerHTML = '<span style="color:#e74c3c;">Error checking email</span>';
                    });
                }
            });

            // Form validation before submission
            document.getElementById('registrationForm').addEventListener('submit', function(e) {
                const requiredFields = this.querySelectorAll('input[required], select[required], textarea[required]');
                let isValid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.style.borderColor = '#e53e3e';
                    } else {
                        field.style.borderColor = '#e2e8f0';
                    }
                });
                
                // Check password match
                if (passwordInput.value !== confirmPasswordInput.value) {
                    isValid = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'Password Mismatch',
                        text: 'Please make sure your passwords match.',
                        confirmButtonColor: '#3B71CA'
                    });
                }
                
                // Check terms acceptance
                const termsCheckbox = document.getElementById('terms');
                if (!termsCheckbox.checked) {
                    isValid = false;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Terms Required',
                        text: 'Please accept the Terms and Conditions.',
                        confirmButtonColor: '#3B71CA'
                    });
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
        
        <?php if (!empty($error)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Registration Failed',
                text: '<?php echo addslashes($error); ?>',
                confirmButtonColor: '#3B71CA'
            });
        <?php elseif (!empty($success)): ?>
            Swal.fire({
                icon: 'success',
                title: 'Registration Successful!',
                text: '<?php echo addslashes($success); ?>',
                confirmButtonColor: '#3B71CA'
            }).then(() => {
                window.location.href = 'login.php';
            });
        <?php endif; ?>
    </script>
</body>
</html>