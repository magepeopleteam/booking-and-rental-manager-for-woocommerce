(function ($) {


    $('#rbfw_rental_lists_search-input').on('keyup', function() {
        let search = $(this).val().toLowerCase().trim();

        $('.rbfw_rental_list').each(function() {
            var name = $(this).data('title_search').toLowerCase();

            // Check if search term is in any of the fields
            if ( name.includes( search ) ) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });


    const itemsPerPage = 20;
    let currentFilteredItems = $('.rbfw_rental_list');

    function updateCounts() {
        let currentlyVisible = currentFilteredItems.filter(':visible').length;
        $('#visibleCount').text(currentlyVisible);
        $('#totalCount').text(currentFilteredItems.length);

        if (currentlyVisible >= currentFilteredItems.length) {
            $('#rbfw_loadMoreBtn').hide();
        } else {
            $('#rbfw_loadMoreBtn').show();
        }
    }

    function rbfw_showNextItems() {
        let hiddenItems = currentFilteredItems.filter(':hidden');
        let itemsToShow = hiddenItems.slice(0, itemsPerPage);

        itemsToShow.fadeIn(200, function() {
            // Only run once when the last item is shown
            if ($(this).is(itemsToShow.last())) {
                updateCounts();
            }
        });
    }

    function initialLoad() {
        $('.rbfw_rental_list').hide();
        currentFilteredItems = $('.rbfw_rental_list');
        rbfw_showNextItems(); // Show the first batch
    }

    initialLoad();



    $(document).on('click', '#rbfw_loadMoreBtn', function () {
        rbfw_showNextItems();
    });
    $(document).on('click', '.mpwem_filter_by_status', function () {
        $('.mpwem_filter_by_status').removeClass('mpwem_filter_btn_active_bg_color').addClass('mpwem_filter_btn_bg_color');
        $(this).removeClass('mpwem_filter_btn_bg_color').addClass('mpwem_filter_btn_active_bg_color');

        let searchText = $(this).attr('data-by-filter').toLowerCase();
        $('.rbfw_rental_list').hide();
        currentFilteredItems = $('.rbfw_rental_list').filter(function () {
            let status = $(this).data('event-status').toLowerCase();
            return (searchText === 'all' || status.includes(searchText));
        });
        // Reset counter and show first N
        $('#visibleCount').text(0);
        rbfw_showNextItems();
    });

    $(document).on('click', '.rbfw_rental_lists_status-tab', function () {
        $('.rbfw_rental_lists_status-tab').removeClass('active');
        $(this).addClass('active');

        let searchText = $(this).attr('data-by-filter').toLowerCase();
        $('.rbfw_rental_list').hide();
        currentFilteredItems = $('.rbfw_rental_list').filter(function () {
            let status = $(this).data('rental-status').toLowerCase();
            return (searchText === 'all' || status.includes(searchText));
        });
        // Reset counter and show first N
        $('#visibleCount').text(0);
        rbfw_showNextItems();
    });

})(jQuery);

