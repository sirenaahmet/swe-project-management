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

// Process deletion if requested
if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $blog_id = intval($_GET['id']);

    $delete_query = "DELETE FROM Blog WHERE blog_id = ?";
    if($stmt = $conn->prepare($delete_query)){
        $stmt->bind_param("i", $blog_id);
        if($stmt->execute()){
            header("location: blogs.php?deleted=true");
            exit();
        } else {
            $error_message = "Something went wrong. Please try again later.";
        }
        $stmt->close();
    }
}

// Get all blog posts
$blogs_query = "SELECT blog_id, title, created_at FROM Blog ORDER BY created_at DESC";
$blogs_result = $conn->query($blogs_query);
$blogs = [];
if ($blogs_result && $blogs_result->num_rows > 0) {
    while ($row = $blogs_result->fetch_assoc()) {
        $blogs[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blogs - Paws & Hearts</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css"> <!-- Assuming your CSS is separated -->

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

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    transition: background-color 0.3s;
    display: inline-flex;
    align-items: center;
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
}

/* Alert Messages */
.alert {
    padding: 16px 20px;
    margin-bottom: 24px;
    border-radius: 6px;
    display: flex;
    align-items: center;
}

.alert i {
    margin-right: 10px;
    font-size: 18px;
}

.alert-success {
    background-color: #e8f5e9;
    color: #2e7d32;
    border-left: 4px solid #4caf50;
}

/* Blogs Section */
.blogs-section {
    background-color: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    margin-bottom: 30px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f0f0f0;
}

.blogs-section-title {
    font-size: 18px;
    font-weight: 600;
}

/* Table Styles */
.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    text-align: left;
    padding: 12px 16px;
    background-color: #f8f8f8;
    font-weight: 600;
    font-size: 14px;
    color: #666;
}

.data-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #f0f0f0;
    font-size: 14px;
}

.data-table tr:last-child td {
    border-bottom: none;
}

.data-table tr:hover {
    background-color: #fafafa;
}

.action-links {
    display: flex;
    gap: 15px;
}

.action-links a {
    color: #81253f;
    font-size: 13px;
}

.action-links a:hover {
    text-decoration: underline;
}

.delete-link {
    color: #d32f2f !important;
}

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

/* Status badges */
.status-badge {
    padding: 4px 8px;
    border-radius: 100px;
    font-size: 12px;
    font-weight: 500;
}

.status-published {
    background-color: #e8f5e9;
    color: #2e7d32;
}

.status-draft {
    background-color: #f5f5f5;
    color: #757575;
}

/* Responsive Adjustments */
@media (max-width: 992px) {
    .main-content {
        margin-left: 200px;
    }
}

@media (max-width: 768px) {
    .admin-container {
        flex-direction: column;
    }
    
    .main-content {
        margin-left: 0;
    }
    
    .data-table th, 
    .data-table td {
        padding: 10px 12px;
    }
}

@media (max-width: 576px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .action-buttons {
        margin-top: 12px;
    }
}
    </style>
</head>
<body>
    <div class="admin-container">

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

        <div class="main-content">
            <div class="page-header">
                <h2>Manage Blog Posts</h2>
                <div class="action-buttons">
                    <a href="addblog.php" class="btn btn-primary"><i class="fas fa-plus"></i> New Blog</a>
                </div>
            </div>

            <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 'true'): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Blog post deleted successfully.
                </div>
            <?php endif; ?>

            <div class="blogs-section">
                <div class="section-header">
                    <h3 class="blogs-section-title">All Blog Posts</h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($blogs) > 0): ?>
                            <?php foreach ($blogs as $blog): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($blog['title']); ?></td>
                                    <td><?php echo date('F j, Y', strtotime($blog['created_at'])); ?></td>
                                    <td class="action-links">
                                        <a href="editblog.php?id=<?php echo $blog['blog_id']; ?>">Edit</a>
                                        <a href="blogs.php?action=delete&id=<?php echo $blog['blog_id']; ?>" class="delete-link" onclick="return confirm('Are you sure you want to delete this blog post?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No blog posts found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
