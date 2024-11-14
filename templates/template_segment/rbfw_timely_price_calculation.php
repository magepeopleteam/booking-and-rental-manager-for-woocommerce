<?php
global $rbfw;
if(isset($_POST['post_id'])) {
    $id = $_POST['post_id'];

    $es_service_price = $_POST['es_service_price'];
    $duration_cost = $_POST['duration_price'];
}
?>


    <div class="item-content rbfw-costing">
        <ul class="rbfw-ul">
            <li class="duration-costing rbfw-cond">
                <?php echo $rbfw->get_option_trans('rbfw_text_duration_cost', 'rbfw_basic_translation_settings', __('Duration Cost','booking-and-rental-manager-for-woocommerce')) ?>
                <?php echo wc_price((int)$duration_cost) ?>
            </li>

                <li class="resource-costing rbfw-cond">
                    <?php echo $rbfw->get_option_trans('rbfw_text_resource_cost', 'rbfw_basic_translation_settings', __('Resource Cost','booking-and-rental-manager-for-woocommerce')) ?>
                    <?php echo wc_price((int)$es_service_price) ?>
                </li>

            <li class="subtotal">
                <?php echo $rbfw->get_option_trans('rbfw_text_subtotal', 'rbfw_basic_translation_settings', __('Subtotal','booking-and-rental-manager-for-woocommerce')) ?>
                <?php echo wc_price((int)$duration_cost + (int)$es_service_price) ?>
            </li>
            <li class="total">
                <strong><?php echo $rbfw->get_option_trans('rbfw_text_total', 'rbfw_basic_translation_settings', __('Total','booking-and-rental-manager-for-woocommerce')) ?></strong>
                <?php echo wc_price((int)$duration_cost + (int)$es_service_price) ?>
            </li>
        </ul>
        <span class="rbfw-loader"><i class="fas fa-spinner fa-spin"></i></span>
    </div>




