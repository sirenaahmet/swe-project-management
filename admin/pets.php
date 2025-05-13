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

// Get recently added pets
$recent_pets_query = "SELECT pet_id, name, species, breed, status FROM Pet ORDER BY created_at";
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
    <title>Manage Pets - Paws & Hearts Admin</title>
    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Admin Styles -->
    <link rel="stylesheet" href="../assets/css/admin-style.css">
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

.custom-file-upload {
    display: inline-block;
    padding: 10px 15px;
    cursor: pointer;
    background-color: #f5f5f7;
    border: 1px dashed #d2d2d7;
    border-radius: 8px;
    text-align: center;
    width: 100%;
    transition: all 0.3s;
}

.custom-file-upload:hover {
    background-color: #e8e8ed;
    border-color: #81253f;
}

.custom-file-upload i {
    margin-right: 8px;
    color: #81253f;
}

input[type="file"] {
    cursor: pointer;
    padding: 10px;
    width: 100%;
    border: 1px solid #d2d2d7;
    border-radius: 8px;
    background-color: #f8f9fa;
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

/* Data Tables & Listing Pages */
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

.all-pets-section-title {
    font-size: 18px;
    font-weight: 600;
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

.status-badge {
    padding: 4px 8px;
    border-radius: 100px;
    font-size: 12px;
    font-weight: 500;
    display: inline-block;
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

.view-all {
    font-size: 14px;
    color: #81253f;
    display: flex;
    align-items: center;
}

.view-all i {
    margin-right: 5px;
}

.view-all:hover {
    text-decoration: underline;
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
                <li><a href="pets.php" class="active"><i class="fas fa-paw"></i> Manage Pets</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
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
                <h2>Manage Pets</h2>
                <div class="action-buttons">
                    <a href="addpet.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Pet</a>
                </div>
            </div>
            
            <div class="recent-section">
                <div class="section-header">
                    <h3 class="all-pets-section-title">All Pets</h3>
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
                            <td><?php echo htmlspecialchars($pet['breed'] ?? 'N/A'); ?></td>
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
                                <a href="../public/pet-details.php?id=<?php echo $pet['pet_id']; ?>" title="View Pet"><i class="fas fa-eye"></i></a>
                                <a href="editpet.php?id=<?php echo $pet['pet_id']; ?>" title="Edit Pet"><i class="fas fa-edit"></i></a>
                                <a href="#" onclick="confirmDelete(<?php echo $pet['pet_id']; ?>)" title="Delete Pet"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        function confirmDelete(petId) {
            if (confirm("Are you sure you want to delete this pet?")) {
                window.location.href = "deletepet.php?id=" + petId;
            }
        }
    </script>
</body>
</html>