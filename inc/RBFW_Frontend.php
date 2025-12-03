<?php
/**
 * @author shahadat <raselsha@gmail.com>
 * @version 2.0.5
 * @since 1.0.0
 */
	if ( ! defined( 'ABSPATH' ) ) die;
		
	if ( ! class_exists( 'RBFW_Frontend' ) ) {
		class RBFW_Frontend {
			
			public function __construct() {
				add_filter( 'single_template', array( $this, 'single_template' ) );	
				add_action( 'booking_form_header', array( $this, 'booking_form_header' ) );	
				add_action( 'rbfw_product_feature_lists',[$this,'feature_lists']);			
			}

			public function single_template($single_template) {
				global $post;
				if ( $post->post_type && $post->post_type == RBFW_Function::get_cpt_name() ){ 
					$single_template = RBFW_Function::get_template_path('single/single-rbfw.php');
				}
				return $single_template;
			}

			public function booking_form_header($post_id) {
				$sub_title = get_post_meta($post_id , 'rbfw_item_sub_title', true);
				?>
					<div class="rbfw-booking-header">
						<h1><?php the_title(); ?></h1>
						<p class="sub-title"><?php echo esc_html($sub_title); ?></p>
					</div>
				<?php
			}

			public static function load_template($post_id) {
				$rent_type_template = RBFW_Frontend::get_rent_type_template($post_id);
				$template_name = RBFW_Frontend::get_template_name($post_id);

				$template_path = 'single/'. $template_name .'/'.$rent_type_template.'.php';
				$template_path = RBFW_Function::get_template_path($template_path);
				include( $template_path );
			}

			public static function get_template_name($post_id) {
				$template_name = !empty(get_post_meta($post_id, 'rbfw_single_template', true)) ? get_post_meta($post_id, 'rbfw_single_template', true) : 'Default';
				$template_name = strtolower($template_name);
				return $template_name;
			}

			public static function get_rent_type($post_id) {
				$rent_type = !empty(get_post_meta( $post_id, 'rbfw_item_type', true )) ? get_post_meta( $post_id, 'rbfw_item_type', true ) : 'bike_car_sd';
				return $rent_type;
			}


			public static function get_rent_type_template($post_id) {

				$rent_type = RBFW_Frontend::get_rent_type($post_id);

                switch($rent_type){
                    case 'bike_car_sd':
                        case 'appointment':
                            $file_name = 'single-day';
                            break;
                            case 'bike_car_md':
                                case 'equipment':
                                    case 'dress':
                                        case 'others':
                                            $file_name = 'multi-day';
                                            break;
                                            case 'resort':
                                                $file_name = 'resort';
                                                break;
                                                case 'multiple_items':
                                                    $file_name = 'multiple-items';
                                                    break;
                                                    default:
                                                        $file_name = 'multi-day';
                }
                return $file_name;
			}

			public function feature_lists($post_id){
				$rbfw_feature_category = rbfw_get_feature_category_meta( $post_id );
				?>
				<div class="rbfw-features" >
					<div class="rbfw-single-left-information-item">
						<?php if ( $rbfw_feature_category ) {
							foreach ( $rbfw_feature_category as $value ) {
								$cat_title = $value['cat_title'];
								$cat_features = $value['cat_features'] ? $value['cat_features'] : [];
								if($cat_title):
								?>
								<h3 class="rbfw-sub-heading"><?php echo esc_html($cat_title); ?></h3>
								<?php endif; ?>
								<ul class="rbfw-feature-lists">
									<?php
									if(!empty($cat_features)){
										
										foreach ($cat_features as $features){
											$icon = !empty($features['icon']) ? $features['icon'] : 'fas fa-check-circle';
											$title = $features['title'];
											if($title){ ?>
												<li>
													<span><i class="<?php echo esc_attr($icon); ?>"></i><?php echo esc_html($title); ?></span>
												</li>
												<?php
											}
										}
									}
									?>
								</ul>
								<?php
							}
						}
						?>
					</div>
				</div>
				<?php
			}

            public static function count_array_dimensions($array) {
                if (is_array($array)) {
                    $maxDepth = 0;
                    foreach ($array as $value) {
                        $depth = RBFW_Frontend::count_array_dimensions($value);
                        if ($depth > $maxDepth) {
                            $maxDepth = $depth;
                        }
                    }
                    return $maxDepth + 1;
                } else {
                    return 0;
                }
            }


		}
		new RBFW_Frontend();

	}
