<?php
session_start();
include('dbConnection.php');

if(isset($_SESSION['is_login'])) {
    echo "<script> location.href='Requester/SubmitRequest.php'; </script>";
    exit;
}

$msg = '';

if(isset($_REQUEST['login'])) {
    $rEmail = filter_input(INPUT_POST, 'rEmail', FILTER_SANITIZE_EMAIL);
    $rPassword = $_POST['rPassword'];

    if(empty($rEmail) || empty($rPassword)) {
        $msg = '<div class="error-message">Please fill all fields</div>';
    } else {
        $sql = "SELECT r_login_id, r_email, r_password, r_name FROM requesterlogin_tb WHERE r_email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $rEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if(password_verify($rPassword, $row['r_password'])) {
                $_SESSION['is_login'] = true;
                $_SESSION['rEmail'] = $rEmail;
                $_SESSION['rName'] = $row['r_name'];
                $_SESSION['r_login_id'] = $row['r_login_id'];
                echo "<script> location.href='Requester/SubmitRequest.php'; </script>";
                exit;
            } else {
                $msg = '<div class="error-message">Invalid password</div>';
            }
        } else {
            $msg = '<div class="error-message">Invalid email</div>';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KaamDar</title>
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
        
        .signup-button {
            position: absolute;
            top: 2rem;
            right: 2rem;
            border: 1px solid #f3961c;
            color: #f3961c;
            background: transparent;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            text-decoration: none;
            transition: background-color 0.2s;
        }
        
        .signup-button:hover {
            background-color: rgba(243, 150, 28, 0.05);
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
        
        .checkbox-container {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .checkbox-input {
            margin-top: 0.25rem;
            width: 1rem;
            height: 1rem;
        }
        
        .checkbox-label {
            font-size: 0.875rem;
            color: #616161;
        }
        
        .checkbox-label a {
            color: #212121;
            text-decoration: underline;
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
        
        .success-message {
            background-color: #4caf50;
            color: white;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 2px;
        }
    </style>
</head>
<body>
    <a href="UserRegistration.php" class="signup-button">SIGN UP</a>
    
    <div class="login-container">
        <h1 class="login-title">
            Log in to <span class="brand-name">KaamDar</span>
        </h1>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="rEmail" class="form-label">Email Address</label>
                <input 
                    type="email" 
                    id="rEmail" 
                    name="rEmail" 
                    class="form-input" 
                    placeholder="ram@example.com"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="rPassword" class="form-label">Password</label>
                <div class="password-container">
                    <input 
                        type="password" 
                        id="rPassword" 
                        name="rPassword" 
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
                Log In
            </button>
        </form>
    </div>
    
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('rPassword');
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