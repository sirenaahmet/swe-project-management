<?php
// Include database connection and header
require_once '../includes/db.php';

// Start session to access user data
session_start();

// Check if pet_id is provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // If no pet ID is provided, redirect to pets listing page
    header("Location: ../public/pets.php");
    exit();
}

$pet_id = $_GET['id'];

// Fetch pet details
$sql = "SELECT * FROM Pet WHERE pet_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pet_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if pet exists
if ($result->num_rows == 0) {
    // If pet doesn't exist, redirect to pets listing page
    header("Location: ../public/pets.php");
    exit();
}

$pet = $result->fetch_assoc();

// Check if user is logged in
$user_logged_in = false;
$user_id = 0;

if (isset($_SESSION['user_id'])) {
    $user_logged_in = true;
    $user_id = $_SESSION['user_id'];
}

// Include header
include_once '../includes/header.php';
?>

<style>
    .pet-details-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px;
    }
    
    .pet-image-container {
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        height: 500px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }
    
    .pet-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .pet-name {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 16px;
        color: #333;
    }
    
    .pet-info-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        padding: 30px;
        height: 100%;
    }
    
    .info-item {
        margin-bottom: 20px;
    }
    
    .info-label {
        font-size: 0.85rem;
        color: #777;
        margin-bottom: 5px;
    }
    
    .info-value {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
    }
    
    .status-badge {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.9rem;
        margin-top: 10px;
    }
    
    .status-badge.available {
        background-color: #E8F5E9;
        color: #2E7D32;
    }
    
    .status-badge.not-available {
        background-color: #FFEBEE;
        color: #C62828;
    }
    
    .action-button {
        display: block;
        width: 100%;
        padding: 16px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1rem;
        text-align: center;
        margin-bottom: 15px;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    
    .primary-button {
        background: #007AFF;
        color: white;
        border: none;
    }
    
    .primary-button:hover {
        background: #0056b3;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 122, 255, 0.15);
    }
    
    .secondary-button {
        background: white;
        color: #007AFF;
        border: 2px solid #007AFF;
    }
    
    .secondary-button:hover {
        background: rgba(0, 122, 255, 0.05);
        transform: translateY(-2px);
    }
    
    .alert-box {
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 20px;
    }
    
    .alert-info {
        background-color: #E3F2FD;
        border-left: 4px solid #2196F3;
        color: #0D47A1;
    }
    
    .alert-warning {
        background-color: #FFF8E1;
        border-left: 4px solid #FFC107;
        color: #FF6F00;
    }
    
    .back-link {
        display: inline-block;
        color: #777;
        text-decoration: none;
        font-weight: 500;
        margin-top: 20px;
        transition: color 0.3s ease;
    }
    
    .back-link:hover {
        color: #007AFF;
    }
    
    .back-link i {
        margin-right: 8px;
    }
    .pet-details-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px;
    }
    
    .row {
        display: flex;
        flex-wrap: wrap;
        margin: -15px;
    }
    
    .col-lg-6 {
        flex: 0 0 50%;
        max-width: 50%;
        padding: 15px;
    }
    
    .pet-image-container {
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        height: 500px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        position: sticky;
        top: 20px;
    }
    
    .pet-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

</style>

<div class="pet-details-container">
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="pet-image-container">
                <?php if (!empty($pet['photos'])): ?>
                    <img src="../<?php echo htmlspecialchars($pet['photos']); ?>" class="pet-image" alt="<?php echo htmlspecialchars($pet['name']); ?>">
                <?php else: ?>
                    <img src="../assets/images/pet-placeholder.jpg" class="pet-image" alt="No image available">
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="pet-info-card">
                <h1 class="pet-name"><?php echo htmlspecialchars($pet['name']); ?></h1>
                
                <div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">Species</div>
                            <div class="info-value"><?php echo htmlspecialchars($pet['species']); ?></div>
                        </div>
                    </div>
                    
                    <?php if (!empty($pet['breed'])): ?>
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">Breed</div>
                            <div class="info-value"><?php echo htmlspecialchars($pet['breed']); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($pet['age'])): ?>
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">Age</div>
                            <div class="info-value"><?php echo htmlspecialchars($pet['age']); ?> year(s)</div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($pet['gender'])): ?>
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">Gender</div>
                            <div class="info-value"><?php echo htmlspecialchars($pet['gender']); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="status-badge <?php echo $pet['status'] == 'Available' ? 'available' : 'not-available'; ?>">
                        <?php echo htmlspecialchars($pet['status']); ?>
                    </div>
                </div>
                
                <div class="mt-4">
                    <?php if ($pet['status'] == 'Available'): ?>
                        <?php if ($user_logged_in): ?>
                            <a href="../public/adopt.php?pet_id=<?php echo $pet_id; ?>" class="action-button primary-button">
                                Adopt <?php echo htmlspecialchars($pet['name']); ?>
                            </a>
                            <a href="../public/foster.php?pet_id=<?php echo $pet_id; ?>" class="action-button secondary-button">
                                Foster <?php echo htmlspecialchars($pet['name']); ?>
                            </a>
                        <?php else: ?>
                            <div class="alert-box alert-info">
                                <strong>Please log in</strong> to adopt or foster this pet.
                                <a href="../public/login.php" style="color: #0D47A1; text-decoration: underline;">Login now</a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert-box alert-warning">
                            <strong>Not Available</strong> - This pet is currently not available for adoption or fostering.
                        </div>
                    <?php endif; ?>
                    
                    <a href="../public/pets.php" class="back-link">
                        <i class="fas fa-arrow-left"></i> Back to Pet Listings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once '../includes/footer.php';
$conn->close();
?>