        </div> <!-- /.sf-body-container -->
    </main> <!-- /.sf-main-content -->
</div> <!-- /.sf-app-container -->

<!-- Custom App Scripts -->
<script>
    // Sidebar Mobile Toggle & Backdrop
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sfSidebar = document.getElementById('sfSidebar');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');

    function toggleSidebar() {
        if (!sfSidebar) return;
        const isOpen = sfSidebar.classList.toggle('show');
        if (sidebarBackdrop) {
            sidebarBackdrop.classList.toggle('show', isOpen);
        }
        document.body.style.overflow = (isOpen && window.innerWidth < 992) ? 'hidden' : '';
    }

    function closeSidebar() {
        if (!sfSidebar) return;
        sfSidebar.classList.remove('show');
        if (sidebarBackdrop) {
            sidebarBackdrop.classList.remove('show');
        }
        document.body.style.overflow = '';
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', closeSidebar);
    }

    // Auto-close on resize to desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 992) {
            closeSidebar();
        }
    });

    // Initialize Bootstrap Tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
</script>
</body>
</html>
