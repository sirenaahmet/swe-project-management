
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
$alert_message = '';
$alert_type = '';

// Add new staff
if(isset($_POST['add_staff'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $position = trim($_POST['position']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $privileges = isset($_POST['privileges']) ? implode(',', $_POST['privileges']) : '';
    
    // Validate input
    if(empty($full_name) || empty($email) || empty($password)) {
        $alert_message = "Please fill in all required fields";
        $alert_type = "danger";
    } else {
        // Check if email already exists
        $check_sql = "SELECT email FROM Staff WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if($check_result->num_rows > 0) {
            $alert_message = "Email already exists. Please use a different email.";
            $alert_type = "danger";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Prepare an insert statement
            $sql = "INSERT INTO Staff (full_name, email, password, position, phone, privileges, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            if($stmt = $conn->prepare($sql)){
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("ssssssi", $full_name, $email, $hashed_password, $position, $phone, $privileges, $_SESSION["admin_id"]);
                
                // Attempt to execute the prepared statement
                if($stmt->execute()){
                    $alert_message = "Staff member added successfully!";
                    $alert_type = "success";
                } else{
                    $alert_message = "Something went wrong. Please try again later.";
                    $alert_type = "danger";
                }
                
                // Close statement
                $stmt->close();
            }
        }
        
        $check_stmt->close();
    }
}

// Update staff privileges
if(isset($_POST['update_staff'])) {
    $staff_id = $_POST['staff_id'];
    $privileges = isset($_POST['privileges']) ? implode(',', $_POST['privileges']) : '';
    
    $sql = "UPDATE Staff SET privileges = ? WHERE staff_id = ?";
    if($stmt = $conn->prepare($sql)){
        $stmt->bind_param("si", $privileges, $staff_id);
        
        if($stmt->execute()){
            $alert_message = "Privileges updated successfully!";
            $alert_type = "success";
        } else{
            $alert_message = "Something went wrong. Please try again later.";
            $alert_type = "danger";
        }
        
        $stmt->close();
    }
}

// Delete staff
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $staff_id = $_GET['delete'];
    
    $sql = "DELETE FROM Staff WHERE staff_id = ?";
    if($stmt = $conn->prepare($sql)){
        $stmt->bind_param("i", $staff_id);
        
        if($stmt->execute()){
            $alert_message = "Staff member deleted successfully!";
            $alert_type = "success";
        } else{
            $alert_message = "Something went wrong. Please try again later.";
            $alert_type = "danger";
        }
        
        $stmt->close();
    }
}

// Get all staff members
$staff_query = "SELECT * FROM Staff ORDER BY created_at DESC";
$staff_result = $conn->query($staff_query);
$staff_members = [];
if ($staff_result && $staff_result->num_rows > 0) {
    while ($row = $staff_result->fetch_assoc()) {
        $staff_members[] = $row;
    }
}

// Get all admin users (for display purposes)
$admin_query = "SELECT admin_id, full_name, email FROM Admin ORDER BY created_at DESC";
$admin_result = $conn->query($admin_query);
$admin_members = [];
if ($admin_result && $admin_result->num_rows > 0) {
    while ($row = $admin_result->fetch_assoc()) {
        $admin_members[] = $row;
    }
}

// Close connection
$conn->close();

