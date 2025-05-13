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

// Process form submissions
$message = '';
$message_type = '';

// Handle user status toggle
if(isset($_POST['toggle_status'])) {
    $user_id = $_POST['user_id'];
    $new_status = $_POST['new_status'];
    
    // First check if the is_active column exists in the EndUser table
    $check_column_query = "SHOW COLUMNS FROM EndUser LIKE 'is_active'";
    $column_result = $conn->query($check_column_query);
    
    if($column_result->num_rows == 0) {
        // Column doesn't exist, let's add it
        $add_column_query = "ALTER TABLE EndUser ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1";
        $conn->query($add_column_query);
    }
    
    $update_query = "UPDATE EndUser SET is_active = ? WHERE user_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $new_status, $user_id);
    
    if($stmt->execute()) {
        $message = "User status updated successfully!";
        $message_type = "success";
    } else {
        $message = "Error updating user status: " . $conn->error;
        $message_type = "error";
    }
    $stmt->close();
}

// Handle user deletion
if(isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    
    // Check if there are any adoptions or fosters for this user
    $check_query = "SELECT COUNT(*) as count FROM Adoption WHERE end_user_id = ?
                   UNION ALL
                   SELECT COUNT(*) FROM Foster WHERE end_user_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $user_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $has_records = false;
    
    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            if($row['count'] > 0) {
                $has_records = true;
                break;
            }
        }
    }
    $check_stmt->close();
    
    if($has_records) {
        $message = "Cannot delete user. This user has adoption or foster records.";
        $message_type = "error";
    } else {
        // First delete all favorites
        $delete_favs = "DELETE FROM Favorites WHERE user_id = ?";
        $fav_stmt = $conn->prepare($delete_favs);
        $fav_stmt->bind_param("i", $user_id);
        $fav_stmt->execute();
        $fav_stmt->close();
        
        // Now delete the user
        $delete_query = "DELETE FROM EndUser WHERE user_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $user_id);
        
        if($stmt->execute()) {
            $message = "User deleted successfully!";
            $message_type = "success";
        } else {
            $message = "Error deleting user: " . $conn->error;
            $message_type = "error";
        }
        $stmt->close();
    }
}

// Get all users with pagination
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

$search_term = '';
if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = $_GET['search'];
    $count_query = "SELECT COUNT(*) as total FROM EndUser 
                   WHERE full_name LIKE ? OR email LIKE ?";
    $user_query = "SELECT * FROM EndUser 
                  WHERE full_name LIKE ? OR email LIKE ? 
                  ORDER BY created_at DESC LIMIT ?, ?";
    $search_param = "%{$search_term}%";
} else {
    $count_query = "SELECT COUNT(*) as total FROM EndUser";
    $user_query = "SELECT * FROM EndUser ORDER BY created_at DESC LIMIT ?, ?";
}

// Get total number of users
$stmt = $conn->prepare($count_query);
if(!empty($search_term)) {
    $stmt->bind_param("ss", $search_param, $search_param);
}
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_records = $row['total'];
$total_pages = ceil($total_records / $records_per_page);
$stmt->close();

// Get users for current page
$stmt = $conn->prepare($user_query);
if(!empty($search_term)) {
    $stmt->bind_param("ssii", $search_param, $search_param, $offset, $records_per_page);
} else {
    $stmt->bind_param("ii", $offset, $records_per_page);
}
$stmt->execute();
$users_result = $stmt->get_result();
$users = [];
if($users_result->num_rows > 0) {
    while($row = $users_result->fetch_assoc()) {
        $users[] = $row;
    }
}
$stmt->close();

