<?php
$pageTitle = "Login";

include_once '../includes/header.php';

$email = "";
$password = "";
$error = "";
$success = false;


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    
    if (empty($email)) {
        $error = "Please enter your email";
    } elseif (empty($password)) {
        $error = "Please enter your password";
    } else {
        
        $success = true;
    }
}
?>

<div class="page-header">
    <div class="container">
        <h1>Login to Your Account</h1>
        <p>Welcome back to Paws & Hearts</p>
    </div>
</div>

<section class="login-section">
    <div class="container">
        <div class="auth-container">
            <?php if ($success): ?>
                <div class="auth-success">
                    <h2>Success!</h2>
                    <p>You have been successfully logged in.</p>
                    <div class="auth-success-actions">
                        <a href="index.php" class="btn btn-primary">Go to Homepage</a>
                        <a href="pets.php" class="btn btn-outline">Browse Pets</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="auth-form-container">
                    <h2>Login</h2>
                    
                    <?php if (!empty($error)): ?>
                        <div class="form-errors">
                            <p><?php echo $error; ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <form class="auth-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        
                        <div class="form-group form-check">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember" class="checkbox-label">Remember me</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">Login</button>
                    </form>
                    
                    <div class="auth-links">
                        <a href="forgot-password.php">Forgot password?</a>
                    </div>
                    
                    <div class="auth-divider">
                        <span>Don't have an account?</span>
                    </div>
                    
                    <a href="register.php" class="btn btn-outline btn-block">Create an Account</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
include_once '../includes/footer.php';
?>
