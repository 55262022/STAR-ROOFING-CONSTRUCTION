<?php
// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input data
    $firstName = $conn->real_escape_string(trim($_POST['firstName']));
    $lastName = $conn->real_escape_string(trim($_POST['lastName']));
    $middleName = $conn->real_escape_string(trim($_POST['middleName']));
    $birthdate = $conn->real_escape_string(trim($_POST['birthdate']));
    $contactNumber = $conn->real_escape_string(trim($_POST['contactNumber']));
    $gender = $conn->real_escape_string(trim($_POST['gender']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];
    $regionId = (int)$_POST['region'];
    $provinceId = (int)$_POST['province'];
    $municipalityId = (int)$_POST['municipality'];
    $barangayId = (int)$_POST['barangay'];
    $streetAddress = $conn->real_escape_string(trim($_POST['street']));

    // Validate required fields
    if (empty($firstName) || empty($lastName) || empty($birthdate) || 
        empty($contactNumber) || empty($gender) || empty($email) || 
        empty($password) || empty($regionId) || empty($provinceId) || 
        empty($municipalityId) || empty($barangayId) || empty($streetAddress)) {
        $_SESSION['error'] = "All required fields must be filled.";
        header("Location: ../register.php");
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: ../register.php");
        exit();
    }

    // Validate password strength
    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long.";
        header("Location: ../register.php");
        exit();
    }

    // Check if email already exists
    $checkEmailQuery = "SELECT user_id FROM users WHERE email = '$email'";
    $result = $conn->query($checkEmailQuery);
    
    if ($result && $result->num_rows > 0) {
        $_SESSION['error'] = "Email address is already registered.";
        header("Location: ../register.php");
        exit();
    }

    // Hash the password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into database
    $conn->autocommit(FALSE); // Start transaction
    
    try {
        $insertUserQuery = "
            INSERT INTO users 
            (first_name, last_name, middle_name, birthdate, contact_number, gender, 
             email, password_hash, region_id, province_id, municipality_id, barangay_id, street_address) 
            VALUES ('$firstName', '$lastName', '$middleName', '$birthdate', '$contactNumber', '$gender',
                    '$email', '$passwordHash', $regionId, $provinceId, $municipalityId, $barangayId, '$streetAddress')
        ";
        
        if ($conn->query($insertUserQuery)) {
            $userId = $conn->insert_id;
            
            // Assign default role (customer)
            $assignRoleQuery = "INSERT INTO user_role_assignments (user_id, role_id) VALUES ($userId, 1)";
            
            if ($conn->query($assignRoleQuery)) {
                // Generate verification token
                $verificationToken = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                $insertTokenQuery = "
                    INSERT INTO verification_tokens (user_id, token, expires_at) 
                    VALUES ($userId, '$verificationToken', '$expiresAt')
                ";
                
                if ($conn->query($insertTokenQuery)) {
                    $conn->commit(); // Commit transaction
                    
                    // Send verification email (pseudo-code)
                    // sendVerificationEmail($email, $firstName, $verificationToken);
                    
                    $_SESSION['success'] = "Registration successful! Please check your email to verify your account.";
                    header("Location: ../login.php");
                    exit();
                } else {
                    throw new Exception("Token creation failed: " . $conn->error);
                }
            } else {
                throw new Exception("Role assignment failed: " . $conn->error);
            }
        } else {
            throw new Exception("User creation failed: " . $conn->error);
        }
    } catch (Exception $e) {
        $conn->rollback(); // Rollback transaction on error
        error_log("Database error: " . $e->getMessage());
        $_SESSION['error'] = "Registration failed. Please try again later.";
        header("Location: ../register.php");
        exit();
    } finally {
        $conn->autocommit(TRUE); // Restore autocommit
    }
} else {
    // If not a POST request, redirect to register page
    header("Location: ../register.php");
    exit();
}

// Close connection - this should be at the very end of processing
$conn->close();
// Don't put any HTML or other code after this line