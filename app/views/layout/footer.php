                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Activar enlace del menú según la página actual
        document.addEventListener('DOMContentLoaded', function() {
            const currentUrl = window.location.href;
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            
            navLinks.forEach(link => {
                if (currentUrl.includes(link.getAttribute('href').split('&')[0])) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
