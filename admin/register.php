<?php
// Start session
session_start();

// Include database connection
require_once '../includes/db.php';

// Define variables and initialize with empty values
$full_name = $email = $password = $confirm_password = "";
$full_name_err = $email_err = $password_err = $confirm_password_err = "";
$registration_success = false;

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Validate full name
    if(empty(trim($_POST["full_name"]))){
        $full_name_err = "Please enter full name.";
    } else{
        $full_name = trim($_POST["full_name"]);
    }
    
    // Validate email
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter an email.";
    } elseif(!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)){
        $email_err = "Please enter a valid email address.";
    } else{
        // Prepare a select statement
        $sql = "SELECT admin_id FROM Admin WHERE email = ?";
        
        if($stmt = $conn->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_email);
            
            // Set parameters
            $param_email = trim($_POST["email"]);
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Store result
                $stmt->store_result();
                
                if($stmt->num_rows == 1){
                    $email_err = "This email is already registered.";
                } else{
                    $email = trim($_POST["email"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }
    
    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST["password"])) < 8){
        $password_err = "Password must have at least 8 characters.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm password.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Passwords did not match.";
        }
    }
    
    // Check input errors before inserting in database
    if(empty($full_name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO Admin (full_name, email, password) VALUES (?, ?, ?)";
         
        if($stmt = $conn->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sss", $param_full_name, $param_email, $param_password);
            
            // Set parameters
            $param_full_name = $full_name;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Registration successful
                $registration_success = true;
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
    <title>Admin Registration - Paws & Hearts</title>
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
            max-width: 440px;
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
        
        input[type="text"],
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
        
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #81253f;
            box-shadow: 0 0 0 2px rgba(129, 37, 63, 0.2);
        }
        
        .error {
            display: block;
            color: #d9534f;
            font-size: 13px;
            margin-top: 5px;
        }
        
        .form-text {
            display: block;
            color: #6e6e73;
            font-size: 13px;
            margin-top: 5px;
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
        
        /* Success Message */
        .auth-success {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            padding: 40px 32px;
            text-align: center;
        }
        
        .auth-success h2 {
            font-size: 24px;
            color: #81253f;
            margin-bottom: 16px;
        }
        
        .auth-success p {
            color: #1d1d1f;
            margin-bottom: 32px;
            font-size: 16px;
        }
        
        .auth-success-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
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
        
        .back-to-site {
            text-align: center;
            margin-top: 16px;
        }
        
        .back-to-site a {
            color: #81253f;
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-to-site a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Admin Registration</h1>
            <p>Create an administrator account</p>
        </div>
        
        <?php if ($registration_success): ?>
            <div class="auth-success">
                <h2>Registration Successful!</h2>
                <p>Your admin account has been created successfully.</p>
                <div class="auth-success-actions">
                    <a href="login.php" class="btn btn-primary">Login Now</a>
                    <a href="index.php" class="btn btn-outline">Go to Admin Dashboard</a>
                </div>
            </div>
        <?php else: ?>
            <div class="auth-form-container">
                <h2>New Administrator</h2>
                
                <form class="auth-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required>
                        <?php if(!empty($full_name_err)) echo '<span class="error">' . $full_name_err . '</span>'; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        <?php if(!empty($email_err)) echo '<span class="error">' . $email_err . '</span>'; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                        <small class="form-text">Password must be at least 8 characters</small>
                        <?php if(!empty($password_err)) echo '<span class="error">' . $password_err . '</span>'; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <?php if(!empty($confirm_password_err)) echo '<span class="error">' . $confirm_password_err . '</span>'; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Create Admin Account</button>
                </form>
                
                <div class="auth-divider">
                    <span>Already have an account?</span>
                </div>
                
                <a href="login.php" class="btn btn-outline">Login</a>
            </div>
        <?php endif; ?>
        
        <div class="back-to-site">
            <a href="../index.php">← Back to main site</a>
        </div>
    </div>
</body>
</html>