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

// Process status updates
if(isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $application_id = $_GET['id'];
    $action = $_GET['action'];
    $status = '';
    $success_message = '';
    
    if($action == 'approve') {
        $status = 'Approved';
        $success_message = 'Application approved successfully!';
    } elseif($action == 'reject') {
        $status = 'Rejected';
        $success_message = 'Application rejected successfully!';
    } elseif($action == 'pending') {
        $status = 'Pending';
        $success_message = 'Application marked as pending!';
    }
    
    if(!empty($status)) {
        $sql = "UPDATE Adoption SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $application_id);
        
        if($stmt->execute()) {
            $message = $success_message;
            
            // If approved, update pet status
            if($status == 'Approved') {
                // Get pet ID
                $sql = "SELECT pet_id FROM Adoption WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $application_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if($row = $result->fetch_assoc()) {
                    $pet_id = $row['pet_id'];
                    
                    // Update pet status to Adopted
                    $sql = "UPDATE Pet SET status = 'Adopted' WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $pet_id);
                    $stmt->execute();
                }
            }
            
            header("Location: adoptions.php?success=" . urlencode($message));
            exit();
        } else {
            $error_message = "Error updating application status.";
        }
    }
}

// Fetch all adoption applications
$sql = "SELECT a.*, p.name as pet_name, u.first_name, u.last_name, u.email, u.phone 
        FROM Adoption a 
        JOIN Pet p ON a.pet_id = p.id 
        JOIN User u ON a.user_id = u.id 
        ORDER BY a.application_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Adoptions - Paws & Hearts Admin</title>
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
        
        /* Success & Error Messages */
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .success-message i {
            margin-right: 10px;
            font-size: 18px;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .error-message i {
            margin-right: 10px;
            font-size: 18px;
        }
        
        /* Table Styles */
        .table-container {
            background-color: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .table-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th,
        .data-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e6e6e6;
        }
        
        .data-table th {
            font-weight: 600;
            background-color: #f5f5f7;
            color: #1d1d1f;
        }
        
        .data-table tbody tr:hover {
            background-color: #f9f9f9;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            font-family: 'Inter', sans-serif;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: #81253f;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #6e1f36;
        }
        
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        
        .btn-warning:hover {
            background-color: #e0a800;
        }
        
        .btn-sm {
            padding: 4px 8px;
            font-size: 11px;
        }
        
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.3;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .sidebar {
                width: 200px;
            }
            
            .main-content {
                margin-left: 200px;
            }
            
            .data-table {
                font-size: 14px;
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
            
            .table-container {
                overflow-x: auto;
            }
            
            .data-table {
                min-width: 800px;
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
                    <img src="../assets/images/logo.png" alt="Paws & Hearts Logo">
                    <h1>Paws & Hearts</h1>
                </div>
                <p class="admin-info">Welcome, <?php echo htmlspecialchars($_SESSION["admin_name"]); ?></p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="pets.php"><i class="fas fa-paw"></i> Manage Pets</a></li>
                <li><a href="adoptions.php" class="active"><i class="fas fa-home"></i> Adoptions</a></li>
                <li><a href="fosters.php"><i class="fas fa-heart"></i> Fosters</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="staff.php"><i class="fas fa-user-tie"></i> Staff</a></li>
                <li><a href="blog.php"><i class="fas fa-blog"></i> Blog Posts</a></li>
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
                <h2>Manage Adoption Applications</h2>
            </div>
            
            <?php if(isset($_GET['success'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($error_message)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <div class="table-container">
                <h3 class="table-title">Adoption Applications</h3>
                
                <?php if($result && $result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Pet</th>
                                    <th>Applicant</th>
                                    <th>Contact</th>
                                    <th>Application Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['pet_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($row['email']); ?><br>
                                            <?php echo htmlspecialchars($row['phone']); ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($row['application_date'])); ?></td>
                                        <td>
                                            <?php 
                                                $statusClass = '';
                                                switch($row['status']) {
                                                    case 'Pending':
                                                        $statusClass = 'status-pending';
                                                        break;
                                                    case 'Approved':
                                                        $statusClass = 'status-approved';
                                                        break;
                                                    case 'Rejected':
                                                        $statusClass = 'status-rejected';
                                                        break;
                                                }
                                            ?>
                                            <span class="status-badge <?php echo $statusClass; ?>">
                                                <?php echo htmlspecialchars($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if($row['status'] !== 'Approved'): ?>
                                                    <a href="adoptions.php?action=approve&id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to approve this application?');">
                                                        <i class="fas fa-check"></i> Approve
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if($row['status'] !== 'Rejected'): ?>
                                                    <a href="adoptions.php?action=reject&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to reject this application?');">
                                                        <i class="fas fa-times"></i> Reject
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if($row['status'] !== 'Pending'): ?>
                                                    <a href="adoptions.php?action=pending&id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-clock"></i> Mark Pending
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <a href="view_application.php?id=<?php echo $row['id']; ?>&type=adoption" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No adoption applications found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php
// Close connection
$conn->close();
?>