jQuery(document).ready(function($) {
    function getCurrentStatusFromHash() {
        var hash = window.location.hash.replace('#', '');
        if (hash === 'dream' || hash === 'visited' || hash === 'all') {
            return hash;
        }
        return 'all';
    }

    function updateMoreButton() {
        $('#show-more-btn').hide();
    }

    function resetMoreButton() {
        $('#show-more-btn').hide();
    }

    function applyStatusFilter(status) {
        var cards = $('.country-card');
        var listItems = $('.country-link');

        if (status === 'all') {
            cards.show();
            listItems.show();
        } else {
            cards.hide();
            listItems.hide();
            cards.filter('[data-status="' + status + '"]').show();
            listItems.filter('[data-status="' + status + '"]').show();
        }

        $('.bucket-tab, .filter-button').removeClass('active');
        $('.bucket-tab[data-status="' + status + '"]').addClass('active');
        $('.filter-button[data-status="' + status + '"]').addClass('active');

        var visibleCount = cards.filter(':visible').length;
        if (visibleCount === 0) {
            $('.no-results-message').removeClass('hide');
        } else {
            $('.no-results-message').addClass('hide');
        }

        resetMoreButton();
        updateMoreButton();
    }

    $('.bucket-tab, .filter-button').on('click', function() {
        var status = $(this).data('status');
        window.location.hash = status;
        applyStatusFilter(status);
    });

    $('#bucket-list-add-form').on('submit', function(event) {
        event.preventDefault();

        var country = $('#bucket-country-input').val().trim();
        var status = $('input[name="status"]:checked').val();
        var message = $('#bucket-form-message');

        if (!country) {
            message.text('Please type a country name.');
            return;
        }

        message.text('Saving...');

        $.post(TravelBucketList.ajaxUrl, {
            action: 'travel_bucket_list_add_country',
            security: TravelBucketList.nonce,
            country: country,
            status: status
        }).done(function(response) {
            if (response.success) {
                message.text(response.data || 'Country added. Reloading...');
                setTimeout(function() {
                    window.location.reload();
                }, 900);
            } else {
                message.text(response.data || 'Unable to add country.');
            }
        }).fail(function() {
            message.text('There was a problem submitting the country.');
        });
    });

    $('.country-link').on('click', function(event) {
        event.preventDefault();
        var target = $(this).data('target');
        var card = $(target);
        var status = $(this).data('status');

        if (card.length) {
            window.location.hash = status;
            applyStatusFilter(status);
            $('.country-link').removeClass('active');
            $(this).addClass('active');

            $('html, body').animate({
                scrollTop: card.offset().top - 90
            }, 450);
            card.addClass('highlighted');
            setTimeout(function() {
                card.removeClass('highlighted');
            }, 1400);
        }
    });

    $('.country-card').hover(
        function() {
            $(this).addClass('hovered');
        },
        function() {
            $(this).removeClass('hovered');
        }
    );

    var startingStatus = getCurrentStatusFromHash();
    applyStatusFilter(startingStatus);

    $(window).on('hashchange', function() {
        applyStatusFilter(getCurrentStatusFromHash());
    });

    // Show More button functionality is disabled because all countries are shown by default.
    $('#show-more-btn').hide();
});