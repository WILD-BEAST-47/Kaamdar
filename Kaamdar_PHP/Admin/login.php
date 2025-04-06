<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include('../dbConnection.php');

// Debug database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if(isset($_POST['login'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Debug input
    error_log("Login attempt - Email: " . $email);
    error_log("Login attempt - Password: " . $password);

    if(empty($email) || empty($password)) {
        $msg = '<div class="error-message">Please fill all fields</div>';
    } else {
        $sql = "SELECT * FROM adminlogin_tb WHERE a_email = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            error_log("Prepare failed: " . $conn->error);
            $msg = '<div class="error-message">Database error occurred</div>';
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                // Debug information
                error_log("Found user - Email: " . $row['a_email']);
                error_log("Stored password: " . $row['a_password']);
                
                if($password === $row['a_password']) {
                    // Clear any existing session data
                    session_unset();
                    
                    // Set new session data
                    $_SESSION['is_adminlogin'] = true;
                    $_SESSION['aEmail'] = $row['a_email'];
                    $_SESSION['admin_id'] = $row['a_login_id'];
                    $_SESSION['admin_name'] = $row['a_name'];
                    $_SESSION['last_activity'] = time();
                    
                    error_log("Login successful for user: " . $email);
                    error_log("Session data: " . print_r($_SESSION, true));
                    
                    // Redirect to dashboard
                    header("Location: dashboard.php");
                    exit();
                } else {
                    error_log("Invalid password for user: " . $email);
                    $msg = '<div class="error-message">Invalid password</div>';
                }
            } else {
                error_log("No user found with email: " . $email);
                $msg = '<div class="error-message">Invalid email</div>';
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - KaamDar</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: #ffffff;
            padding: 1rem;
        }
        
        .login-container {
            width: 100%;
            max-width: 28rem;
            background-color: #ffffff;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 2px;
        }
        
        .login-title {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 500;
            margin-bottom: 2rem;
        }
        
        .brand-name {
            color: #f3961c;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #757575;
            margin-bottom: 0.5rem;
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e0e0e0;
            outline: none;
            font-size: 1rem;
        }
        
        .form-input:focus {
            border-color: #f3961c;
            box-shadow: 0 0 0 1px #f3961c;
        }
        
        .password-container {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #757575;
        }
        
        .submit-button {
            width: 100%;
            background-color: #f3961c;
            color: white;
            border: none;
            padding: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .submit-button:hover {
            background-color: rgba(243, 150, 28, 0.9);
        }
        
        .error-message {
            color: #d32f2f;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1 class="login-title">
            Admin Login - <span class="brand-name">KaamDar</span>
        </h1>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-input" 
                    placeholder="admin@example.com"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="password-container">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="••••••••••"
                        required
                    >
                    <button 
                        type="button" 
                        id="togglePassword" 
                        class="password-toggle"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>
            
            <?php if(isset($msg)) { echo $msg; } ?>
            
            <button type="submit" name="login" class="submit-button">
                Login
            </button>
        </form>
    </div>
    
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Change the eye icon
            if (type === 'text') {
                this.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                        <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                        <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                        <line x1="2" x2="22" y1="2" y2="22"></line>
                    </svg>
                `;
            } else {
                this.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                `;
            }
        });
    </script>
</body>
</html>