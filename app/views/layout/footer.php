        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');
            const toggleDesktop = document.getElementById('sidebar-toggle-desktop');
            const toggleMobile = document.getElementById('sidebar-toggle-mobile');
            const toggleMobileClose = document.getElementById('sidebar-toggle-mobile-close');
            const mainContentWrapper = document.getElementById('main-content-wrapper');
            
            let isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
            
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
                const icon = toggleDesktop.querySelector('i');
                const text = toggleDesktop.querySelector('.nav-text');
                icon.className = 'bi bi-chevron-right';
                text.textContent = 'Expandir';
                
                mainContentWrapper.classList.add('collapsed');
            }
            
            if (toggleDesktop) {
                toggleDesktop.addEventListener('click', function() {
                    const icon = toggleDesktop.querySelector('i');
                    const text = toggleDesktop.querySelector('.nav-text');
                    
                    sidebar.classList.toggle('collapsed');
                    mainContentWrapper.classList.toggle('collapsed');
                    isCollapsed = sidebar.classList.contains('collapsed');
                    
                    if (isCollapsed) {
                        icon.className = 'bi bi-chevron-right';
                        text.textContent = 'Expandir';
                    } else {
                        icon.className = 'bi bi-chevron-left';
                        text.textContent = 'Colapsar';
                    }
                    
                    localStorage.setItem('sidebar-collapsed', isCollapsed);
                });
            }
            
            if (toggleMobile) {
                toggleMobile.addEventListener('click', function() {
                    sidebar.classList.add('show');
                    sidebarOverlay.classList.add('show');
                    document.body.style.overflow = 'hidden';
                });
            }
            
            if (toggleMobileClose) {
                toggleMobileClose.addEventListener('click', function() {
                    closeMobileSidebar();
                });
            }
            
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    closeMobileSidebar();
                });
            }
            
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeMobileSidebar();
                }
            });
            
            function closeMobileSidebar() {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                document.body.style.overflow = '';
            }
            
            const currentUrl = window.location.href;
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            
            navLinks.forEach(link => {
                if (currentUrl.includes(link.getAttribute('href').split('&')[0])) {
                    link.classList.add('active');
                }
            });
            
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 768) {
                        closeMobileSidebar();
                    }
                });
            });
        });
    </script>
</body>
</html>
