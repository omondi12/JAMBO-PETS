</main>
    <!-- Lightbox Modal -->
<div id="profileLightbox" class="lightbox">
    <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
    <div class="lightbox-content">
        <img id="lightboxImage" src="" alt="Profile Picture">
        <div class="mt-3">
            <h5 id="lightboxTitle" class="text-white"></h5>
        </div>
    </div>
</div>
<style>
    /* Enhanced Footer Styling for Jambo Pets */

/* Footer Base Styling */
footer.bg-dark {
  background: linear-gradient(135deg, #1f2937 0%, #111827 50%, #0f172a 100%) !important;
  position: relative;
  overflow: hidden;
  border-top: 4px solid var(--primary-color, #2563eb);
}

footer.bg-dark::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: 
    radial-gradient(circle at 20% 80%, rgba(37, 99, 235, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(16, 185, 129, 0.1) 0%, transparent 50%);
  pointer-events: none;
  z-index: 1;
}

footer.bg-dark .container {
  position: relative;
  z-index: 2;
}

/* Footer Headings */
footer h5.fw-bold {
  color: #ffffff !important;
  font-weight: 700;
  font-size: 1.25rem;
  margin-bottom: 1.5rem;
  position: relative;
  padding-bottom: 0.5rem;
}

footer h5.fw-bold::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 3px;
  background: linear-gradient(90deg, var(--primary-color, #2563eb), var(--secondary-color, #10b981));
  border-radius: 2px;
}

/* Footer Paragraphs */
footer p {
  color: #d1d5db !important;
  line-height: 1.7;
  font-size: 0.95rem;
  margin-bottom: 1rem;
}

footer p:last-child {
  margin-bottom: 0;
}

/* Footer Links */
footer a {
  color: #e5e7eb !important;
  text-decoration: none !important;
  transition: all 0.3s ease;
  position: relative;
  display: inline-block;
}

footer a:hover {
  color: var(--primary-color, #3b82f6) !important;
  transform: translateX(5px);
}

footer a::before {
  content: '→';
  position: absolute;
  left: -20px;
  opacity: 0;
  transition: all 0.3s ease;
  color: var(--primary-color, #3b82f6);
}

footer a:hover::before {
  opacity: 1;
  left: -15px;
}

/* Footer Lists */
footer ul.list-unstyled {
  padding-left: 0;
}

footer ul.list-unstyled li {
  margin-bottom: 0.75rem;
  padding-left: 0;
  transition: all 0.3s ease;
}

footer ul.list-unstyled li:hover {
  padding-left: 10px;
}

/* Social Links Enhancement */
.social-links {
  margin-top: 1.5rem;
  display: flex;
  gap: 1rem;
}

.social-links a {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 45px;
  height: 45px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  color: #ffffff !important;
  font-size: 1.2rem;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  position: relative;
  overflow: hidden;
}

.social-links a::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.social-links a:hover::before {
  left: 100%;
}

.social-links a:hover {
  transform: translateY(-3px) scale(1.1);
  background: var(--primary-color, #3b82f6);
  box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
  color: #ffffff !important;
}

/* Contact Info Icons */
footer .fas,
footer .fab {
  color: var(--primary-color, #3b82f6) !important;
  margin-right: 0.75rem;
  font-size: 1.1rem;
  width: 20px;
  text-align: center;
}

/* Footer Divider */
footer hr {
  border: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  margin: 2.5rem 0;
}

/* Copyright Section */
footer .row:last-child {
  padding-top: 1rem;
}

footer .row:last-child p {
  font-size: 0.9rem;
  color: #9ca3af !important;
  margin-bottom: 0;
}

footer .row:last-child .text-md-end a {
  font-size: 0.9rem;
  margin-left: 1rem;
  color: #9ca3af !important;
  border-bottom: 1px solid transparent;
  transition: all 0.3s ease;
}

footer .row:last-child .text-md-end a:hover {
  color: var(--primary-color, #3b82f6) !important;
  border-bottom-color: var(--primary-color, #3b82f6);
  transform: none;
}

footer .row:last-child .text-md-end a::before {
  display: none;
}

/* Responsive Design */
@media (max-width: 768px) {
  footer {
    padding-top: 3rem !important;
    padding-bottom: 2rem !important;
  }
  
  footer .col-md-3 {
    margin-bottom: 2.5rem;
  }
  
  footer .col-md-3:last-child {
    margin-bottom: 1.5rem;
  }
  
  footer h5.fw-bold {
    font-size: 1.1rem;
    margin-bottom: 1rem;
  }
  
  .social-links {
    justify-content: flex-start;
    gap: 0.75rem;
  }
  
  .social-links a {
    width: 40px;
    height: 40px;
    font-size: 1rem;
  }
  
  footer .row:last-child .text-md-end {
    text-align: left !important;
    margin-top: 1rem;
  }
  
  footer .row:last-child .text-md-end a {
    display: block;
    margin: 0.5rem 0;
  }
}

@media (max-width: 576px) {
  footer p {
    font-size: 0.9rem;
  }
  
  footer .fas,
  footer .fab {
    font-size: 1rem;
    margin-right: 0.5rem;
  }
  
  .social-links a {
    width: 38px;
    height: 38px;
    font-size: 0.9rem;
  }
}

/* Animation for footer elements */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

footer .col-md-3 {
  animation: fadeInUp 0.6s ease-out forwards;
}

footer .col-md-3:nth-child(1) { animation-delay: 0.1s; }
footer .col-md-3:nth-child(2) { animation-delay: 0.2s; }
footer .col-md-3:nth-child(3) { animation-delay: 0.3s; }
footer .col-md-3:nth-child(4) { animation-delay: 0.4s; }

/* Enhanced hover effects for better interactivity */
footer .col-md-3 {
  transition: all 0.3s ease;
  padding: 1.5rem;
  border-radius: 10px;
}

footer .col-md-3:hover {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  transform: translateY(-5px);
}

/* Brand name enhancement in footer */
footer h5.fw-bold:first-child {
  background: linear-gradient(135deg, #ffffff, var(--primary-color, #3b82f6));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-size: 1.5rem;
  font-weight: 800;
}

/* Improved accessibility */
footer a:focus {
  outline: 2px solid var(--primary-color, #3b82f6);
  outline-offset: 2px;
  border-radius: 3px;
}

.social-links a:focus {
  outline: 2px solid var(--primary-color, #3b82f6);
  outline-offset: 3px;
}
</style>
    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 pt-5 pb-4">
        <div class="container">
            <div class="row">
                <!-- About -->
                <div class="col-md-3 mb-4">
                    <h5 class="fw-bold mb-3">Jambo Pets</h5>
                    <p>Kenya's premier online marketplace for pets and pet products. Connecting pet lovers with trusted breeders and sellers.</p>
                    <div class="social-links">
                        <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-md-3 mb-4">
                    <h5 class="fw-bold mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>" class="text-white text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>about.php" class="text-white text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>buyer/browse.php" class="text-white text-decoration-none">Browse Pets</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>buyer/browse.php?type=product" class="text-white text-decoration-none">Browse Products</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>contact.php" class="text-white text-decoration-none">Contact Us</a></li>
                    </ul>
                </div>
                
                <!-- Pet Categories -->
                <div class="col-md-3 mb-4">
                    <h5 class="fw-bold mb-3">Pet Categories</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>buyer/browse.php?category=1" class="text-white text-decoration-none">Dogs</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>buyer/browse.php?category=2" class="text-white text-decoration-none">Cats</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>buyer/browse.php?category=3" class="text-white text-decoration-none">Birds</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>buyer/browse.php?category=4" class="text-white text-decoration-none">Fish</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>buyer/browse.php?category=5" class="text-white text-decoration-none">Small Pets</a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div class="col-md-3 mb-4">
                    <h5 class="fw-bold mb-3">Contact Us</h5>
                    <p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> <?php echo $contactAddress?></p>
                    <p class="mb-2"><i class="fas fa-phone me-2"></i> <?php echo $contactPhone?></p>
                    <p class="mb-2"><i class="fas fa-envelope me-2"></i> <?php echo $contactEmail?></p>
                    <p class="mb-0"><i class="fas fa-clock me-2"></i> Mon-Fri: 9am-5pm</p>
                </div>
            </div>
            
            <hr class="my-4">
            
            <!-- Copyright -->
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-md-0">&copy; <?php echo date('Y'); ?> Jambo Pets. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="<?php echo BASE_URL; ?>privacy-policy.php" class="text-white text-decoration-none me-3">Privacy Policy</a>
                    <a href="<?php echo BASE_URL; ?>terms.php" class="text-white text-decoration-none">Terms & Conditions</a>
                </div>
            </div>
        </div>
    </footer>
   
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS Bundle with Popper -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <!-- Custom JS -->
    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
    <!-- Make sure these are included in your footer.php -->
   
    
 <script>
function openLightbox(imageSrc, userName) {
    console.log('Opening lightbox with:', imageSrc, userName);
    
    const lightbox = document.getElementById('profileLightbox');
    const lightboxImage = document.getElementById('lightboxImage');
    const lightboxTitle = document.getElementById('lightboxTitle');
    
    if (!lightbox || !lightboxImage || !lightboxTitle) {
        console.error('Lightbox elements not found');
        return;
    }
    
    lightboxImage.src = imageSrc;
    lightboxImage.alt = userName + "'s Profile Picture";
    lightboxTitle.textContent = userName + "'s Profile Picture";
    lightbox.style.display = 'block';
    
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    const lightbox = document.getElementById('profileLightbox');
    if (lightbox) {
        lightbox.style.display = 'none';
    }
    document.body.style.overflow = 'auto';
}

// Close lightbox when clicking on the backdrop
document.addEventListener('DOMContentLoaded', function() {
    const lightbox = document.getElementById('profileLightbox');
    const lightboxContent = document.querySelector('.lightbox-content');
    
    if (lightbox) {
        lightbox.addEventListener('click', function(event) {
            if (event.target === this) {
                closeLightbox();
            }
        });
    }
    
    if (lightboxContent) {
        lightboxContent.addEventListener('click', function(event) {
            event.stopPropagation();
        });
    }
});

// Close lightbox with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeLightbox();
    }
});
</script>
</body>
</html>
<?php
ob_end_flush(); 
?>