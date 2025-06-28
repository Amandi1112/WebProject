<?php
// Database Configuration
$host = 'localhost';
$dbname = 'allura_estrella';
$username = 'root';
$password = '';

// Initialize response variables
$response = null;
$error = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        // Get form data
        $firstName = trim($_POST['firstName'] ?? '');
        $lastName = trim($_POST['lastName'] ?? '');
        $email = trim(strtolower($_POST['email'] ?? ''));
        $userPassword = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        $address = trim($_POST['address'] ?? '');
        $isDeliveryAddress = isset($_POST['isDeliveryAddress']) ? 1 : 0;
        
        // Validation
        $errors = [];
        
        if (empty($firstName) || strlen($firstName) < 2) {
            $errors[] = 'First name must be at least 2 characters';
        }
        
        if (empty($lastName) || strlen($lastName) < 2) {
            $errors[] = 'Last name must be at least 2 characters';
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address';
        }
        
        if (empty($userPassword) || strlen($userPassword) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        }
        
        if ($userPassword !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }
        
        if (empty($address) || strlen($address) < 10) {
            $errors[] = 'Please enter a complete address';
        }
        
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }
        
        // Database connection
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check if email exists
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->execute([$email]);
        
        if ($checkStmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'errors' => ['Email already registered']]);
            exit;
        }
        
        // Hash password and insert user
        $hashedPassword = password_hash($userPassword, PASSWORD_DEFAULT);
        
        $insertStmt = $pdo->prepare("
            INSERT INTO users (first_name, last_name, email, password_hash, address, is_delivery_address, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $result = $insertStmt->execute([
            $firstName, $lastName, $email, $hashedPassword, $address, $isDeliveryAddress
        ]);
        
        if ($result) {
            echo json_encode([
                'success' => true, 
                'message' => 'Account created successfully!',
                'user' => [
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'email' => $email,
                    'isDeliveryAddress' => $isDeliveryAddress
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'errors' => ['Registration failed']]);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'errors' => ['Database error: ' . $e->getMessage()]]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'errors' => ['Error: ' . $e->getMessage()]]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Create Your Account</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .signup-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #667eea;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .signup-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .signup-header h1 {
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .signup-header p {
            color: #7f8c8d;
            font-size: 1.1em;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        label {
            display: block;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.95em;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e8ecf4;
            border-radius: 10px;
            font-size: 1em;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 25px;
            padding: 15px;
            background: #f0f4ff;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }

        .checkbox-group input[type="checkbox"] {
            margin-top: 3px;
            width: 18px;
            height: 18px;
            accent-color: #667eea;
        }

        .checkbox-group label {
            margin-bottom: 0;
            color: #2c3e50;
            font-weight: 500;
            line-height: 1.4;
        }

        .signup-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .signup-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .signup-btn:active {
            transform: translateY(0);
        }

        .signup-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: none;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            color: #7f8c8d;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #764ba2;
        }

        .loading {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .loading::after {
            content: "";
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 600px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .signup-container {
                padding: 30px 20px;
            }
            
            .signup-header h1 {
                font-size: 2em;
            }

            .logo {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="logo-container">
            <img src="allura_estrella.png" alt="Allura Estrella Logo" class="logo">
        </div>

        <div class="signup-header">
            <h1>Create Account</h1>
            <p>Join us today and get started!</p>
        </div>

        <div class="message success-message" id="successMessage"></div>
        <div class="message error-message" id="errorMessage"></div>

        <form id="signupForm" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="firstName">First Name *</label>
                    <input type="text" id="firstName" name="firstName" required>
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name *</label>
                    <input type="text" id="lastName" name="lastName" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password *</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                </div>
            </div>

            <div class="form-group">
                <label for="address">Address *</label>
                <textarea id="address" name="address" placeholder="Enter your full address" required></textarea>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="isDeliveryAddress" name="isDeliveryAddress" checked>
                <label for="isDeliveryAddress">
                    <strong>Use this address as delivery address?</strong><br>
                    <small>Check this if you want to use the above address for product deliveries</small>
                </label>
            </div>

            <button type="submit" class="signup-btn" id="submitBtn">
                <span id="btnText">Create Account</span>
                <div class="loading" id="loading"></div>
            </button>
        </form>

        <div class="login-link">
            Already have an account? <a href="#" onclick="showLogin()">Sign In</a>
        </div>
    </div>

    <script>
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const loading = document.getElementById('loading');
            const successMessage = document.getElementById('successMessage');
            const errorMessage = document.getElementById('errorMessage');
            
            // Hide previous messages
            successMessage.style.display = 'none';
            errorMessage.style.display = 'none';
            
            // Show loading state
            submitBtn.disabled = true;
            btnText.style.opacity = '0';
            loading.style.display = 'block';
            
            // Get form data
            const formData = new FormData(this);
            
            // Send AJAX request
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Reset button state
                submitBtn.disabled = false;
                btnText.style.opacity = '1';
                loading.style.display = 'none';
                
                if (data.success) {
                    successMessage.textContent = data.message + ' Welcome, ' + data.user.firstName + '! 🎉';
                    successMessage.style.display = 'block';
                    document.getElementById('signupForm').reset();
                } else {
                    errorMessage.textContent = data.errors.join(', ');
                    errorMessage.style.display = 'block';
                }
            })
            .catch(error => {
                // Reset button state
                submitBtn.disabled = false;
                btnText.style.opacity = '1';
                loading.style.display = 'none';
                
                errorMessage.textContent = 'An error occurred. Please try again.';
                errorMessage.style.display = 'block';
                console.error('Error:', error);
            });
        });

        // Real-time password match validation
        document.getElementById('confirmPassword').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.style.borderColor = '#e74c3c';
            } else {
                this.style.borderColor = '#e8ecf4';
            }
        });

        function showLogin() {
            alert('Redirect to login page');
        }
    </script>
</body>
</html>