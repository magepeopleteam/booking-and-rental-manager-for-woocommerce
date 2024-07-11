<div class="feature-lists">
    <?php if ( $rbfw_feature_category ) :
        foreach ( $rbfw_feature_category as $value ) :
            $cat_title = $value['cat_title'];
            $cat_features = $value['cat_features'] ? $value['cat_features'] : [];
        ?>
        <h2 class="feature-title">
            <?php echo esc_html($cat_title); ?>
            <div class="devider"></div>
        </h2>
        <div class="feature-items">
            <?php
            if(!empty($cat_features)):
                $i = 1;
                foreach ($cat_features as $features):
                    $icon = !empty($features['icon']) ? $features['icon'] : 'fas fa-check-circle';
                    $title = $features['title'];
                    if($title):?>
                        <div class="item">
                            <i class="<?php echo esc_attr(mep_esc_html($icon)); ?>"></i>
                            <h2><?php echo mep_esc_html($title); ?></h2>
                        </div>
                    <?php
                    endif;
                    $i++;
                endforeach;
            endif;
            ?>
        </div>
    <?php  endforeach; endif; ?>
</div>