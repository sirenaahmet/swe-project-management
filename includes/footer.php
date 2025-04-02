</main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>Pet Adoption Center</h3>
                    <p>We are dedicated to finding loving homes for animals in need.</p>
                   
                </div>
                
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="pets.php">Available Pets</a></li>
                        <li><a href="about-us.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Resources</h3>
                    <ul>
                        <li><a href="#">Adoption Process</a></li>
                        <li><a href="#">Pet Care Tips</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Success Stories</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Contact Us</h3>
                    <p>Tirana<br>Epoka Campus</p>
                    <p>Email: info@petadoption.com<br>Phone: +355 068 207 8217</p>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> Pet Adoption Center. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Font Awesome for icons -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    
    <!-- Main JavaScript -->
    <script src="../assets/js/main.js"></script>
    
    <!-- Any additional page-specific scripts can be included here -->
    <?php if (isset($additionalScripts)) echo $additionalScripts; ?>
</body>
</html>