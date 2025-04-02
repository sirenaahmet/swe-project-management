<?php
// Set the page title
$pageTitle = "Sign Up";

// Include the header
include_once '../includes/header.php';

// Initialize variables
$full_name = "";
$email = "";
$error = "";
$success = false;

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Simple validation
    if (empty($full_name)) {
        $error = "Please enter your full name";
    } elseif (empty($email)) {
        $error = "Please enter your email";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } elseif (empty($password)) {
        $error = "Please enter a password";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // In a real application, you would:
        // 1. Check if the email already exists in the database
        // 2. Hash the password
        // 3. Insert the new user into the database
        // 4. Set session variables
        
        // For demonstration purposes, we'll just set success to true
        $success = true;
        
        // In a real application, you would set session variables here
        // $_SESSION['user_id'] = $user_id;
        // $_SESSION['user_name'] = $full_name;
        
        // And potentially redirect to another page
        // header("Location: account.php");
        // exit();
    }
}
?>

<div class="page-header">
    <div class="container">
        <h1>Create an Account</h1>
        <p>Join Paws & Hearts and start your adoption journey</p>
    </div>
</div>

<section class="register-section">
    <div class="container">
        <div class="auth-container">
            <?php if ($success): ?>
                <div class="auth-success">
                    <h2>Success!</h2>
                    <p>Your account has been created successfully.</p>
                    <div class="auth-success-actions">
                        <a href="index.php" class="btn btn-primary">Go to Homepage</a>
                        <a href="pets.php" class="btn btn-outline">Browse Pets</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="auth-form-container">
                    <h2>Sign Up</h2>
                    
                    <?php if (!empty($error)): ?>
                        <div class="form-errors">
                            <p><?php echo $error; ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <form class="auth-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                            <small class="form-text">Password must be at least 8 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div class="form-group form-check">
                            <input type="checkbox" id="terms" name="terms" required>
                            <label for="terms" class="checkbox-label">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">Create Account</button>
                    </form>
                    
                    <div class="auth-divider">
                        <span>Already have an account?</span>
                    </div>
                    
                    <a href="login.php" class="btn btn-outline btn-block">Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
// Include the footer
include_once '../includes/footer.php';
?>