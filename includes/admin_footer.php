</div>
        <!-- /.container-fluid -->
    </div>
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


    <!-- Footer -->
    <footer class="bg-white text-center py-3 mt-auto" style="margin-left: 250px;">
        <div class="container">
            <span class="text-muted">© <?php echo date('Y'); ?> Jambo Pets. All rights reserved.</span>
        </div>
    </footer>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
   <!-- Include this before closing body tag -->
    <script src="../admin/admin_dashboard.js"></script>
   
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    
    <!-- Custom JS -->
    <script>
        // Toggle sidebar on mobile
        document.querySelector('.navbar-toggler').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('d-none');
        });
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Initialize monthly sales chart if element exists
        if (document.getElementById('monthlySalesChart')) {
            const ctx = document.getElementById('monthlySalesChart').getContext('2d');
            const monthlySalesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Sales',
                        data: [12, 19, 3, 5, 2, 3, 7, 8, 9, 10, 11, 12],
                        backgroundColor: 'rgba(40, 167, 69, 0.5)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
        
        // Initialize user types chart if element exists
        if (document.getElementById('userTypesChart')) {
            const ctx = document.getElementById('userTypesChart').getContext('2d');
            const userTypesChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Buyers', 'Sellers', 'Admin'],
                    datasets: [{
                        data: [12, 19, 3],
                        backgroundColor: [
                            'rgba(0, 123, 255, 0.5)',
                            'rgba(40, 167, 69, 0.5)',
                            'rgba(255, 193, 7, 0.5)'
                        ],
                        borderColor: [
                            'rgba(0, 123, 255, 1)',
                            'rgba(40, 167, 69, 1)',
                            'rgba(255, 193, 7, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
        
        // Initialize listing types chart if element exists
        if (document.getElementById('listingTypesChart')) {
            const ctx = document.getElementById('listingTypesChart').getContext('2d');
            const listingTypesChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Dogs', 'Cats', 'Birds', 'Fish', 'Other Pets', 'Accessories'],
                    datasets: [{
                        data: [12, 19, 3, 5, 2, 8],
                        backgroundColor: [
                            'rgba(0, 123, 255, 0.5)',
                            'rgba(40, 167, 69, 0.5)',
                            'rgba(255, 193, 7, 0.5)',
                            'rgba(220, 53, 69, 0.5)',
                            'rgba(111, 66, 193, 0.5)',
                            'rgba(23, 162, 184, 0.5)'
                        ],
                        borderColor: [
                            'rgba(0, 123, 255, 1)',
                            'rgba(40, 167, 69, 1)',
                            'rgba(255, 193, 7, 1)',
                            'rgba(220, 53, 69, 1)',
                            'rgba(111, 66, 193, 1)',
                            'rgba(23, 162, 184, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    </script>
    <script>
    // Enable tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
    
    // Toggle sidebar on small screens
    document.querySelector('.navbar-toggler').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('d-none');
    });
    
    // Scroll to top functionality
    window.addEventListener('scroll', function() {
        var scrollToTop = document.querySelector('.scroll-to-top');
        if (window.pageYOffset > 100) {
            scrollToTop.style.display = 'block';
        } else {
            scrollToTop.style.display = 'none';
        }
    });
    
    document.querySelector('.scroll-to-top').addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({top: 0, behavior: 'smooth'});
    });
</script>
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