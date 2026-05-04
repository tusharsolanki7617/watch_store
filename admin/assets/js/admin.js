/**
 * Admin Panel - Custom Scripts
 */

$(document).ready(function () {

    // ── Sidebar toggle ────────────────────────────────────────
    function openSidebar() {
        $('#sidebar').addClass('active');
        $('.sidebar-overlay').addClass('active');
        $('body').css('overflow', 'hidden'); // prevent scroll behind overlay on mobile
    }

    function closeSidebar() {
        $('#sidebar').removeClass('active');
        $('.sidebar-overlay').removeClass('active');
        $('body').css('overflow', '');
    }

    $('#sidebarCollapse').on('click', openSidebar);
    $('.sidebar-close, .sidebar-overlay').on('click', closeSidebar);

    // Close on ESC key
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') closeSidebar();
    });

    // Handle window resize: auto-close overlay on desktop
    $(window).on('resize', function () {
        if ($(window).width() > 991.98) {
            closeSidebar();
        }
    });

    // ── Navbar scroll shadow ──────────────────────────────────
    $(window).on('scroll', function () {
        if ($(this).scrollTop() > 10) {
            $('.navbar').addClass('shadow-glow');
        } else {
            $('.navbar').removeClass('shadow-glow');
        }
    });

    // ── Auto-dismiss alerts after 5 seconds ──────────────────
    setTimeout(function () {
        $('.alert-dismissible').fadeOut('slow');
    }, 5000);

    // ── Swipe-left to close sidebar on mobile ─────────────────
    var touchStartX = 0;
    var touchEndX = 0;

    document.addEventListener('touchstart', function (e) {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    document.addEventListener('touchend', function (e) {
        touchEndX = e.changedTouches[0].screenX;
        // Swipe left > 60px: close sidebar
        if (touchStartX - touchEndX > 60 && $('#sidebar').hasClass('active')) {
            closeSidebar();
        }
        // Swipe right from left edge > 60px: open sidebar
        if (touchEndX - touchStartX > 60 && touchStartX < 30) {
            openSidebar();
        }
    }, { passive: true });

});
