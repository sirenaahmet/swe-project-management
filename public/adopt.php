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

// Check if pet_id is provided in the URL
if (!isset($_GET['pet_id']) || empty($_GET['pet_id'])) {
    header("Location: ../public/pets.php");
    exit();
}

$pet_id = $_GET['pet_id'];
$user_id = $_SESSION['user_id'];

// Fetch pet details to display
$sql = "SELECT * FROM Pet WHERE pet_id = ? AND status = 'Available'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pet_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if pet exists and is available
if ($result->num_rows == 0) {
    header("Location: ../public/pets.php");
    exit();
}

$pet = $result->fetch_assoc();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
    
    // Insert adoption application into database
    $sql = "INSERT INTO Adoption (end_user_id, pet_id, notes) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $user_id, $pet_id, $notes);
    
    if ($stmt->execute()) {
        // Set success message and redirect
        $_SESSION['success_message'] = "Your adoption application has been submitted successfully!";
        header("Location: ../public/account.php");
        exit();
    } else {
        $error_message = "There was an error submitting your application. Please try again.";
    }
}

include_once '../includes/header.php';
?>

<style>
    .adoption-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 40px 20px;
    }
    
    .adoption-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }
    
    .card-header {
        background: #007AFF;
        color: white;
        padding: 30px;
        position: relative;
    }
    
    .card-title {
        font-size: 1.8rem;
        font-weight: 700;
        margin: 0;
    }
    
    .card-body {
        padding: 30px;
    }
    
    .pet-preview {
        display: flex;
        align-items: center;
        margin-bottom: 30px;
        background: #f9f9f9;
        border-radius: 12px;
        padding: 20px;
    }
    
    .pet-image-container {
        width: 120px;
        height: 120px;
        border-radius: 12px;
        overflow: hidden;
        margin-right: 20px;
        flex-shrink: 0;
    }
    
    .pet-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .pet-preview-details h3 {
        font-size: 1.4rem;
        font-weight: 600;
        margin-bottom: 10px;
        color: #333;
    }
    
    .pet-preview-details p {
        color: #666;
        margin-bottom: 5px;
    }
    
    .info-box {
        background: #f1f8ff;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 30px;
        border-left: 4px solid #007AFF;
    }
    
    .info-box-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #007AFF;
        margin-bottom: 10px;
    }
    
    .info-box p {
        margin-bottom: 0;
        color: #444;
    }
    
    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }
    
    .form-control {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 12px 15px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #007AFF;
        box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
    }
    
    .form-text {
        color: #777;
        font-size: 0.85rem;
        margin-top: 6px;
    }
    
    .action-buttons {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }
    
    .btn {
        padding: 14px 30px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .btn-primary {
        background: #007AFF;
        border: none;
    }
    
    .btn-primary:hover {
        background: #0056b3;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 122, 255, 0.15);
    }
    
    .btn-outline-secondary {
        background: transparent;
        border: 2px solid #ddd;
        color: #666;
    }
    
    .btn-outline-secondary:hover {
        border-color: #999;
        color: #333;
        background: #f9f9f9;
    }
    
    .alert {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .alert-danger {
        background-color: #FFEBEE;
        color: #C62828;
        border-left: 4px solid #EF5350;
    }
</style>

<div class="adoption-container">
    <div class="adoption-card">
        <div class="card-header">
            <h1 class="card-title">Adopt <?php echo htmlspecialchars($pet['name']); ?></h1>
        </div>
        <div class="card-body">
            <div class="pet-preview">
                <div class="pet-image-container">
                    <?php if (!empty($pet['photos'])): ?>
                        <img src="../<?php echo htmlspecialchars($pet['photos']); ?>" class="pet-image" alt="<?php echo htmlspecialchars($pet['name']); ?>">
                    <?php else: ?>
                        <img src="../assets/images/pet-placeholder.jpg" class="pet-image" alt="No image available">
                    <?php endif; ?>
                </div>
                <div class="pet-preview-details">
                    <h3><?php echo htmlspecialchars($pet['name']); ?></h3>
                    <p><strong>Species:</strong> <?php echo htmlspecialchars($pet['species']); ?></p>
                    <?php if (!empty($pet['breed'])): ?>
                        <p><strong>Breed:</strong> <?php echo htmlspecialchars($pet['breed']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="info-box">
                <div class="info-box-title">Adoption Process</div>
                <p>By submitting this form, you are applying to adopt <?php echo htmlspecialchars($pet['name']); ?>. Our team will review your application and contact you soon. You can view the status of your application on your account page.</p>
            </div>

            <form method="post" action="">
                <div class="mb-4">
                    <label for="notes" class="form-label">Why do you want to adopt this pet?</label>
                    <br>
                    <textarea class="form-control" id="notes" name="notes" rows="5" placeholder="Tell us why you'd be a good match for this pet..."><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                    <div class="form-text">Your response helps us ensure this is the right pet for your home and lifestyle.</div>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary">
                        Submit Adoption Application
                    </button>
                    <a href="../public/pet-details.php?id=<?php echo $pet_id; ?>" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include_once '../includes/footer.php';
$conn->close();
?>