// Define available privileges
$available_privileges = [
    'manage_pets' => 'Manage Pets',
    'manage_adoptions' => 'Manage Adoptions',
    'manage_fosters' => 'Manage Fosters',
    'manage_users' => 'Manage Users',
    'manage_blog' => 'Manage Blog Posts'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff - Paws & Hearts</title>
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
        
        .add-new-btn {
            padding: 8px 16px;
            background-color: #81253f;
            color: white;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: background-color 0.3s;
            cursor: pointer;
            border: none;
        }
        
        .add-new-btn:hover {
            background-color: #6e1f36;
        }
        
        .add-new-btn i {
            margin-right: 8px;
        }
        
        /* Alert Message */
        .alert {
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 6px;
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
        
        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .data-table th {
            text-align: left;
            padding: 14px 16px;
            background-color: #f8f8f8;
            font-weight: 600;
            font-size: 14px;
            color: #666;
        }
        
        .data-table td {
            padding: 14px 16px;
            border-top: 1px solid #f0f0f0;
            font-size: 14px;
            vertical-align: middle;
        }
        
        .data-table tbody tr:hover {
            background-color: #fafafa;
        }
        
        .action-links {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            transition: background-color 0.3s, color 0.3s;
        }
        
        .action-btn i {
            margin-right: 6px;
            font-size: 12px;
        }
        
        .edit-btn {
            background-color: #e3f2fd;
            color: #0277bd;
        }
        
        .edit-btn:hover {
            background-color: #bbdefb;
        }
        
        .delete-btn {
            background-color: #ffebee;
            color: #e53935;
        }
        
        .delete-btn:hover {
            background-color: #ffcdd2;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 50px auto;
            padding: 20px;
            border-radius: 12px;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            position: relative;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 16px;
            margin-bottom: 20px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .modal-title {
            font-size: 18px;
            font-weight: 600;
        }
        
        .close-btn {
            font-size: 22px;
            font-weight: 700;
            color: #999;
            cursor: pointer;
            transition: color 0.3s;
        }
        
        .close-btn:hover {
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #555;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: #81253f;
            outline: none;
        }
        
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            cursor: pointer;
            user-select: none;
        }
        
        .checkbox-item input[type="checkbox"] {
            margin-right: 8px;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-cancel {
            padding: 10px 16px;
            background-color: #f5f5f5;
            color: #333;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-cancel:hover {
            background-color: #e0e0e0;
        }
        
        .btn-submit {
            padding: 10px 16px;
            background-color: #81253f;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-submit:hover {
            background-color: #6e1f36;
        }
        
        /* Admin section */
        .admin-section {
            margin-top: 40px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 10px;
            color: #81253f;
        }
        
        /* Badge styles */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 500;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        
        .badge-primary {
            background-color: #e3f2fd;
            color: #0277bd;
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
            
            .data-table {
                display: block;
                overflow-x: auto;
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
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="staff.php" class="active"><i class="fas fa-user-tie"></i> Staff</a></li>
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
                <h2>Manage Staff</h2>
                <button class="add-new-btn" id="openAddModal">
                    <i class="fas fa-plus"></i> Add New Staff
                </button>
            </div>
            
            <?php if(!empty($alert_message)): ?>
                <div class="alert alert-<?php echo $alert_type; ?>">
                    <?php echo $alert_message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Staff Table -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Position</th>
                        <th>Phone</th>
                        <th>Privileges</th>
                        <th>Added By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($staff_members)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">No staff members found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($staff_members as $staff): ?>
                            <tr>
                                <td><?php echo $staff['staff_id']; ?></td>
                                <td><?php echo htmlspecialchars($staff['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($staff['email']); ?></td>
                                <td><?php echo htmlspecialchars($staff['position']); ?></td>
                                <td><?php echo htmlspecialchars($staff['phone']); ?></td>
                                <td>
                                    <?php 
                                    $staff_privileges = explode(',', $staff['privileges']);
                                    foreach($staff_privileges as $privilege) {
                                        if(isset($available_privileges[$privilege])) {
                                            echo '<span class="badge badge-primary">' . $available_privileges[$privilege] . '</span>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    foreach($admin_members as $admin) {
                                        if($admin['admin_id'] == $staff['created_by']) {
                                            echo htmlspecialchars($admin['full_name']);
                                            break;
                                        }
                                    }
                                    ?>
                                </td>
                                <td class="action-links">
                                    <button class="action-btn edit-btn" onclick="openEditModal(<?php echo $staff['staff_id']; ?>, '<?php echo addslashes($staff['privileges']); ?>')">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <a href="staff.php?delete=<?php echo $staff['staff_id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this staff member?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <!-- Admin Section -->
            <div class="admin-section">
                <h3 class="section-title"><i class="fas fa-user-shield"></i> Admin Users</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($admin_members)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">No admin users found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($admin_members as $admin): ?>
                                <tr>
                                    <td><?php echo $admin['admin_id']; ?></td>
                                    <td><?php echo htmlspecialchars($admin['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                    <td><span class="badge badge-primary">Administrator</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Add Staff Modal -->
    <div id="addStaffModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add New Staff Member</h3>
                <span class="close-btn" id="closeAddModal">&times;</span>
            </div>
            <form action="staff.php" method="post">
                <div class="form-group">
                    <label class="form-label" for="full_name">Full Name *</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password *</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="position">Position</label>
                    <input type="text" class="form-control" id="position" name="position">
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="text" class="form-control" id="phone" name="phone">
                </div>
                <div class="form-group">
                    <label class="form-label">Privileges</label>
                    <div class="checkbox-group">
                        <?php foreach($available_privileges as $key => $value): ?>
                            <label class="checkbox-item">
                                <input type="checkbox" name="privileges[]" value="<?php echo $key; ?>">
                                <?php echo $value; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" id="cancelAddBtn">Cancel</button>
                    <button type="submit" class="btn-submit" name="add_staff">Add Staff</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Staff Modal -->
    <div id="editStaffModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Staff Privileges</h3>
                <span class="close-btn" id="closeEditModal">&times;</span>
            </div>
            <form action="staff.php" method="post">
                <input type="hidden" id="edit_staff_id" name="staff_id">
                <div class="form-group">
                    <label class="form-label">Privileges</label>
                    <div class="checkbox-group">
                        <?php foreach($available_privileges as $key => $value): ?>
                            <label class="checkbox-item">
                                <input type="checkbox" name="privileges[]" value="<?php echo $key; ?>" class="edit-privilege">
                                <?php echo $value; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" id="cancelEditBtn">Cancel</button>
                    <button type="submit" class="btn-submit" name="update_staff">Update Privileges</button>
                </div>
            </form>
        </div>
    </div>
    <script>
// Modal functionality
const addModal = document.getElementById('addStaffModal');
const editModal = document.getElementById('editStaffModal');
const openAddModalBtn = document.getElementById('openAddModal');
const closeAddModalBtn = document.getElementById('closeAddModal');
const cancelAddBtn = document.getElementById('cancelAddBtn');
const closeEditModalBtn = document.getElementById('closeEditModal');
const cancelEditBtn = document.getElementById('cancelEditBtn');

openAddModalBtn.addEventListener('click', () => {
    addModal.style.display = 'block';
});

closeAddModalBtn.addEventListener('click', () => {
    addModal.style.display = 'none';
});

cancelAddBtn.addEventListener('click', () => {
    addModal.style.display = 'none';
});

closeEditModalBtn.addEventListener('click', () => {
    editModal.style.display = 'none';
});

cancelEditBtn.addEventListener('click', () => {
    editModal.style.display = 'none';
});

window.addEventListener('click', (event) => {
    if (event.target === addModal) {
        addModal.style.display = 'none';
    }
    if (event.target === editModal) {
        editModal.style.display = 'none';
    }
});

// Function to open edit modal
function openEditModal(staffId, privileges) {
    document.getElementById('edit_staff_id').value = staffId;
    
    // Clear all checkboxes first
    const checkboxes = document.querySelectorAll('.edit-privilege');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Check the appropriate ones
    if (privileges) {
        const privilegeArray = privileges.split(',');
        privilegeArray.forEach(privilege => {
            const checkbox = document.querySelector(`.edit-privilege[value="${privilege}"]`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });
    }
    
    editModal.style.display = 'block';
}

// Auto-dismiss alerts after 3 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        setTimeout(function() {
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(() => alert.style.display = 'none', 500);
            });
        }, 3000);
    }
});
</script>