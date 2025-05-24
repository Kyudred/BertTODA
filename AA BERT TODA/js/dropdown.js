document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Toggle active class
            this.classList.toggle('active');
            
            // Get the submenu
            const submenu = this.nextElementSibling;
            const toggleIcon = this.querySelector('.toggle-icon i');
            
            // Toggle submenu visibility with animation
            if (submenu.classList.contains('show')) {
                submenu.classList.remove('show');
                toggleIcon.classList.remove('fa-chevron-up');
                toggleIcon.classList.add('fa-chevron-down');
            } else {
                submenu.classList.add('show');
                toggleIcon.classList.remove('fa-chevron-down');
                toggleIcon.classList.add('fa-chevron-up');
            }
            
            // Close other dropdowns
            dropdownToggles.forEach(otherToggle => {
                if (otherToggle !== this) {
                    otherToggle.classList.remove('active');
                    const otherSubmenu = otherToggle.nextElementSibling;
                    const otherIcon = otherToggle.querySelector('.toggle-icon i');
                    otherSubmenu.classList.remove('show');
                    otherIcon.classList.remove('fa-chevron-up');
                    otherIcon.classList.add('fa-chevron-down');
                }
            });
        });
    });

    // Set active state for current page
    const currentPage = window.location.pathname.split('/').pop();
    const menuLinks = document.querySelectorAll('.sidebar a');
    
    menuLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage) {
            link.classList.add('active');
            // If it's in a submenu, also activate the parent dropdown
            const parentDropdown = link.closest('.submenu')?.previousElementSibling;
            if (parentDropdown && parentDropdown.classList.contains('dropdown-toggle')) {
                parentDropdown.classList.add('active');
                const submenu = parentDropdown.nextElementSibling;
                const toggleIcon = parentDropdown.querySelector('.toggle-icon i');
                submenu.classList.add('show');
                toggleIcon.classList.remove('fa-chevron-down');
                toggleIcon.classList.add('fa-chevron-up');
            }
        }
    });
}); 