// Auto-set active navbar item based on current page
document.addEventListener('DOMContentLoaded', function() {
    // Get current page filename
    const currentPage = window.location.pathname.split('/').pop();
    
    // Remove active class from all nav links
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    navLinks.forEach(link => {
        link.classList.remove('active');
        link.removeAttribute('aria-current');
    });
    
    // Set active class based on current page
    let activeLink = null;
    
    switch(currentPage) {
        case 'invoiceslist.html':
            activeLink = document.querySelector('a[href="invoiceslist.html"]');
            break;
        case 'invoices.html':
            activeLink = document.querySelector('a[href="invoices.html"]');
            break;
        case 'vehicleslist.html':
            activeLink = document.querySelector('a[href="vehicleslist.html"]');
            break;
        case 'vehicles.html':
            activeLink = document.querySelector('a[href="vehicles.html"]');
            break;
        default:
            // Default to Dashboard if no specific page matches
            activeLink = document.querySelector('.navbar-brand');
            break;
    }
    
    if (activeLink && activeLink.classList.contains('nav-link')) {
        activeLink.classList.add('active');
        activeLink.setAttribute('aria-current', 'page');
    }
});