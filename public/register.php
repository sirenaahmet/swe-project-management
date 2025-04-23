<?php
//session_start();
$pageTitle = "Register";

require_once '../includes/db.php';
include_once '../includes/header.php';

$full_name = $email = $password = $confirm_password = "";
$full_name_err = $email_err = $password_err = $confirm_password_err = $terms_err = "";
$registration_success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST["full_name"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';
    $confirm_password = $_POST["confirm_password"] ?? '';

    // Validate full name
    if (empty($full_name)) {
        $full_name_err = "Please enter your full name.";
    }

    // Validate email
    if (empty($email)) {
        $email_err = "Please enter an email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email address.";
    } else {
        $sql = "SELECT user_id FROM EndUser WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $email_err = "This email is already registered.";
                }
            }
            $stmt->close();
        }
    }

    // Validate password
    if (empty($password)) {
        $password_err = "Please enter a password.";
    } elseif (strlen($password) < 8) {
        $password_err = "Password must have at least 8 characters.";
    }

    // Confirm password
    if (empty($confirm_password)) {
        $confirm_password_err = "Please confirm your password.";
    } elseif ($password !== $confirm_password) {
        $confirm_password_err = "Passwords do not match.";
    }

    // Terms checkbox
    if (!isset($_POST["terms"]) || $_POST["terms"] != "on") {
        $terms_err = "You must agree to the Terms of Service and Privacy Policy.";
    }

    // If no errors, insert into DB
    if (empty($full_name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err) && empty($terms_err)) {
        $sql = "INSERT INTO EndUser (full_name, email, password) VALUES (?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bind_param("sss", $full_name, $email, $hashed_password);
            if ($stmt->execute()) {
                $registration_success = true;
            }
            $stmt->close();
        }
    }

    $conn->close();
}
?>



<section class="auth-section">
<div class="page-header">
    <div class="container">
        <h1>Create an Account</h1>
        <p>Join Paws & Hearts and start your adoption journey</p>
    </div>
</div>
    <div class="container">
        <div class="auth-container">
            <?php if ($registration_success): ?>
                <div class="auth-success">
                    <div class="auth-success-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#81253F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <h2>Registration Successful!</h2>
                    <p>Your account has been created successfully.</p>
                    <div class="auth-success-actions">
                        <a href="login.php" class="btn btn-primary">Login Now</a>
                        <a href="index.php" class="btn btn-outline">Go to Homepage</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="auth-form-container">
                    <h2>Sign Up</h2>

                    <?php if (!empty($terms_err)): ?>
                        <div class="form-errors"><p><?php echo $terms_err; ?></p></div>
                    <?php endif; ?>

                    <form class="auth-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required>
                            <?php if (!empty($full_name_err)) echo '<span class="error">' . $full_name_err . '</span>'; ?>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                            <?php if (!empty($email_err)) echo '<span class="error">' . $email_err . '</span>'; ?>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                            <small class="form-text">Password must be at least 8 characters</small>
                            <?php if (!empty($password_err)) echo '<span class="error">' . $password_err . '</span>'; ?>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                            <?php if (!empty($confirm_password_err)) echo '<span class="error">' . $confirm_password_err . '</span>'; ?>
                        </div>

                        <div class="form-group terms-check">
                            <div class="checkbox-wrapper">
                                <input type="checkbox" id="terms" name="terms">
                                <label for="terms" class="checkbox-label">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Create Account</button>
                    </form>

                    <div class="auth-divider"><span>Already have an account?</span></div>
                    <a href="login.php" class="btn btn-outline btn-block">Login</a>
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

.form-check,
.terms-check {
    display: flex;
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

<?php include_once '../includes/footer.php'; ?>