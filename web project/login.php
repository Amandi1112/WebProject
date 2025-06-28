<?php
// Start session
session_start();

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
        $email = trim(strtolower($_POST['email'] ?? ''));
        $userPassword = $_POST['password'] ?? '';
        
        // Validation
        $errors = [];
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address';
        }
        
        if (empty($userPassword)) {
            $errors[] = 'Please enter your password';
        }
        
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }
        
        // Database connection
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check if user exists and get user data
        $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, password_hash FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo json_encode(['success' => false, 'errors' => ['Invalid email or password']]);
            exit;
        }
        
        // Verify password
        if (!password_verify($userPassword, $user['password_hash'])) {
            echo json_encode(['success' => false, 'errors' => ['Invalid email or password']]);
            exit;
        }
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['logged_in'] = true;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Login successful!',
            'user' => [
                'id' => $user['id'],
                'firstName' => $user['first_name'],
                'lastName' => $user['last_name'],
                'email' => $user['email'],
                'fullName' => $user['first_name'] . ' ' . $user['last_name']
            ]
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'errors' => ['Database error occurred. Please try again.']]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'errors' => ['An error occurred. Please try again.']]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Welcome Back</title>
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

        .signin-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
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

        .signin-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .signin-header h1 {
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .signin-header p {
            color: #7f8c8d;
            font-size: 1.1em;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        label {
            display: block;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.95em;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e8ecf4;
            border-radius: 10px;
            font-size: 1.1em;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .forgot-password {
            text-align: right;
            margin-bottom: 25px;
        }

        .forgot-password a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9em;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: #764ba2;
        }

        .signin-btn {
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

        .signin-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .signin-btn:active {
            transform: translateY(0);
        }

        .signin-btn:disabled {
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

        .signup-link {
            text-align: center;
            margin-top: 25px;
            color: #7f8c8d;
        }

        .signup-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .signup-link a:hover {
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

        .remember-me {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #667eea;
        }

        .remember-me label {
            margin-bottom: 0;
            font-weight: 500;
            cursor: pointer;
        }

        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
            color: #7f8c8d;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e8ecf4;
        }

        .divider span {
            background: rgba(255, 255, 255, 0.95);
            padding: 0 15px;
        }

        @media (max-width: 600px) {
            .signin-container {
                padding: 30px 20px;
            }
            
            .signin-header h1 {
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
    <div class="signin-container">
        <div class="logo-container">
            <img src="allura_estrella.png" alt="Allura Estrella Logo" class="logo">
        </div>

        <div class="signin-header">
            <h1>Welcome Back</h1>
            <p>Sign in to your account</p>
        </div>

        <div class="message success-message" id="successMessage"></div>
        <div class="message error-message" id="errorMessage"></div>

        <form id="signinForm" method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="remember-me">
                <input type="checkbox" id="rememberMe" name="rememberMe">
                <label for="rememberMe">Remember me</label>
            </div>

            <div class="forgot-password">
                <a href="#" onclick="showForgotPassword()">Forgot Password?</a>
            </div>

            <button type="submit" class="signin-btn" id="submitBtn">
                <span id="btnText">Sign In</span>
                <div class="loading" id="loading"></div>
            </button>
        </form>

        <div class="divider">
            <span>or</span>
        </div>

        <div class="signup-link">
            Don't have an account? <a href="Signup.php" onclick="showSignup()">Create Account</a>
        </div>
    </div>

    <script>
        document.getElementById('signinForm').addEventListener('submit', function(e) {
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
                    successMessage.textContent = data.message + ' Welcome back, ' + data.user.firstName + '! 🎉';
                    successMessage.style.display = 'block';
                    
                    // Redirect after successful login
                    setTimeout(() => {
                        window.location.href = 'abc.php';
                    }, 1500);
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

        // Auto-fill demo (remove in production)
        document.addEventListener('DOMContentLoaded', function() {
            // You can remove this in production
            console.log('Sign-in page loaded successfully');
        });

        function showForgotPassword() {
            alert('Redirect to forgot password page');
        }

        function showSignup() {
            alert('Redirect to signup page');
        }

        // Add enter key support
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && document.activeElement.tagName !== 'BUTTON') {
                document.getElementById('signinForm').dispatchEvent(new Event('submit'));
            }
        });
    </script>
</body>
</html>