<?php
session_start();

if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once '../includes/db.php';

if(!$conn || $conn->connect_error){
    die("Database connection failed: " . ($conn ? $conn->connect_error : "Connection variable not set"));
}

$title = $content = "";
$title_err = $content_err = "";
$success_message = "";
$error_message = "";

// Get the actual columns from the Blog table
$columns = [];
$debug_sql = "SHOW COLUMNS FROM Blog";
if($debug_result = $conn->query($debug_sql)) {
    while($row = $debug_result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    $debug_result->free();
} else {
    $error_message = "Error getting table structure: " . $conn->error;
}

// Check existence of id parameter before processing further
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    // Get URL parameter
    $id = trim($_GET["id"]);
    
    // Validate ID is numeric
    if(!is_numeric($id)) {
        $error_message = "Invalid blog ID format.";
    } else {
        // Prepare a select statement
        $sql = "SELECT * FROM Blog WHERE blog_id = ?";
        
        if($stmt = $conn->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("i", $id);
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                $result = $stmt->get_result();
                
                if($result->num_rows == 1){
                    // Fetch result row as an associative array
                    $row = $result->fetch_assoc();
                    
                    // Retrieve individual field value
                    $title = $row["title"];
                    $content = $row["content"];
                } else{
                    $error_message = "No blog found with ID: $id";
                }
                
            } else{
                $error_message = "Error retrieving blog: " . $stmt->error;
            }
            
            // Close statement
            $stmt->close();
        } else {
            $error_message = "Error preparing statement: " . $conn->error;
        }
    }
    
} else if($_SERVER["REQUEST_METHOD"] != "POST") {
    // URL doesn't contain id parameter. Redirect to blogs page
    header("location: blogs.php");
    exit();
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Get hidden input value
    $id = $_POST["id"];
    
    // Validate id is numeric
    if(!is_numeric($id)) {
        $error_message = "Invalid blog ID format.";
    } else {
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
            
            try {
                // Prepare update statement
                $sql = "UPDATE Blog SET title = ?, content = ?";
                
                // Add updated_at if it exists in the columns
                if(in_array('updated_at', $columns)) {
                    $sql .= ", updated_at = NOW()";
                }
                
                $sql .= " WHERE blog_id = ?";
                
                if($stmt = $conn->prepare($sql)){
                    // Bind variables to the prepared statement as parameters
                    $stmt->bind_param("ssi", $title, $content, $id);
                    
                    // Attempt to execute the prepared statement
                    if($stmt->execute()){
                        // Set success message
                        $success_message = "Blog updated successfully!";
                        
                        // Refresh the page after a short delay to display the success message
                        header("Refresh: 2; URL=blogs.php?updated=true");
                    } else{
                        $error_message = "Error updating blog: " . $stmt->error;
                    }
                    
                    // Close statement
                    $stmt->close();
                } else {
                    $error_message = "Error preparing statement: " . $conn->error;
                }
            } catch (Exception $e) {
                $error_message = "Exception occurred: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blog - Paws & Hearts</title>
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

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-danger:hover {
    background-color: #c82333;
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

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 14px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
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

/* Blog metadata */
.blog-metadata {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    font-size: 14px;
}

.metadata-item {
    display: flex;
    margin-bottom: 8px;
}

.metadata-label {
    font-weight: 600;
    width: 120px;
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
        <p class="admin-info">Welcome, <?php echo htmlspecialchars($_SESSION["admin_name"] ?? "Admin"); ?></p>
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
            <h2>Edit Blog Post</h2>
            <div class="action-buttons">
                <a href="blogs.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Blogs</a>
            </div>
        </div>

        <?php
        // Display error message if any
        if(!empty($error_message)){
            echo '<div class="alert alert-danger">' . htmlspecialchars($error_message) . '</div>';
        }
        
        // Display success message if any
        if(!empty($success_message)){
            echo '<div class="alert alert-success">' . htmlspecialchars($success_message) . '</div>';
        }
        ?>

        <?php if(isset($id) && is_numeric($id) && empty($error_message) || $_SERVER["REQUEST_METHOD"] == "POST"): ?>
        <div class="form-container">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="editBlogForm">
                <input type="hidden" name="id" value="<?php echo isset($id) ? htmlspecialchars($id) : ''; ?>">
                
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($title); ?>">
                    <span class="invalid-feedback"><?php echo $title_err; ?></span>
                </div>    
                
                <div class="form-group">
                    <label>Content</label>
                    <textarea name="content" id="content" class="form-control <?php echo (!empty($content_err)) ? 'is-invalid' : ''; ?>" rows="10"><?php echo htmlspecialchars($content); ?></textarea>
                    <span class="invalid-feedback"><?php echo $content_err; ?></span>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Blog Post</button>
                    <a href="blogs.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                    <a href="delete_blog.php?id=<?php echo isset($id) ? htmlspecialchars($id) : ''; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this blog post?');">
                        <i class="fas fa-trash-alt"></i> Delete Blog
                    </a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add a rich text editor for the content area -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.3.1/tinymce.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize TinyMCE
        tinymce.init({
            selector: 'textarea#content',
            height: 400,
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount',
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            content_style: 'body { font-family:Inter,Arial,sans-serif; font-size:16px }',
            setup: function(editor) {
                // Save form when editor loses focus
                editor.on('blur', function() {
                    editor.save();
                });
            }
        });
        
        // Add form submission event listener
        const form = document.getElementById('editBlogForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Make sure TinyMCE updates the textarea
                tinymce.triggerSave();
            });
        }
    });
</script>
</body>
</html>