// Get adoption stats for each user
foreach($users as &$user) {
    $adoption_query = "SELECT COUNT(*) as total,
                        SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
                        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected
                       FROM Adoption WHERE end_user_id = ?";
    $stmt = $conn->prepare($adoption_query);
    $stmt->bind_param("i", $user['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $adoption_stats = $result->fetch_assoc();
    $user['adoption_stats'] = $adoption_stats;
    $stmt->close();
    
    // Get foster stats
    $foster_query = "SELECT COUNT(*) as total,
                     SUM(CASE WHEN status = 'Ongoing' THEN 1 ELSE 0 END) as ongoing,
                     SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed
                     FROM Foster WHERE end_user_id = ?";
    $stmt = $conn->prepare($foster_query);
    $stmt->bind_param("i", $user['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $foster_stats = $result->fetch_assoc();
    $user['foster_stats'] = $foster_stats;
    $stmt->close();
    
    // Get favorite pets count
    $fav_query = "SELECT COUNT(*) as total FROM Favorites WHERE user_id = ?";
    $stmt = $conn->prepare($fav_query);
    $stmt->bind_param("i", $user['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $fav_count = $result->fetch_assoc();
    $user['favorites_count'] = $fav_count['total'];
    $stmt->close();
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Paws & Hearts</title>
    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
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
        
        .search-form {
            display: flex;
            margin-bottom: 20px;
        }
        
        .search-form input {
            flex: 1;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px 0 0 6px;
            font-size: 14px;
        }
        
        .search-form button {
            padding: 10px 16px;
            background-color: #81253f;
            color: white;
            border: none;
            border-radius: 0 6px 6px 0;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .search-form button:hover {
            background-color: #6e1f36;
        }
        
        /* Alert Messages */
        .alert {
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .alert-success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }
        
        .alert-error {
            background-color: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }
        
        /* Users Table */
        .data-table-container {
            background-color: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            overflow-x: auto;
        }
        
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
        
        /* User Status Badge */
        .status-badge {
            padding: 4px 8px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-active {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-inactive {
            background-color: #f5f5f5;
            color: #757575;
        }
        
        /* Action Buttons */
        .action-btns {
            display: flex;
            gap: 8px;
        }
        
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s;
        }
        
        .btn-view {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        
        .btn-view:hover {
            background-color: #bbdefb;
        }
        
        .btn-activate {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .btn-activate:hover {
            background-color: #c8e6c9;
        }
        
        .btn-deactivate {
            background-color: #fff3e0;
            color: #e65100;
        }
        
        .btn-deactivate:hover {
            background-color: #ffe0b2;
        }
        
        .btn-delete {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .btn-delete:hover {
            background-color: #ffcdd2;
        }
        
        /* User Stats */
        .user-stats {
            display: flex;
            gap: 8px;
        }
        
        .stat-pill {
            background-color: #f5f5f5;
            color: #666;
            padding: 4px 8px;
            border-radius: 100px;
            font-size: 12px;
            display: flex;
            align-items: center;
        }
        
        .stat-pill i {
            margin-right: 4px;
            font-size: 10px;
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            list-style: none;
        }
        
        .pagination li {
            margin: 0 5px;
        }
        
        .pagination a {
            display: block;
            padding: 8px 12px;
            background-color: white;
            border-radius: 4px;
            font-size: 14px;
            color: #81253f;
            transition: background-color 0.3s;
        }
        
        .pagination a:hover {
            background-color: #f0f0f0;
        }
        
        .pagination a.active {
            background-color: #81253f;
            color: white;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            max-width: 90%;
            position: relative;
        }
        
        .close-modal {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #aaa;
        }
        
        .close-modal:hover {
            color: #333;
        }
        
        .modal-header {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .modal-footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #f0f0f0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
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
            
            .data-table th, 
            .data-table td {
                padding: 10px 12px;
            }
            
            .user-stats {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
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
                <li><a href="users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="staff.php"><i class="fas fa-user-tie"></i> Staff</a></li>
                <li><a href="blogs.php"><i class="fas fa-blog"></i> Blog Posts</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            </ul>
            
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h2>Manage Users</h2>
            </div>
            
            <!-- Search Form -->
            <form class="search-form" method="GET" action="users.php">
                <input type="text" name="search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search_term); ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
            
            <!-- Alert Messages -->
            <?php if(!empty($message)): ?>
                <div class="alert <?php echo $message_type == 'success' ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Users Table -->
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Activity Stats</th>
                            <th>Registered On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($users)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No users found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['user_id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php if(isset($user['is_active']) && $user['is_active'] == 0): ?>
                                            <span class="status-badge status-inactive">Inactive</span>
                                        <?php else: ?>
                                            <span class="status-badge status-active">Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="user-stats">
                                            <span class="stat-pill">
                                                <i class="fas fa-home"></i> 
                                                <?php echo $user['adoption_stats']['total']; ?> Adoptions
                                            </span>
                                            <span class="stat-pill">
                                                <i class="fas fa-heart"></i> 
                                                <?php echo $user['foster_stats']['total']; ?> Fosters
                                            </span>
                                            <span class="stat-pill">
                                                <i class="fas fa-star"></i> 
                                                <?php echo $user['favorites_count']; ?> Favorites
                                            </span>
                                        </div>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="action-btns">
                                            <?php if(isset($user['is_active']) && $user['is_active'] == 0): ?>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                    <input type="hidden" name="new_status" value="1">
                                                    <button type="submit" name="toggle_status" class="btn btn-activate">
                                                        <i class="fas fa-check"></i> Activate
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                    <input type="hidden" name="new_status" value="0">
                                                    <button type="submit" name="toggle_status" class="btn btn-deactivate">
                                                        <i class="fas fa-ban"></i> Deactivate
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <button type="button" class="btn btn-delete" 
                                                    onclick="confirmDelete(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                    <ul class="pagination">
                        <?php if($page > 1): ?>
                            <li>
                                <a href="?page=<?php echo $page-1; ?><?php echo !empty($search_term) ? '&search='.urlencode($search_term) : ''; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li>
                                <a href="?page=<?php echo $i; ?><?php echo !empty($search_term) ? '&search='.urlencode($search_term) : ''; ?>" 
                                   <?php echo $page == $i ? 'class="active"' : ''; ?>>
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if($page < $total_pages): ?>
                            <li>
                                <a href="?page=<?php echo $page+1; ?><?php echo !empty($search_term) ? '&search='.urlencode($search_term) : ''; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <div class="modal-header">
                <h3>Confirm Deletion</h3>
            </div>
            <p>Are you sure you want to delete user <strong id="deleteUserName"></strong>?</p>
            <p>This action cannot be undone.</p>
            <div class="modal-footer">
                <button type="button" class="btn btn-view" onclick="closeModal()">Cancel</button>
                <form id="deleteForm" method="POST" action="">
                    <input type="hidden" id="deleteUserId" name="user_id">
                    <button type="submit" name="delete_user" class="btn btn-delete">Delete</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Modal functions
        function confirmDelete(userId, userName) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('deleteUserName').textContent = userName;
            document.getElementById('deleteModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        // Close modal if clicked outside
        window.onclick = function(event) {
            var modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>