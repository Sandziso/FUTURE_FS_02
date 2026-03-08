(function($) {
    "use strict";

    // Spinner
    const spinner = function() {
        setTimeout(() => {
            const spinnerEl = document.getElementById('spinner');
            if (spinnerEl) spinnerEl.classList.remove('show');
        }, 1);
    };
    spinner();

    // Back to top button
    $(window).scroll(function() {
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });
    $('.back-to-top').click(function() {
        $('html, body').animate({ scrollTop: 0 }, 800);
        return false;
    });

    // Sidebar Toggler
    $('#sidebarToggle, #sidebarToggleTop').on('click', function(e) {
        e.preventDefault();
        $('#wrapper').toggleClass('toggled');
        // Store toggle state in localStorage
        localStorage.setItem('sidebarToggled', $('#wrapper').hasClass('toggled'));
    });

    // Restore sidebar state
    if (localStorage.getItem('sidebarToggled') === 'true') {
        $('#wrapper').addClass('toggled');
    }

    // Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

})(jQuery);