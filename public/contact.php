<?php
// Set the page title
$pageTitle = "Contact Us";

// Include the header
include_once '../includes/header.php';

// Process form submission
$formSubmitted = false;
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Simple validation
    if (empty($_POST['name'])) {
        $errors[] = "Please enter your name";
    }
    
    if (empty($_POST['email'])) {
        $errors[] = "Please enter your email";
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    
    if (empty($_POST['message'])) {
        $errors[] = "Please enter a message";
    }
    
    // If no errors, process the form
    if (empty($errors)) {
        // In a real application, you would send an email or save to database here
        // For now, we'll just set a success flag
        $formSubmitted = true;
    }
}
?>

<div class="page-header">
    <div class="container">
        <h1>Contact Paws & Hearts</h1>
        <p>We're here to help you with your pet adoption journey</p>
    </div>
</div>

<section class="contact-info">
    <div class="container">
        <div class="contact-grid">
            <div class="contact-details">
                <h2>Get in Touch</h2>
                <p>Have questions about adopting a pet? Interested in volunteering or fostering? Want to make a donation? We'd love to hear from you! Reach out using any of the methods below.</p>
                
                <div class="contact-methods">
                    <div class="contact-method">
                        <div class="method-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2C8.13 2 5 5.13 5 9C5 14.25 12 22 12 22C12 22 19 14.25 19 9C19 5.13 15.87 2 12 2ZM12 11.5C10.62 11.5 9.5 10.38 9.5 9C9.5 7.62 10.62 6.5 12 6.5C13.38 6.5 14.5 7.62 14.5 9C14.5 10.38 13.38 11.5 12 11.5Z" fill="#81253F"/>
                            </svg>
                        </div>
                        <div class="method-details">
                            <h3>Visit Our Shelter</h3>
                            <p>Epoka Campus<br>Tirana,Albania</p>
                        </div>
                    </div>
                    
                    <div class="contact-method">
                        <div class="method-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 4H4C2.9 4 2.01 4.9 2.01 6L2 18C2 19.1 2.9 20 4 20H20C21.1 20 22 19.1 22 18V6C22 4.9 21.1 4 20 4ZM20 8L12 13L4 8V6L12 11L20 6V8Z" fill="#81253F"/>
                            </svg>
                        </div>
                        <div class="method-details">
                            <h3>Email Us</h3>
                            <p>info@pawsandhearts.com</p>
                        </div>
                    </div>
                    
                    <div class="contact-method">
                        <div class="method-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20.01 15.38C18.78 15.38 17.59 15.18 16.48 14.82C16.13 14.7 15.74 14.79 15.47 15.06L13.9 17.03C11.07 15.68 8.42 13.13 7.01 10.2L8.96 8.54C9.23 8.26 9.31 7.87 9.2 7.52C8.83 6.41 8.64 5.22 8.64 3.99C8.64 3.45 8.19 3 7.65 3H4.19C3.65 3 3 3.24 3 3.99C3 13.28 10.73 21 20.01 21C20.72 21 21 20.37 21 19.82V16.37C21 15.83 20.55 15.38 20.01 15.38Z" fill="#81253F"/>
                            </svg>
                        </div>
                        <div class="method-details">
                            <h3>Call Us</h3>
                            <p>+355 68 207 8217</p>
                        </div>
                    </div>
                    
                    <div class="contact-method">
                        <div class="method-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M11.99 2C6.47 2 2 6.48 2 12C2 17.52 6.47 22 11.99 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 11.99 2ZM12 20C7.58 20 4 16.42 4 12C4 7.58 7.58 4 12 4C16.42 4 20 7.58 20 12C20 16.42 16.42 20 12 20ZM12.5 7H11V13L16.25 16.15L17 14.92L12.5 12.25V7Z" fill="#81253F"/>
                            </svg>
                        </div>
                        <div class="method-details">
                            <h3>Shelter Hours</h3>
                            <p>Monday-Friday: 9am-6pm<br>Saturday: 10am-4pm<br>Sunday: 12pm-4pm</p>
                        </div>
                    </div>
                </div>
                
                <div class="social-connect">
                    <h3>Follow Us</h3>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M22 12C22 6.48 17.52 2 12 2C6.48 2 2 6.48 2 12C2 16.84 5.44 20.87 10 21.8V15H8V12H10V9.5C10 7.57 11.57 6 13.5 6H16V9H14C13.45 9 13 9.45 13 10V12H16V15H13V21.95C18.05 21.45 22 17.19 22 12Z" fill="#81253F"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="Instagram">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2C14.717 2 15.056 2.01 16.122 2.06C17.187 2.11 17.912 2.277 18.55 2.525C19.21 2.779 19.766 3.123 20.322 3.678C20.8305 4.1779 21.224 4.78259 21.475 5.45C21.722 6.087 21.89 6.813 21.94 7.878C21.987 8.944 22 9.283 22 12C22 14.717 21.99 15.056 21.94 16.122C21.89 17.187 21.722 17.912 21.475 18.55C21.2247 19.2178 20.8311 19.8226 20.322 20.322C19.822 20.8303 19.2173 21.2238 18.55 21.475C17.913 21.722 17.187 21.89 16.122 21.94C15.056 21.987 14.717 22 12 22C9.283 22 8.944 21.99 7.878 21.94C6.813 21.89 6.088 21.722 5.45 21.475C4.78233 21.2245 4.17753 20.8309 3.678 20.322C3.16941 19.8222 2.77593 19.2175 2.525 18.55C2.277 17.913 2.11 17.187 2.06 16.122C2.013 15.056 2 14.717 2 12C2 9.283 2.01 8.944 2.06 7.878C2.11 6.812 2.277 6.088 2.525 5.45C2.77524 4.78218 3.1688 4.17732 3.678 3.678C4.17767 3.16923 4.78243 2.77573 5.45 2.525C6.088 2.277 6.812 2.11 7.878 2.06C8.944 2.013 9.283 2 12 2ZM12 7C10.6739 7 9.40215 7.52678 8.46447 8.46447C7.52678 9.40215 7 10.6739 7 12C7 13.3261 7.52678 14.5979 8.46447 15.5355C9.40215 16.4732 10.6739 17 12 17C13.3261 17 14.5979 16.4732 15.5355 15.5355C16.4732 14.5979 17 13.3261 17 12C17 10.6739 16.4732 9.40215 15.5355 8.46447C14.5979 7.52678 13.3261 7 12 7ZM18.5 6.75C18.5 6.41848 18.3683 6.10054 18.1339 5.86612C17.8995 5.6317 17.5815 5.5 17.25 5.5C16.9185 5.5 16.6005 5.6317 16.3661 5.86612C16.1317 6.10054 16 6.41848 16 6.75C16 7.08152 16.1317 7.39946 16.3661 7.63388C16.6005 7.8683 16.9185 8 17.25 8C17.5815 8 17.8995 7.8683 18.1339 7.63388C18.3683 7.39946 18.5 7.08152 18.5 6.75ZM12 9C12.7956 9 13.5587 9.31607 14.1213 9.87868C14.6839 10.4413 15 11.2044 15 12C15 12.7956 14.6839 13.5587 14.1213 14.1213C13.5587 14.6839 12.7956 15 12 15C11.2044 15 10.4413 14.6839 9.87868 14.1213C9.31607 13.5587 9 12.7956 9 12C9 11.2044 9.31607 10.4413 9.87868 9.87868C10.4413 9.31607 11.2044 9 12 9Z" fill="#81253F"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="Twitter">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M22.46 6C21.69 6.35 20.86 6.58 20 6.69C20.88 6.16 21.56 5.32 21.88 4.31C21.05 4.81 20.13 5.16 19.16 5.36C18.37 4.5 17.26 4 16 4C13.65 4 11.73 5.92 11.73 8.29C11.73 8.63 11.77 8.96 11.84 9.27C8.28 9.09 5.11 7.38 3 4.79C2.63 5.42 2.42 6.16 2.42 6.94C2.42 8.43 3.17 9.75 4.33 10.5C3.62 10.5 2.96 10.3 2.38 10V10.03C2.38 12.11 3.86 13.85 5.82 14.24C5.19 14.41 4.53 14.44 3.89 14.31C4.16 15.14 4.69 15.86 5.41 16.38C6.13 16.89 7 17.17 7.89 17.17C6.37 18.34 4.47 18.99 2.5 19C2.22 19 1.94 18.98 1.67 18.96C3.56 20.15 5.77 20.79 8 20.79C16 20.79 20.33 14.32 20.33 8.79C20.33 8.6 20.33 8.42 20.32 8.24C21.16 7.63 21.88 6.87 22.46 6Z" fill="#81253F"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="YouTube">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M21.58 7.17C21.48 6.77 21.28 6.41 21 6.12C20.72 5.83 20.37 5.62 19.98 5.51C18.25 5.01 12 5.01 12 5.01C12 5.01 5.75 5.01 4.02 5.51C3.63 5.62 3.28 5.83 3 6.12C2.72 6.41 2.52 6.77 2.42 7.17C2.03 8.96 2.03 12.5 2.03 12.5C2.03 12.5 2.03 16.04 2.42 17.83C2.52 18.23 2.72 18.59 3 18.88C3.28 19.17 3.63 19.38 4.02 19.49C5.75 19.99 12 19.99 12 19.99C12 19.99 18.25 19.99 19.98 19.49C20.37 19.38 20.72 19.17 21 18.88C21.28 18.59 21.48 18.23 21.58 17.83C21.97 16.04 21.97 12.5 21.97 12.5C21.97 12.5 21.97 8.96 21.58 7.17ZM9.98 15.71V9.29L15.01 12.5L9.98 15.71Z" fill="#81253F"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="contact-form-container">
                <h2>Send Us a Message</h2>
                
                <?php if ($formSubmitted): ?>
                    <div class="form-success">
                        <h3>Thank You!</h3>
                        <p>Your message has been sent successfully. We'll get back to you as soon as possible.</p>
                    </div>
                <?php else: ?>
                    <?php if (!empty($errors)): ?>
                        <div class="form-errors">
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form id="contactForm" class="contact-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-group">
                            <label for="name">Your Name</label>
                            <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Your Email</label>
                            <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number (optional)</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">I'm Interested In</label>
                            <select id="subject" name="subject">
                                <option value="adoption" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'adoption') ? 'selected' : ''; ?>>Adopting a Pet</option>
                                <option value="foster" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'foster') ? 'selected' : ''; ?>>Fostering a Pet</option>
                                <option value="volunteer" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'volunteer') ? 'selected' : ''; ?>>Volunteering</option>
                                <option value="donation" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'donation') ? 'selected' : ''; ?>>Making a Donation</option>
                                <option value="general" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'general') ? 'selected' : ''; ?>>General Information</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Your Message</label>
                            <textarea id="message" name="message" rows="5" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Success Modal Popup -->
