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

// Define variables and initialize with empty values
$name = $species = $breed = $age = $gender = $status = "";
$name_err = $species_err = $status_err = $photo_err = "";
$success_message = "";
$is_featured = false; // Initialize is_featured
$photos = null;
$videos = null;
$pet = null;

// Check if pet_id is provided
if (isset($_GET['pet_id']) && is_numeric($_GET['pet_id'])) {
    $pet_id = $_GET['pet_id'];
    
    // Delete pet if delete action is requested
    if (isset($_GET['action']) && $_GET['action'] === 'delete') {
        // Get pet image path before deletion
        $sql = "SELECT photos FROM Pet WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $pet_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pet_image = $result->fetch_assoc()['photos'];
        
        // Delete the pet record
        $sql = "DELETE FROM Pet WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $pet_id);
        
        if ($stmt->execute()) {
            // Delete the image file if it exists
            $root_dir = $_SERVER['DOCUMENT_ROOT'] . '/swe-project-management/';
            if ($pet_image && file_exists($root_dir . $pet_image)) {
                unlink($root_dir . $pet_image);
            }
            // Redirect to pet list after deletion
            header("Location: pets.php?success=Pet successfully deleted.");
            exit();
        } else {
            $error_message = "Error deleting pet.";
        }
    }
    
    // Fetch pet details for editing
    $sql = "SELECT * FROM Pet WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pet_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $pet = $result->fetch_assoc();
        // Set current values
        $name = $pet['name'];
        $species = $pet['species'];
        $breed = $pet['breed'];
        $age = $pet['age'];
        $gender = $pet['gender'];
        $status = $pet['status'];
        $is_featured = $pet['is_featured'] == 1;
        $photos = $pet['photos'];
        $videos = $pet['videos'];
    } else {
        header("Location: pets.php");
        exit();
    }
} else {
    header("Location: pets.php");
    exit();
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Validate name
    if(empty(trim($_POST["name"]))){
        $name_err = "Please enter the pet's name.";
    } else{
        $name = trim($_POST["name"]);
    }
    
    // Validate species
    if(empty(trim($_POST["species"]))){
        $species_err = "Please enter the pet's species.";
    } else{
        $species = trim($_POST["species"]);
    }
    
    // Validate status
    if(empty(trim($_POST["status"]))){
        $status_err = "Please select the pet's status.";
    } else{
        $status = trim($_POST["status"]);
    }
    
    // Other fields (not required but we'll capture them)
    $breed = !empty($_POST["breed"]) ? trim($_POST["breed"]) : null;
    $age = !empty($_POST["age"]) ? intval($_POST["age"]) : null;
    $gender = !empty($_POST["gender"]) ? trim($_POST["gender"]) : null;
    $is_featured = isset($_POST["is_featured"]) ? 1 : 0;
    
    $videos = !empty($_POST["videos"]) ? trim($_POST["videos"]) : null;
    
    // Handle photo upload if a new image is provided
    if(isset($_FILES["pet_photo"]) && $_FILES["pet_photo"]["error"] == 0) {
        $allowed = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];
        $filename = $_FILES["pet_photo"]["name"];
        $filetype = $_FILES["pet_photo"]["type"];
        $filesize = $_FILES["pet_photo"]["size"];
        
        // Validate file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(!array_key_exists($ext, $allowed)) {
            $photo_err = "Please select a valid file format (JPG, JPEG, PNG, GIF).";
        }
        
        // Validate file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if($filesize > $maxsize) {
            $photo_err = "File size is larger than the allowed limit (5MB).";
        }
        
        // Validate MIME type
        if(in_array($filetype, $allowed) && empty($photo_err)) {
            // Create upload directory with absolute path
            $root_dir = $_SERVER['DOCUMENT_ROOT'] . '/swe-project-management/';
            $upload_dir = $root_dir . 'assets/uploads/pets/';
            
            // Make sure parent directories exist
            if(!is_dir($root_dir . 'assets/')) {
                mkdir($root_dir . 'assets/', 0777, true);
            }
            
            if(!is_dir($root_dir . 'assets/uploads/')) {
                mkdir($root_dir . 'assets/uploads/', 0777, true);
            }
            
            if(!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Create a unique file name to prevent overwriting
            $new_filename = uniqid('pet_') . '.' . $ext;
            $upload_path = $upload_dir . $new_filename;
            
            // Save the file
            if(move_uploaded_file($_FILES["pet_photo"]["tmp_name"], $upload_path)) {
                // Delete old image if it exists
                if ($photos && file_exists($root_dir . $photos)) {
                    unlink($root_dir . $photos);
                }
                $photos = 'assets/uploads/pets/' . $new_filename; // Store the relative path in database
            } else {
                $photo_err = "Error uploading the file.";
            }
        }
    }
    
    // Check input errors before updating database
    if(empty($name_err) && empty($species_err) && empty($status_err) && empty($photo_err)){
        
        // Prepare an update statement
        $sql = "UPDATE Pet SET name = ?, species = ?, breed = ?, age = ?, gender = ?, status = ?, is_featured = ?, photos = ?, videos = ? WHERE id = ?";
         
        if($stmt = $conn->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sssississi", $name, $species, $breed, $age, $gender, $status, $is_featured, $photos, $videos, $pet_id);
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Pet updated successfully
                $success_message = "Pet updated successfully!";
                
                // Refresh pet data
                $sql = "SELECT * FROM Pet WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $pet_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $pet = $result->fetch_assoc();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pet - Paws & Hearts Admin</title>
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
        
        .back-btn {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #81253f;
        }
        
        .back-btn i {
            margin-right: 8px;
        }
        
        .back-btn:hover {
            text-decoration: underline;
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
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.full-width {
            grid-column: span 2;
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
            margin-top: 20px;
        }
        
        input[type="checkbox"] {
            margin-right: 8px;
        }
        
        .checkbox-label {
            font-size: 14px;
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
            display: none;
        }
        
        .file-preview {
            margin-top: 12px;
            display: <?php echo !empty($photos) ? 'block' : 'none'; ?>;
        }
        
        .file-preview img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            border: 1px solid #d2d2d7;
        }
        
        .file-info {
            display: flex;
            align-items: center;
            margin-top: 8px;
            font-size: 13px;
            color: #666;
        }
        
        .file-info i {
            margin-right: 5px;
            color: #81253f;
        }
        
        /* Buttons */
        .form-buttons {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }
        
        .btn {
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
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
        
        .btn-danger {
            background-color: #d9534f;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c9302c;
        }
        
        /* Error & Success Messages */
        .error-message {
            color: #d9534f;
            font-size: 13px;
            margin-top: 5px;
        }
        
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
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-group.full-width {
                grid-column: span 1;
            }
            
            .form-buttons {
                flex-direction: column;
                gap: 15px;
            }
            
            .form-buttons > div {
                width: 100%;
                display: flex;
                justify-content: space-between;
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
                <li><a href="pets.php" class="active"><i class="fas fa-paw"></i> Manage Pets</a></li>
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
            <div class="page-header">
                <h2>Edit Pet</h2>
                <a href="pets.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Pets
                </a>
            </div>
            
            <?php if(!empty($success_message)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if($pet): ?>
                <div class="form-container">
                    <h3 class="form-title">Pet Information</h3>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?pet_id=" . $pet_id); ?>" method="post" enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name">Pet Name *</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                                <?php if(!empty($name_err)): ?>
                                    <span class="error-message"><?php echo $name_err; ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="species">Species *</label>
                                <input type="text" id="species" name="species" value="<?php echo htmlspecialchars($species); ?>" placeholder="e.g. Dog, Cat, Rabbit" required>
                                <?php if(!empty($species_err)): ?>
                                    <span class="error-message"><?php echo $species_err; ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="breed">Breed</label>
                                <input type="text" id="breed" name="breed" value="<?php echo htmlspecialchars($breed); ?>" placeholder="e.g. Golden Retriever, Siamese">
                            </div>
                            
                            <div class="form-group">
                                <label for="age">Age (in years)</label>
                                <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($age); ?>" min="0" max="30" step="0.5">
                            </div>
                            
                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select id="gender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php if($gender == "Male") echo "selected"; ?>>Male</option>
                                    <option value="Female" <?php if($gender == "Female") echo "selected"; ?>>Female</option>
                                    <option value="Unknown" <?php if($gender == "Unknown") echo "selected"; ?>>Unknown</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status *</label>
                                <select id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="Available" <?php if($status == "Available") echo "selected"; ?>>Available</option>
                                    <option value="Fostered" <?php if($status == "Fostered") echo "selected"; ?>>Fostered</option>
                                    <option value="Adopted" <?php if($status == "Adopted") echo "selected"; ?>>Adopted</option>
                                    <option value="Not Available" <?php if($status == "Not Available") echo "selected"; ?>>Not Available</option>
                                </select>
                                <?php if(!empty($status_err)): ?>
                                    <span class="error-message"><?php echo $status_err; ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="pet_photo">Pet Photo</label>
                                <label for="pet_photo" class="custom-file-upload">
                                    <i class="fas fa-upload"></i> Choose Photo
                                </label>
                                <input type="file" id="pet_photo" name="pet_photo" accept="image/*">
                                <?php if(!empty($photo_err)): ?>
                                    <span class="error-message"><?php echo $photo_err; ?></span>
                                <?php endif; ?>
                                <div id="file-preview" class="file-preview">
                                    <?php if(!empty($photos)): ?>
                                        <img id="preview-image" src="../<?php echo htmlspecialchars($photos); ?>" alt="<?php echo htmlspecialchars($name); ?>">
                                        <div class="file-info">
                                            <i class="fas fa-file-image"></i>
                                            <span id="file-name">Current photo</span>
                                        </div>
                                    <?php else: ?>
                                        <img id="preview-image" src="#" alt="Preview">
                                        <div class="file-info">
                                            <i class="fas fa-file-image"></i>
                                            <span id="file-name"></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="videos">Video URL (optional)</label>
                                <input type="url" id="videos" name="videos" value="<?php echo htmlspecialchars($videos); ?>" placeholder="e.g. YouTube or Vimeo URL">
                            </div>
                            
                            <div class="form-group full-width">
                                <div class="form-check">
                                    <input type="checkbox" id="is_featured" name="is_featured" <?php if($is_featured) echo "checked"; ?>>
                                    <label for="is_featured" class="checkbox-label">Feature this pet on the homepage</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-buttons">
                            <div>
                                <a href="editpet.php?pet_id=<?php echo $pet_id; ?>&action=delete" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this pet? This action cannot be undone.');">Delete Pet</a>
                            </div>
                            <div>
                                <a href="pets.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Pet</button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="form-container">
                    <div style="color: #d9534f; padding: 20px; text-align: center;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 24px; margin-bottom: 10px;"></i>
                        <p>Pet not found. The requested pet may have been deleted or does not exist.</p>
                        <a href="pets.php" class="btn btn-primary" style="margin-top: 15px;">Go back to pet management</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Preview the selected image
        document.getElementById('pet_photo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    document.getElementById('preview-image').src = e.target.result;
                    document.getElementById('file-name').textContent = file.name;
                    document.getElementById('file-preview').style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>

<?php
// Close connection
$conn->close();
?>