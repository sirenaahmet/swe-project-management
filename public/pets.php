<?php
// Check if session is already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set page title
$pageTitle = "Available Pets";

// Include database connection
require_once '../includes/db.php';

// Initialize filter variables
$species_filter = isset($_GET['species']) ? $_GET['species'] : '';
$gender_filter = isset($_GET['gender']) ? $_GET['gender'] : '';
$age_filter = isset($_GET['age']) ? $_GET['age'] : '';
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Build query based on filters
$query = "SELECT * FROM Pet WHERE status = 'Available'";

// Apply species filter
if (!empty($species_filter)) {
    $query .= " AND species = '" . $conn->real_escape_string($species_filter) . "'";
}

// Apply gender filter
if (!empty($gender_filter)) {
    $query .= " AND gender = '" . $conn->real_escape_string($gender_filter) . "'";
}

// Apply age filter
if (!empty($age_filter)) {
    switch ($age_filter) {
        case 'baby':
            $query .= " AND age < 1";
            break;
        case 'young':
            $query .= " AND age >= 1 AND age < 3";
            break;
        case 'adult':
            $query .= " AND age >= 3 AND age < 8";
            break;
        case 'senior':
            $query .= " AND age >= 8";
            break;
    }
}

// Apply search term
if (!empty($search_term)) {
    $query .= " AND (name LIKE '%" . $conn->real_escape_string($search_term) . "%' OR breed LIKE '%" . $conn->real_escape_string($search_term) . "%')";
}

// Get all unique species for the filter dropdown
$species_query = "SELECT DISTINCT species FROM Pet WHERE status = 'Available' ORDER BY species";
$species_result = $conn->query($species_query);
$species_options = [];
if ($species_result && $species_result->num_rows > 0) {
    while ($row = $species_result->fetch_assoc()) {
        $species_options[] = $row['species'];
    }
}

// Final query with ordering
$query .= " ORDER BY is_featured DESC, created_at DESC";
$result = $conn->query($query);
$pets = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pets[] = $row;
    }
}

// Get user's favorites if logged in
$user_favorites = [];
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $favorites_query = "SELECT pet_id FROM Favorites WHERE user_id = ?";
    $favorites_stmt = $conn->prepare($favorites_query);
    $favorites_stmt->bind_param("i", $user_id);
    $favorites_stmt->execute();
    $favorites_result = $favorites_stmt->get_result();
    
    if ($favorites_result && $favorites_result->num_rows > 0) {
        while ($row = $favorites_result->fetch_assoc()) {
            $user_favorites[] = $row['pet_id'];
        }
    }
}

// Handle adding or removing favorites
$favorite_message = '';
if (isset($_POST['add_favorite']) || isset($_POST['remove_favorite'])) {
    if (!isset($_SESSION['user_id'])) {
        // Redirect to login with return URL
        header("Location: login.php?redirect=pets.php");
        exit;
    } else {
        $pet_id = isset($_POST['pet_id']) ? (int)$_POST['pet_id'] : 0;
        $user_id = $_SESSION['user_id'];
        
        if (isset($_POST['add_favorite'])) {
            // Check if this pet is already in favorites
            $check_query = "SELECT * FROM Favorites WHERE user_id = ? AND pet_id = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param("ii", $user_id, $pet_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows == 0) {
                // Add to favorites
                $insert_query = "INSERT INTO Favorites (user_id, pet_id) VALUES (?, ?)";
                $insert_stmt = $conn->prepare($insert_query);
                $insert_stmt->bind_param("ii", $user_id, $pet_id);
                
                if ($insert_stmt->execute()) {
                    $favorite_message = "Pet added to your favorites!";
                    // Add to the local array for immediate UI update
                    $user_favorites[] = $pet_id;
                } else {
                    $favorite_message = "Error adding pet to favorites.";
                }
            } else {
                $favorite_message = "This pet is already in your favorites.";
            }
        } else if (isset($_POST['remove_favorite'])) {
            // Remove from favorites
            $delete_query = "DELETE FROM Favorites WHERE user_id = ? AND pet_id = ?";
            $delete_stmt = $conn->prepare($delete_query);
            $delete_stmt->bind_param("ii", $user_id, $pet_id);
            
            if ($delete_stmt->execute()) {
                $favorite_message = "Pet removed from your favorites.";
                // Remove from the local array for immediate UI update
                $key = array_search($pet_id, $user_favorites);
                if ($key !== false) {
                    unset($user_favorites[$key]);
                }
            } else {
                $favorite_message = "Error removing pet from favorites.";
            }
        }
    }
}

// Include the header
include_once '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Find Your Perfect Companion</h1>
        <p>Browse our available pets and find your new best friend</p>
    </div>
</div>