<div id="successModal" class="modal <?php echo $formSubmitted ? 'show' : ''; ?>">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div class="modal-icon">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM10 17L5 12L6.41 10.59L10 14.17L17.59 6.58L19 8L10 17Z" fill="#81253F"/>
            </svg>
        </div>
        <h3>Thank You!</h3>
        <p>Your message has been sent successfully. A member of our team will respond to your inquiry as soon as possible.</p>
        <button class="btn btn-primary modal-close-btn">Close</button>
    </div>
</div>

<script>
// JavaScript for modal popup
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('successModal');
    var closeBtn = document.querySelector('.close');
    var modalCloseBtn = document.querySelector('.modal-close-btn');
    
    // Close when X is clicked
    if (closeBtn) {
        closeBtn.onclick = function() {
            modal.classList.remove('show');
        }
    }
    
    // Close when button is clicked
    if (modalCloseBtn) {
        modalCloseBtn.onclick = function() {
            modal.classList.remove('show');
        }
    }
    
    // Close when clicking outside the modal
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.classList.remove('show');
        }
    }
});
</script>

<section class="map-section">
    <div class="container">
        <h2>Find Our Shelter</h2>
        <div class="map-container">
            <!-- Placeholder for map - in a real implementation, you would integrate Google Maps or another mapping service -->
            <div class="map-placeholder">
                <img src="../assets/images/map-placeholder.jpg" alt="Map showing our shelter location">
            </div>
        </div>
    </div>
