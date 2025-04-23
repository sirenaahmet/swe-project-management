<?php
// Include database connection
require_once '../includes/db.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$sql = "SELECT * FROM EndUser WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // User not found, redirect to login
    header("Location: ../public/login.php");
    exit();
}

$user = $result->fetch_assoc();

// Determine which tab is active
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'applications';
$active_app_tab = isset($_GET['app_tab']) ? $_GET['app_tab'] : 'adoptions';

// Fetch adoption applications
$sql = "SELECT a.*, p.name as pet_name, p.species, p.breed, p.photos, p.gender, p.age
        FROM Adoption a 
        JOIN Pet p ON a.pet_id = p.pet_id 
        WHERE a.end_user_id = ? 
        ORDER BY a.application_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$adoptions = $stmt->get_result();

// Fetch fostering applications
$sql = "SELECT f.*, p.name as pet_name, p.species, p.breed, p.photos, p.gender, p.age
        FROM Foster f 
        JOIN Pet p ON f.pet_id = p.pet_id 
        WHERE f.end_user_id = ? 
        ORDER BY f.start_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$fosters = $stmt->get_result();

// We'll initialize an empty array for favorites
$favorites = [];

// Check if the Favorites table exists first to avoid errors
$table_exists = false;
$check_table_sql = "SHOW TABLES LIKE 'Favorites'";
$table_result = $conn->query($check_table_sql);
if ($table_result->num_rows > 0) {
    $table_exists = true;
}

// Only query the favorites if the table exists
if ($table_exists) {
    try {
        $sql = "SELECT f.*, p.name as pet_name, p.species, p.breed, p.photos, p.gender, p.age, p.status
                FROM Favorites f 
                JOIN Pet p ON f.pet_id = p.pet_id 
                WHERE f.user_id = ? 
                ORDER BY f.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $favorites_result = $stmt->get_result();
        while($row = $favorites_result->fetch_assoc()) {
            $favorites[] = $row;
        }
    } catch (Exception $e) {
        // Handle any other errors that might occur
        // Just continue with empty favorites
    }
}

include_once '../includes/header.php';
?>

