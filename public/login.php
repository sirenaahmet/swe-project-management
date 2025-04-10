<?php
// Start session
session_start();

// Set page title
$pageTitle = "Login";

// Include database connection
require_once '../includes/db.php';

// Check if user is already logged in
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
    exit;
}

// Define variables and initialize with empty values
$email = $password = "";
$email_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Check if email is empty
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter your email.";
    } else{
        $email = trim($_POST["email"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if(empty($email_err) && empty($password_err)){
        // Prepare a select statement
        $sql = "SELECT user_id, full_name, email, password FROM EndUser WHERE email = ?";
        
        if($stmt = $conn->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_email);
            
            // Set parameters
            $param_email = $email;
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Store result
                $stmt->store_result();
                
                // Check if email exists, if yes then verify password
                if($stmt->num_rows == 1){                    
                    // Bind result variables
                    $stmt->bind_result($id, $full_name, $email, $hashed_password);
                    if($stmt->fetch()){
                        if(password_verify($password, $hashed_password)){
                            // Password is correct, so start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $id;
                            $_SESSION["user_name"] = $full_name;
                            $_SESSION["user_email"] = $email;
                            
                            // Redirect user to welcome page
                            header("location: index.php");
                            exit;
                        } else{
                            // Password is not valid, display a generic error message
                            $login_err = "Invalid email or password.";
                        }
                    }
                } else{
                    // Email doesn't exist, display a generic error message
                    $login_err = "Invalid email or password.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }
    
    // Close connection
    $conn->close();
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Paws & Hearts</title>
    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* General Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f5f5f7;
            color: #1d1d1f;
            line-height: 1.5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 400px;
        }
        
        /* Header */
        .page-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #81253f;
        }
        
        .page-header p {
            color: #6e6e73;
            font-size: 16px;
        }
        
        /* Form Container */
        .auth-form-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            padding: 32px;
            margin-bottom: 20px;
        }
        
        .auth-form-container h2 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 24px;
            text-align: center;
        }
        
        /* Form Elements */
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #d2d2d7;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #81253f;
            box-shadow: 0 0 0 2px rgba(129, 37, 63, 0.2);
        }
        
        .form-check {
            display: flex;
            align-items: center;
        }
        
        input[type="checkbox"] {
            margin-right: 8px;
        }
        
        .checkbox-label {
            font-size: 14px;
            font-weight: 400;
        }
        
        /* Buttons */
        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.3s;
        }
        
        .btn-primary {
            background-color: #81253f;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #6e1f36;
            transform: translateY(-1px);
        }
        
        .btn-outline {
            background-color: transparent;
            color: #81253f;
            border: 1px solid #81253f;
            margin-top: 12px;
        }
        
        .btn-outline:hover {
            background-color: rgba(129, 37, 63, 0.05);
        }
        
        /* Error Message */
        .form-errors {
            padding: 12px;
            margin-bottom: 20px;
            background-color: #fff2f2;
            border-left: 4px solid #d9534f;
            color: #d9534f;
            border-radius: 4px;
        }
        
        /* Links */
        .auth-links {
            text-align: center;
            margin-top: 20px;
        }
        
        .auth-links a {
            color: #81253f;
            text-decoration: none;
            font-size: 14px;
        }
        
        .auth-links a:hover {
            text-decoration: underline;
        }
        
        /* Divider */
        .auth-divider {
            position: relative;
            text-align: center;
            margin: 24px 0;
        }
        
        .auth-divider:before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background-color: #d2d2d7;
        }
        
        .auth-divider span {
            position: relative;
            background-color: white;
            padding: 0 12px;
            font-size: 14px;
            color: #6e6e73;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Login to Your Account</h1>
            <p>Welcome back to Paws & Hearts</p>
        </div>
        
        <div class="auth-form-container">
            <h2>Sign In</h2>
            
            <?php 
            if(!empty($login_err)){
                echo '<div class="form-errors"><p>' . $login_err . '</p></div>';
            }        
            ?>
            
            <form class="auth-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    <?php if(!empty($email_err)) echo '<span class="error">' . $email_err . '</span>'; ?>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <?php if(!empty($password_err)) echo '<span class="error">' . $password_err . '</span>'; ?>
                </div>
                
                <div class="form-group form-check">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember" class="checkbox-label">Remember me</label>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <div class="auth-links">
                <a href="forgot-password.php">Forgot password?</a>
            </div>
            
            <div class="auth-divider">
                <span>Don't have an account?</span>
            </div>
            
            <a href="register.php" class="btn btn-outline">Create an Account</a>
        </div>
    </div>
</body>
</html>