</section>

<section class="faq-section">
    <div class="container">
        <h2>Frequently Asked Questions</h2>
        <p class="section-description">Find quick answers to common questions</p>
        
        <div class="faq-container">
            <div class="faq-item">
                <h3>What is your adoption process?</h3>
                <p>Our adoption process begins with browsing available pets online or visiting our shelter. Once you find a potential match, you'll complete an adoption application, meet the pet, and if approved, complete the adoption paperwork. The entire process typically takes 1-3 days.</p>
            </div>
            
            
            <div class="faq-item">
                <h3>Do you offer foster programs?</h3>
                <p>Yes! We're always looking for loving temporary homes for our animals. Foster periods typically range from 2 weeks to 3 months, depending on the animal's needs. We provide all necessary supplies and medical care.</p>
            </div>
            
            <div class="faq-item">
                <h3>How can I volunteer at the shelter?</h3>
                <p>We welcome volunteers aged 16 and up. You can help with animal care, administrative tasks, special events, and more. Please fill out our volunteer application form or contact our volunteer coordinator for more information.</p>
            </div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container">
        <h2>Ready to Make a Difference?</h2>
        <p>Whether you're looking to adopt, foster, volunteer, or donate, your support helps animals in need.</p>
        <div class="cta-buttons">
            <a href="pets.php" class="btn btn-outline-light">Browse Available Pets</a>
            <a href="donate.php" class="btn btn-outline-light">Support Our Mission</a>
        </div>
    </div>
</section>

<?php
// Include the footer
include_once '../includes/footer.php';
?>