<section class="pets-section">
    <div class="container">
        <?php if (!empty($favorite_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $favorite_message; ?>
            </div>
        <?php endif; ?>

        <div class="filter-container">
            <form action="pets.php" method="get" class="filter-form">
                <div class="search-box">
                    <input type="text" name="search" placeholder="Search by name or breed..." value="<?php echo htmlspecialchars($search_term); ?>">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                
                <div class="filters">
                    <div class="filter-group">
                        <label for="species">Species</label>
                        <select name="species" id="species">
                            <option value="">All Species</option>
                            <?php foreach ($species_options as $option): ?>
                                <option value="<?php echo htmlspecialchars($option); ?>" <?php if ($species_filter === $option) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($option); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="gender">Gender</label>
                        <select name="gender" id="gender">
                            <option value="">All Genders</option>
                            <option value="Male" <?php if ($gender_filter === 'Male') echo 'selected'; ?>>Male</option>
                            <option value="Female" <?php if ($gender_filter === 'Female') echo 'selected'; ?>>Female</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="age">Age</label>
                        <select name="age" id="age">
                            <option value="">All Ages</option>
                            <option value="baby" <?php if ($age_filter === 'baby') echo 'selected'; ?>>Baby (< 1 year)</option>
                            <option value="young" <?php if ($age_filter === 'young') echo 'selected'; ?>>Young (1-3 years)</option>
                            <option value="adult" <?php if ($age_filter === 'adult') echo 'selected'; ?>>Adult (3-8 years)</option>
                            <option value="senior" <?php if ($age_filter === 'senior') echo 'selected'; ?>>Senior (8+ years)</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-filter">Apply Filters</button>
                    <a href="pets.php" class="btn btn-reset">Reset</a>
                </div>
            </form>
        </div>
        
        <div class="results-info">
            <p>Found <strong><?php echo count($pets); ?></strong> available pets</p>
        </div>
        
        <div class="pets-grid">
            <?php if (empty($pets)): ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>No pets found</h3>
                    <p>Try adjusting your search filters or check back later for new arrivals.</p>
                </div>
            <?php else: ?>
                <?php foreach ($pets as $pet): ?>
                    <?php $is_favorite = in_array($pet['pet_id'], $user_favorites); ?>
                    <div class="pet-card <?php if ($pet['is_featured']) echo 'featured'; ?>">
                        <?php if ($pet['is_featured']): ?>
                            <div class="featured-badge">
                                <i class="fas fa-star"></i> Featured
                            </div>
                        <?php endif; ?>
                        
                        <div class="pet-image">
                            <?php if (!empty($pet['photos'])): ?>
                                <img src="../<?php echo htmlspecialchars($pet['photos']); ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>">
                            <?php else: ?>
                                <img src="../assets/images/pet-placeholder.jpg" alt="No image available">
                            <?php endif; ?>
                            
                            <div class="pet-actions">
                                <form method="post" action="pets.php" class="favorite-form">
                                    <input type="hidden" name="pet_id" value="<?php echo $pet['pet_id']; ?>">
                                    <?php if ($is_favorite): ?>
                                        <button type="submit" name="remove_favorite" class="btn-favorite active" title="Remove from Favorites">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" name="add_favorite" class="btn-favorite" title="Add to Favorites">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                        
                        <div class="pet-details">
                            <h3><?php echo htmlspecialchars($pet['name']); ?></h3>
                            <div class="pet-info">
                                <p>
                                    <span class="info-label">Species:</span>
                                    <?php echo htmlspecialchars($pet['species']); ?>
                                </p>
                                <?php if (!empty($pet['breed'])): ?>
                                    <p>
                                        <span class="info-label">Breed:</span>
                                        <?php echo htmlspecialchars($pet['breed']); ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($pet['age'])): ?>
                                    <p>
                                        <span class="info-label">Age:</span>
                                        <?php echo htmlspecialchars($pet['age']); ?> year<?php if ($pet['age'] != 1) echo 's'; ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($pet['gender'])): ?>
                                    <p>
                                        <span class="info-label">Gender:</span>
                                        <?php echo htmlspecialchars($pet['gender']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="pet-buttons">
                                <a href="pet-details.php?id=<?php echo $pet['pet_id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Adoption/Login Modal -->
<div id="login-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Sign in Required</h2>
        <p>To add pets to favorites, please sign in or create an account.</p>
        <div class="modal-buttons">
            <a href="login.php?redirect=pets.php" class="btn btn-primary">Sign In</a>
            <a href="register.php?redirect=pets.php" class="btn btn-secondary">Create Account</a>
        </div>
    </div>
</div>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
/* Pets Page Specific Styles */
.filter-container {
    background-color: white;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    padding: 20px;
    margin-bottom: 30px;
}

.filter-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.search-box {
    display: flex;
    width: 100%;
}

.search-box input {
    flex: 1;
    padding: 12px 16px;
    border: 1px solid #d2d2d7;
    border-radius: 8px 0 0 8px;
    font-family: 'Inter', sans-serif;
    font-size: 16px;
}

.search-box input:focus {
    outline: none;
    border-color: #81253f;
}

.search-btn {
    background-color: #81253f;
    color: white;
    border: none;
    border-radius: 0 8px 8px 0;
    padding: 0 20px;
    cursor: pointer;
}

.filters {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-group label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    font-weight: 500;
}

.filter-group select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d2d2d7;
    border-radius: 8px;
    font-family: 'Inter', sans-serif;
    background-color: white;
}

