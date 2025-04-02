<?php
// Set the page title
$pageTitle = "Home";

// Include the header
include_once '../includes/header.php';

// Fetch featured pets from database (example)
// $featured_pets = $db->query("SELECT * FROM Pet WHERE is_featured = 1 AND status = 'Available' LIMIT 6")->fetchAll();
// For now, we'll use dummy data
$featured_pets = [
    ['pet_id' => 1, 'name' => 'Max', 'species' => 'Dog', 'breed' => 'Golden Retriever', 'age' => 3, 'photos' => 'dog1.jpg'],
    ['pet_id' => 2, 'name' => 'Luna', 'species' => 'Cat', 'breed' => 'Siamese', 'age' => 2, 'photos' => 'cat1.jpg'],
    ['pet_id' => 3, 'name' => 'Buddy', 'species' => 'Dog', 'breed' => 'Labrador', 'age' => 1, 'photos' => 'dog2.jpg'],
];
?>

<div class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1>Find Your Forever Friend</h1>
            <p>Adopt a pet and give them a loving home. Our shelter has dogs, cats, and other animals waiting for their forever families.</p>
            <div class="hero-buttons">
                <a href="pets.php" class="btn btn-primary">Browse Pets</a>
                <a href="about.php" class="btn btn-secondary">Learn More</a>
            </div>
        </div>
    </div>
</div>

<section class="featured-pets">
    <div class="container">
        <h2>Featured Pets</h2>
        <p class="section-description">Meet some of our amazing pets looking for a loving home</p>
        
        <div class="pet-cards">
            <?php foreach ($featured_pets as $pet): ?>
                <div class="pet-card">
                    <div class="pet-image">
                        <img src="../assets/images/pets/<?php echo $pet['photos']; ?>" alt="<?php echo $pet['name']; ?>">
                    </div>
                    <div class="pet-details">
                        <h3><?php echo $pet['name']; ?></h3>
                        <p><?php echo $pet['breed']; ?>, <?php echo $pet['age']; ?> years old</p>
                        <a href="pet-details.php?id=<?php echo $pet['pet_id']; ?>" class="btn btn-outline-secondary">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="view-all">
            <a href="pets.php" class="btn btn-primary">View All Pets</a>
        </div>
    </div>
</section>

<section class="adoption-process">
    <div class="container">
        <h2>How to Adopt</h2>
        <p class="section-description">Our adoption process is designed to ensure the best match between pets and their new families</p>
        
        <div class="process-steps">
            <div class="step">
                <div class="step-icon">1</div>
                <h3>Browse Available Pets</h3>
                <p>Look through our listings of available pets to find your perfect match.</p>
            </div>
            
            <div class="step">
                <div class="step-icon">2</div>
                <h3>Submit Application</h3>
                <p>Fill out our adoption application form with your information.</p>
            </div>
            
            <div class="step">
                <div class="step-icon">3</div>
                <h3>Meet Your Pet</h3>
                <p>Visit our shelter to meet your potential new family member.</p>
            </div>
            
            <div class="step">
                <div class="step-icon">4</div>
                <h3>Take Home</h3>
                <p>Once approved, complete the adoption and welcome your pet home!</p>
            </div>
        </div>
    </div>
</section>

<section class="success-stories">
    <div class="container">
        <h2>Success Stories</h2>
        <p class="section-description">Heartwarming stories from families who have adopted from us</p>
        
        <div class="story-card">
            <div class="story-image">
                <img src="../assets/images/success-story1.jpg" alt="Success Story">
            </div>
            <div class="story-content">
                <h3>"Max has changed our lives"</h3>
                <p class="testimonial">"Adopting Max was the best decision we ever made. He has brought so much joy and love into our home. The adoption process was smooth, and the staff was incredibly helpful. We're forever grateful!"</p>
                <p class="author">- The Johnson Family</p>
            </div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container">
        <h2>Ready to Find Your New Best Friend?</h2>
        <p>Our shelter has many loving animals waiting for their forever homes.</p>
        <div class="cta-buttons">
            <a href="pets.php" class="btn btn-primary">Browse Pets</a>
            <a href="contact.php" class="btn btn-outline-light">Contact Us</a>
        </div>
    </div>
</section>

<?php
// Include the footer
include_once '../includes/footer.php';
?>