<?php
// Check if session is already started
/*if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
    */
$pageTitle = "Login";

require_once '../includes/db.php';
include_once '../includes/header.php';

$email = $password = "";
$email_err = $password_err = $login_err = "";
$success = false;

// On form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? '');
    $password = trim($_POST["password"] ?? '');

    if (empty($email)) {
        $email_err = "Please enter your email.";
    }

    if (empty($password)) {
        $password_err = "Please enter your password.";
    }

    if (empty($email_err) && empty($password_err)) {
        $sql = "SELECT user_id, full_name, email, password FROM EndUser WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = $email;

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $full_name, $email, $hashed_password);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            // No need to start session again, already started above
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $id;
                            $_SESSION["user_name"] = $full_name;
                            $_SESSION["user_email"] = $email;
                            $success = true;
                        } else {
                            $login_err = "Invalid email or password.";
                        }
                    }
                } else {
                    $login_err = "Invalid email or password.";
                }
            }
            $stmt->close();
        }
        $conn->close();
    }
}
?>



<section class="auth-section">

<div class="page-header">
    <div class="container">
        <h1>Login to Your Account</h1>
        <p>Welcome back to Paws & Hearts</p>
    </div>
</div>
    <div class="container">
        <div class="auth-container">
            <?php if ($success): ?>
                <div class="auth-success">
                    <div class="auth-success-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#81253F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
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

                    <?php if (!empty($login_err)): ?>
                        <div class="form-errors">
                            <p><?php echo $login_err; ?></p>
                        </div>
                    <?php endif; ?>

                    <form class="auth-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                            <?php if (!empty($email_err)) echo '<span class="error">' . $email_err . '</span>'; ?>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                            <?php if (!empty($password_err)) echo '<span class="error">' . $password_err . '</span>'; ?>
                        </div>

                        <div class="form-group form-check">
                            <div class="checkbox-wrapper">
                                <input type="checkbox" id="remember" name="remember">
                                <label for="remember" class="checkbox-label">Remember me</label>
                            </div>
                            <a href="forgot-password.php" class="form-link">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Login</button>
                    </form>

                    <div class="auth-divider">
                        <span>Don't have an account?</span>
                    </div>

                    <a href="register.php" class="btn btn-outline btn-block">Create an Account</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
/* Auth Pages Common Styles */
.page-header {
    background-color: #F5F5F7;
    text-align: center;
    padding: 4rem 0 2rem;
    margin-bottom: 2rem;
}

.page-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1D1D1F;
    margin-bottom: 0.5rem;
}

.page-header p {
    font-size: 1.2rem;
    color: #6e6e73;
    margin: 0;
}

.auth-section {
    padding: 2rem 0 4rem;
}

.auth-container {
    max-width: 440px;
    margin: 0 auto;
}

.auth-form-container {
    background-color: white;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    padding: 32px;
    margin-bottom: 20px;
}

.auth-form-container h2 {
    font-size: 1.5rem;
    font-weight: 600;
    text-align: center;
    margin-bottom: 1.5rem;
    color: #1D1D1F;
}

.form-errors {
    background-color: #fff2f2;
    border-left: 4px solid #d9534f;
    color: #d9534f;
    padding: 12px 16px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.form-errors p {
    margin: 0;
    font-size: 0.9rem;
}

.auth-form .form-group {
    margin-bottom: 1.25rem;
}

.auth-form label {
    display: block;
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: #1D1D1F;
}

.auth-form input[type="email"],
.auth-form input[type="password"],
.auth-form input[type="text"] {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #d2d2d7;
    border-radius: 8px;
    font-family: inherit;
    font-size: 1rem;
    transition: border-color 0.3s, box-shadow 0.3s;
}

.auth-form input[type="email"]:focus,
.auth-form input[type="password"]:focus,
.auth-form input[type="text"]:focus {
    outline: none;
    border-color: #81253F;
    box-shadow: 0 0 0 2px rgba(129, 37, 63, 0.2);
}

.error {
    color: #d9534f;
    font-size: 0.8rem;
    margin-top: 0.5rem;
    display: block;
}

.form-text {
    color: #6e6e73;
    font-size: 0.8rem;
    margin-top: 0.5rem;
    display: block;
}

.form-check {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.checkbox-wrapper {
    display: flex;
    align-items: center;
}

.checkbox-wrapper input[type="checkbox"] {
    margin-right: 8px;
    width: 18px;
    height: 18px;
    accent-color: #81253F;
}

.checkbox-label {
    font-size: 0.9rem;
    font-weight: 400;
}

.checkbox-label a {
    color: #81253F;
    text-decoration: none;
}

.checkbox-label a:hover {
    text-decoration: underline;
}

.form-link {
    color: #81253F;
    font-size: 0.9rem;
    text-decoration: none;
}

.form-link:hover {
    text-decoration: underline;
}

.btn {
    display: inline-block;
    text-align: center;
    text-decoration: none;
    font-weight: 500;
    border-radius: 8px;
    transition: all 0.3s ease;
    cursor: pointer;
    padding: 14px 24px;
    font-family: inherit;
    font-size: 1rem;
    border: none;
}

.btn-block {
    display: block;
    width: 100%;
}

.btn-primary {
    background-color: #81253F;
    color: white;
}

.btn-primary:hover {
    background-color: #6e1f36;
}

.btn-outline {
    background-color: transparent;
    border: 1px solid #81253F;
    color: #81253F;
}

.btn-outline:hover {
    background-color: rgba(129, 37, 63, 0.05);
}

.auth-divider {
    position: relative;
    text-align: center;
    margin: 1.5rem 0;
}

.auth-divider:before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background-color: #e0e0e0;
    z-index: 1;
}

.auth-divider span {
    position: relative;
    background-color: white;
    padding: 0 12px;
    font-size: 0.9rem;
    color: #6e6e73;
    z-index: 2;
}

.auth-success {
    background-color: white;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    padding: 40px 32px;
    text-align: center;
}

.auth-success-icon {
    margin-bottom: 1.5rem;
}

.auth-success h2 {
    font-size: 1.8rem;
    color: #81253F;
    margin-bottom: 1rem;
}

.auth-success p {
    color: #1D1D1F;
    margin-bottom: 2rem;
    font-size: 1.1rem;
}

.auth-success-actions {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    max-width: 300px;
    margin: 0 auto;
}

@media (max-width: 576px) {
    .auth-form-container {
        padding: 24px 20px;
    }
    
    .page-header {
        padding: 3rem 0 1.5rem;
    }
    
    .page-header h1 {
        font-size: 2rem;
    }
    
    .auth-success {
        padding: 32px 24px;
    }
}
</style>

<?php
include_once '../includes/footer.php';
?>