.btn-filter {
    background-color: #81253f;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    cursor: pointer;
}

.btn-reset {
    background-color: transparent;
    color: #81253f;
    border: 1px solid #81253f;
    border-radius: 8px;
    padding: 10px 20px;
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    text-decoration: none;
    display: inline-block;
}

.results-info {
    margin-bottom: 20px;
    font-size: 16px;
    color: #666;
}

.pets-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.pet-card {
    background-color: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
}

.pet-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.pet-card.featured {
    border: 2px solid #fecc6b;
}

.featured-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    background-color: #fecc6b;
    color: #333;
    padding: 5px 10px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    z-index: 2;
}

.featured-badge i {
    margin-right: 5px;
}

.pet-image {
    position: relative;
    height: 220px;
    overflow: hidden;
}

.pet-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.pet-card:hover .pet-image img {
    transform: scale(1.05);
}

.pet-actions {
    position: absolute;
    top: 12px;
    left: 12px;
    z-index: 2;
}

.btn-favorite {
    background-color: white;
    color: #81253f;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.btn-favorite:hover {
    background-color: #81253f;
    color: white;
}

.btn-favorite.active {
    background-color: #81253f;
    color: white;
}

.pet-details {
    padding: 20px;
}

.pet-details h3 {
    font-size: 20px;
    margin-bottom: 12px;
    color: #333;
}

.pet-info {
    margin-bottom: 20px;
}

.pet-info p {
    margin-bottom: 8px;
    font-size: 14px;
    color: #666;
}

.info-label {
    font-weight: 600;
    color: #333;
    display: inline-block;
    width: 70px;
}

.pet-buttons {
    display: flex;
    gap: 10px;
}

.pet-buttons .btn {
    flex: 1;
    text-align: center;
    padding: 10px 12px;
    border-radius: 8px;
    font-weight: 500;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-primary {
    background-color: #81253f;
    color: white;
}

.btn-primary:hover {
    background-color: #6e1f36;
}

.btn-secondary {
    background-color: transparent;
    border: 1px solid #81253f;
    color: #81253f;
}

.btn-secondary:hover {
    background-color: rgba(129, 37, 63, 0.1);
}

.no-results {
    grid-column: 1 / -1;
    text-align: center;
    padding: 40px 20px;
    background-color: white;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.no-results i {
    font-size: 40px;
    color: #ccc;
    margin-bottom: 20px;
}

.no-results h3 {
    font-size: 20px;
    margin-bottom: 10px;
    color: #333;
}

.no-results p {
    color: #666;
    max-width: 400px;
    margin: 0 auto;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
}

.alert i {
    margin-right: 10px;
    font-size: 18px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    align-items: center;
    justify-content: center;
}

.modal.show {
    display: flex;
}

.modal-content {
    background-color: white;
    padding: 30px;
    border-radius: 12px;
    max-width: 500px;
    width: 90%;
    text-align: center;
    position: relative;
}

.close {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 24px;
    cursor: pointer;
    color: #aaa;
}

.close:hover {
    color: #333;
}

.modal-content h2 {
    margin-bottom: 15px;
    color: #333;
}

.modal-content p {
    margin-bottom: 25px;
    color: #666;
}

.modal-buttons {
    display: flex;
    gap: 15px;
}

.modal-buttons .btn {
    flex: 1;
    padding: 12px;
    text-align: center;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .filters {
        flex-direction: column;
        gap: 15px;
    }
    
    .filter-group {
        width: 100%;
    }
    
    .pets-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }
}

@media (max-width: 576px) {
    .pets-grid {
        grid-template-columns: 1fr;
    }
    
    .pet-buttons {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle modal close
    const closeBtn = document.querySelector('.modal .close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            document.getElementById('login-modal').classList.remove('show');
        });
    }
    
    // Close modal on outside click
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('login-modal');
        if (event.target === modal) {
            modal.classList.remove('show');
        }
    });
    
    // Auto-submit form when filters change
    const filterSelects = document.querySelectorAll('.filter-form select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            document.querySelector('.filter-form').submit();
        });
    });
    
    // Check if user is logged in before submitting favorite form
    const favoriteButtons = document.querySelectorAll('.btn-favorite');
    favoriteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            <?php if (!isset($_SESSION['user_id'])): ?>
                e.preventDefault();
                document.getElementById('login-modal').classList.add('show');
                return false;
            <?php endif; ?>
        });
    });
});
</script>

<?php
// Include the footer
include_once '../includes/footer.php';
?>