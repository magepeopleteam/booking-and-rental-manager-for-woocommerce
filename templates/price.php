<div class="item rbfw_bikecarmd_price_summary">
    <div class="item-content rbfw-costing">
        <ul class="rbfw-ul">
            <li class="duration-costing rbfw-cond">Duration Cost
                <span class="price-figure" data-price="<?php echo $duration_price ?>">
                    <?php echo wc_price($duration_price); ?>
                </span>
            </li>
            <li class="resource-costing rbfw-cond">Resource Cost
                <span class="price-figure" data-price="<?php echo $service_cost ?>">
                    <?php echo wc_price($service_cost) ?>
                </span>
            </li>
            <li class="subtotal">Subtotal
                <span class="price-figure" data-price="<?php echo $duration_price+$service_cost ?>">
                    <?php echo wc_price($duration_price+$service_cost) ?>
                </span>
            </li>
            <li class="discount">Discount<span>15%</span></li>
            <li class="total">
                <strong>Total</strong>
                <span class="price-figure" data-price="1190">
                    <span class="woocommerce-Price-amount amount">
                        <bdi><span class="woocommerce-Price-currencySymbol">$</span>1,190.00</bdi>
                    </span>
                </span>
            </li>
        </ul>
        <span class="rbfw-loader"><i class="fas fa-spinner fa-spin"></i></span>
    </div>
</div>