<?php
// Initialize the session
session_start();

// Check if the admin is logged in, if not redirect to login page
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include database connection
require_once '../includes/db.php';

// Define variables and initialize with empty values
$title = $content = "";
$title_err = $content_err = "";
$success_message = "";

// Get the actual columns from the Blog table
$columns = [];
$debug_sql = "SHOW COLUMNS FROM Blog";
if($debug_result = $conn->query($debug_sql)) {
    while($row = $debug_result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    $debug_result->free();
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Validate title
    if(empty(trim($_POST["title"]))){
        $title_err = "Please enter a title.";
    } else {
        $title = trim($_POST["title"]);
    }
    
    // Validate content
    if(empty(trim($_POST["content"]))){
        $content_err = "Please enter content.";
    } else {
        $content = trim($_POST["content"]);
    }
    
    // Check if there are any errors before proceeding
    if(empty($title_err) && empty($content_err)){
        
        // Prepare SQL statement based on available columns
        $sql = "INSERT INTO Blog (title, content";
        
        // Check if admin_id column exists and is needed
        if(in_array('admin_id', $columns) && isset($_SESSION["admin_id"])) {
            $sql .= ", admin_id";
        }
        
        // Add created_at if it exists
        if(in_array('created_at', $columns)) {
            $sql .= ", created_at";
        }
        
        $sql .= ") VALUES (?, ?";
        
        // Add parameter placeholders for optional columns
        if(in_array('admin_id', $columns) && isset($_SESSION["admin_id"])) {
            $sql .= ", ?";
        }
        
        if(in_array('created_at', $columns)) {
            $sql .= ", NOW()";
        }
        
        $sql .= ")";
        
        if($stmt = $conn->prepare($sql)){
            // Set parameter types and values
            $param_types = "ss"; // Start with string, string for title and content
            $params = [$title, $content];
            
            // Add admin_id if needed
            if(in_array('admin_id', $columns) && isset($_SESSION["admin_id"])) {
                $param_types .= "i"; // integer
                $params[] = $_SESSION["admin_id"];
            }
            
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param($param_types, ...$params);
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Redirect to blogs page with success message
                header("location: blogs.php?added=true");
                exit();
            } else{
                echo "Oops! Something went wrong: " . $conn->error;
            }
            
            // Close statement
            $stmt->close();
        } else {
            echo "Prepare statement failed: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Blog - Paws & Hearts</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
}

a {
    text-decoration: none;
    color: inherit;
}

/* Layout */
.admin-container {
    display: flex;
    min-height: 100vh;
}

/* Sidebar */
.sidebar {
    width: 250px;
    background-color: #81253f;
    color: white;
    padding: 20px 0;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
}

.sidebar-header {
    padding: 0 20px 20px;
    margin-bottom: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-logo {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.sidebar-logo img {
    width: 40px;
    height: 40px;
    margin-right: 10px;
}

.sidebar-logo h1 {
    font-size: 18px;
    font-weight: 600;
}

.admin-info {
    font-size: 14px;
    opacity: 0.8;
}

.sidebar-menu {
    list-style: none;
}

.sidebar-menu li {
    margin-bottom: 5px;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    transition: background-color 0.3s;
}

.sidebar-menu a:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.sidebar-menu a.active {
    background-color: rgba(255, 255, 255, 0.2);
    border-left: 4px solid white;
}

.sidebar-menu i {
    width: 24px;
    margin-right: 10px;
}

.sidebar-footer {
    padding: 20px;
    position: absolute;
    bottom: 0;
    width: 100%;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.logout-btn {
    display: flex;
    align-items: center;
    color: white;
    opacity: 0.8;
    transition: opacity 0.3s;
}

.logout-btn:hover {
    opacity: 1;
}

.logout-btn i {
    margin-right: 10px;
}

/* Main Content */
.main-content {
    flex: 1;
    margin-left: 250px;
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e6e6e6;
}

.page-header h2 {
    font-size: 24px;
    font-weight: 600;
}

.action-buttons {
    display: flex;
    gap: 10px;
}

/* Form Section */
.form-container {
    background-color: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    margin-bottom: 30px;
}

.form-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #f0f0f0;
}

/* Form Elements */
.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    font-weight: 500;
}

input[type="text"],
input[type="number"],
input[type="url"],
select,
textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d2d2d7;
    border-radius: 8px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    transition: border-color 0.3s;
}

input[type="text"]:focus,
input[type="number"]:focus,
input[type="url"]:focus,
select:focus,
textarea:focus {
    outline: none;
    border-color: #81253f;
    box-shadow: 0 0 0 2px rgba(129, 37, 63, 0.2);
}

textarea {
    resize: vertical;
    min-height: 100px;
}

.form-check {
    display: flex;
    align-items: center;
    margin-top: 10px;
}

.form-check-input {
    margin-right: 8px;
}

.form-check-label {
    font-size: 14px;
}

.form-text {
    font-size: 12px;
    color: #6c757d;
    margin-top: 5px;
    display: block;
}

/* Buttons */
.btn {
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn i {
    margin-right: 8px;
}

.btn-primary {
    background-color: #81253f;
    color: white;
}

.btn-primary:hover {
    background-color: #6e1f36;
    transform: translateY(-1px);
}

.btn-secondary {
    background-color: #f5f5f7;
    color: #333;
}

.btn-secondary:hover {
    background-color: #e8e8ed;
}

/* Error & Success Messages */
.invalid-feedback {
    color: #d9534f;
    font-size: 13px;
    margin-top: 5px;
    display: block;
}

.is-invalid {
    border-color: #d9534f !important;
}

.is-invalid:focus {
    box-shadow: 0 0 0 2px rgba(217, 83, 79, 0.25) !important;
}

/* Debug info */
.debug-info {
    margin-top: 20px;
    padding: 15px;
    background-color: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
}

.debug-title {
    font-weight: 600;
    margin-bottom: 10px;
}

.debug-columns {
    font-family: monospace;
}

/* TinyMCE Editor Styling */
.tox-tinymce {
    border-radius: 8px !important;
    border-color: #d2d2d7 !important;
}

.tox .tox-toolbar, .tox .tox-toolbar__overflow, .tox .tox-toolbar__primary {
    background-color: #f8f9fa !important;
}

/* Responsive Adjustments */
@media (max-width: 992px) {
    .sidebar {
        width: 200px;
    }
    
    .main-content {
        margin-left: 200px;
    }
}

@media (max-width: 768px) {
    .admin-container {
        flex-direction: column;
    }
    
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
        padding: 15px;
    }
    
    .sidebar-menu {
        display: flex;
        flex-wrap: wrap;
    }
    
    .sidebar-menu li {
        margin-right: 10px;
    }
    
    .sidebar-footer {
        position: relative;
        margin-top: 20px;
    }
    
    .main-content {
        margin-left: 0;
    }
    
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .action-buttons {
        width: 100%;
    }
}
</style>
</head>
<body>
<div class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <h1>Paws & Hearts</h1>
                </div>
                <p class="admin-info">Welcome, <?php echo htmlspecialchars($_SESSION["admin_name"]); ?></p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                
                <li><a href="adoptions.php"><i class="fas fa-home"></i> Adoptions</a></li>
                <li><a href="fosters.php"><i class="fas fa-heart"></i> Fosters</a></li>
                <li><a href="pets.php"><i class="fas fa-paw"></i> Manage Pets</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="staff.php"><i class="fas fa-user-tie"></i> Staff</a></li>
                <li><a href="blogs.php" class="active"><i class="fas fa-blog"></i> Blog Posts</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            </ul>
            
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
    <div class="admin-container">
        <div class="main-content">
            <div class="page-header">
                <h2>Add New Blog Post</h2>
                <div class="action-buttons">
                    <a href="blogs.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Blogs</a>
                </div>
            </div>

            <div class="form-container">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $title; ?>">
                        <span class="invalid-feedback"><?php echo $title_err; ?></span>
                    </div>    
                    
                    <div class="form-group">
                        <label>Content</label>
                        <textarea name="content" class="form-control <?php echo (!empty($content_err)) ? 'is-invalid' : ''; ?>" rows="10"><?php echo $content; ?></textarea>
                        <span class="invalid-feedback"><?php echo $content_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <input type="submit" class="btn btn-primary" value="Create Blog Post">
                        <a href="blogs.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add a rich text editor for the content area -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.3.1/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: 'textarea[name="content"]',
            height: 400,
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount',
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            content_style: 'body { font-family:Inter,Arial,sans-serif; font-size:16px }'
        });
    </script>
</body>
</html>