<style>
    /* Main styles */
    body {
        background-color: #f5f5f7;
        color: #1d1d1f;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    }
    
    .account-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px;
    }
    
    .page-title {
        font-size: 40px;
        font-weight: 700;
        margin-bottom: 30px;
        color: #1d1d1f;
    }
    
    /* Card styles */
    .card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 30px;
        border: none;
        will-change: transform, opacity;
    }
    
    .card-header {
        padding: 20px 25px;
        background: white;
        border-bottom: 1px solid #f2f2f2;
    }
    
    .card-title {
        font-size: 22px;
        font-weight: 600;
        color: #1d1d1f;
        margin: 0;
    }
    
    .card-body {
        padding: 25px;
    }
    
    /* Profile card */
    .profile-card {
        text-align: center;
    }
    
    .profile-image {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        margin: 0 auto 20px;
        border: 4px solid #f2f2f2;
    }
    
    .profile-initials {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: #007AFF;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        font-weight: 600;
        margin: 0 auto 20px;
    }
    
    .profile-name {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .profile-email {
        color: #666;
        margin-bottom: 20px;
    }
    
    /* Navigation menu */
    .nav-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .nav-item {
        padding: 0;
        margin-bottom: 8px;
    }
    
    .nav-link {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        border-radius: 10px;
        text-decoration: none;
        color: #1d1d1f;
        font-weight: 500;
        transition: background-color 0.2s ease, color 0.2s ease;
    }
    
    .nav-link:hover {
        background: #f5f5f7;
    }
    
    .nav-link.active {
        background: #e8f0fe;
        color: #007AFF;
    }
    
    .nav-link i {
        margin-right: 12px;
        color: #007AFF;
        width: 20px;
        text-align: center;
    }
    
    .nav-link.logout {
        color: #ff3b30;
    }
    
    .nav-link.logout i {
        color: #ff3b30;
    }
    
    /* Tab navigation */
    .tabs-container {
        position: relative;
        margin-bottom: 20px;
    }
    
    .tab-links {
        display: flex;
        border-bottom: 1px solid #eaeaea;
        margin-bottom: 20px;
    }
    
    .tab-link {
        color: #666;
        font-weight: 600;
        padding: 12px 20px;
        margin-right: 10px;
        text-decoration: none;
        border-bottom: 2px solid transparent;
        transition: color 0.2s ease, border-color 0.2s ease;
    }
    
    .tab-link:hover {
        color: #333;
    }
    
    .tab-link.active {
        color: #007AFF;
        border-bottom-color: #007AFF;
    }
    
    /* Content areas */
    .tab-content {
        opacity: 1;
        transition: opacity 0.3s ease;
    }
    
    /* Application and pet cards */
    .application-card, .pet-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 20px;
        display: flex;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        will-change: transform, box-shadow;
    }
    
    .application-card:hover, .pet-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    
    .application-img, .pet-img {
        width: 120px;
        height: 120px;
        object-fit: cover;
    }
    
    .application-details, .pet-details {
        padding: 15px;
        flex: 1;
    }
    
    .pet-name {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .pet-info {
        color: #666;
        margin-bottom: 3px;
        font-size: 14px;
    }
    
    .application-status {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 600;
        margin-top: 5px;
    }
    
    .status-pending {
        background-color: #FFF3E0;
        color: #F57C00;
    }
    
    .status-approved {
        background-color: #E8F5E9;
        color: #2E7D32;
    }
    
    .status-rejected {
        background-color: #FFEBEE;
        color: #C62828;
    }
    
    .status-ongoing {
        background-color: #E3F2FD;
        color: #1565C0;
    }
    
    .status-completed {
        background-color: #E0F2F1;
        color: #00695C;
    }
    
    .pet-status {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .pet-status.available {
        background-color: #E8F5E9;
        color: #2E7D32;
    }
    
    .pet-status.not-available {
        background-color: #FFEBEE;
        color: #C62828;
    }
    
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #999;
    }
    
    .empty-state i {
        font-size: 48px;
        margin-bottom: 20px;
        color: #ccc;
    }
    
    .empty-state-text {
        font-size: 18px;
        margin-bottom: 15px;
    }
    
    .empty-state-subtext {
        font-size: 14px;
        max-width: 450px;
        margin: 0 auto;
    }
    
    .action-button {
        margin-top: 20px;
        display: inline-block;
        padding: 10px 20px;
        background: #007AFF;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: background-color 0.3s ease, transform 0.3s ease;
    }
    
    .action-button:hover {
        background: #0056b3;
        transform: translateY(-2px);
    }
    
    /* Alert message */
    .alert {
        border-radius: 10px;
        padding: 15px 20px;
        margin-bottom: 20px;
        border: none;
    }
    
    .alert-success {
        background-color: #E8F5E9;
        color: #2E7D32;
    }
    
    /* Favorites grid */
    .favorites-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }
    
    .favorite-card {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background: white;
        position: relative;
        height: 100%;
        will-change: transform, box-shadow;
    }
    
    .favorite-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    
    .favorite-img {
        height: 180px;
        width: 100%;
        object-fit: cover;
    }
    
    .favorite-details {
        padding: 15px;
    }
    
    /* Prevent layout shifts */
    .sidebar-container {
        position: sticky;
        top: 20px;
    }
    
    /* Smooth scrolling */
    html {
        scroll-behavior: smooth;
    }
</style>

