<div class="rbfw-swiper">
    <div class="swiper-wrapper">
        <?php 
        
            print_r($gallery_images);
            foreach($gallery_images as $key => $value):?>
            <div class="swiper-slide">
                <img src="<?php  echo wp_get_attachment_url($value ); ?>" />
            </div>
        <?php endforeach; ?>
    </div>
    <div class="swiper-navigation">
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
</div>
<div class="rbfw-swiper-thumbnail">
    <div class="swiper-wrapper">
        <?php 
            foreach($gallery_images as $key => $value):?>
            <div class="swiper-slide">
                <img src="<?php  echo wp_get_attachment_url($value ); ?>" />
            </div>
        <?php endforeach; ?>
    </div>
</div>
