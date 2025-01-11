<?php
/*
* Author 	:	MagePeople Team
* Copyright	:	mage-people.com
* Developer :   Ariful
* Version	:	1.0.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!class_exists('RBFWOrderPage')) {


	class RBFWOrderPage {

		private $posts_per_page = 10;

		public function rbfw_order_page() {
			$this->posts_per_page = isset($_GET['posts_per_page']) ? intval(sanitize_text_field($_GET['posts_per_page'])) : $this->posts_per_page;

			$args = array(
				'post_type' => 'rbfw_order',
				'order' => 'DESC',
				'posts_per_page' => -1
			);
			$query = new WP_Query($args);
			$total_posts = $query->post_count;
			?>
            <div class="rbfw_order_page_wrap wrap">
                <h1 class="awesome-heading"><?php esc_html_e('Order List', 'booking-and-rental-manager-for-woocommerce'); ?></h1>
                <input type="text" id="search" class="search-input awesome-search" placeholder="<?php esc_attr_e('Search by order id or customer name..', 'booking-and-rental-manager-for-woocommerce'); ?>" />
                <table class="rbfw_order_page_table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Order', 'booking-and-rental-manager-for-woocommerce'); ?></th>
                            <th><?php esc_html_e('Billing Name', 'booking-and-rental-manager-for-woocommerce'); ?></th>
                            <th><?php esc_html_e('Order Created Date', 'booking-and-rental-manager-for-woocommerce'); ?></th>
                            <th><?php esc_html_e('Booking Start Date', 'booking-and-rental-manager-for-woocommerce'); ?></th>
                            <th><?php esc_html_e('Booking End Date', 'booking-and-rental-manager-for-woocommerce'); ?></th>
                            <th><?php esc_html_e('Status', 'booking-and-rental-manager-for-woocommerce'); ?></th>
                            <th><?php esc_html_e('Total', 'booking-and-rental-manager-for-woocommerce'); ?></th>
                            <th><?php esc_html_e('Action', 'booking-and-rental-manager-for-woocommerce'); ?></th>
                        </tr>
                    </thead>
    <tbody id="order-list">
        <?php if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post();
            global $post;
            $post_id = $post->ID;
            $billing_name = get_post_meta($post_id, 'rbfw_billing_name', true);
            $rbfw_order_id = get_post_meta($post_id, 'rbfw_order_id', true);
            $status = get_post_meta($post_id, 'rbfw_order_status', true);
            $total_price = get_post_meta($post_id, 'rbfw_ticket_total_price', true);
            $ticket_infos = get_post_meta($post_id, 'rbfw_ticket_info', true);
            $ticket_info_array = maybe_unserialize($ticket_infos);

            $rbfw_start_datetime = '';
            $rbfw_end_datetime = '';
            if (!empty($ticket_info_array) && is_array($ticket_info_array)) {
                foreach ($ticket_info_array as $ticket_info) {
                    $rbfw_start_datetime = isset($ticket_info['rbfw_start_datetime']) ? $ticket_info['rbfw_start_datetime'] : '';
                    $rbfw_end_datetime = isset($ticket_info['rbfw_end_datetime']) ? $ticket_info['rbfw_end_datetime'] : '';
                }
            }
        ?>
            <tr class="order-row">
                <td><?php echo esc_html($rbfw_order_id); ?></td>
                <td><?php echo esc_html($billing_name); ?></td>
                <td><?php echo esc_html(get_the_date('F j, Y') . ' ' . get_the_time()); ?></td>
                <td><?php echo esc_html( !empty($rbfw_start_datetime) ? date_i18n('F j, Y g:i a', strtotime($rbfw_start_datetime)) : ''); ?></td>
               <td><?php echo esc_html( !empty($rbfw_end_datetime) ? date_i18n('F j, Y g:i a', strtotime($rbfw_end_datetime)) : ''); ?></td>
                <td><span class="rbfw_order_status <?php echo esc_attr($status); ?>"><?php echo esc_html($status); ?></span></td>
                <td><?php echo wc_price($total_price); ?></td>
                <?php if (function_exists('rbfw_pro_tab_menu_list')) { ?>
                    <td>
                        <a href="javascript:void(0);" class="rbfw_order_view_btn" data-post-id="<?php echo esc_attr($post_id); ?>">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <?php esc_html_e('View Details', 'booking-and-rental-manager-for-woocommerce'); ?>
                        </a>
                        <a href="<?php echo esc_url(admin_url('post.php?post=' . $post_id . '&action=edit')); ?>" class="rbfw_order_edit_btn">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <?php esc_html_e('Order status changes', 'booking-and-rental-manager-for-woocommerce'); ?>
                        </a>
                    </td>
                <?php
                } else {
                    ?>

                    <td>
                        <a href="javascript:void(0);" class="rbfw_order_view_btn pro-overlay">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <?php esc_html_e('View Details', 'booking-and-rental-manager-for-woocommerce'); ?>
                        </a>
                        <a href="javascript:void(0);" class="rbfw_order_edit_btn pro-overlay">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <?php esc_html_e('Order status changes', 'booking-and-rental-manager-for-woocommerce'); ?>
                        </a>
                    </td>
                    <script>
                        document.querySelectorAll('.pro-overlay').forEach(function(button) {
                            button.replaceWith(button.cloneNode(true));
                        });
                        document.querySelectorAll('.pro-overlay').forEach(function(button) {
                            button.addEventListener('click', function(event) {
                                event.preventDefault(); // Prevent default link behavior
                                window.open('https://mage-people.com/product/booking-and-rental-manager-for-woocommerce/', '_blank');
                            });
                        });
                    </script>
                    <?php
                }
                ?>
            </tr>
            <tr id="order-details-<?php echo $post_id; ?>" class="order-details" style="display: none;">
                <td colspan="12"><div class="order-details-content"></div></td>
            </tr>
            <?php endwhile; else : ?>
            <tr>
                <td colspan="12"><?php esc_html_e('Sorry, No data found!', 'booking-and-rental-manager-for-woocommerce'); ?></td>
            </tr>
        <?php endif; wp_reset_postdata(); ?>
    </tbody>
</table>

<div id="loader" style="display: none;">
    <div class="loader"></div> <!-- Loader element -->
</div>

					<label for="posts-per-page"><?php esc_html_e('Posts per Page:', 'booking-and-rental-manager-for-woocommerce'); ?></label>
						<select id="posts-per-page">
							<option value="2" <?php selected($this->posts_per_page, 2); ?>>2</option>
							<option value="5" <?php selected($this->posts_per_page, 5); ?>>5</option>
							<option value="10" <?php selected($this->posts_per_page, 10); ?>>10</option>
							<option value="20" <?php selected($this->posts_per_page, 20); ?>>20</option>
							<option value="25" <?php selected($this->posts_per_page, 25); ?>>25</option>
							<option value="30" <?php selected($this->posts_per_page, 30); ?>>30</option>
						</select>
				<div id="pagination" class="pagination"></div>
			</div>
			<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows = document.querySelectorAll('.order-row');
    const rowsPerPageSelect = document.getElementById('posts-per-page');
    let rowsPerPage = parseInt(rowsPerPageSelect.value);
    let currentPage = 1;
    const paginationElement = document.getElementById('pagination');
    
    function displayRows(page, rowsToShow = rows) {
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        rows.forEach(row => row.style.display = 'none'); // Hide all rows initially
        rowsToShow.forEach((row, index) => {
            if (index >= start && index < end) {
                row.style.display = ''; // Show rows within the range
            }
        });
    }

    function setupPagination(rowsToShow = rows) {
        paginationElement.innerHTML = '';
        const totalPages = Math.ceil(rowsToShow.length / rowsPerPage);

        for (let i = 1; i <= totalPages; i++) {
            const button = document.createElement('button');
            button.className = 'page-button';
            button.textContent = i;
            button.dataset.page = i;

            button.addEventListener('click', function () {
                currentPage = parseInt(this.dataset.page);
                displayRows(currentPage, rowsToShow);
                document.querySelectorAll('.page-button').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
            });

            paginationElement.appendChild(button);
        }

        // Set the first button as active by default
        if (paginationElement.firstChild) {
            paginationElement.firstChild.classList.add('active');
        }
    }

    document.getElementById('search').addEventListener('keyup', function () {
    const filter = this.value.toLowerCase();
    const filteredRows = Array.from(rows).filter(row => {
        const orderId = row.cells[0].textContent.toLowerCase(); // Order ID
        const billingName = row.cells[1].textContent.toLowerCase(); // Billing Name
        return orderId.includes(filter) || billingName.includes(filter); // Match either Order ID or Billing Name
    });

    // Reset pagination and display filtered rows
    currentPage = 1;
    setupPagination(filteredRows);
    displayRows(currentPage, filteredRows);
});


    // Dropdown change event
    rowsPerPageSelect.addEventListener('change', function () {
        rowsPerPage = parseInt(this.value);
        setupPagination();
        displayRows(1); // Reset to the first page
    });

    // Initial setup
    setupPagination();
    displayRows(currentPage);
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.rbfw_order_view_btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const postId = this.getAttribute('data-post-id');
            const orderDetailsRow = document.getElementById(`order-details-${postId}`);
            const loader = document.getElementById('loader');

            if (orderDetailsRow.style.display === 'none') {
                // Show the loader
                loader.style.display = 'flex';

                // Make an AJAX request to fetch the order details
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'fetch_order_details',
                        post_id: postId,
                    })
                })
                .then(response => response.text())
                .then(data => {
                    orderDetailsRow.querySelector('.order-details-content').innerHTML = data;
                    orderDetailsRow.style.display = 'table-row';
                    // Hide the loader
                    loader.style.display = 'none';
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Hide the loader in case of an error
                    loader.style.display = 'none';
                });
            } else {
                orderDetailsRow.style.display = 'none';
            }
        });
    });
});

</script>


			<?php
		}
	}
	new RBFWOrderPage();
}

