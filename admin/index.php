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

// Get some basic stats for the dashboard
// Count total pets
$pet_query = "SELECT COUNT(*) as total_pets FROM Pet";
$pet_result = $conn->query($pet_query);
$total_pets = 0;
if ($pet_result && $pet_result->num_rows > 0) {
    $row = $pet_result->fetch_assoc();
    $total_pets = $row['total_pets'];
}

// Count pending adoptions
$adoption_query = "SELECT COUNT(*) as pending_adoptions FROM Adoption WHERE status = 'Pending'";
$adoption_result = $conn->query($adoption_query);
$pending_adoptions = 0;
if ($adoption_result && $adoption_result->num_rows > 0) {
    $row = $adoption_result->fetch_assoc();
    $pending_adoptions = $row['pending_adoptions'];
}

// Count active fosters
$foster_query = "SELECT COUNT(*) as active_fosters FROM Foster WHERE status = 'Ongoing'";
$foster_result = $conn->query($foster_query);
$active_fosters = 0;
if ($foster_result && $foster_result->num_rows > 0) {
    $row = $foster_result->fetch_assoc();
    $active_fosters = $row['active_fosters'];
}

// Count users
$user_query = "SELECT COUNT(*) as total_users FROM EndUser";
$user_result = $conn->query($user_query);
$total_users = 0;
if ($user_result && $user_result->num_rows > 0) {
    $row = $user_result->fetch_assoc();
    $total_users = $row['total_users'];
}

// Get recently added pets
$recent_pets_query = "SELECT pet_id, name, species, breed, status FROM Pet ORDER BY created_at DESC LIMIT 5";
$recent_pets_result = $conn->query($recent_pets_query);
$recent_pets = [];
if ($recent_pets_result && $recent_pets_result->num_rows > 0) {
    while ($row = $recent_pets_result->fetch_assoc()) {
        $recent_pets[] = $row;
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Paws & Hearts</title>
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
        }
        
        .add-new-btn:hover {
            background-color: #6e1f36;
        }
        
        .add-new-btn i {
            margin-right: 8px;
        }
        
        /* Dashboard Stats */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        
        .stat-title {
            font-size: 16px;
            font-weight: 600;
            color: #666;
        }
        
        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }
        
        .stat-icon.pets {
            background-color: #4CAF50;
        }
        
        .stat-icon.adoptions {
            background-color: #2196F3;
        }
        
        .stat-icon.fosters {
            background-color: #FF9800;
        }
        
        .stat-icon.users {
            background-color: #9C27B0;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        
        .stat-description {
            font-size: 14px;
            color: #666;
        }
        
        /* Recent Entries */
        .recent-section {
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
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
        }
        
        .view-all {
            font-size: 14px;
            color: #81253f;
        }
        
        .view-all:hover {
            text-decoration: underline;
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
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-available {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-adopted {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        
        .status-fostered {
            background-color: #fff3e0;
            color: #e65100;
        }
        
        .status-notavailable {
            background-color: #f5f5f5;
            color: #757575;
        }
        
        .action-links a {
            margin-right: 10px;
            color: #81253f;
            font-size: 13px;
        }
        
        .action-links a:hover {
            text-decoration: underline;
        }
        
        /* Welcome Message */
        .welcome-message {
            background-color: #fff8e1;
            border-left: 4px solid #ffca28;
            padding: 16px 20px;
            margin-bottom: 24px;
            border-radius: 6px;
        }
        
        .welcome-message h3 {
            font-size: 18px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .welcome-message p {
            font-size: 14px;
            color: #666;
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
            
            .stats-container {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            
            .data-table th, 
            .data-table td {
                padding: 10px 12px;
            }
        }
        
        @media (max-width: 576px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .add-new-btn {
                margin-top: 12px;
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
                <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="pets.php"><i class="fas fa-paw"></i> Manage Pets</a></li>
                <li><a href="adoptions.php"><i class="fas fa-home"></i> Adoptions</a></li>
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
            <div class="welcome-message">
                <h3>Welcome to Admin Dashboard</h3>
                <p>This dashboard provides an overview of your pet adoption system. Use the sidebar menu to navigate to different sections.</p>
            </div>
            
            <div class="page-header">
                <h2>Dashboard Overview</h2>
                <a href="addpet.php" class="add-new-btn">
                    <i class="fas fa-plus"></i> Add New Pet
                </a>
            </div>
            
            <!-- Stats Section -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Total Pets</span>
                        <div class="stat-icon pets">
                            <i class="fas fa-paw"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $total_pets; ?></div>
                    <div class="stat-description">Pets in the system</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Pending Adoptions</span>
                        <div class="stat-icon adoptions">
                            <i class="fas fa-home"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $pending_adoptions; ?></div>
                    <div class="stat-description">Waiting for approval</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Active Fosters</span>
                        <div class="stat-icon fosters">
                            <i class="fas fa-heart"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $active_fosters; ?></div>
                    <div class="stat-description">Currently in foster care</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Registered Users</span>
                        <div class="stat-icon users">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $total_users; ?></div>
                    <div class="stat-description">Total user accounts</div>
                </div>
            </div>
            
            <!-- Recent Pets Section -->
            <div class="recent-section">
                <div class="section-header">
                    <h3 class="section-title">Recently Added Pets</h3>
                    <a href="pets.php" class="view-all">View All Pets</a>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Species</th>
                            <th>Breed</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_pets)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No pets found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_pets as $pet): ?>
                                <tr>
                                    <td><?php echo $pet['pet_id']; ?></td>
                                    <td><?php echo htmlspecialchars($pet['name']); ?></td>
                                    <td><?php echo htmlspecialchars($pet['species']); ?></td>
                                    <td><?php echo htmlspecialchars($pet['breed']); ?></td>
                                    <td>
                                        <?php 
                                        $status_class = '';
                                        switch($pet['status']) {
                                            case 'Available':
                                                $status_class = 'status-available';
                                                break;
                                            case 'Adopted':
                                                $status_class = 'status-adopted';
                                                break;
                                            case 'Fostered':
                                                $status_class = 'status-fostered';
                                                break;
                                            default:
                                                $status_class = 'status-notavailable';
                                        }
                                        ?>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo htmlspecialchars($pet['status']); ?>
                                        </span>
                                    </td>
                                    <td class="action-links">
                                        <a href="viewpet.php?id=<?php echo $pet['pet_id']; ?>">View</a>
                                        <a href="editpet.php?id=<?php echo $pet['pet_id']; ?>">Edit</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>