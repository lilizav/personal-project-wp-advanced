jQuery(document).ready(function($) {
    // Toggle functionality for Visited vs Dream
    $('#show-all').click(function() {
        $('.country-card').show();
        $('.toggle button').removeClass('active');
        $(this).addClass('active');
    });

    $('#show-visited').click(function() {
        $('.country-card').hide();
        $('.country-card[data-status="visited"]').show();
        $('.toggle button').removeClass('active');
        $(this).addClass('active');
    });

    $('#show-dream').click(function() {
        $('.country-card').hide();
        $('.country-card[data-status="dream"]').show();
        $('.toggle button').removeClass('active');
        $(this).addClass('active');
    });

    // Hover effects for country cards
    $('.country-card').hover(
        function() {
            $(this).addClass('hovered');
        },
        function() {
            $(this).removeClass('hovered');
        }
    );

    // Add more interactive features as needed
});