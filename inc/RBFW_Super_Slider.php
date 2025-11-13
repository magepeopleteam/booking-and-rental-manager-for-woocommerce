<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MP_SUPER_SLIDER' ) ) {
		class MP_SUPER_SLIDER {
			public function __construct() {
				add_filter( 'rbfw_settings_sec_reg', array( $this, 'slider_tab_name' ), 20 );
				add_filter( 'rbfw_settings_sec_fields', array( $this, 'slider_settings' ), 10, 1 );
				add_action( 'rbfw_slider', array( $this, 'super_slider' ), 10, 2 );
				add_action( 'rbfw_slider_only', array( $this, 'super_slider_only' ), 10, 1 );
				add_action( 'rbfw_slider_icon_indicator', array( $this, 'icon_indicator' ), 10 );
			}

			public function super_slider( $post_id = '', $meta_key = '' ) {
				$type      = RBFW_Function::get_settings( 'super_slider_type', 'super_slider_settings', 'slider' );
				$post_id   = $post_id > 0 ? $post_id : get_the_id();
				$image_ids = $this->get_slider_ids( $post_id, $meta_key );
				if ( is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					if ( $type == 'slider' && sizeof( $image_ids ) > 1 ) {
						$this->slider( $post_id, $image_ids );
					} else {
						$this->post_thumbnail( $image_ids[0] );
					}
				} else {
					$this->post_thumbnail();
				}
			}

			public function super_slider_only( $image_ids ) {
				if ( is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					?>
                    <div class="superSlider placeholder_area">
						<?php $this->slider_all_item( $image_ids ); ?>
                    </div>
					<?php
				}
			}

			public function slider( $post_id, $image_ids ) {
				if ( is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					$showcase_position = RBFW_Function::get_settings( 'super_slider_showcase_position', 'super_slider_settings', 'right' );
					$column_class      = $showcase_position == 'top' || $showcase_position == 'bottom' ? 'area_column' : '';
					$slider_style      = RBFW_Function::get_settings( 'super_slider_style', 'super_slider_settings', 'style_1' );
					?>
                    <div class="superSlider placeholder_area fdColumn">
                        <input type="hidden" name="slider_height_type" value="<?php echo esc_attr( RBFW_Function::get_settings( 'slider_height', 'super_slider_settings', 'avg' ) ); ?>"/>
                        <div class="dFlex  <?php echo esc_attr( $column_class ); ?>">
							<?php
								if ( $showcase_position == 'top' || $showcase_position == 'left' ) {
									$this->slider_showcase( $image_ids );
								}
								$this->slider_all_item( $image_ids );
								if ( $showcase_position == 'bottom' || $showcase_position == 'right' ) {
									$this->slider_showcase( $image_ids );
								}
								if ( $slider_style == 'style_2' ) {
									?>
                                    <div class="abTopLeft">
                                        <button type="button" class="_dButton_bgWhite_textDefault" data-target-popup="superSlider" data-slide-index="1">
											<?php echo esc_html__( 'View All', 'service-booking-manager' ) . ' ' . esc_html( sizeof( $image_ids ) ) . ' ' . esc_html__( 'Images', 'service-booking-manager' ); ?>
                                        </button>
                                    </div>
									<?php
								}
							?>
                        </div>
						<?php
							$slider_indicator = RBFW_Function::get_settings( 'super_slider_indicator_visible', 'super_slider_settings', 'on' );
							$icon             = RBFW_Function::get_settings( 'super_slider_indicator_type', 'super_slider_settings', 'icon' );
							if ( $slider_indicator == 'on' && $icon == 'image' ) {
								$this->image_indicator( $image_ids );
							}
						?>
						<?php $this->slider_popup( $post_id, $image_ids ); ?>
                    </div>
					<?php
				}
			}

			public function post_thumbnail( $image_id = '' ) {
				$thumbnail = RBFW_Function::get_image_url( '', $image_id );
				if ( $thumbnail ) {
					?>
                    <div class="superSlider">
                        <div data-bg-image="<?php echo esc_html( $thumbnail ); ?>"></div>
                    </div>
					<?php
				}
			}

			public function slider_all_item( $image_ids, $popup_slider_icon = '' ) {
				if ( is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					?>
                    <div class="sliderAllItem">
						<?php
							$count = 1;
							foreach ( $image_ids as $id ) {
								$image_url = RBFW_Function::get_image_url( '', $id, 'large' );
								if ( $image_url ) {
									$image_url = $image_url ?: RBFW_PLUGIN_URL . '/assets/images/no_image.png';
									$size      = getimagesize( $image_url );
									$width     = 0;
									$height    = 0;
									if ( $size ) {
										$width  = $size[0];
										$height = $size[1];
									}
									?>
                                    <div class="sliderItem" data-slide-index="<?php echo esc_html( $count ); ?>" data-target-popup="superSlider" data-placeholder>
                                        <div data-bg-image="<?php echo esc_html( $image_url ); ?>" data-width="<?php echo esc_html( $width ); ?>" data-height="<?php echo esc_html( $height ); ?>"></div>
                                    </div>
									<?php
									$count ++;
								}
							}
						?>
						<?php
							$icon = RBFW_Function::get_settings( 'super_slider_indicator_type', 'super_slider_settings', 'icon' );
							if ( ( $icon == 'icon' || $popup_slider_icon == 'on' ) && sizeof( $image_ids ) > 1 ) {
								$this->icon_indicator( $popup_slider_icon );
							}
						?>
                    </div>
					<?php
				}
			}

			public function slider_showcase( $image_ids ) {
				$showcase = RBFW_Function::get_settings( 'super_slider_showcase_visible', 'super_slider_settings', 'on' );
				if ( $showcase == 'on' && is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					$showcase_position = RBFW_Function::get_settings( 'super_slider_showcase_position', 'super_slider_settings', 'right' );
					$slider_style      = RBFW_Function::get_settings( 'super_slider_style', 'super_slider_settings', 'style_1' );
					?>
                    <div class="sliderShowcase <?php echo esc_attr( $showcase_position . ' ' . $slider_style ); ?>">
						<?php
							if ( $slider_style == 'style_1' ) {
								$this->slider_showcase_style_1( $image_ids );
							} else {
								$this->slider_showcase_style_2( $image_ids );
							}
						?>
                    </div>
					<?php
				}
			}

			public function slider_showcase_style_1( $image_ids ) {
				$count = 1;
				foreach ( $image_ids as $id ) {
					$image_url = RBFW_Function::get_image_url( '', $id );
					if ( $count < 4 ) {
						?>
                        <div class="sliderShowcaseItem" data-slide-target="<?php echo esc_html( $count ); ?>" data-placeholder>
                            <div data-bg-image="<?php echo esc_html( $image_url ); ?>"></div>
                        </div>
						<?php
					}
					if ( $count == 4 ) {
						?>
                        <div class="sliderShowcaseItem" data-target-popup="superSlider" data-placeholder>
                            <div data-bg-image="<?php echo esc_html( $image_url ); ?>"></div>
                            <div class="sliderMoreItem">
                                <span class="fas fa-plus"></span>
								<?php echo esc_html( sizeof( $image_ids ) - 4 ); ?>
                                <span class="far fa-image"></span>
                            </div>
                        </div>
						<?php
					}
					$count ++;
				}
			}

			public function slider_showcase_style_2( $image_ids ) {
				$count = 1;
				foreach ( $image_ids as $id ) {
					$image_url = RBFW_Function::get_image_url( '', $id );
					if ( $count > 1 && $count < 5 ) {
						?>
                        <div class="sliderShowcaseItem" data-target-popup="superSlider" data-slide-index="<?php echo esc_html( $count ); ?>" data-placeholder>
                            <div data-bg-image="<?php echo esc_html( $image_url ); ?>"></div>
                        </div>
						<?php
					}
					$count ++;
				}
			}

			public function image_indicator( $image_ids ) {
				if ( is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					?>
                    <div class="slideIndicator">
						<?php
							$count = 1;
							foreach ( $image_ids as $id ) {
								$image_url = RBFW_Function::get_image_url( '', $id, array( 150, 100 ) );
								?>
                                <div class="slideIndicatorItem" data-slide-target="<?php echo esc_html( $count ); ?>">
                                    <div data-bg-image="<?php echo esc_html( $image_url ); ?>"></div>
                                </div>
								<?php
								$count ++;
							}
						?>
                    </div>
					<?php
				}
			}

			public function icon_indicator( $popup_slider_icon = '' ) {
				$slider_indicator = RBFW_Function::get_settings( 'super_slider_indicator_visible', 'super_slider_settings', 'on' );
				if ( $slider_indicator == 'on' || $popup_slider_icon == 'on' ) {
					?>
                    <div class="iconIndicator prevItem">
                        <span class="fas fa-chevron-circle-left"></span>
                    </div>
                    <div class="iconIndicator nextItem">
                        <span class="fas fa-chevron-circle-right"></span>
                    </div>
					<?php
				}
			}

			public function slider_popup( $post_id, $image_ids ) {
				if ( is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					$popup_icon_indicator = RBFW_Function::get_settings( 'super_slider_popup_icon_indicator', 'super_slider_settings', 'on' );
					?>
                    <div class="sliderPopup" data-popup="superSlider">
                        <div class="superSlider">
                            <div class="popupHeader">
                                <h2><?php echo wp_kses_post( get_the_title( $post_id ) ); ?></h2>
                                <span class="fas fa-times popupClose"></span>
                            </div>
                            <div class="popupBody">
								<?php $this->slider_all_item( $image_ids, $popup_icon_indicator ); ?>
                            </div>
                            <div class="popupFooter">
								<?php
									$indicator = RBFW_Function::get_settings( 'super_slider_popup_image_indicator', 'super_slider_settings', 'on' );
									if ( $indicator == 'on' ) {
										$this->image_indicator( $image_ids );
									}
								?>
                            </div>
                        </div>
                    </div>
					<?php
				}
			}

			public function get_slider_ids( $post_id, $key ) {
				$thumb_id  = get_post_thumbnail_id( $post_id );
				$image_ids = RBFW_Function::get_post_info( $post_id, $key, array() );
				if ( $thumb_id ) {
					array_unshift( $image_ids, $thumb_id );
				}

				return array_unique( $image_ids );
			}

			//==============//
			public function slider_tab_name( $default_sec ): array {
				$sections = array(
					array(
						'id'    => 'super_slider_settings',
						'title' => '<i class="fas fa-sliders"></i>' . esc_html__( 'Super Slider Settings', 'booking-and-rental-manager-for-woocommerce' )
					)
				);

				return array_merge( $default_sec, $sections );
			}

			public function slider_settings( $default_fields ): array {
				$settings_fields = array(
					'super_slider_settings' => array(
						array(
							'name'    => 'super_slider_type',
							'label'   => esc_html__( 'Slider Type', 'booking-and-rental-manager-for-woocommerce' ),
							'desc'    => esc_html__( 'Please Select Slider Type Default Slider', 'booking-and-rental-manager-for-woocommerce' ),
							'type'    => 'select',
							'default' => 'slider',
							'options' => array(
								'slider'       => esc_html__( 'Slider', 'booking-and-rental-manager-for-woocommerce' ),
								'single_image' => esc_html__( 'Post Thumbnail', 'booking-and-rental-manager-for-woocommerce' )
							)
						),
						array(
							'name'    => 'super_slider_style',
							'label'   => esc_html__( 'Slider Style', 'booking-and-rental-manager-for-woocommerce' ),
							'desc'    => esc_html__( 'Please Select Slider Style Default Style One', 'booking-and-rental-manager-for-woocommerce' ),
							'type'    => 'select',
							'default' => 'style_1',
							'options' => array(
								'style_1' => esc_html__( 'Style One', 'booking-and-rental-manager-for-woocommerce' ),
								'style_2' => esc_html__( 'Style Two', 'booking-and-rental-manager-for-woocommerce' ),
							)
						),
						array(
							'name'    => 'super_slider_indicator_visible',
							'label'   => esc_html__( 'Slider Indicator Visible?', 'booking-and-rental-manager-for-woocommerce' ),
							'desc'    => esc_html__( 'Please Select Slider Indicator Visible or Not? Default ON', 'booking-and-rental-manager-for-woocommerce' ),
							'type'    => 'select',
							'default' => 'on',
							'options' => array(
								'on'  => esc_html__( 'ON', 'booking-and-rental-manager-for-woocommerce' ),
								'off' => esc_html__( 'Off', 'booking-and-rental-manager-for-woocommerce' )
							)
						),
						array(
							'name'    => 'super_slider_indicator_type',
							'label'   => esc_html__( 'Slider Indicator Type', 'booking-and-rental-manager-for-woocommerce' ),
							'desc'    => esc_html__( 'Please Select Slider Indicator Type Default Icon', 'booking-and-rental-manager-for-woocommerce' ),
							'type'    => 'select',
							'default' => 'icon',
							'options' => array(
								'icon'  => esc_html__( 'Icon Indicator', 'booking-and-rental-manager-for-woocommerce' ),
								'image' => esc_html__( 'image Indicator', 'booking-and-rental-manager-for-woocommerce' )
							)
						),
						array(
							'name'    => 'super_slider_showcase_visible',
							'label'   => esc_html__( 'Slider Showcase Visible?', 'booking-and-rental-manager-for-woocommerce' ),
							'desc'    => esc_html__( 'Please Select Slider Showcase Visible or Not? Default ON', 'booking-and-rental-manager-for-woocommerce' ),
							'type'    => 'select',
							'default' => 'on',
							'options' => array(
								'on'  => esc_html__( 'ON', 'booking-and-rental-manager-for-woocommerce' ),
								'off' => esc_html__( 'Off', 'booking-and-rental-manager-for-woocommerce' )
							)
						),
						array(
							'name'    => 'super_slider_showcase_position',
							'label'   => esc_html__( 'Slider Showcase Position', 'booking-and-rental-manager-for-woocommerce' ),
							'desc'    => esc_html__( 'Please Select Slider Showcase Position Default Right', 'booking-and-rental-manager-for-woocommerce' ),
							'type'    => 'select',
							'default' => 'right',
							'options' => array(
								'top'    => esc_html__( 'At Top Position', 'booking-and-rental-manager-for-woocommerce' ),
								'right'  => esc_html__( 'At Right Position', 'booking-and-rental-manager-for-woocommerce' ),
								'bottom' => esc_html__( 'At Bottom Position', 'booking-and-rental-manager-for-woocommerce' ),
								'left'   => esc_html__( 'At Left Position', 'booking-and-rental-manager-for-woocommerce' )
							)
						),
						array(
							'name'    => 'super_slider_popup_image_indicator',
							'label'   => esc_html__( 'Slider Popup Image Indicator', 'booking-and-rental-manager-for-woocommerce' ),
							'desc'    => esc_html__( 'Please Select Slider Popup Indicator Image ON or Off? Default ON', 'booking-and-rental-manager-for-woocommerce' ),
							'type'    => 'select',
							'default' => 'on',
							'options' => array(
								'on'  => esc_html__( 'ON', 'booking-and-rental-manager-for-woocommerce' ),
								'off' => esc_html__( 'Off', 'booking-and-rental-manager-for-woocommerce' )
							)
						),
						array(
							'name'    => 'super_slider_popup_icon_indicator',
							'label'   => esc_html__( 'Slider Popup Icon Indicator', 'booking-and-rental-manager-for-woocommerce' ),
							'desc'    => esc_html__( 'Please Select Slider Popup Indicator Icon ON or Off? Default ON', 'booking-and-rental-manager-for-woocommerce' ),
							'type'    => 'select',
							'default' => 'on',
							'options' => array(
								'on'  => esc_html__( 'ON', 'booking-and-rental-manager-for-woocommerce' ),
								'off' => esc_html__( 'Off', 'booking-and-rental-manager-for-woocommerce' )
							)
						),
						array(
							'name'    => 'slider_height',
							'label'   => esc_html__( 'Slider height', 'booking-and-rental-manager-for-woocommerce' ),
							'desc'    => esc_html__( 'Please Select Slider Height', 'booking-and-rental-manager-for-woocommerce' ),
							'type'    => 'select',
							'default' => 'avg',
							'options' => array(
								'min' => esc_html__( 'Minimum', 'booking-and-rental-manager-for-woocommerce' ),
								'avg' => esc_html__( 'Average', 'booking-and-rental-manager-for-woocommerce' ),
								'max' => esc_html__( 'Maximum', 'booking-and-rental-manager-for-woocommerce' )
							)
						)
					)
				);

				return array_merge( $default_fields, $settings_fields );
			}
		}
		new MP_SUPER_SLIDER();
	}