<div class="account-container">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-title">My Account</h1>
        </div>
    </div>
    
    <!-- Success Message -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $_SESSION['success_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <div class="row">
        <!-- Left Sidebar -->
        <div class="col-lg-3">
            <div class="sidebar-container">
                <!-- Profile Card -->
                <div class="card profile-card mb-4">
                    <div class="card-body">
                        <?php if (!empty($user['profile_photo'])): ?>
                            <img src="../<?php echo htmlspecialchars($user['profile_photo']); ?>" class="profile-image" alt="Profile Photo">
                        <?php else: ?>
                            <div class="profile-initials">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <h2 class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                        <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                        
                        <a href="../public/edit-profile.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-user-edit me-2"></i>Edit Profile
                        </a>
                    </div>
                </div>
                
                <!-- Navigation Menu -->
                <div class="card">
                    <div class="card-body p-3">
                        <ul class="nav-menu">
                            <li class="nav-item">
                                <a href="#applications" class="nav-link <?php echo $active_tab == 'applications' ? 'active' : ''; ?>" 
                                   onclick="showTab('applications'); return false;">
                                    <i class="fas fa-clipboard-list"></i>
                                    My Applications
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#favorites" class="nav-link <?php echo $active_tab == 'favorites' ? 'active' : ''; ?>"
                                   onclick="showTab('favorites'); return false;">
                                    <i class="fas fa-heart"></i>
                                    Favorite Pets
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="../public/pets.php" class="nav-link">
                                    <i class="fas fa-search"></i>
                                    Browse Pets
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="../public/logout.php" class="nav-link logout">
                                    <i class="fas fa-sign-out-alt"></i>
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Applications Tab -->
            <div id="applications" class="tab-content" style="display: <?php echo $active_tab == 'applications' ? 'block' : 'none'; ?>">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">My Applications</h3>
                    </div>
                    <div class="card-body">
                        <!-- Application Type Tabs -->
                        <div class="tabs-container">
                            <div class="tab-links">
                                <a href="#" class="tab-link <?php echo $active_app_tab == 'adoptions' ? 'active' : ''; ?>" 
                                   onclick="showAppTab('adoptions'); return false;">
                                    Adoption Applications
                                </a>
                                <a href="#" class="tab-link <?php echo $active_app_tab == 'fosters' ? 'active' : ''; ?>"
                                   onclick="showAppTab('fosters'); return false;">
                                    Foster Applications
                                </a>
                            </div>
                            
                            <!-- Adoptions Content -->
                            <div id="adoptions-content" class="app-content" style="display: <?php echo $active_app_tab == 'adoptions' ? 'block' : 'none'; ?>">
                                <?php if ($adoptions->num_rows > 0): ?>
                                    <?php while($adoption = $adoptions->fetch_assoc()): ?>
                                        <div class="application-card">
                                            <?php if (!empty($adoption['photos'])): ?>
                                                <img src="../<?php echo htmlspecialchars($adoption['photos']); ?>" class="application-img" alt="<?php echo htmlspecialchars($adoption['pet_name']); ?>">
                                            <?php else: ?>
                                                <img src="../assets/images/pet-placeholder.jpg" class="application-img" alt="No image available">
                                            <?php endif; ?>
                                            
                                            <div class="application-details">
                                                <h4 class="pet-name"><?php echo htmlspecialchars($adoption['pet_name']); ?></h4>
                                                <p class="pet-info">
                                                    <strong>Species:</strong> <?php echo htmlspecialchars($adoption['species']); ?>
                                                    <?php if (!empty($adoption['breed'])): ?>
                                                        | <strong>Breed:</strong> <?php echo htmlspecialchars($adoption['breed']); ?>
                                                    <?php endif; ?>
                                                </p>
                                                <p class="pet-info">
                                                    <strong>Application Date:</strong> <?php echo date('M d, Y', strtotime($adoption['application_date'])); ?>
                                                </p>
                                                
                                                <span class="application-status status-<?php echo strtolower($adoption['status']); ?>">
                                                    <?php echo htmlspecialchars($adoption['status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-clipboard"></i>
                                        <h4 class="empty-state-text">No adoption applications yet</h4>
                                        <p class="empty-state-subtext">
                                            You haven't submitted any adoption applications. Browse our available pets to find your perfect companion.
                                        </p>
                                        <a href="../public/pets.php" class="action-button">
                                            <i class="fas fa-search me-2"></i>Browse Pets
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Fosters Content -->
                            <div id="fosters-content" class="app-content" style="display: <?php echo $active_app_tab == 'fosters' ? 'block' : 'none'; ?>">
                                <?php if ($fosters->num_rows > 0): ?>
                                    <?php while($foster = $fosters->fetch_assoc()): ?>
                                        <div class="application-card">
                                            <?php if (!empty($foster['photos'])): ?>
                                                <img src="../<?php echo htmlspecialchars($foster['photos']); ?>" class="application-img" alt="<?php echo htmlspecialchars($foster['pet_name']); ?>">
                                            <?php else: ?>
                                                <img src="../assets/images/pet-placeholder.jpg" class="application-img" alt="No image available">
                                            <?php endif; ?>
                                            
                                            <div class="application-details">
                                                <h4 class="pet-name"><?php echo htmlspecialchars($foster['pet_name']); ?></h4>
                                                <p class="pet-info">
                                                    <strong>Species:</strong> <?php echo htmlspecialchars($foster['species']); ?>
                                                    <?php if (!empty($foster['breed'])): ?>
                                                        | <strong>Breed:</strong> <?php echo htmlspecialchars($foster['breed']); ?>
                                                    <?php endif; ?>
                                                </p>
                                                <p class="pet-info">
                                                    <strong>Start Date:</strong> <?php echo date('M d, Y', strtotime($foster['start_date'])); ?>
                                                    <?php if (!empty($foster['end_date'])): ?>
                                                        | <strong>End Date:</strong> <?php echo date('M d, Y', strtotime($foster['end_date'])); ?>
                                                    <?php endif; ?>
                                                </p>
                                                
                                                <span class="application-status status-<?php echo strtolower($foster['status']); ?>">
                                                    <?php echo htmlspecialchars($foster['status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-home"></i>
                                        <h4 class="empty-state-text">No fostering applications yet</h4>
                                        <p class="empty-state-subtext">
                                            You haven't submitted any fostering applications. Fostering is a great way to help pets while they wait for their forever home.
                                        </p>
                                        <a href="../public/pets.php" class="action-button">
                                            <i class="fas fa-search me-2"></i>Browse Pets
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Favorites Tab -->
            <div id="favorites" class="tab-content" style="display: <?php echo $active_tab == 'favorites' ? 'block' : 'none'; ?>">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">My Favorite Pets</h3>
                    </div>
                    <div class="card-body">
                        <?php if (count($favorites) > 0): ?>
                            <div class="favorites-grid">
                                <?php foreach($favorites as $favorite): ?>
                                    <div class="favorite-card">
                                        <?php if (!empty($favorite['photos'])): ?>
                                            <img src="../<?php echo htmlspecialchars($favorite['photos']); ?>" class="favorite-img" alt="<?php echo htmlspecialchars($favorite['pet_name']); ?>">
                                        <?php else: ?>
                                            <img src="../assets/images/pet-placeholder.jpg" class="favorite-img" alt="No image available">
                                        <?php endif; ?>
                                        
                                        <div class="favorite-details">
                                            <h4 class="pet-name"><?php echo htmlspecialchars($favorite['pet_name']); ?></h4>
                                            <p class="pet-info">
                                                <strong>Species:</strong> <?php echo htmlspecialchars($favorite['species']); ?>
                                                <?php if (!empty($favorite['breed'])): ?>
                                                    | <strong>Breed:</strong> <?php echo htmlspecialchars($favorite['breed']); ?>
                                                <?php endif; ?>
                                            </p>
                                            <p class="pet-info">
                                                <?php if (!empty($favorite['age'])): ?>
                                                    <strong>Age:</strong> <?php echo htmlspecialchars($favorite['age']); ?> year(s)
                                                <?php endif; ?>
                                                <?php if (!empty($favorite['gender'])): ?>
                                                    | <strong>Gender:</strong> <?php echo htmlspecialchars($favorite['gender']); ?>
                                                <?php endif; ?>
                                            </p>
                                            
                                            <a href="../public/pet-details.php?id=<?php echo $favorite['pet_id']; ?>" class="btn btn-sm btn-outline-primary mt-2">
                                                View Details
                                            </a>
                                            
                                            <span class="pet-status <?php echo $favorite['status'] == 'Available' ? 'available' : 'not-available'; ?>">
                                                <?php echo htmlspecialchars($favorite['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-heart"></i>
                                <h4 class="empty-state-text">No favorite pets yet</h4>
                                <p class="empty-state-subtext">
                                    You haven't added any pets to your favorites list. Browse our available pets and click the heart icon to save your favorites.
                                </p>
                                <a href="../public/pets.php" class="action-button">
                                    <i class="fas fa-search me-2"></i>Browse Pets
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Tab navigation without page reloads
    let activeTab = '<?php echo $active_tab; ?>';
    let activeAppTab = '<?php echo $active_app_tab; ?>';
    
    function showTab(tabId) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.style.display = 'none';
        });
        
        // Show the selected tab content
        document.getElementById(tabId).style.display = 'block';
        
        // Update active class on nav links
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        document.querySelector(`.nav-link[href="#${tabId}"]`).classList.add('active');
        
        // Update the activeTab variable
        activeTab = tabId;
        
        // Update URL without reloading page
        window.history.replaceState(null, '', `?tab=${tabId}${activeAppTab ? `&app_tab=${activeAppTab}` : ''}`);
    }
    
    function showAppTab(tabId) {
        // Hide all app tab contents
        document.querySelectorAll('.app-content').forEach(tab => {
            tab.style.display = 'none';
        });
        
        // Show the selected app tab content
        document.getElementById(tabId + '-content').style.display = 'block';
        
        // Update active class on tab links
        document.querySelectorAll('.tab-link').forEach(link => {
            link.classList.remove('active');
        });
        document.querySelector(`.tab-link[onclick*="${tabId}"]`).classList.add('active');
        
        // Update the activeAppTab variable
        activeAppTab = tabId;
        
        // Update URL without reloading page
        window.history.replaceState(null, '', `?tab=${activeTab}&app_tab=${tabId}`);
    }
    
    // Initialize the tabs on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Make sure the correct tab is active
        showTab(activeTab);
        if (activeTab === 'applications') {
            showAppTab(activeAppTab);
        }
    });
</script>

<?php
include_once '../includes/footer.php';
$conn->close();
?>
