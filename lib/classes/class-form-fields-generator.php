<?php
if ( ! defined('ABSPATH')) exit;  // if direct access


/*Input fields
*  Text
*  Select
*  Checkbox
*  Checkbox Multi
*  Radio
*  Textarea
*  Number
*  Hidden
*  Range
*  Color
*  Email
*  URL
*  Tel
*  Search
*  Month
*  Week
*  Date
*  Time
*  Submit
 *
 *
*  Text multi
*  Select multi
*  Select2
*  Range with input
*  Color picker
*  Datepicker
*  Media
*  Media Gallery
*  Switcher
*  Switch
*  Switch multi
*  Switch image
*  Dimensions (width, height, custom)
*  WP Editor
*  Code Editor
*  Link Color
*  Repeatable
*  Icon
*  Icon multi
*  Date format
*  Time format
*  FAQ
*  Grid
*  Custom_html
*  Color palette
*  Color palette multi
 * Color set
*  User select
*  Color picker multi
*  Google reCaptcha
*  Nonce
*  Border
*  Margin
*  Padding
*  Google Map
*  Image Select
 *
 *
 * Background
 *
 * Typography
 * Spinner


*/






if( ! class_exists( 'RbfwFormFieldsGenerator' ) ) {

    class RbfwFormFieldsGenerator {




        public function field_post_objects( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $sortable 	    = isset( $option['sortable'] ) ? $option['sortable'] : true;
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $args 	        = isset( $option['args'] ) ? $option['args'] : array();

            $values 	    = !empty( $option['value'] ) ? $option['value'] : array();
            $values         = !empty($values) ? $values : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;

            if(!empty($values)):

                foreach ($values as $value):
                    $values_sort[$value] = $value;
                endforeach;
                $args = array_replace($values_sort, $args);
            endif;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';
            endif;
            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field';
                    ?> field-wrapper field-post-objects-wrapper field-post-objects-wrapper-<?php echo esc_attr($field_id); ?>">
                <div class="field-list <?php if($sortable){ echo 'sortable'; }?>" id="<?php echo esc_attr($field_id); ?>">
                    <?php
                    if(!empty($args)):
                        foreach ($args as $argsKey=>$arg):
                            ?>
                            <div class="item">
                                <?php if($sortable):?>
                                    <span class="ppof-button sort"><i class="fas fa-arrows-alt"></i></span>
                                <?php endif; ?>
                                <label>
                                    <input type="checkbox" <?php if(in_array($argsKey,$values)) echo 'checked';?>  value="<?php
                                    echo esc_attr($argsKey); ?>" name="<?php echo esc_attr($field_name); ?>[]">
                                    <span><?php echo esc_attr($arg); ?></span>
                                </label>
                            </div>
                        <?php
                        endforeach;
                    endif;
                    ?>
                </div>
                <div class="error-mgs"></div>

            </div>
            <?php
            return ob_get_clean();
        }



        public function field_add_to_cart_shortcode( $option ){
            global $post;
            $post_id = $post->ID;
            ob_start();
            ?>
            <code class="rbfw_add_to_cart_shortcode_code">[rent-add-to-cart  id='<?php echo $post_id; ?>']</code>

            <?php
            return ob_get_clean();
        }

        public function field_rbfw_add_category( $option ){
            global $post;
            $post_id = $post->ID;
            ob_start();
            ?>
            <section class="d-flex justify-content-between w-50">
                <div class="d-flex justify-content-between align-items-center">

                    <div class="d-flex justify-content-between align-items-center" style="width: 700px">
                        <?php
                        $rbfw_categories = get_post_meta($post_id,'rbfw_categories',true) ? maybe_unserialize(get_post_meta($post_id, 'rbfw_categories', true)) : [];
                        ?>

                        <select name="rbfw_categories[]" multiple class="category2">
                            <?php
                            $terms = get_terms( array(
                                'taxonomy'   => 'rbfw_item_caregory',
                                'hide_empty' => false,
                            ) );
                            foreach ( $terms as $key => $value ) {
                                ?>
                                <option <?php echo (in_array($value->name,$rbfw_categories))?'selected':'' ?> value="<?php echo $value->name ?>"> <?php echo $value->name ?> </option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>

                </div>
            </section>

            <?php
            return ob_get_clean();
        }

        public function field_switcher( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 		= isset( $option['default'] ) ? $option['default'] : '';
            $args 	        = isset( $option['args'] ) ? $option['args'] : "";
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $checked = !empty($value) ? 'checked':'';
            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-switcher-wrapper
            field-switcher-wrapper-<?php echo esc_attr($id); ?>">
                <label class="switcher <?php echo esc_attr($checked); ?>">
                    <input type="checkbox" id="<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($value); ?>"
                           name="<?php echo esc_attr($field_name); ?>" <?php echo esc_attr($checked); ?>>
                    <span class="layer"></span>
                    <span class="slider"></span>
                    <?php
                    if(!empty($args))
                    foreach ($args as $index=>$arg):
                        ?>
                        <span  unselectable="on" class="switcher-text <?php echo esc_attr($index); ?>"><?php echo esc_html($arg);
                        ?></span>
                    <?php
                    endforeach;
                    ?>
                </label>
                <div class="error-mgs"></div>
            </div>




            <?php
            return ob_get_clean();
        }




        public function field_google_map( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $args 	        = isset( $option['args'] ) ? $option['args'] : "";
            $preview 	        = isset( $option['preview'] ) ? $option['preview'] : false;
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            $lat  = isset($values['lat']) ? $values['lat'] : '';
            $lng   = isset($values['lng']) ? $values['lng'] :'';
            $zoom  = isset($values['zoom']) ? $values['zoom'] : '';
            $title  = isset($values['title']) ? $values['title'] : '';
            $apikey  = isset($values['apikey']) ? $values['apikey'] : '';


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            if(!empty($args)):
                ?>

                <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                        id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-google-map-wrapper
                field-google-map-wrapper-<?php echo esc_attr($id); ?>">
                    <div class="item-list">
                        <?php
                        foreach ($args as $index=>$name):
                            ?>
                            <div class="item">
                                <span class="field-title"><?php echo esc_html($name); ?></span>
                                <span class="input-wrapper"><input type='text' name='<?php echo esc_attr($field_name);?>[<?php
                                    echo esc_attr($index); ?>]' value='<?php
                                    echo esc_attr($values[$index]); ?>' /></span>
                            </div>
                        <?php
                        endforeach;
                        ?>
                    </div>
                    <div class="error-mgs"></div>
                </div>

                <?php
                if($preview):
                    ?>
                    <div id="map-<?php echo esc_attr($field_id); ?>"></div>
                    <script>
                        function initMap() {
                            var myLatLng = {lat: <?php echo esc_html($lat); ?>, lng: <?php echo esc_html($lng); ?>};
                            var map = new google.maps.Map(document.getElementById('map-<?php echo esc_html($field_id); ?>'), {
                                zoom: <?php echo esc_html($zoom); ?>,
                                center: myLatLng
                            });
                            var marker = new google.maps.Marker({
                                position: myLatLng,
                                map: map,
                                title: '<?php echo esc_html($title); ?>'
                            });
                        }
                    </script>
                    <script async defer
                            src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_html($apikey); ?>&callback=initMap">
                    </script>
                    <?php
                endif;
            endif;
            return ob_get_clean();
        }



        public function field_border( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;

            $width  = $values['width'];
            $unit   = $values['unit'];
            $style  = $values['style'];
            $color  = $values['color'];



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-border-wrapper
            field-border-wrapper-<?php echo esc_attr($id); ?>">
                <div class="item-list">
                        <div class="item">
                            <span class="field-title">Width</span>
                            <span class="input-wrapper">
                                <input type='number' name='<?php echo esc_attr($field_name);?>[width]' value='<?php  echo esc_attr($width); ?>' />
                            </span>
                            <select name="<?php echo esc_attr($field_name);?>[unit]">
                                <option <?php if($unit == 'px') echo 'selected'; ?> value="px">px</option>
                                <option <?php if($unit == '%') echo 'selected'; ?> value="%">%</option>
                                <option <?php if($unit == 'em') echo 'selected'; ?> value="em">em</option>
                                <option <?php if($unit == 'cm') echo 'selected'; ?> value="cm">cm</option>
                                <option <?php if($unit == 'mm') echo 'selected'; ?> value="mm">mm</option>
                                <option <?php if($unit == 'in') echo 'selected'; ?> value="in">in</option>
                                <option <?php if($unit == 'pt') echo 'selected'; ?> value="pt">pt</option>
                                <option <?php if($unit == 'pc') echo 'selected'; ?> value="pc">pc</option>
                                <option <?php if($unit == 'ex') echo 'selected'; ?> value="ex">ex</option>
                            </select>
                        </div>
                        <div class="item">
                            <span class="field-title">Style</span>
                            <select name="<?php echo esc_attr($field_name);?>[style]">
                                <option <?php if($style == 'dotted') echo 'selected'; ?> value="dotted">dotted</option>
                                <option <?php if($style == 'dashed') echo 'selected'; ?> value="dashed">dashed</option>
                                <option <?php if($style == 'solid') echo 'selected'; ?> value="solid">solid</option>
                                <option <?php if($style == 'double') echo 'selected'; ?> value="double">double</option>
                                <option <?php if($style == 'groove') echo 'selected'; ?> value="groove">groove</option>
                                <option <?php if($style == 'ridge') echo 'selected'; ?> value="ridge">ridge</option>
                                <option <?php if($style == 'inset') echo 'selected'; ?> value="inset">inset</option>
                                <option <?php if($style == 'outset') echo 'selected'; ?> value="outset">outset</option>
                                <option <?php if($style == 'none') echo 'selected'; ?> value="none">none</option>
                            </select>
                        </div>
                    <div class="item">
                        <span class="field-title">Color</span>
                        <span class="input-wrapper"><input class="colorpicker" type='text' name='<?php echo esc_attr($field_name);
                        ?>[color]' value='<?php echo esc_attr($color); ?>' /></span>
                    </div>
                </div>
                <div class="error-mgs"></div>
            </div>


            <?php
            return ob_get_clean();
        }



        public function field_dimensions( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $args 	        = isset( $option['args'] ) ? $option['args'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : array();
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            if(!empty($args)):
                ?>
                <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-margin-wrapper
                field-margin-wrapper-<?php echo esc_attr($id); ?>">
                    <div class="item-list">
                        <?php
                        foreach ($args as $index=>$arg):
                            $name = $arg['name'];
                            $unit = $values[$index]['unit'];
                            ?>
                            <div class="item">
                                <span class="field-title"><?php echo esc_html($name); ?></span>
                                <span class="input-wrapper"><input type='number' name='<?php echo esc_attr($field_name);?>[<?php
                                    echo esc_attr($index); ?>][val]' value='<?php
                                    echo esc_attr($values[$index]['val']); ?>' /></span>
                                <select name="<?php echo esc_attr($field_name);?>[<?php echo esc_attr($index); ?>][unit]">
                                    <option <?php if($unit == 'px') echo 'selected'; ?> value="px">px</option>
                                    <option <?php if($unit == '%') echo 'selected'; ?> value="%">%</option>
                                    <option <?php if($unit == 'em') echo 'selected'; ?> value="em">em</option>
                                    <option <?php if($unit == 'cm') echo 'selected'; ?> value="cm">cm</option>
                                    <option <?php if($unit == 'mm') echo 'selected'; ?> value="mm">mm</option>
                                    <option <?php if($unit == 'in') echo 'selected'; ?> value="in">in</option>
                                    <option <?php if($unit == 'pt') echo 'selected'; ?> value="pt">pt</option>
                                    <option <?php if($unit == 'pc') echo 'selected'; ?> value="pc">pc</option>
                                    <option <?php if($unit == 'ex') echo 'selected'; ?> value="ex">ex</option>
                                </select>
                            </div>
                        <?php
                        endforeach;
                        ?>
                    </div>
                    <div class="error-mgs"></div>
                </div>

            <?php
            endif;
            return ob_get_clean();
        }



        public function field_padding( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $args 	        = isset( $option['args'] ) ? $option['args'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : array();
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            if(!empty($args)):
                ?>
                <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-padding-wrapper
                field-padding-wrapper-<?php echo esc_attr($id); ?>">
                    <label><input type="checkbox" class="change-together">Apply for all</label>
                    <div class="item-list">
                        <?php
                        foreach ($args as $index=>$arg):
                            $name = $arg['name'];
                            $unit = $values[$index]['unit'];
                            ?>
                            <div class="item">
                                <span class="field-title"><?php echo esc_html($name); ?></span>
                                <span class="input-wrapper">
                                    <input type='number' name='<?php echo esc_attr($field_name);?>[<?php echo esc_attr($index); ?>][val]' value='<?php echo esc_attr($values[$index]['val']); ?>' />
                                </span>
                                <select name="<?php echo esc_attr($field_name);?>[<?php echo esc_attr($index); ?>][unit]">
                                    <option <?php if($unit == 'px') echo 'selected'; ?> value="px">px</option>
                                    <option <?php if($unit == '%') echo 'selected'; ?> value="%">%</option>
                                    <option <?php if($unit == 'em') echo 'selected'; ?> value="em">em</option>
                                    <option <?php if($unit == 'cm') echo 'selected'; ?> value="cm">cm</option>
                                    <option <?php if($unit == 'mm') echo 'selected'; ?> value="mm">mm</option>
                                    <option <?php if($unit == 'in') echo 'selected'; ?> value="in">in</option>
                                    <option <?php if($unit == 'pt') echo 'selected'; ?> value="pt">pt</option>
                                    <option <?php if($unit == 'pc') echo 'selected'; ?> value="pc">pc</option>
                                    <option <?php if($unit == 'ex') echo 'selected'; ?> value="ex">ex</option>
                                </select>
                            </div>
                        <?php
                        endforeach;
                        ?>
                    </div>
                    <div class="error-mgs"></div>
                </div>

                <script>
                    jQuery(document).ready(function($) {
                        jQuery(document).on('keyup change', '.field-padding-wrapper-<?php echo esc_attr($id); ?>  input[type="number"]',
                            function() {
                                is_checked = jQuery('.field-padding-wrapper-<?php echo esc_attr($id); ?> .change-together').attr('checked');
                                if(is_checked == 'checked'){
                                    val = jQuery(this).val();
                                    i = 0;
                                    $('.field-padding-wrapper-<?php echo esc_attr($id); ?> input[type="number"]').each(function( index ) {
                                        if(i > 0){
                                            jQuery(this).val(val);
                                        }
                                        i++;
                                    });
                                }
                            })
                        jQuery(document).on('click', '.field-padding-wrapper-<?php echo esc_attr($id); ?> .change-together', function() {
                            is_checked = this.checked;
                            if(is_checked){
                                i = 0;
                                $('.field-padding-wrapper-<?php echo esc_attr($id); ?> input[type="number"]').each(function( index ) {
                                    if(i > 0){
                                        jQuery(this).attr('readonly','readonly');
                                    }
                                    i++;
                                });
                                i = 0;
                                $('.field-padding-wrapper-<?php echo esc_attr($id); ?> select').each(function( index ) {
                                    if(i > 0){
                                        //jQuery(this).attr('disabled','disabled');
                                    }
                                    i++;
                                });
                            }else{
                                jQuery('.field-padding-wrapper-<?php echo esc_attr($id); ?> input[type="number"]').removeAttr('readonly');
                                //jQuery('.field-margin-wrapper-<?php echo esc_attr($id); ?> select').removeAttr('disabled');
                            }
                        })
                    })
                </script>
            <?php
            endif;
            return ob_get_clean();
        }



        public function field_margin( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $args 	        = isset( $option['args'] ) ? $option['args'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : array();
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            if(!empty($args)):
                ?>
                <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-margin-wrapper field-margin-wrapper-<?php echo esc_attr($id); ?>">
                    <label><input type="checkbox" class="change-together">Apply for all</label>
                    <div class="item-list">
                        <?php
                        foreach ($args as $index=>$arg):
                            $name = $arg['name'];
                            $unit = $values[$index]['unit'];
                            ?>
                            <div class="item">
                                <span class="field-title"><?php echo esc_attr($name); ?></span>
                                <span class="input-wrapper">
                                    <input class="<?php echo esc_attr($index); ?>" type='number' name='<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][val]' value='<?php echo esc_attr($values[$index]['val']); ?>' />
                                </span>
                                <select name="<?php echo esc_attr($field_name);?>[<?php echo esc_attr($index); ?>][unit]">
                                    <option <?php if($unit == 'px') echo 'selected'; ?> value="px">px</option>
                                    <option <?php if($unit == '%') echo 'selected'; ?> value="%">%</option>
                                    <option <?php if($unit == 'em') echo 'selected'; ?> value="em">em</option>
                                    <option <?php if($unit == 'cm') echo 'selected'; ?> value="cm">cm</option>
                                    <option <?php if($unit == 'mm') echo 'selected'; ?> value="mm">mm</option>
                                    <option <?php if($unit == 'in') echo 'selected'; ?> value="in">in</option>
                                    <option <?php if($unit == 'pt') echo 'selected'; ?> value="pt">pt</option>
                                    <option <?php if($unit == 'pc') echo 'selected'; ?> value="pc">pc</option>
                                    <option <?php if($unit == 'ex') echo 'selected'; ?> value="ex">ex</option>
                                </select>
                            </div>
                        <?php
                        endforeach;
                        ?>
                    </div>
                    <div class="error-mgs"></div>
                </div>
                <script>
                    jQuery(document).ready(function($) {
                        jQuery(document).on('keyup change', '.field-margin-wrapper-<?php echo esc_attr($id); ?>  input[type="number"]',
                            function() {
                                is_checked = jQuery('.field-margin-wrapper-<?php echo esc_attr($id); ?> .change-together').attr('checked');
                                if(is_checked == 'checked'){
                                    val = jQuery(this).val();
                                    i = 0;
                                    $('.field-margin-wrapper-<?php echo esc_attr($id); ?> input[type="number"]').each(function( index ) {
                                        if(i > 0){
                                            jQuery(this).val(val);
                                        }
                                        i++;
                                    });
                                }
                            })
                        jQuery(document).on('click', '.field-margin-wrapper-<?php echo esc_attr($id); ?> .change-together', function() {
                            is_checked = this.checked;
                            if(is_checked){
                                i = 0;
                                $('.field-margin-wrapper-<?php echo esc_attr($id); ?> input[type="number"]').each(function( index ) {
                                    if(i > 0){
                                        jQuery(this).attr('readonly','readonly');
                                    }
                                    i++;
                                });
                                i = 0;
                                $('.field-margin-wrapper-<?php echo esc_attr($id); ?> select').each(function( index ) {
                                    if(i > 0){
                                        //jQuery(this).attr('disabled','disabled');
                                    }
                                    i++;
                                });
                            }else{
                                jQuery('.field-margin-wrapper-<?php echo esc_attr($id); ?> input[type="number"]').removeAttr('readonly');
                                //jQuery('.field-margin-wrapper-<?php echo esc_attr($id); ?> select').removeAttr('disabled');
                            }
                        })
                    })
                </script>

            <?php
            endif;
            return ob_get_clean();
        }



        public function field_google_recaptcha( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $secret_key 	= isset( $option['secret_key'] ) ? $option['secret_key'] : "";
            $site_key 	    = isset( $option['site_key'] ) ? $option['site_key'] : "";
            $version 	    = isset( $option['version'] ) ? $option['version'] : "";
            $action_name 	= isset( $option['action_name'] ) ? $option['action_name'] : "action_name";

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-google-recaptcha-wrapper
            field-google-recaptcha-wrapper-<?php echo esc_attr($id);
            ?>">
                <?php if($version == 'v2'):?>
                    <div class="g-recaptcha" data-sitekey="<?php echo esc_attr($site_key); ?>"></div>
                    <script src='https://www.google.com/recaptcha/api.js'></script>
            <?php elseif($version == 'v3'):?>
                    <script src='https://www.google.com/recaptcha/api.js?render=<?php echo esc_attr($site_key); ?>'></script>
                    <script>
                        grecaptcha.ready(function() {
                            grecaptcha.execute('<?php echo esc_attr($site_key); ?>', {action: '<?php echo esc_attr($action_name); ?>'})
                                .then(function(token) {
// Verify the token on the server.
                                });
                        });
                    </script>

                <?php endif;?>
                <div class="error-mgs"></div>
            </div>


            <?php

            return ob_get_clean();
        }


        public function field_img_select( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $width			= isset( $option['width'] ) ? $option['width'] : "";
            $height			= isset( $option['height'] ) ? $option['height'] : "";
            $default 		= isset( $option['default'] ) ? $option['default'] : '';
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value 	        = isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-img-select-wrapper
            field-img-select-wrapper-<?php echo esc_attr($id); ?>">
                <div class="img-list">
                    <?php
                    foreach( $args as $key => $arg ):
                        $checked = ( $arg == $value ) ? "checked" : "";
                        ?><label class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'><input type='radio' id='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><span class="sw-button"><img data-id="<?php echo esc_attr($id); ?>" src="<?php echo esc_attr($arg); ?>"> </span></label><?php

                    endforeach;
                    ?>
                </div>
                <div class="img-val">
                    <input type="text" name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($value); ?>">
                </div>
                <div class="error-mgs"></div>
            </div>


            <?php
            return ob_get_clean();

        }





        public function field_submit( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-submit-wrapper
            field-submit-wrapper-<?php echo esc_attr($id); ?>">
                <input type='submit' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }


        public function field_nonce( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $action_name 	    = isset( $option['action_name'] ) ? $option['action_name'] : "";

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-nonce-wrapper
            field-nonce-wrapper-<?php echo esc_attr($id); ?>">
                <?php wp_nonce_field( $action_name, $field_name ); ?>
                <div class="error-mgs"></div>
            </div>

            <?php

            return ob_get_clean();
        }



        public function field_color( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-color-wrapper
            field-color-wrapper-<?php echo esc_attr($id); ?>">
                <input type='color' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php

            return ob_get_clean();
        }




        public function field_email( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-email-wrapper
            field-email-wrapper-<?php echo esc_attr($id); ?>">
                <input type='email' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php

            return ob_get_clean();
        }


        public function field_password( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";
            $password_meter = isset( $option['password_meter'] ) ? $option['password_meter'] : true;
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-password-wrapper
            field-password-wrapper-<?php echo esc_attr($id); ?>">
                <input type='password' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <?php if($password_meter): ?>
                <div class="scorePassword"></div>
                <div class="scoreText"></div>
                <?php endif; ?>
                <div class="error-mgs"></div>
            </div>


            <?php

            return ob_get_clean();
        }

        public function field_search( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-search-wrapper
            field-search-wrapper-<?php echo esc_attr($id); ?>">
                <input type='search' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php

            return ob_get_clean();
        }

        public function field_month( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-month-wrapper
            field-month-wrapper-<?php echo esc_attr($id); ?>">
                <input type='time' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php

            return ob_get_clean();
        }

        public function field_date( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-date-wrapper
            field-date-wrapper-<?php echo esc_attr($id); ?>">
                <input type='date' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }

        public function field_url( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-url-wrapper field-url-wrapper-<?php echo esc_attr($id); ?>">
                <input type='url' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }



        public function field_time( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-time-wrapper
            field-time-wrapper-<?php echo esc_attr($id); ?>">
                <input type='time' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }


        public function field_tel( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-tel-wrapper field-tel-wrapper-<?php
            echo esc_attr($id); ?>">
                <input type='tel' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }

        public function field_text( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id))  return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $visible 	    = isset( $option['visible'] ) ? $option['visible'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();




            ?>


            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-text-wrapper
         field-text-wrapper-<?php echo esc_attr($id); ?>">
                <input type='text' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>'
                       placeholder='<?php
                echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>


            <?php

            return ob_get_clean();
        }


        public function field_hidden( $option ){




            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";

            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>


            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-hidden-wrapper
            field-hidden-wrapper-<?php echo esc_attr($id); ?>">
                <input type='hidden' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php
                echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php

            return ob_get_clean();
        }



        




        public function field_service_price( $option ){

          

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $remove_text 	= isset( $option['remove_text'] ) ? $option['remove_text'] : '<i class="fa-solid fa-trash-can"></i>';
            $sortable 	    = isset( $option['sortable'] ) ? $option['sortable'] : true;
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();

            $values 	    = isset( $option['value'] ) ? $option['value'] : array();
            $values         = !empty($values) ? $values : $default;
            $limit 	        = !empty( $option['limit'] ) ? $option['limit'] : '';



            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;

            ob_start();
            ?>

            <div id="field-wrapper-<?php echo esc_attr($id); ?>" class="field-wrapper field-text-multi-wrapper field-text-multi-wrapper-<?php echo esc_attr($field_id); ?>">
                <table class="form-table rbfw_service_category_table">
                    <tbody class="sortable_tr">
                    <?php
                    if(!empty($values)):
                        $i = 0;
                    foreach ($values as $value):?>
                        <tr data-cat="<?php echo $i; ?>">
                            <td>
                                <div class="services_category_wrapper">
                                    <div class="field-list <?php echo esc_attr($field_id); ?>">
                                        <div class="service_category_inner_wrap">
                                            <div class="service_category_title">
                                                <label class=" mb-1">
                                                    <?php echo esc_html__('Service Category Title','booking-and-rental-manager-for-woocommerce'); ?>
                                                </label>
                                                <input type="text" value="<?php echo esc_attr($value['cat_title']); ?>" name="rbfw_service_category_price[<?php echo $i; ?>][cat_title]" data-key="<?php echo $i; ?>" placeholder="<?php echo esc_attr__('Service Category Label','booking-and-rental-manager-for-woocommerce'); ?>"/>
                                            </div>
                                            <div class="service_category_inner_item_wrap sortable">





                                                <?php
                                                if(!empty($value['cat_services'])){
                                                    $c = 0;
                                                    foreach ($value['cat_services'] as $service) {
                                                        $icon = $service['icon'];
                                                        $title = $service['title'];
                                                        $price = $service['price'];
                                                        $stock_quantity = $service['stock_quantity'];
                                                        $service_price_type = $service['service_price_type'];
                                                        ?>
                                                            <div class="item">
                                                                <a href="#rbfw_services_icon_list_wrapper" class="rbfw_service_icon_btn btn" data-key="<?php echo $c; ?>"><i class="fa-solid fa-circle-plus"></i> <?php echo esc_html__('Add Icon','booking-and-rental-manager-for-woocommerce'); ?></a>
                                                                <div class="rbfw_service_icon_preview p-1" data-key="<?php echo $c; ?>"><?php if($icon){ echo '<i class="'.$icon.'"></i>'; } ?></div>

                                                                <input type='hidden' name='rbfw_service_category_price[<?php echo $i; ?>][cat_services][<?php echo $c; ?>][icon]' placeholder='<?php echo esc_attr__('Icon','booking-and-rental-manager-for-woocommerce'); ?>' value='<?php echo esc_attr($icon); ?>' data-key="<?php echo $c; ?>" class="rbfw_service_icon"/>
                                                                <input type='text' name='rbfw_service_category_price[<?php echo $i; ?>][cat_services][<?php echo $c; ?>][title]'  placeholder='<?php echo esc_attr($placeholder); ?>' value="<?php  echo esc_attr($title); ?>" data-key="<?php echo $c; ?>"/>

                                                                <input type='text' name='rbfw_service_category_price[<?php echo $i; ?>][cat_services][<?php echo $c; ?>][price]'  placeholder='<?php echo __('Price','booking-and-rental-manager-for-woocommerce'); ?>' value='<?php  echo esc_attr($price); ?>'  data-key="0"/>

                                                                <input type='text' name='rbfw_service_category_price[<?php echo $i; ?>][cat_services][<?php echo $c; ?>][stock_quantity]'  placeholder='<?php echo __('Stock','booking-and-rental-manager-for-woocommerce'); ?>' value='<?php  echo esc_attr($stock_quantity); ?>'  data-key="0"/>

                                                                <label class="" for="rbfw_dt_sidebar_switch-on">
                                                                    <input name="rbfw_service_category_price[<?php echo $i; ?>][cat_services][<?php echo $c; ?>][service_price_type]" type="radio" <?php echo ($service_price_type=='one_time')?'checked':''  ?> id="rbfw_dt_sidebar_switch-on" value="one_time">
                                                                    <span class="sw-button"> One Time</span>
                                                                </label>
                                                                <label class="checked" for="rbfw_dt_sidebar_switch-off">
                                                                    <input name="rbfw_service_category_price[<?php echo $i; ?>][cat_services][<?php echo $c; ?>][service_price_type]" type="radio" <?php echo ($service_price_type=='day_wise')?'checked':''  ?> id="rbfw_dt_sidebar_switch-off" value="day_wise">
                                                                    <span class="sw-button"> Day Wise</span>
                                                                </label>

                                                                <?php if($sortable):?>
                                                                    <span class="button sort"><i class="fas fa-arrows-alt"></i></span>
                                                                <?php endif; ?>
                                                                <span class="button remove" onclick="jQuery(this).parent().remove()"><?php echo ($remove_text); ?></span>
                                                            </div>
                                                            <?php
                                                            $c++;
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="ppof-button add-new-service">
                                            <i class="fa-solid fa-circle-plus"></i>
                                            <?php echo __('Add New Service','booking-and-rental-manager-for-woocommerce'); ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <?php if($sortable):?>
                                        <span class="button tr_sort_handler"><i class="fas fa-arrows-alt"></i></span>
                                    <?php endif; ?>
                                    <span class="button tr_remove" onclick="jQuery(this).parent('tr').remove()"><?php echo ($remove_text); ?></span>
                                </td>
                            </tr>
                            <?php
                            $i++;
                        endforeach;
                    else:
                        ?>

                        <tr data-cat="0">
                            <td>
                                <div class="services_category_wrapper">
                                    <div class="field-list <?php echo esc_attr($field_id); ?>">
                                        <div class="service_category_inner_wrap">
                                            <div class="service_category_title">
                                                <label>
                                                    <?php echo esc_html__('Service Category Title','booking-and-rental-manager-for-woocommerce'); ?>
                                                </label>
                                                <input type="text" name="rbfw_service_category_price[0][cat_title]" data-key="0" placeholder="<?php echo esc_attr__('Service Category Label','booking-and-rental-manager-for-woocommerce'); ?>"/>
                                            </div>
                                            <div class="service_category_inner_item_wrap sortable">

                                                <div class="item">

                                                    <a href="#rbfw_services_icon_list_wrapper" class="rbfw_service_icon_btn btn" data-key="0">
                                                        <i class="fa-solid fa-circle-plus"></i>
                                                        <?php echo esc_html__('Add Icon','booking-and-rental-manager-for-woocommerce'); ?>
                                                    </a>
                                                    <div class="rbfw_service_icon_preview p-1" data-key="0"></div>
                                                    <input type='hidden' name='rbfw_service_category_price[0][cat_services][0][icon]' placeholder='<?php echo esc_attr__('Icon','booking-and-rental-manager-for-woocommerce'); ?>' data-key="0" class="rbfw_service_icon"/>
                                                    <input type='text' name='rbfw_service_category_price[0][cat_services][0][title]'  placeholder='<?php echo esc_attr($placeholder); ?>' value='' data-key="0"/>

                                                    <input type='text' name='rbfw_service_category_price[0][cat_services][0][price]'  placeholder='<?php echo __('Price','booking-and-rental-manager-for-woocommerce'); ?>' value='' data-key="0"/>

                                                    <input type='text' name='rbfw_service_category_price[0][cat_services][0][stock_quantity]'  placeholder='<?php echo __('Stock','booking-and-rental-manager-for-woocommerce'); ?>' value=''  data-key="0"/>

                                                    <label class="" for="rbfw_dt_sidebar_switch-on">
                                                        <input name="rbfw_service_category_price[0][cat_services][0][service_price_type]" type="radio" id="rbfw_dt_sidebar_switch-on" value="one_time">
                                                        <span class="sw-button"> One Time</span>
                                                    </label>
                                                    <label class="checked" for="rbfw_dt_sidebar_switch-off">
                                                        <input name="rbfw_service_category_price[0][cat_services][0][service_price_type]" type="radio" id="rbfw_dt_sidebar_switch-off" value="day_wise" checked="">
                                                        <span class="sw-button"> Day Wise</span>
                                                    </label>

                                                    <?php if($sortable):?>
                                                        <span class="button sort">
                                                            <i class="fas fa-arrows-alt"></i>
                                                        </span>
                                                    <?php endif; ?>
                                                    <span class="button remove" onclick="jQuery(this).parent().remove()">
                                                        <?php echo ($remove_text); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="ppof-button add-new-service">
                                        <i class="fa-solid fa-circle-plus"></i>
                                        <?php echo __('Add New Feature','booking-and-rental-manager-for-woocommerce'); ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <?php if($sortable):?>
                                    <span class="button tr_sort_handler"><i class="fas fa-arrows-alt"></i></span>
                                <?php endif; ?>
                                <span class="button tr_remove" onclick="jQuery(this).parent('tr').remove()">
                                    <?php echo ($remove_text); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                <span class="ppof-button add-service-category mt-1">
                    <i class="fa-solid fa-circle-plus"></i>
                    <?php echo __('Add New Service Category','booking-and-rental-manager-for-woocommerce'); ?>
                </span>
            </div>
            <script>
                jQuery( ".sortable_tr" ).sortable({ handle: '.tr_sort_handler' });
                jQuery('.tr_remove').click(function (e) {
                    jQuery(this).closest("tr").remove();
                });

                jQuery(document).on('click', '.add-service-category',function(e){
                    e.stopImmediatePropagation();
                    let dataCat = jQuery('.rbfw_service_category_table tbody tr:last-child').attr('data-cat');
                    let nextCat = parseInt(dataCat) + 1;
                    let html = '<tr data-cat="'+nextCat+'"><td><div class="services_category_wrapper"><div class="field-list rbfw_service_category_price"><div class="service_category_inner_wrap"><div class="service_category_title"><label><?php echo esc_html__('Feature Category Title','booking-and-rental-manager-for-woocommerce'); ?></label><input type="text" class="rbfw_service_category_title" name="rbfw_service_category_price['+nextCat+'][cat_title]" data-cat="'+nextCat+'" placeholder="<?php echo esc_attr__('Feature Category Label','booking-and-rental-manager-for-woocommerce'); ?>"></div>';
                    html +='<div class="service_category_inner_item_wrap sortable"><div class="item"><a href="#rbfw_services_icon_list_wrapper" class="rbfw_service_icon_btn btn" data-key="0"><i class="fa-solid fa-circle-plus"></i> <?php echo esc_html__('Add Icon','booking-and-rental-manager-for-woocommerce'); ?></a><div class="rbfw_service_icon_preview p-1" data-key="0"></div><input type="hidden" name="rbfw_service_category_price['+nextCat+'][cat_services][0][icon]" placeholder="<?php echo esc_attr__('Icon','booking-and-rental-manager-for-woocommerce'); ?>" data-key="0" class="rbfw_service_icon">';
                    html +='<input type="text" name="rbfw_service_category_price['+nextCat+'][cat_services][0][title]" placeholder="<?php echo esc_attr($placeholder); ?>" value="" data-key="0">';
                    html +='<input type="text" name="rbfw_service_category_price['+nextCat+'][cat_services][0][price]" placeholder="Price" value="" data-key="0">';
                    html +='<input type="text" name="rbfw_service_category_price['+nextCat+'][cat_services][0][stock_quantity]" placeholder="Stock" value="" data-key="0">';
                    html += '<label class="" for="rbfw_dt_sidebar_switch-on"> <input name="rbfw_service_category_price['+ nextCat +'][cat_services][0][service_price_type]" type="radio" id="rbfw_dt_sidebar_switch-on" value="one_time"> <span class="sw-button"> One Time</span> </label>';
                    html += '<label class="checked" for="rbfw_dt_sidebar_switch-off"> <input name="rbfw_service_category_price['+ nextCat +'][cat_services][0][service_price_type]" type="radio" id="rbfw_dt_sidebar_switch-off" value="day_wise" checked=""> <span class="sw-button"> Day Wise</span> </label>';

                    html +='<?php if($sortable):?><span class="button sort"><i class="fas fa-arrows-alt"></i></span> <?php endif; ?> <span class="button remove" onclick="jQuery(this).parent().remove()"><?php echo ($remove_text); ?></span></div></div></div></div><span class="ppof-button add-new-service"><i class="fa-solid fa-circle-plus"></i> <?php echo __('Add New Feature','booking-and-rental-manager-for-woocommerce'); ?></span></div></td><td> <?php if($sortable):?> <span class="button tr_sort_handler"><i class="fas fa-arrows-alt"></i></span> <?php endif; ?> <span class="button tr_remove"><?php echo ($remove_text); ?></span></td></tr>';
                    jQuery('.rbfw_service_category_table tbody').append(html);
                    jQuery( ".sortable_tr" ).sortable({ handle: '.tr_sort_handler' });
                    jQuery('.tr_remove').click(function (e) { jQuery(this).closest("tr").remove();});
                });

                jQuery(document).on('click', '.add-new-service',function(e){
                    e.stopImmediatePropagation();
                    let data_key = jQuery(this).siblings(".rbfw_service_category_price").find("div.item:last-child input").attr('data-key');
                    let i = parseInt(data_key);
                    let c = i + 1;
                    let theTarget = jQuery(this).siblings('.rbfw_service_category_price').find('.service_category_inner_wrap .service_category_inner_item_wrap');
                    jQuery( ".sortable" ).sortable({ handle: '.sort' });
                    let dataCat = jQuery(this).closest('tr').attr('data-cat');

                    html = '<div class="item">';

                    html += '<a href="#rbfw_services_icon_list_wrapper" class="rbfw_service_icon_btn btn" data-key="'+ c +'"><i class="fa-solid fa-circle-plus"></i> <?php echo esc_html__('Add Icon','booking-and-rental-manager-for-woocommerce'); ?></a>';
                    html += '<div class="rbfw_service_icon_preview p-1" data-key="'+ c +'"></div>';
                    html += '<input type="hidden" name="rbfw_service_category_price['+ dataCat +'][cat_services]['+ c +'][icon]" placeholder="<?php echo esc_html__('Icon','booking-and-rental-manager-for-woocommerce'); ?>" data-key="'+ c +'" class="rbfw_service_icon"/>';

                    html += '<input type="text" name="rbfw_service_category_price['+ dataCat +'][cat_services]['+ c +'][title]" placeholder="<?php echo esc_attr($placeholder); ?>" data-key="'+ c +'"/>';
                    html += '<input type="text" name="rbfw_service_category_price['+ dataCat +'][cat_services]['+ c +'][price]" placeholder="Price" data-key="'+ c +'"/>';
                    html += '<input type="text" name="rbfw_service_category_price['+ dataCat +'][cat_services]['+ c +'][stock_quantity]" placeholder="Stock" data-key="'+ c +'"/>';

                    html += '<label class="" for="rbfw_dt_sidebar_switch-on"> <input name="rbfw_service_category_price['+ dataCat +'][cat_services]['+ c +'][service_price_type]" type="radio" id="rbfw_dt_sidebar_switch-on" value="one_time"> <span class="sw-button"> One Time</span> </label>';
                    html += '<label class="checked" for="rbfw_dt_sidebar_switch-off"> <input name="rbfw_service_category_price['+ dataCat +'][cat_services]['+ c +'][service_price_type]" type="radio" id="rbfw_dt_sidebar_switch-off" value="day_wise" checked=""> <span class="sw-button"> Day Wise</span> </label>';

                    <?php if($sortable):?>
                    html += ' <span class="button sort" ><i class="fas fa-arrows-alt"></i></span>';
                    <?php endif; ?>

                    html += '<span class="button remove" onclick="jQuery(this).parent().remove()' + '"><?php echo ($remove_text); ?></span>';
                    html += '</div>';

                    theTarget.append(html);
                });

                // Features Icon Popup
                jQuery(document).on('click', '.rbfw_service_icon_btn',function(e){
                    e.stopImmediatePropagation();
                    let remove_exist_data_key 	= jQuery("#rbfw_features_icon_list_wrapper").removeAttr('data-key');
                    let remove_active_label 	= jQuery('#rbfw_features_icon_list_wrapper label').removeClass('selected');
                    let data_key 				= jQuery(this).attr('data-key');
                    let data_cat 				= jQuery(this).parents('tr').attr('data-cat');



                    jQuery('#rbfw_services_search_icon').val('');
                    jQuery('.rbfw_services_icon_list_body label').show();
                    jQuery("#rbfw_features_icon_list_wrapper").attr('data-key', data_key);
                    jQuery("#rbfw_features_icon_list_wrapper").attr('data-cat', data_cat);
                    jQuery("#rbfw_features_icon_list_wrapper").mage_modal({
                        escapeClose: false,
                        clickClose: false,
                        showClose: false
                    });

                    // Selected Feature Icon Action
                    jQuery(document).on('click', '.ggggg label',function(e){
                        e.stopImmediatePropagation();
                        let selected_label 		= jQuery(this);
                        let selected_val 		= jQuery('input', this).val();
                        let selected_data_key 	= jQuery("#rbfw_features_icon_list_wrapper").attr('data-key');
                        let selected_data_cat 	= jQuery("#rbfw_features_icon_list_wrapper").attr('data-cat');

                        console.log('selected_val',selected_val);
                        console.log('selected_label',selected_label);
                        console.log('selected_data_key',selected_data_key);
                        console.log('selected_data_cat',selected_data_cat);

                        jQuery('#rbfw_features_icon_list_wrapper label').removeClass('selected');

                        jQuery('.rbfw_service_category_table tr[data-cat="'+selected_data_cat+'"]').find('.rbfw_service_icon_preview[data-key="'+selected_data_key+'"]').empty();
                        jQuery(selected_label).addClass('selected');
                        jQuery('.rbfw_service_category_table tr[data-cat="'+selected_data_cat+'"]').find('.rbfw_service_icon[data-key="'+selected_data_key+'"]').val(selected_val);
                        jQuery('.rbfw_service_category_table tr[data-cat="'+selected_data_cat+'"]').find('.rbfw_service_icon_preview[data-key="'+selected_data_key+'"]').append('<i class="'+selected_val+'"></i>');
                    });

                    // Icon Filter
                    jQuery('#rbfw_services_search_icon').keyup(function (e) {
                        let value = jQuery(this).val().toLowerCase();
                        jQuery(".rbfw_services_icon_list_body label[data-id]").show().filter(function() {
                            jQuery(this).toggle(jQuery(this).attr('data-id').toLowerCase().indexOf(value) > -1)
                        }).hide();
                    });
                });

            </script>
            <?php
            return ob_get_clean();
        }

        public function field_text_multi( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $remove_text 	= isset( $option['remove_text'] ) ? $option['remove_text'] : '<i class="fa-solid fa-trash-can"></i>';
            $sortable 	    = isset( $option['sortable'] ) ? $option['sortable'] : true;
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();

            $values 	    = isset( $option['value'] ) ? $option['value'] : array();
            $values         = !empty($values) ? $values : $default;
            $limit 	        = !empty( $option['limit'] ) ? $option['limit'] : '';

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;
            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-text-multi-wrapper
            field-text-multi-wrapper-<?php echo esc_attr($field_id); ?>">
            <div class="field-list <?php if($sortable){ echo 'sortable'; }?>" id="<?php echo esc_attr($field_id); ?>"></div>
            </div>
            <?php
            return ob_get_clean();

        }



        public function field_textarea( $option ){

            $id             = isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $visible 	    = isset( $option['visible'] ) ? $option['visible'] : "";
            $placeholder    = isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $_value         = !empty($value) ? $value : $default;
            $__value        = str_replace('<br />', PHP_EOL, html_entity_decode($_value));

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-textarea-wrapper field-textarea-wrapper-<?php echo esc_attr($field_id); ?>">
                <textarea name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' cols='40' rows='5' placeholder='<?php echo esc_attr($placeholder); ?>'><?php echo mep_esc_html($__value); ?></textarea>
                <div class="error-mgs"></div>
            </div>



            <?php
            return ob_get_clean();
        }


        public function field_code( $option ){

            $id             = isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $placeholder    = isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;
            $args	        = isset( $option['args'] ) ? $option['args'] : array(
                'lineNumbers'	=> true,
                'mode'	=> "javascript",
            );


            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>"  class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper  field-code-wrapper
            field-code-wrapper-<?php echo esc_attr($field_id); ?>">
                <textarea name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' cols='40' rows='5' placeholder='<?php echo esc_attr($placeholder); ?>'><?php echo esc_attr($value); ?></textarea>
                <div class="error-mgs"></div>
            </div>
            <script>
                var editor = CodeMirror.fromTextArea(document.getElementById("<?php echo esc_attr($field_id); ?>"), {
                    <?php
                    foreach ($args as $argkey=>$arg):
                        echo esc_html($argkey).':'.esc_html($arg).',';
                    endforeach;
                    ?>
                });
            </script>

            <?php
            return ob_get_clean();
        }

        public function field_checkbox( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : "";

            $default 		= isset( $option['default'] ) ? $option['default'] : array();
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value			= isset( $option['value'] ) ? $option['value'] : array();
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field      = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type       = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern    = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier   = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like       = isset($conditions['like']) ? $conditions['like'] : '';
                $strict     = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty      = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign       = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min        = isset($conditions['min']) ? $conditions['min'] : '';
                $max        = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-checkbox-wrapper
            field-checkbox-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $args as $key => $argName ):

                    if(!empty($value) && is_array($value)){
                        $checked =   in_array($key,$value) ? "checked" : "";
                    }else{
                        $checked =  $key == $value  ? "checked" : "";
                    }

                    ?>
                    <label for='<?php echo esc_attr($field_id); ?>'><input class="<?php echo esc_attr($field_id); ?>" name='<?php echo esc_attr($field_name); ?>' type='checkbox' id='<?php echo esc_attr($field_id); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><?php echo esc_html($argName); ?></label><br>
                <?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>


            <?php
            return ob_get_clean();
        }

        public function field_checkbox_multi( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : "";

            $default 		= isset( $option['default'] ) ? $option['default'] : array();
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value			= isset( $option['value'] ) ? $option['value'] : array();
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name.'[]' : $id.'[]';



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-checkbox-wrapper
            field-checkbox-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $args as $key => $argName ):
                    $checked = is_array( $value ) && in_array( $key, $value ) ? "checked" : "";
                    ?>
                    <label for='<?php echo esc_attr($field_id).'-'.esc_attr($key); ?>'><input class="<?php echo esc_attr($field_id); ?>" name='<?php
                        echo esc_attr($field_name); ?>' type='checkbox' id='<?php echo esc_attr($field_id).'-'.esc_attr($key); ?>' value='<?php
                        echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><?php echo esc_html($argName); ?></label><br>
                    <?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }



        public function field_radio( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 		= isset( $option['default'] ) ? $option['default'] : array();
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value			= isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-radio-wrapper
            field-radio-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $args as $key => $argName ):
                    $checked = ( $key == $value ) ? "checked" : "";
                    ?>
                    <label for='<?php echo esc_attr($field_id).'-'.esc_attr($key); ?>'><input name='<?php echo esc_attr($field_name); ?>' type='radio' id='<?php echo esc_attr($field_id).'-'.esc_attr($key); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><?php echo esc_html($argName); ?></label><br>
                <?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }


        public function field_select( $option ){


            $id 	    = isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args 	        = isset( $option['args'] ) ? $option['args'] : "";
            $args	    = is_array( $args ) ? $args : $this->args_from_string( $args );
            $default    = isset( $option['default'] ) ? $option['default'] : "";
            $multiple 	= isset( $option['multiple'] ) ? $option['multiple'] : false;
            $limit 	    = !empty( $option['limit'] ) ? $option['limit'] : '';
            $value		= isset( $option['value'] ) ? $option['value'] : '';
            $value      = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;

            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-select-wrapper
            field-select-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                if($multiple):
                    ?>
                    <select name='<?php echo esc_attr($field_name); ?>[]' id='<?php echo esc_attr($field_id); ?>' multiple>
                    <?php
                else:
                    ?>
                        <select name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>'>
                    <?php
                endif;

                foreach( $args as $key => $argName ):
                    if( $multiple ) $selected = is_array( $value ) && in_array( $key, $value ) ? "selected" : "";
                    else $selected = ($value == $key) ? "selected" : "";
                    ?>
                    <option <?php echo esc_attr($selected); ?> value='<?php echo esc_attr($key); ?>'><?php echo esc_html($argName); ?></option>
                    <?php
                endforeach;
                ?>
                </select>

                <div class="error-mgs"></div>

            </div>
            <script>
                jQuery(document).ready(function($) {

                    <?php
                    if($limit > 0):
                        ?>
                        jQuery(document).on('change', '.field-select-wrapper-<?php echo esc_attr($id); ?> select', function() {

                            last_value = $('.field-select-wrapper-<?php echo esc_attr($id); ?> select :selected').last().val();

                            var node_count = $( ".field-select-wrapper-<?php echo esc_attr($id); ?> select :selected" ).size();

                            console.log(last_value);

                            var limit = <?php  echo esc_attr($limit); ?>;
                            //var node_count = $(".field-select-wrapper-<?php echo esc_attr($id); ?> select :selected").length;
                            //var node_count = $( ".field-select-wrapper-<?php echo esc_attr($id); ?> .field-list .item-wrap" ).size();
                            //console.log(node_count);
                            if(limit >= node_count){

                                //jQuery('.<?php echo 'field-select-wrapper-'.$id; ?> .field-list').append(html);
                            }else{
                                $(".field-select-wrapper-<?php echo esc_attr($id); ?> select option[value='"+last_value+"']").prop("selected", false);
                                jQuery('.field-select-wrapper-<?php echo esc_attr($id); ?> .error-mgs').html('Sorry! you can select max '+limit+' item').stop().fadeIn(400).delay(3000).fadeOut(400);
                            }

                        })
                        <?php
                    endif;
                    ?>
                })

            </script>
            <?php
            return ob_get_clean();
        }


        public function field_range( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $default 	    = isset( $option['default'] ) ? $option['default'] : "";
            $args 	        = isset( $option['args'] ) ? $option['args'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $min            = isset( $args['min'] ) ? $args['min'] : 0;
            $max            = isset( $args['max'] ) ? $args['max'] : 100;
            $step           = isset( $args['step'] ) ? $args['step'] : 1;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-range-wrapper
            field-range-wrapper-<?php echo esc_attr($id); ?>">
                <input type='range' min='<?php echo esc_attr($min); ?>' max='<?php echo esc_attr($max); ?>' step='<?php echo esc_attr($args['step']); ?>' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }

        public function field_range_input( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 	= isset( $option['default'] ) ? $option['default'] : "";
            $args 	= isset( $option['args'] ) ? $option['args'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value = !empty($value) ? $value : $default;

            $min            = isset( $args['min'] ) ? $args['min'] : 0;
            $max            = isset( $args['max'] ) ? $args['max'] : 100;
            $step           = isset( $args['step'] ) ? $args['step'] : 1;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-range-input-wrapper
            field-range-input-wrapper-<?php echo esc_attr($id); ?>">
                <input type="number" class="range-val" name='<?php echo esc_attr($field_name); ?>' value="<?php echo esc_attr($value); ?>">
                <input type='range' class='range-hndle' id="<?php echo esc_attr($field_id); ?>" min='<?php echo esc_attr($args['min']); ?>' max='<?php echo esc_attr($args['max']); ?>' step='<?php echo esc_attr($args['step']); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }


        public function field_switch( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 		= isset( $option['default'] ) ? $option['default'] : '';
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;

            ob_start();
            ?>
            <div <?php if(!empty($depends)) { ?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-switch-wrapper
            field-switch-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $args as $key => $argName ):
                    $checked = ( $key == $value ) ? "checked" : "";
                    ?><label class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'><input name='<?php echo esc_attr($field_name); ?>' type='radio' id='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><span class="sw-button"><?php echo esc_html($argName); ?></span></label><?php

                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>
            <?php
            return ob_get_clean();
        }



        public function field_switch_multi( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 		= isset( $option['default'] ) ? $option['default'] : '';
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value			= isset( $option['value'] ) ? $option['value'] : array();
            $value      = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-switch-multi-wrapper
            field-switch-multi-wrapper-<?php echo
            $id; ?>">
                <?php
                foreach( $args as $key => $argName ):
                    $checked = is_array( $value ) && in_array( $key, $value ) ? "checked" : "";
                    ?><label class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($field_id); ?>-<?php echo esc_attr($key); ?>'><input name='<?php echo esc_attr($field_name); ?>[]' type='checkbox' id='<?php echo esc_attr($field_id); ?>-<?php echo esc_attr($key); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><span class="sw-button"><?php echo esc_html($argName); ?></span></label><?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }



        public function field_switch_img( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $width			= isset( $option['width'] ) ? $option['width'] : "";
            $height			= isset( $option['height'] ) ? $option['height'] : "";
            $default 		= isset( $option['default'] ) ? $option['default'] : '';
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-switch-img-wrapper
            field-switch-img-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $args as $key => $arg ):
                    $src = isset( $arg['src'] ) ? $arg['src'] : "";

                    $checked = ( $key == $value ) ? "checked" : "";
                    ?><label class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'><input name='<?php echo esc_attr($field_name); ?>' type='radio' id='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><span class="sw-button"><img src="<?php echo esc_attr($src); ?>"> </span></label><?php

                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }



        public function field_time_format( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 	= isset( $option['default'] ) ? $option['default'] : "";
            $args 	= isset( $option['args'] ) ? $option['args'] : "";

            $value 	= isset( $option['value'] ) ? $option['value'] : "";
            $value 	 		= !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;




            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-time-format-wrapper
            field-time-format-wrapper-<?php echo esc_attr($id); ?>">
                <div class="format-list">
                    <?php
                    if(!empty($args)):
                        foreach ($args as $item):
                            $checked = ($item == $value) ? 'checked':false;
                            ?>
                            <div class="format" datavalue="<?php echo esc_attr($item); ?>">
                                <label><input type="radio" <?php echo esc_attr($checked); ?> name="preset_<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($item); ?>">
                                    <span class="name"><?php echo date($item); ?></span></label>
                                <span class="format"><code><?php echo esc_attr($item); ?></code></span>
                            </div>
                        <?php
                        endforeach;
                        ?>
                        <div class="format-value">
                            <span class="format"><input value="<?php echo esc_attr($value); ?>" name="<?php echo esc_attr($field_name); ?>"></span>
                            <div class="">Preview: <?php echo date($value); ?></div>
                        </div>
                    <?php
                    endif;
                    ?>
                </div>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }






        public function field_date_format( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 	= isset( $option['default'] ) ? $option['default'] : "";
            $args 	= isset( $option['args'] ) ? $option['args'] : "";

            $value 	= isset( $option['value'] ) ? $option['value'] : "";
            $value 	 		= !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-date-format-wrapper
            field-date-format-wrapper-<?php echo esc_attr($id); ?>">
                <div class="format-list">
                    <?php
                    if(!empty($args)):
                        foreach ($args as $item):
                            $checked = ($item == $value) ? 'checked':false;
                            ?>
                            <div class="format" datavalue="<?php echo esc_attr($item); ?>">
                                <label><input type="radio" <?php echo esc_attr($checked); ?> name="preset_<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($item); ?>"><span class="name"><?php echo date($item); ?></span></label>
                                <span class="format"><code><?php echo esc_html($item); ?></code></span>
                            </div>
                            <?php
                        endforeach;
                        ?>
                        <div class="format-value">
                            <span class="format"><input value="<?php echo esc_attr($value); ?>" name="<?php echo esc_attr($field_name); ?>"></span>
                            <div class="">Preview: <?php echo date($value); ?></div>
                        </div>
                    <?php
                    endif;
                    ?>
                </div>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }


        public function field_datepicker( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";
            $date_format	= isset( $option['date_format'] ) ? $option['date_format'] : "dd-mm-yy";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ?$value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-datepicker-wrapper
            field-datepicker-wrapper-<?php echo esc_attr($id); ?>">
                <input type='text' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>
            <script>
                jQuery(document).ready(function($) {
                    jQuery('#<?php echo esc_attr($field_id); ?>').datepicker({dateFormat : '<?php echo esc_attr($date_format); ?>'})});
            </script>

            <?php
            return ob_get_clean();
        }






        public function field_colorpicker( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-colorpicker-wrapper
            field-colorpicker-wrapper-<?php echo esc_attr($id); ?>">
                <input type='text'  name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>
            <script>jQuery(document).ready(function($) { $('#<?php echo esc_attr($field_id); ?>').wpColorPicker();});</script>

            <?php
            return ob_get_clean();
        }


        public function field_colorpicker_multi( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $limit 	        = isset( $option['limit'] ) ? $option['limit'] : "";
            $remove_text 	= isset( $option['remove_text'] ) ? $option['remove_text'] : "X";
            $default 	= isset( $option['default'] ) ? $option['default'] : array();

            $values = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            if(!empty($values)):
                ?>
                <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-colorpicker-multi-wrapper
                field-colorpicker-multi-wrapper-<?php echo esc_attr($id);
                ?>">
                    <div class="ppof-button add"><?php echo __('Add','pickplugins-options-framework'); ?></div>
                    <div class="item-list">
                        <?php
                        foreach ($values as $value):
                            ?>
                            <div class="item">
                                <span class="ppof-button remove"><?php echo esc_html($remove_text); ?></span>
                                <input type='text' name='<?php echo esc_attr($field_name); ?>[]' value='<?php echo esc_attr($value); ?>' />
                            </div>
                        <?php
                        endforeach;
                        ?>
                    </div>
                    <div class="error-mgs"></div>
                </div>
                <?php
            endif;
            ?>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.field-colorpicker-multi-wrapper-<?php echo esc_attr($id); ?> .item-list .remove', function(){
                        jQuery(this).parent().remove();
                    })
                    jQuery(document).on('click', '.field-colorpicker-multi-wrapper-<?php echo esc_attr($id); ?> .add', function() {
                        html='<div class="item">';
                        html+='<span class="button remove"><?php echo esc_html($remove_text); ?></span> <input type="text"  name="<?php echo esc_attr($field_name); ?>[]" value="" />';
                        html+='</div>';


                        <?php
                        if(!empty($limit)):
                        ?>
                        var limit = <?php  echo esc_attr($limit); ?>;
                        var node_count = $( ".field-colorpicker-multi-wrapper-<?php echo esc_attr($id); ?> .item-list .item" ).size();
                        if(limit > node_count){

                            $('.field-colorpicker-multi-wrapper-<?php echo esc_attr($id); ?> .item-list').append(html);
                            $('.field-colorpicker-multi-wrapper-<?php echo esc_attr($id); ?> input').wpColorPicker();


                        }else{
                            jQuery('.field-colorpicker-multi-wrapper-<?php echo esc_attr($id); ?> .error-mgs').html('Sorry! you can add max '+limit+' item').stop().fadeIn(400).delay(3000).fadeOut(400);
                        }
                        <?php
                        endif;
                        ?>





                    })
                    $('.field-colorpicker-multi-wrapper-<?php echo esc_attr($id); ?> input').wpColorPicker();
                });
            </script>

            <?php

            return ob_get_clean();
        }




        public function field_link_color( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $args 	        = isset( $option['args'] ) ? $option['args'] : array('link'	=> '#1B2A41','hover' => '#3F3244','active' => '#60495A','visited' => '#7D8CA3' );

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-link-color-wrapper
            field-link-color-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                if(!empty($values) && is_array($values)):
                    foreach ($args as $argindex=>$value):
                        ?>
                        <div>
                            <div class="item"><span class="title">a:<?php echo esc_html($argindex); ?> Color</span><div class="colorpicker"><input type='text' class='<?php echo esc_attr($id); ?>' name='<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($argindex); ?>]'   value='<?php echo esc_attr($values[$argindex]); ?>' /></div></div>
                        </div>
                        <?php
                    endforeach;
                else:
                    foreach ($args as $argindex=>$value):
                        ?>
                        <div>
                            <div class="item"><span class="title">a:<?php echo esc_html($argindex); ?> Color</span><div class="colorpicker"><input type='text' class='<?php echo esc_attr($id); ?>' name='<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($argindex); ?>]'   value='<?php echo esc_attr($value); ?>' /></div></div>
                        </div>
                    <?php
                    endforeach;
                endif;
                ?>
                <div class="error-mgs"></div>
            </div>
            <script>jQuery(document).ready(function($) { $('.<?php echo esc_attr($id); ?>').wpColorPicker();});</script>

            <?php
            return ob_get_clean();
        }






        public function field_user( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args 			= isset( $option['args'] ) ? $option['args'] : array();
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $icons		    = is_array( $args ) ? $args :  $this->args_from_string( $args );

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-user-multi-wrapper
            field-user-multi-wrapper-<?php echo esc_attr($id); ?>">
                <div class="users-wrapper" >
                    <?php if(!empty($values)):
                        foreach ($values as $user_id):
                            $get_avatar_url = get_avatar_url($user_id,array('size'=>'60'));

                            ?><div class="item" title="click to remove"><img src="<?php echo esc_attr($get_avatar_url); ?>" /><input type="hidden" name="<?php echo esc_attr($field_name); ?>[]" value="<?php echo esc_attr($user_id); ?>"></div><?php
                        endforeach;
                    endif; ?>
                </div>
                <div class="user-list">
                    <div class="ppof-button select-user" ><?php echo __('Choose User','pickplugins-options-framework');?></div>
                    <div class="search-user" ><input class="" type="text" placeholder="<?php echo __('Start typing...','pickplugins-options-framework');?>"></div>
                    <ul>
                        <?php
                        if(!empty($icons)):
                            foreach ($icons as $user_id=>$iconTitle):
                                $user_data = get_user_by('ID',$user_id);
                                $get_avatar_url = get_avatar_url($user_id,array('size'=>'60'));
                                ?>
                                <li title="<?php echo esc_attr($user_data->display_name); ?>(#<?php echo esc_attr($user_id); ?>)"
                                    userSrc="<?php echo
                                $get_avatar_url; ?>"
                                    iconData="<?php echo esc_attr($user_id); ?>"><img src="<?php echo esc_attr($get_avatar_url); ?>" />
                                </li>
                            <?php
                            endforeach;
                        endif;
                        ?>
                    </ul>
                </div>
                <div class="error-mgs"></div>
            </div>


            <script>
                jQuery(document).ready(function($){
                    jQuery(document).on('click', '.field-user-multi-wrapper-<?php echo esc_attr($id); ?> .users-wrapper .item', function(){
                        jQuery(this).remove();
                    })
                    jQuery(document).on('click', '.field-user-multi-wrapper-<?php echo esc_attr($id); ?> .select-user', function(){
                        if(jQuery(this).parent().hasClass('active')){
                            jQuery(this).parent().removeClass('active');
                        }else{
                            jQuery(this).parent().addClass('active');
                        }
                    })
                    jQuery(document).on('keyup', '.field-user-multi-wrapper-<?php echo esc_attr($id); ?> .search-user input', function(){
                        text = jQuery(this).val();
                        $('.field-user-multi-wrapper-<?php echo esc_attr($id); ?> .user-list li').each(function( index ) {
                            //console.log( index + ": " + $( this ).attr('title') );
                            title = $( this ).attr('title');
                            n = title.indexOf(text);
                            if(n<0){
                                $( this ).hide();
                            }else{
                                $( this ).show();
                            }
                        });
                    })
                    jQuery(document).on('click', '.field-user-multi-wrapper-<?php echo esc_attr($id); ?> .user-list li', function(){
                        iconData = jQuery(this).attr('iconData');
                        userSrc = jQuery(this).attr('userSrc');
                        html = '';
                        html = '<div class="item" title="click to remove"><img src="'+userSrc+'" /><input type="hidden" ' +
                        'name="<?php echo esc_attr($field_name); ?>[]" value="'+iconData+'"></div>';
                        jQuery('.field-user-multi-wrapper-<?php echo esc_attr($id); ?> .users-wrapper').append(html);
                    })
                })
            </script>

            <?php
            return ob_get_clean();
        }



        public function field_icon( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $args 			= isset( $option['args'] ) ? $option['args'] : array();
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $icons		    = is_array( $args ) ? $args : $this->args_from_string( $args );

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-icon-wrapper
            field-icon-wrapper-<?php echo esc_attr($id); ?>">
                <div class="icon-wrapper" >
                    <span><i class="<?php echo esc_attr($value); ?>"></i></span>
                    <input type="hidden" name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($value); ?>">
                </div>
                <div class="icon-list">
                    <div class="ppof-button select-icon" ><?php echo __('Choose Icon','pickplugins-options-framework'); ?></div>
                    <div class="search-icon" ><input class="" type="text" placeholder="<?php echo __('Start typing...','pickplugins-options-framework'); ?>"></div>
                    <ul>
                        <?php
                        if(!empty($icons)):
                            foreach ($icons as $iconindex=>$iconTitle):
                                ?>
                                <li title="<?php echo esc_attr($iconTitle); ?>" iconData="<?php echo esc_attr($iconindex); ?>"><i class="<?php echo esc_attr($iconindex); ?>"></i></li>
                                <?php
                            endforeach;
                        endif;
                        ?>
                    </ul>
                </div>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }



        public function field_icon_multi( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $args 			= isset( $option['args'] ) ? $option['args'] : array();
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $icons		    = is_array( $args ) ? $args :  $this->args_from_string( $args );

            $limit 	        = isset( $option['limit'] ) ? $option['limit'] : "";
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-icon-multi-wrapper
            field-icon-multi-wrapper-<?php echo esc_attr($id); ?>">
                <div class="icons-wrapper" >
                    <?php if(!empty($values)):
                        foreach ($values as $value):
                            ?><div class="item" title="click to remove"><span><i class="<?php echo esc_attr($value); ?>"></i></span><input type="hidden" name="<?php echo esc_attr($field_name); ?>[]" value="<?php echo esc_attr($value); ?>"></div><?php
                        endforeach;
                    endif; ?>
                </div>
                <div class="icon-list">
                    <div class="ppof-button select-icon" ><?php echo __('Choose Icon','pickplugins-options-framework'); ?></div>
                    <div class="search-icon" ><input class="" type="text" placeholder="<?php echo __('Start typing...','pickplugins-options-framework'); ?>"></div>
                    <ul>
                        <?php
                        if(!empty($icons)):
                            foreach ($icons as $iconindex=>$iconTitle):
                                ?><li title="<?php echo esc_attr($iconTitle); ?>" iconData="<?php echo esc_attr($iconindex); ?>"><i class="<?php echo esc_attr($iconindex); ?>"></i></li><?php
                            endforeach;
                        endif;
                        ?>
                    </ul>
                </div>
                <div class="error-mgs"></div>
            </div>


            <script>
                jQuery(document).ready(function($){


                    jQuery(document).on('click', '.field-icon-multi-wrapper-<?php echo esc_attr($id); ?> .icons-wrapper .item', function(){
                        jQuery(this).remove();
                    })
                    jQuery(document).on('click', '.field-icon-multi-wrapper-<?php echo esc_attr($id); ?> .select-icon', function(){
                        if(jQuery(this).parent().hasClass('active')){
                            jQuery(this).parent().removeClass('active');
                        }else{
                            jQuery(this).parent().addClass('active');
                        }
                    })
                    jQuery(document).on('keyup', '.field-icon-multi-wrapper-<?php echo esc_attr($id); ?> .search-icon input', function(){
                        text = jQuery(this).val();
                        $('.field-icon-multi-wrapper-<?php echo esc_attr($id); ?> .icon-list li').each(function( index ) {
                            console.log( index + ": " + $( this ).attr('title') );
                            title = $( this ).attr('title');
                            n = title.indexOf(text);
                            if(n<0){
                                $( this ).hide();
                            }else{
                                $( this ).show();
                            }
                        });
                    })
                    jQuery(document).on('click', '.field-icon-multi-wrapper-<?php echo esc_attr($id); ?> .icon-list li', function(){
                        iconData = jQuery(this).attr('iconData');
                        html = '<div class="item" title="click to remove"><span><i class="'+iconData+'"></i></span><input type="hidden" name="<?php echo esc_attr($field_name); ?>[]" value="'+iconData+'"></div>';


                        <?php
                        if(!empty($limit)):
                        ?>
                        var limit = <?php  echo esc_attr($limit); ?>;
                        var node_count = $( ".field-icon-multi-wrapper-<?php echo esc_attr($id); ?> .icons-wrapper .item" ).size();
                        if(limit > node_count){

                            jQuery('.field-icon-multi-wrapper-<?php echo esc_attr($id); ?> .icons-wrapper').append(html);


                        }else{
                            jQuery('.field-icon-multi-wrapper-<?php echo esc_attr($id); ?> .error-mgs').html('Sorry! you can add max '+limit+' item').stop().fadeIn(400).delay(3000).fadeOut(400);
                        }
                        <?php
                        endif;
                        ?>




                    })


                })
            </script>

            <?php
            return ob_get_clean();
        }









        public function field_number( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 			= isset( $option['default'] ) ? $option['default'] : "";
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";

            $value 			= isset( $option['value'] ) ? $option['value'] : "";
            $value = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
             <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-number-wrapper
             field-number-wrapper-<?php echo esc_attr($id); ?>">
                <input type='number' class='' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                 <div class="error-mgs"></div>
             </div>

            <?php
            return ob_get_clean();
        }



        public function field_wp_editor( $option ){

            $id = isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder    = isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default        = isset( $option['default'] ) ? $option['default'] : "";
            $editor_settings= isset( $option['editor_settings'] ) ? $option['editor_settings'] : array('textarea_name'=>$field_name);
            $value 			= isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;
            $field_id       = $id;

            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-wp_editor-wrapper
            field-wp_editor-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                wp_editor( html_entity_decode(nl2br($value)), $id, $settings = $editor_settings);
                ?>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }



        public function field_select2( $option ){



            $id 	    = isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args 	        = isset( $option['args'] ) ? $option['args'] : "";
            $args	    = is_array( $args ) ? $args : $this->args_from_string( $args );
            $default    = isset( $option['default'] ) ? $option['default'] : "";
            $multiple 	= isset( $option['multiple'] ) ? $option['multiple'] : false;

            $value		= isset( $option['value'] ) ? $option['value'] : '';
            $value      = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;

            if($multiple):
                $value = !empty($value) ? $value : array();
            endif;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-select2-wrapper
            field-select2-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                if($multiple):
                    ?>
                    <select name='<?php echo esc_attr($field_name); ?>[]' id='<?php echo esc_attr($field_id); ?>' multiple>
                    <?php
                else:
                    ?>
                    <select name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>'>
                    <?php
                endif;
                foreach( $args as $key => $name ):

                    if( $multiple ) $selected = in_array( $key, $value ) ? "selected" : "";
                    else $selected = $value == $key ? "selected" : "";
                    ?>
                    <option <?php echo esc_attr($selected); ?> value='<?php echo esc_attr($key); ?>'><?php echo esc_attr($name); ?></option>
                    <?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>
            </select>


            <?php
            return ob_get_clean();

        }

        public function field_time_slot( $option ){



            $id 	    = isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args 	        = isset( $option['args'] ) ? $option['args'] : "";
            $args	    = is_array( $args ) ? $args : $this->args_from_string( $args );
            $default    = isset( $option['default'] ) ? $option['default'] : "";
            $multiple 	= isset( $option['multiple'] ) ? $option['multiple'] : false;

            $value		= isset( $option['value'] ) ? $option['value'] : '';
            $value      = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;

            if($multiple):
                $value = !empty($value) ? $value : array();
            endif;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;

            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-select2-wrapper
            field-select2-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                if($multiple):
                    ?>
                    <select name='<?php echo esc_attr($field_name); ?>[]' id='<?php echo esc_attr($field_id); ?>' multiple>
                    <?php
                else:
                    ?>
                    <select name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>'>
                    <?php
                endif;
                foreach( $args as $key => $name ):

                    if( $multiple ) $selected = in_array( $name, $value ) ? "selected" : "";
                    else $selected = $value == $name ? "selected" : "";
                    ?>
                    <option <?php echo esc_attr($selected); ?> value='<?php echo esc_attr($name); ?>'><?php echo esc_attr($key); ?></option>
                    <?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>
            </select>

            <?php
            return ob_get_clean();

        }


        public function field_option_group( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            $options			= isset( $option['options'] ) ? $option['options'] : array();
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $values			= isset( $option['value'] ) ? $option['value'] : '';
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $FormFieldsGenerator = new RbfwFormFieldsGenerator();

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;




            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>

            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-option-group-tabs-wrapper
            field-option-group-tabs-wrapper-<?php echo esc_attr($id); ?>">

                        <?php
                        if(!empty($options)):
                            ?>
                            <div class="rbfw-form-table-wrap">
                            <table class="form-table">
                                <tbody>

                                <?php

                                foreach ($options as $key =>$option):

                                    $option_id = isset($option['id']) ? $option['id'] : '';
                                    $option_title = isset($option['title']) ? $option['title'] : '';


                                    $option['field_name'] = $field_name.'['.$option_id.']';
                                    $option['value'] = isset($values[$option_id]) ? $values[$option_id] : '';


                                    ?>
                                    <tr>
                                        <th scope="row"><?php echo esc_html($option_title); ?></th>
                                        <td>
                                            <?php
                                            if (sizeof($option) > 0 && isset($option['type'])) {
                                                echo mep_field_generator($option['type'], $option);
                                                do_action("wp_theme_settings_field_$type", $option);
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php




                                endforeach;

                                ?>


                                </tbody>
                            </table>
                            </div>
                        <?php

                        endif;
                        ?>


                    <?php

                ?>

                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }

        public function field_option_group_tabs( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $values			= isset( $option['value'] ) ? $option['value'] : '';
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $FormFieldsGenerator = new RbfwFormFieldsGenerator();

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;




            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.faq-list-<?php echo esc_attr($id); ?> .faq-header', function() {
                        if(jQuery(this).parent().hasClass('active')){
                            jQuery(this).parent().removeClass('active');
                        }else{
                            jQuery(this).parent().addClass('active');
                        }
                    })
                })
            </script>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-option-group-tabs-wrapper
            field-option-group-tabs-wrapper-<?php echo esc_attr($id); ?>">

                <ul class="tab-navs">
                    <?php
                    $i = 1;
                    foreach( $args as $key => $value ):
                        $title = $value['title'];
                        ?>
                        <li index="<?php echo esc_attr($i); ?>" class="<?php if($i == 1) echo 'active'; ?>"><?php echo esc_html($title); ?></li>
                        <?php
                        $i++;
                    endforeach;
                    ?>
                </ul>


                    <?php
                    $i = 1;
                    foreach( $args as $key => $value ):
                        $title = $value['title'];
                        $link = $value['link'];
                        $options = $value['options'];
                        ?>
                        <div class="tab-content tab-content-<?php echo esc_attr($i); ?> <?php if($i == 1) echo 'active'; ?>">


                                <?php
                                if(!empty($options)):
                                    ?>
                                    <div class="rbfw-form-table-wrap">
                                    <table class="form-table">
                                        <tbody>

                                        <?php

                                        foreach ($options as $option):

                                            $option_id = isset($option['id']) ? $option['id'] : '';
                                            $option_title = isset($option['title']) ? $option['title'] : '';

                                            $option['field_name'] = $field_name.'['.$key.']['.$option_id.']';
                                            $option['value'] = isset($values[$key][$option_id]) ? $values[$key][$option_id] : '';


                                            ?>
                                            <tr>
                                                <th scope="row"><?php echo esc_html($option['title']); ?></th>
                                                <td>
                                                    <?php
                                                        if (sizeof($option) > 0 && isset($option['type'])) {
                                                            echo mep_field_generator($option['type'], $option);
                                                            do_action("wp_theme_settings_field_$type", $option);
                                                        }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php




                                        endforeach;

                                        ?>


                                        </tbody>
                                    </table>
                                    </div>
                                <?php

                                endif;
                                ?>

                        </div>
                    <?php
                        $i++;
                    endforeach;
                    ?>

                <div class="error-mgs"></div>
            </div>
            <script>

                jQuery(document).on('click', '.field-option-group-tabs-wrapper-<?php echo esc_attr($id); ?> .tab-navs li', function() {

                    index = $(this).attr('index');

                    jQuery(".field-option-group-tabs-wrapper-<?php echo esc_attr($id); ?> .tab-navs li").removeClass('active');
                    jQuery(".field-option-group-tabs-wrapper-<?php echo esc_attr($id); ?> .tab-content").removeClass('active');
                    if(jQuery(this).hasClass('active')){

                    }else{
                        jQuery(this).addClass('active');
                        jQuery(".field-option-group-tabs-wrapper-<?php echo esc_attr($id); ?> .tab-content-"+index).addClass('active');
                    }



                })


            </script>
            <?php
            return ob_get_clean();
        }


        public function field_option_group_accordion( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $values			= isset( $option['value'] ) ? $option['value'] : '';
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $FormFieldsGenerator = new RbfwFormFieldsGenerator();

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;




            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.faq-list-<?php echo esc_attr($id); ?> .faq-header', function() {
                        if(jQuery(this).parent().hasClass('active')){
                            jQuery(this).parent().removeClass('active');
                        }else{
                            jQuery(this).parent().addClass('active');
                        }
                    })
                })
            </script>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-faq-wrapper
            field-faq-wrapper-<?php echo esc_attr($id); ?>">
                <div class='faq-list faq-list-<?php echo esc_attr($id); ?>'>
                    <?php
                    foreach( $args as $key => $value ):
                        $title      = $value['title'];
                        $link       = $value['link'];
                        $options    = $value['options'];
                        ?>
                        <div class="faq-item">
                            <div class="faq-header"><?php echo esc_html($title); ?></div>
                            <div class="faq-content">
                                <?php
                                if(!empty($options)):
                                    ?>
                                    <div class="rbfw-form-table-wrap">
                                    <table class="form-table">
                                        <tbody>
                                        <?php
                                        foreach ($options as $option):
                                            $option['field_name'] = $field_name.'['.$key.']['.$option['id'].']';
                                            $option['value'] = $values[$key][$option['id']];
                                                ?>
                                                <tr>
                                                    <th scope="row"><?php echo esc_html($option['title']); ?></th>
                                                    <td>
                                                        <?php
                                                            if (sizeof($option) > 0 && isset($option['type'])) {
                                                                echo mep_field_generator($option['type'], $option);
                                                                do_action("wp_theme_settings_field_$type", $option);
                                                            }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <?php
                                            endforeach;
                                        ?>
                                        </tbody>
                                    </table>
                                        </div>
                                    <?php

                                endif;
                                ?>
                            </div>
                        </div>
                    <?php
                    endforeach;
                    ?>
                </div>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }


        public function field_faq( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            $args			= isset( $option['args'] ) ? $option['args'] : array();

            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.faq-list-<?php echo esc_attr($id); ?> .faq-header', function() {
                        if(jQuery(this).parent().hasClass('active')){
                            jQuery(this).parent().removeClass('active');
                        }else{
                            jQuery(this).parent().addClass('active');
                        }
                    })
                })
            </script>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-faq-wrapper
            field-faq-wrapper-<?php echo esc_attr($id); ?>">
                <div class='faq-list faq-list-<?php echo esc_attr($id); ?>'>
                    <?php
                    foreach( $args as $key => $value ):
                        $title = $value['title'];
                        $link = $value['link'];
                        $content = $value['content'];
                        ?>
                        <div class="faq-item">
                            <div class="faq-header"><?php echo esc_html($title); ?></div>
                            <div class="faq-content"><?php echo esc_html($content); ?></div>
                        </div>
                    <?php
                    endforeach;
                    ?>
                </div>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }




        public function field_grid( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            $args 			= isset( $option['args'] ) ? $option['args'] : "";
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $widths 		= isset( $option['width'] ) ? $option['width'] : array('768px'=>'100%','992px'=>'50%', '1200px'=>'30%', );
            $heights 		= isset( $option['height'] ) ? $option['height'] : array('768px'=>'auto','992px'=>'250px', '1200px'=>'250px', );


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;




            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-grid-wrapper
            field-grid-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach($args as $key=>$grid_item){
                    $title = isset($grid_item['title']) ? $grid_item['title'] : '';
                    $link = isset($grid_item['link']) ? $grid_item['link'] : '';
                    $thumb = isset($grid_item['thumb']) ? $grid_item['thumb'] : '';
                    ?>
                    <div class="item">
                        <div class="thumb"><a href="<?php echo esc_attr($link); ?>"><img src="<?php echo esc_attr($thumb); ?>"></img></a></div>
                        <div class="name"><a href="<?php echo esc_attr($link); ?>"><?php echo esc_html($title); ?></a></div>
                    </div>
                    <?php
                }
                ?>
                <div class="error-mgs"></div>
            </div>
            <style type="text/css">
                <?php
                if(!empty($widths)):
                    foreach ($widths as $screen_size=>$width):
                    $height = !empty($heights[$screen_size]) ? $heights[$screen_size] : 'auto';
                    ?>
                    @media screen and (min-width: <?php echo esc_attr($screen_size); ?>) {
                        .field-grid-wrapper-<?php echo esc_attr($id); ?> .item{
                            width: <?php echo esc_attr($width); ?>;
                        }
                        .field-grid-wrapper-<?php echo esc_attr($id); ?> .item .thumb{
                            height: <?php echo esc_attr($height); ?>;
                        }
                    }
                    <?php
                    endforeach;
                endif;
                ?>
            </style>

            <?php
            return ob_get_clean();
        }





        public function field_color_sets( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $width			= isset( $args['width'] ) ? $args['width'] : "";
            $height			= isset( $args['height'] ) ? $args['height'] : "";
            $sets		    = isset( $option['sets'] ) ? $option['sets'] : array();
            //$option_value	= get_option( $id );
            $default		= isset( $option['default'] ) ? $option['default'] : '';
            $value			= isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-color-sets-wrapper
            field-color-sets-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $sets as $key => $set ):

                    //var_dump($value);

                    $checked = ( $key == $value ) ? "checked" : "";
                    ?>
                    <label  class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'>
                        <input name='<?php echo esc_attr($field_name); ?>' type='radio' id='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>>
                        <?php
                        foreach ($set as $color):
                            ?>
                            <span class="color-srick" style="background-color: <?php echo esc_attr($color); ?>;"></span>
                            <?php

                        endforeach;
                        ?>


                        <span class="checked-icon"><i class="fas fa-check"></i></span>

                    </label><?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>
            <style type="text/css">
                .field-color-palette-wrapper-<?php echo esc_attr($id); ?> .sw-button{
                    transition: ease all 1s;
                <?php if(!empty($width)):  ?>
                    width: <?php echo esc_attr($width); ?>;
                <?php endif; ?>
                <?php if(!empty($height)):  ?>
                    height: <?php echo esc_attr($height); ?>;
                <?php endif; ?>
                }
                .field-color-palette-wrapper-<?php echo esc_attr($id); ?> label:hover .sw-button{
                }
            </style>


            <?php
            return ob_get_clean();

        }


        public function field_image_link( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $width				= isset( $args['width'] ) ? $args['width'] : "";
            $height				= isset( $args['height'] ) ? $args['height'] : "";
            $links		= isset( $option['links'] ) ? $option['links'] : array();
            //$option_value	= get_option( $id );
            $default			= isset( $option['default'] ) ? $option['default'] : '';
            $value			= isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-image-link-wrapper
            field-image-link-wrapper-<?php echo esc_attr($id); ?>">
                <?php


                    if(!empty($links))
                        foreach( $links as $key => $link ):



                            $checked = ( $link == $value ) ? "checked" : "";
                            ?><label  class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'><input
                                    type='radio' id='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'
                                    value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>>
                            <img src="<?php echo esc_attr($link); ?>">
                            <span class="checked-icon"><i class="fas fa-check"></i></span>
                            </label><?php
                        endforeach;
                if(!in_array($value, $links)){
                    ?><label  class="checked" for='<?php echo esc_attr($id); ?>-custom'><input
                            type='radio' id='<?php echo esc_attr($id); ?>-custom'
                            value='<?php echo esc_attr($value); ?>' checked>
                    <img src="<?php echo esc_attr($value); ?>">
                    <span class="checked-icon"><i class="fas fa-check"></i></span>
                    </label><?php
                }


                ?>
                <div class="val-wrap">
                    <input class="link-val" name='<?php echo esc_attr($field_name); ?>' type="text" value="<?php echo esc_attr($value); ?>"> <span class='ppof-button upload' id='media_upload_<?php echo esc_attr($id); ?>'><?php echo __('Upload','pickplugins-options-framework');?></span> <span class="ppof-button clear">Clear</span>
                </div>
                <div class="error-mgs"></div>
            </div>
            <script>jQuery(document).ready(function($){
                    $('#media_upload_<?php echo esc_attr($id); ?>').click(function() {
                        //var send_attachment_bkp = wp.media.editor.send.attachment;
                        wp.media.editor.send.attachment = function(props, attachment) {
                            //$('#media_preview_<?php echo esc_attr($id); ?>').attr('src', attachment.url);
                            //$('#media_input_<?php echo esc_attr($id); ?>').val(attachment.url);
                            jQuery('.field-image-link-wrapper-<?php echo esc_attr($id); ?> .link-val').val(attachment.url);
                            //wp.media.editor.send.attachment = send_attachment_bkp;
                        }
                        wp.media.editor.open($(this));
                        return false;
                    });

                });
            </script>
            <style type="text/css">
                .field-image-link-wrapper-<?php echo esc_attr($id); ?> img{
                    transition: ease all 1s;
                <?php if(!empty($width)):  ?>
                    width: <?php echo esc_attr($width); ?>;
                <?php endif; ?>
                <?php if(!empty($height)):  ?>
                    height: <?php echo esc_attr($height); ?>;
                <?php endif; ?>
                }
                .field-color-palette-wrapper-<?php echo esc_attr($id); ?> label:hover .sw-button{
                }
            </style>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.field-image-link-wrapper-<?php echo esc_attr($id); ?> .clear', function() {
                        jQuery('.field-image-link-wrapper-<?php echo esc_attr($id); ?> .link-val').val("");
                    })

                    jQuery(document).on('click', '.field-image-link-wrapper-<?php echo esc_attr($id); ?> img', function() {

                        var src = $(this).attr('src');
                        jQuery('.field-image-link-wrapper-<?php echo esc_attr($id); ?> .link-val').val(src);

                        jQuery('.field-image-link-wrapper-<?php echo esc_attr($id); ?> label').removeClass('checked');
                        if(jQuery(this).parent().hasClass('checked')){
                            jQuery(this).parent().removeClass('checked');
                        }else{
                            jQuery(this).parent().addClass('checked');
                        }
                    })
                })
            </script>

            <?php
            return ob_get_clean();

        }


        public function field_color_palette( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $width				= isset( $args['width'] ) ? $args['width'] : "";
            $height				= isset( $args['height'] ) ? $args['height'] : "";
            $colors			= isset( $option['colors'] ) ? $option['colors'] : array();
            //$option_value	= get_option( $id );
            $default			= isset( $option['default'] ) ? $option['default'] : '';
            $value			= isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-color-palette-wrapper
            field-color-palette-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $colors as $key => $color ):

                    $checked = ( $key == $value ) ? "checked" : "";
                    ?><label  class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'><input
                            name='<?php echo esc_attr($field_name); ?>' type='radio' id='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'
                            value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><span title="<?php echo esc_attr($color); ?>" style="background-color: <?php
                    echo esc_attr($color); ?>" class="sw-button"></span>
                    <span class="checked-icon"><i class="fas fa-check"></i></span>
                    </label><?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>
            <style type="text/css">
                .field-color-palette-wrapper-<?php echo esc_attr($id); ?> .sw-button{
                    transition: ease all 1s;
                <?php if(!empty($width)):  ?>
                    width: <?php echo esc_attr($width); ?>;
                <?php endif; ?>
                <?php if(!empty($height)):  ?>
                    height: <?php echo esc_attr($height); ?>;
                <?php endif; ?>
                }
                .field-color-palette-wrapper-<?php echo esc_attr($id); ?> label:hover .sw-button{
                }
            </style>


            <?php
            return ob_get_clean();

        }




        public function field_color_palette_multi( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";

            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $width				= isset( $args['width'] ) ? $args['width'] : "";
            $height				= isset( $args['height'] ) ? $args['height'] : "";
            $colors			= isset( $option['colors'] ) ? $option['colors'] : array();
            $default			= isset( $option['default'] ) ? $option['default'] : '';
            $value			= isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-color-palette-multi-wrapper
            field-color-palette-multi-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $colors as $key => $color ):
                    $checked = is_array( $value ) && in_array( $key, $value ) ? "checked" : "";
                    ?><label  class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'><input
                            name='<?php echo esc_attr($field_name); ?>[]' type='checkbox' id='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'
                            value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><span title="<?php echo esc_attr($color); ?>" style="background-color: <?php
                    echo esc_attr($color); ?>" class="sw-button"></span>
                    <span class="checked-icon"><i class="fas fa-check"></i></span>
                    </label><?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>
            <style type="text/css">
                .field-color-palette-multi-wrapper-<?php echo esc_attr($id); ?> .sw-button{
                    transition: ease all 1s;
                <?php if(!empty($width)):  ?>
                    width: <?php echo esc_attr($width); ?>;
                <?php endif; ?>
                <?php if(!empty($height)):  ?>
                    height: <?php echo esc_attr($height); ?>;
                <?php endif; ?>
                }
                .field-color-palette-multi-wrapper-<?php echo esc_attr($id); ?> label:hover .sw-button{
                }
            </style>


            <?php
            return ob_get_clean();
        }




        public function field_media( $option ){

            $id			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";

            $default			= isset( $option['default'] ) ? $option['default'] : '';
            $value			= isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ?  $value : $default;

            $media_url	= wp_get_attachment_url( $value );
            $media_type	= get_post_mime_type( $value );
            $media_title= get_the_title( $value );
            $media_url = !empty($media_url) ? $media_url : $placeholder;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            wp_enqueue_media();

            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-media-wrapper
            field-media-wrapper-<?php echo esc_attr($id); ?>">
                <div class='media_preview' style='width: 150px;margin-bottom: 10px;background: #eee;padding: 5px;    text-align: center;'>
                    <?php

                    if( "audio/mpeg" == $media_type ){
                        ?>
                        <div id='media_preview_$id' class='dashicons dashicons-format-audio' style='font-size: 70px;display: inline;'></div>
                        <div><?php echo esc_html($media_title); ?></div>
                        <?php
                    }
                    else {
                        ?>
                        <img id='media_preview_<?php echo esc_attr($id); ?>' src='<?php echo esc_attr($media_url); ?>' style='width:100%'/>
                        <?php
                    }
                    ?>
                </div>
                <input type='hidden' name='<?php echo esc_attr($field_name); ?>' id='media_input_<?php echo esc_attr($id); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class='ppof-button upload' id='media_upload_<?php echo esc_attr($id); ?>'><?php echo __('Upload','pickplugins-options-framework');?></div><div class='ppof-button clear' id='media_clear_<?php echo esc_attr($id); ?>'><?php echo __('Clear','pickplugins-options-framework');?></div>
                <div class="error-mgs"></div>
            </div>

            <script>jQuery(document).ready(function($){
                    $('#media_upload_<?php echo esc_attr($id); ?>').click(function() {
                        var send_attachment_bkp = wp.media.editor.send.attachment;
                        wp.media.editor.send.attachment = function(props, attachment) {
                            $('#media_preview_<?php echo esc_attr($id); ?>').attr('src', attachment.url);
                            $('#media_input_<?php echo esc_attr($id); ?>').val(attachment.id);
                            wp.media.editor.send.attachment = send_attachment_bkp;
                        }
                        wp.media.editor.open($(this));
                        return false;
                    });
                    $('#media_clear_<?php echo esc_attr($id); ?>').click(function() {
                        $('#media_input_<?php echo esc_attr($id); ?>').val('');
                        $('#media_preview_<?php echo esc_attr($id); ?>').attr('src','');
                    })

                });
            </script>

            <?php
            return ob_get_clean();
        }




        public function field_media_multi( $option ){
                    

            $id			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $remove_text			= isset( $option['remove_text'] ) ? $option['remove_text'] : 'X';
            $default			= isset( $option['default'] ) ? $option['default'] : '';
            $values			= isset( $option['value'] ) ? $option['value'] : '';

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            wp_enqueue_media();

            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-media-multi-wrapper
            field-media-multi-wrapper-<?php echo esc_attr($id); ?>">
                <div class='button upload' id='media_upload_<?php echo esc_attr($id); ?>'>
                    <?php echo __('Upload','pickplugins-options-framework');?>
                </div>
                <div class='button clear' id='media_clear_<?php echo $id; ?>'>
                    <?php echo __('Clear','pickplugins-options-framework');?>
                </div>
                <div class="media-list my-1 media-list-<?php echo esc_attr($id); ?> sortable">
                    <?php
                    if(!empty($values) && is_array($values)):
                        foreach ($values as $value ):
                            $media_url	= wp_get_attachment_url( $value );
                            $media_type	= get_post_mime_type( $value );
                            $media_title= get_the_title( $value );
                            ?>
                            <div class="item m-1">
                                <span class="remove" onclick="jQuery(this).parent().remove()"><?php echo mep_esc_html($remove_text); ?></span>
                                <span class="sort" ><i class="fa-solid fa-grip"></i></span>
                                <img id='media_preview_<?php echo esc_attr($id); ?>' src='<?php echo esc_attr($media_url); ?>' style='width:100%'/>
                                <div class="item-title"><?php // echo esc_html($media_title); ?></div>
                                <input type='hidden' name='<?php echo esc_attr($field_name); ?>[]' value='<?php echo esc_attr($value); ?>' />
                            </div>
                        <?php
                        endforeach;
                    endif;
                    ?>
                </div>
                <div class="error-mgs"></div>
            </div>
            <script>jQuery(document).ready(function($){
                    $('#media_upload_<?php echo esc_attr($id); ?>').click(function() {
                        //var send_attachment_bkp = wp.media.editor.send.attachment;
                        wp.media.editor.send.attachment = function(props, attachment) {
                            attachment_id = attachment.id;
                            attachment_url = attachment.url;
                            html = '<div class="item">';
                            html += '<span class="remove" onclick="jQuery(this).parent().remove()"><?php echo esc_html($remove_text); ?></span>';
                            html += '<img src="'+attachment_url+'" style="width:100%"/>';
                            html += '<input type="hidden" name="<?php echo esc_attr($field_name); ?>[]" value="'+attachment_id+'" />';
                            html += '</div>';
                            $('.media-list-<?php echo esc_attr($id); ?>').append(html);
                            //wp.media.editor.send.attachment = send_attachment_bkp;
                        }
                        wp.media.editor.open($(this));
                        return false;
                    });
                    $('#media_clear_<?php echo esc_attr($id); ?>').click(function() {
                        $('.media-list-<?php echo esc_attr($id); ?> .item').remove();
                    })
                });
            </script>

            <?php
            return ob_get_clean();
        }




        public function field_custom_html( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            $args 			= isset( $option['args'] ) ? $option['args'] : "";
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $html 			= isset( $option['html'] ) ? $option['html'] : "";


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;




            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-custom-html-wrapper
            field-custom-html-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                echo esc_html($html);
                ?>
                <div class="error-mgs"></div>
            </div>

            <?php

            return ob_get_clean();


        }

        function get_form_title($arr,$val){
            foreach ($arr as $_arr) {
                $name[] = $val[$_arr];
            }

            return join(' - ',$name);
        }

        public function field_repeatable( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $sortable 	    = isset( $option['sortable'] ) ? $option['sortable'] : true;
            $collapsible 	= isset( $option['collapsible'] ) ? $option['collapsible'] : true;
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $values			= isset( $option['value'] ) ? $option['value'] : '';
            $btntext		= isset( $option['btn_text'] ) ? $option['btn_text'] : 'Add';
            $fields 		= isset( $option['fields'] ) ? $option['fields'] : array();
            $title_field 	= isset( $option['title_field'] ) ? $option['title_field'] : '';
            $remove_text 	= isset( $option['remove_text'] ) ? $option['remove_text'] : '<i class="fa-solid fa-trash-can"></i>';
            $limit 	        = isset( $option['limit'] ) ? $option['limit'] : '';
            $args 	        = isset( $option['args'] ) ? $option['args'] : '';
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );
            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            $new_title      =  explode('/',$title_field);
            $title_field    = $new_title;
            foreach ($fields as $key => $value) {
                # code...
                $new[$key]['type']      = $fields[$key]['type'];
                $new[$key]['default']   = $fields[$key]['default'];
                $new[$key]['item_id']   = $fields[$key]['item_id'];
                $new[$key]['name']      = $fields[$key]['name'];
                if(array_key_exists('args',$value)){
                 $new[$key]['args']      = !is_array($fields[$key]['args']) ? $this->args_from_string($fields[$key]['args']) : $fields[$key]['args'];
                }

            }
            $fields = $new;


            if(!empty($conditions)):

                $depends = '';

                $field      = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type       = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern    = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier   = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like       = isset($conditions['like']) ? $conditions['like'] : '';
                $strict     = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty      = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign       = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min        = isset($conditions['min']) ? $conditions['min'] : '';
                $max        = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';
            endif;
            ob_start();
            ?>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.field-repeatable-wrapper-<?php echo esc_attr($id); ?> .collapsible .header .title-text', function() {
                        if(jQuery(this).parent().parent().hasClass('active')){
                            jQuery(this).parent().parent().removeClass('active');
                            jQuery(this).empty();
                            jQuery(this).html('<i class="fa-solid fa-angles-down"></i> Expand');
                        }else{
                            jQuery(this).parent().parent().addClass('active');
                            jQuery(this).empty();
                            jQuery(this).html('<i class="fa-solid fa-angles-up"></i> Collapse');
                        }
                    })

                    jQuery(document).on('click', '.field-repeatable-wrapper-<?php echo esc_attr($id); ?> .clone',function(){

                        //event.preventDefault();

                        index_id = $(this).attr('index_id');
                        now = jQuery.now();
                        <?php
                        if(!empty($limit)):


                        ?>
                        var limit = <?php  echo esc_attr($limit); ?>;
                        var node_count = $( ".field-repeatable-wrapper-<?php echo esc_attr($id); ?> .field-list .item-wrap" ).size();
                        if(limit > node_count){
                            $( this ).parent().parent().clone().appendTo('.field-repeatable-wrapper-<?php echo esc_attr($id); ?> .field-list' );
                           // html = $( this ).parent().parent().clone();
                            //var html_new = html.replace(index_id, now);
                            //jQuery('.<?php echo 'field-repeatable-wrapper-'.$id; ?> .field-list').append(html_new);
                            //console.log(html);

                        }else{
                            jQuery('.field-repeatable-wrapper-<?php echo esc_attr($id); ?> .error-mgs').html('Sorry! you can add max '+limit+' item').stop().fadeIn(400).delay(3000).fadeOut(400);
                        }
                        <?php
                        else:
                        ?>
                        $( this ).parent().clone().appendTo('.field-repeatable-wrapper-<?php echo esc_attr($id); ?> .field-list' );
                        <?php
                        endif;
                        ?>
                    })

                    jQuery(document).on('click', '.field-repeatable-wrapper-<?php echo esc_attr($id); ?> .add-item', function() {
                        now = jQuery.now();
                        fields_arr = <?php echo json_encode($fields); ?>;
                        html = '<div class="item-wrap collapsible component"><div class="header">';
                        <?php if($collapsible === true): ?>
                        html += '<span  class="title-text button"><i class="fa-solid fa-angles-down"></i> Expand</span>';
                        <?php endif; ?>
                        <?php if($sortable):?>
                        html += '<span class="button sort" ><i class="fas fa-arrows-alt"></i></span>';
                        <?php endif; ?>
						html += '<span class="button remove " ' +
                            'onclick="jQuery(this).parent().parent().remove()"><?php echo mep_esc_html($remove_text); ?></span>';
                        html += '</div>';
                        // html += ' <span  class="title-text">#'+now+'</span></div>';
                        fields_arr.forEach(function(element) {
                            type = element.type;
                            item_id = element.item_id;
                            default_val = element.default;
                            html+='<div class="item">';
                            <?php if($collapsible):?>
                            html+='<div class="content">';
                            <?php endif; ?>
                            html+='<div class="item-title">'+element.name+'</div>';
                            if(type == 'text'){
                                html+='<input type="text" value="'+default_val+'" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'number'){
                                html+='<input type="number" value="'+default_val+'" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'tel'){
                                html+='<input type="tel" value="'+default_val+'" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'time'){
                                html+='<input type="time" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'url'){
                                html+='<input type="url" value="'+default_val+'" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'date'){
                                html+='<input type="date" value="'+default_val+'" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'month'){
                                html+='<input type="month" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'search'){
                                html+='<input type="search" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'color'){
                                html+='<input type="color" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'email'){
                                html+='<input type="email" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'textarea'){
                                html+='<textarea name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']">'+default_val+'</textarea>';
                            }else if(type == 'select'){
                                args = element.args;
                                html+='<select name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']">';
                                for(argKey in args){
                                    html+='<option value="'+argKey+'">'+args[argKey]+'</option>';
                                }
                                html+='</select>';
                            }else if(type == 'radio'){
                                args = element.args;
                                for(argKey in args){
                                    html+='<label>';
                                    html+='<input value="'+argKey+'" type="radio" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                                    html+= args[argKey];
                                    html+='</label ><br/>';
                                }
                            }else if(type == 'checkbox'){
                                args = element.args;
                                for(argKey in args){
                                    html+='<label>';
                                    html+='<input value="'+argKey+'" type="checkbox" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                                    html+= args[argKey];
                                    html+='</label ><br/>';
                                }
                            }
                            <?php if($collapsible):?>
                            html+='</div>';
                            <?php endif; ?>
                            html+='</div>';
                        });
                        html+='</div>';
                        
                        <?php
                        if(!empty($limit)):
                            ?>
                            var limit = <?php  echo esc_attr($limit); ?>;
                            var node_count = $( ".field-repeatable-wrapper-<?php echo esc_attr($id); ?> .field-list .item-wrap" ).size();
                            if(limit > node_count){
                                jQuery('.<?php echo 'field-repeatable-wrapper-'.$id; ?> .field-list').append(html);
                            }else{
                                jQuery('.field-repeatable-wrapper-<?php echo esc_attr($id); ?> .error-mgs').html('Sorry! you can add max '+limit+' item').stop().fadeIn(400).delay(3000).fadeOut(400);
                            }

                            <?php
                        else:
                            ?>
                            jQuery('.<?php echo 'field-repeatable-wrapper-'.$id; ?> .field-list').append(html);
                            <?php
                        endif;
                        ?>
                    })
                });
            </script>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-repeatable-wrapper
            field-repeatable-wrapper-<?php echo esc_attr($id); ?>">

                <div class="field-list <?php if($sortable){ echo 'sortable'; }?>" id="<?php echo esc_attr($id); ?>">
                    <?php
                    if(!empty($values)):
                        $count = 1;
                        foreach ($values as $index=>$val):
                            $title_field_val = !empty($title_field) ? $this->get_form_title($title_field,$val) : '==> Click to Expand';
                            ?>
                            <div class="item-wrap <?php if($collapsible) echo 'collapsible'; ?>">

                                <div class="header">

                                    <?php if($collapsible === true): ?>
                                    <span class="title-text button"><i class="fa-solid fa-angles-down"></i> Expand</span>
                                    <?php endif; ?>
                                    <?php if($sortable):?>
                                        <span class="button sort"><i class="fas fa-arrows-alt"></i></span>
                                    <?php endif; ?>

                                    <span class="button remove" onclick="jQuery(this).parent().parent().remove()"><?php echo mep_esc_html($remove_text); ?></span>

                                </div>

                                <?php foreach ($fields as $field_index => $field):
                                    $type               = $field['type'];
                                    $item_id            = $field['item_id'];
                                    $name               = $field['name'];
                                    $title_field_class = ($title_field == $field_index) ? 'title-field':'';
                                    ?>
                                    <div class="item <?php echo esc_attr($title_field_class); ?>">
                                        <?php if($collapsible):?>
                                        <div class="content">
                                            <?php endif; ?>
                                            <div class="item-title"><?php echo esc_attr($name); ?></div>
                                            <?php if($type == 'text'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="text" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'number'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="number" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'url'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="url" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'tel'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="tel" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'time'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="time" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'search'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="search" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'month'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="month" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'color'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="color" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'date'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="date" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'email'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="email" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'textarea'):
                                                $default    = isset($field['default']) ? $field['default'] : '';
                                                $_value     = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                $__value    = str_replace('<br />', PHP_EOL, html_entity_decode($_value));;
                                                ?>
                                                <textarea name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]"><?php echo mep_esc_html($__value); ?></textarea>
                                            <?php elseif($type == 'select'):
                                                $args = isset($field['args']) ? $field['args'] : array();
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;


                                                ?>
                                                <select class="" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]">
                                                <?php
                                                   if(!is_array($args)){
                                                   $this->args_from_string($args);
                                                   }else{
                                                        foreach ($args as $argIndex => $argName):
                                                        $selected = ($argIndex == $value) ? 'selected' : '';
                                                        ?>
                                                        <option <?php echo esc_attr($selected); ?>  value="<?php echo esc_attr($argIndex); ?>"><?php echo esc_html($argName); ?></option>
                                                    <?php endforeach; }?>
                                                </select>
                                            <?php elseif($type == 'radio'):
                                                $args = isset($field['args']) ? $field['args'] : array();
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <?php
                                                if(!is_array($args)){
                                                    $this->args_from_string($args);
                                                }else{
                                                foreach ($args as $argIndex => $argName):
                                                $checked = ($argIndex == $value) ? 'checked' : '';

                                                ?>
                                                <label class="" >
                                                    <input  type="radio" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" <?php echo esc_attr($checked); ?>  value="<?php echo esc_attr($argIndex); ?>"><?php echo esc_html($argName); ?></input>
                                                </label>
                                            <?php endforeach; } ?>
                                            <?php elseif($type == 'checkbox'):
                                                $args = isset($field['args']) ? $field['args'] : array();
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <?php

                                                foreach ($args as $argIndex => $argName):
                                                $value = is_array($value) ? $value : array();
                                                // print_r($value);
                                                $checked = in_array($argIndex, $value ) ? 'checked' : '';
                                                // $checked = isset($argIndex) ? 'checked' : '';
                                                ?>
                                                <label class="" >
                                                    <input  type="checkbox" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>][]" <?php echo esc_attr($checked); ?>  value="<?php echo esc_attr($argIndex); ?>"><?php echo esc_html($argName); ?></input>
                                                </label>
                                            <?php endforeach; ?>
                                            <?php
                                            else:
                                                do_action('repeatable_custom_input_field_'.$type, $field);
                                                ?>
                                            <?php endif;?>
                                            <?php if($collapsible):?>
                                        </div>
                                    <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?php
                            //endforeach;
                            $count++;
                        endforeach;
                        ?>

                        <?php
                    else:
                        ?>
                    <?php
                    endif;
                    ?>
                </div>
                <div class="error-mgs"></div>
                <div class="ppof-button add-item"><i class="fas fa-plus-square"></i> <?php echo esc_html($btntext); ?></div>
            </div>


            <?php
            return ob_get_clean();
        }

        public function get_tax_data($args){
            foreach ($this->get_rep_taxonomies_array( $args ) as $argIndex => $argName):
            $selected = ($argIndex == $value) ? 'selected' : ''; ?><option <?php echo esc_attr($selected); ?>  value="<?php echo esc_attr($argIndex); ?>"><?php echo esc_html($argName); ?></option> <?php endforeach;
        }


        public function args_from_string( $string ){

            if( strpos( $string, 'PAGES_IDS_ARRAY' )    !== false ) return $this->get_pages_array();
            if( strpos( $string, 'POSTS_IDS_ARRAY' )    !== false ) return $this->get_posts_array();
            if( strpos( $string, 'POST_TYPES_ARRAY' )   !== false ) return $this->get_post_types_array();
            if( strpos( $string, 'TAX_' )               !== false ) return $this->get_taxonomies_array( $string );
            if( strpos( $string, 'TAXN_' )               !== false ) return $this->get_rep_taxonomies_array( $string );
            if( strpos( $string, 'CPT_' )               !== false ) return $this->get_cpt_array( $string );
            if( strpos( $string, 'USER_ROLES' )         !== false ) return $this->get_user_roles_array();
            if( strpos( $string, 'USER_IDS_ARRAY' )     !== false ) return $this->get_user_ids_array();
            if( strpos( $string, 'MENUS' )              !== false ) return $this->get_menus_array();
            if( strpos( $string, 'SIDEBARS_ARRAY' )     !== false ) return $this->get_sidebars_array();
            if( strpos( $string, 'THUMB_SIEZS_ARRAY' )  !== false ) return $this->get_thumb_sizes_array();
            if( strpos( $string, 'FONTAWESOME_ARRAY' )  !== false ) return $this->get_font_aws_array();
            return array();
        }




        public function get_rep_taxonomies_array( $string ){

            $taxonomies = array();

            preg_match_all( "/\%([^\]]*)\%/", $string, $matches );

            if( isset( $matches[1][0] ) ) $taxonomy = $matches[1][0];
            else throw new Pick_error('Invalid taxonomy declaration !');

            if( ! taxonomy_exists( $taxonomy ) ) throw new Pick_error("Taxonomy <strong>$taxonomy</strong> doesn't exists !");

            $terms = get_terms( $taxonomy, array(
                'hide_empty' => false,
            ) );

            foreach( $terms as $term ) $taxonomies[ $term->name ] = $term->name;

            return $taxonomies;
        }



        public function get_taxonomies_array( $string ){
            $taxonomies = array();
            preg_match_all( "/\%([^\]]*)\%/", $string, $matches );
            if( isset( $matches[1][0] ) ) $taxonomy = $matches[1][0];
            else throw new Pick_error('Invalid taxonomy declaration !');
            if( ! taxonomy_exists( $taxonomy ) ) throw new Pick_error("Taxonomy <strong>$taxonomy</strong> doesn't exists !");
            $terms = get_terms( $taxonomy, array(
                'hide_empty' => false,
            ) );
            foreach( $terms as $term ) $taxonomies[ $term->term_id ] = $term->name;
            return $taxonomies;
        }

        public function get_cpt_array( $string ){
            preg_match_all( "/\%([^\]]*)\%/", $string, $matches );
            $cpt_name = $matches[1][0];
            $defaults = array(
                'numberposts'      => -1,
                'post_type' => $cpt_name,
            );
            $cpt_arr = get_posts($defaults);
            $cpt = array();
            foreach ($cpt_arr as $_cpt_arr) {
                $cpt[$_cpt_arr->ID]  = $_cpt_arr->post_title;
            }
            return $cpt;
        }

        public function get_user_ids_array(){
            $user_ids = array();
            $users = get_users();
            foreach( $users as $user ) $user_ids[ $user->ID ] = $user->display_name. '(#'.$user->ID.')';
            return apply_filters( 'USER_IDS_ARRAY', $user_ids );
        }


        public function get_pages_array(){
            $pages_array = array();
            foreach( get_pages() as $page ) $pages_array[ $page->ID ] = $page->post_title;
            return apply_filters( 'PAGES_IDS_ARRAY', $pages_array );
        }

        public function get_menus_array(){
            $menus = get_registered_nav_menus();
            return apply_filters( 'MENUS_ARRAY', $menus );
        }

        public function get_sidebars_array(){

            global $wp_registered_sidebars;
            $sidebars = $wp_registered_sidebars;

            foreach ($sidebars as $index => $sidebar){

                $sidebars_name[$index] = $sidebar['name'];
            }


            return apply_filters( 'SIDEBARS_ARRAY', $sidebars_name );
        }

        public function get_user_roles_array(){
            require_once ABSPATH . 'wp-admin/includes/user.php';

            $roles = get_editable_roles();

            foreach ($roles as $index => $data){

                $role_name[$index] = $data['name'];
            }

            return apply_filters( 'USER_ROLES', $role_name );
        }



        public function get_post_types_array(){

            $post_types = get_post_types('', 'names' );
            $pages_array = array();
            foreach( $post_types as $index => $name ) $pages_array[ $index ] = $name;

            return apply_filters( 'POST_TYPES_ARRAY', $pages_array );
        }


        public function get_posts_array(){

            $posts_array = array();
            foreach( get_posts(array('posts_per_page'=>-1)) as $page ) $posts_array[ $page->ID ] = $page->post_title;

            return apply_filters( 'POSTS_IDS_ARRAY', $posts_array );
        }


        public function get_thumb_sizes_array(){

            $get_intermediate_image_sizes =  get_intermediate_image_sizes();
            $get_intermediate_image_sizes = array_merge($get_intermediate_image_sizes,array('full'));
            $thumb_sizes_array = array();

            foreach( $get_intermediate_image_sizes as $key => $name ):
                $size_key = str_replace('_', ' ',$name);
                $size_key = str_replace('-', ' ',$size_key);
                $size_name = ucfirst($size_key);
                $thumb_sizes_array[$name] = $size_name;
            endforeach;

            return apply_filters( 'THUMB_SIEZS_ARRAY', $get_intermediate_image_sizes );
        }


        public function get_font_aws_array(){

            $fonts_arr = array (
                'fab fa-500px' => __( '500px', 'buildr' ),
                'fab fa-accessible-icon' => __( 'accessible-icon', 'buildr' ),
                'fab fa-accusoft' => __( 'accusoft', 'buildr' ),
                'fas fa-address-book' => __( 'address-book', 'buildr' ),
                'far fa-address-book' => __( 'address-book', 'buildr' ),
                'fas fa-address-card' => __( 'address-card', 'buildr' ),
                'far fa-address-card' => __( 'address-card', 'buildr' ),
                'fas fa-adjust' => __( 'adjust', 'buildr' ),
                'fab fa-adn' => __( 'adn', 'buildr' ),
                'fab fa-adversal' => __( 'adversal', 'buildr' ),
                'fab fa-affiliatetheme' => __( 'affiliatetheme', 'buildr' ),
                'fab fa-algolia' => __( 'algolia', 'buildr' ),
                'fas fa-align-center' => __( 'align-center', 'buildr' ),
                'fas fa-align-justify' => __( 'align-justify', 'buildr' ),
                'fas fa-align-left' => __( 'align-left', 'buildr' ),
                'fas fa-align-right' => __( 'align-right', 'buildr' ),
                'fas fa-allergies' => __( 'allergies', 'buildr' ),
                'fab fa-amazon' => __( 'amazon', 'buildr' ),
                'fab fa-amazon-pay' => __( 'amazon-pay', 'buildr' ),
                'fas fa-ambulance' => __( 'ambulance', 'buildr' ),
                'fas fa-american-sign-language-interpreting' => __( 'american-sign-language-interpreting', 'buildr' ),
                'fab fa-amilia' => __( 'amilia', 'buildr' ),
                'fas fa-anchor' => __( 'anchor', 'buildr' ),
                'fab fa-android' => __( 'android', 'buildr' ),
                'fab fa-angellist' => __( 'angellist', 'buildr' ),
                'fas fa-angle-double-down' => __( 'angle-double-down', 'buildr' ),
                'fas fa-angle-double-left' => __( 'angle-double-left', 'buildr' ),
                'fas fa-angle-double-right' => __( 'angle-double-right', 'buildr' ),
                'fas fa-angle-double-up' => __( 'angle-double-up', 'buildr' ),
                'fas fa-angle-down' => __( 'angle-down', 'buildr' ),
                'fas fa-angle-left' => __( 'angle-left', 'buildr' ),
                'fas fa-angle-right' => __( 'angle-right', 'buildr' ),
                'fas fa-angle-up' => __( 'angle-up', 'buildr' ),
                'fab fa-angrycreative' => __( 'angrycreative', 'buildr' ),
                'fab fa-angular' => __( 'angular', 'buildr' ),
                'fab fa-app-store' => __( 'app-store', 'buildr' ),
                'fab fa-app-store-ios' => __( 'app-store-ios', 'buildr' ),
                'fab fa-apper' => __( 'apper', 'buildr' ),
                'fab fa-apple' => __( 'apple', 'buildr' ),
                'fab fa-apple-pay' => __( 'apple-pay', 'buildr' ),
                'fas fa-archive' => __( 'archive', 'buildr' ),
                'fas fa-arrow-alt-circle-down' => __( 'arrow-alt-circle-down', 'buildr' ),
                'far fa-arrow-alt-circle-down' => __( 'arrow-alt-circle-down', 'buildr' ),
                'fas fa-arrow-alt-circle-left' => __( 'arrow-alt-circle-left', 'buildr' ),
                'far fa-arrow-alt-circle-left' => __( 'arrow-alt-circle-left', 'buildr' ),
                'fas fa-arrow-alt-circle-right' => __( 'arrow-alt-circle-right', 'buildr' ),
                'far fa-arrow-alt-circle-right' => __( 'arrow-alt-circle-right', 'buildr' ),
                'fas fa-arrow-alt-circle-up' => __( 'arrow-alt-circle-up', 'buildr' ),
                'far fa-arrow-alt-circle-up' => __( 'arrow-alt-circle-up', 'buildr' ),
                'fas fa-arrow-circle-down' => __( 'arrow-circle-down', 'buildr' ),
                'fas fa-arrow-circle-left' => __( 'arrow-circle-left', 'buildr' ),
                'fas fa-arrow-circle-right' => __( 'arrow-circle-right', 'buildr' ),
                'fas fa-arrow-circle-up' => __( 'arrow-circle-up', 'buildr' ),
                'fas fa-arrow-down' => __( 'arrow-down', 'buildr' ),
                'fas fa-arrow-left' => __( 'arrow-left', 'buildr' ),
                'fas fa-arrow-right' => __( 'arrow-right', 'buildr' ),
                'fas fa-arrow-up' => __( 'arrow-up', 'buildr' ),
                'fas fa-arrows-alt' => __( 'arrows-alt', 'buildr' ),
                'fas fa-arrows-alt-h' => __( 'arrows-alt-h', 'buildr' ),
                'fas fa-arrows-alt-v' => __( 'arrows-alt-v', 'buildr' ),
                'fas fa-assistive-listening-systems' => __( 'assistive-listening-systems', 'buildr' ),
                'fas fa-asterisk' => __( 'asterisk', 'buildr' ),
                'fab fa-asymmetrik' => __( 'asymmetrik', 'buildr' ),
                'fas fa-at' => __( 'at', 'buildr' ),
                'fab fa-audible' => __( 'audible', 'buildr' ),
                'fas fa-audio-description' => __( 'audio-description', 'buildr' ),
                'fab fa-autoprefixer' => __( 'autoprefixer', 'buildr' ),
                'fab fa-avianex' => __( 'avianex', 'buildr' ),
                'fab fa-aviato' => __( 'aviato', 'buildr' ),
                'fab fa-aws' => __( 'aws', 'buildr' ),
                'fas fa-backward' => __( 'backward', 'buildr' ),
                'fas fa-balance-scale' => __( 'balance-scale', 'buildr' ),
                'fas fa-ban' => __( 'ban', 'buildr' ),
                'fas fa-band-aid' => __( 'band-aid', 'buildr' ),
                'fab fa-bandcamp' => __( 'bandcamp', 'buildr' ),
                'fas fa-barcode' => __( 'barcode', 'buildr' ),
                'fas fa-bars' => __( 'bars', 'buildr' ),
                'fas fa-baseball-ball' => __( 'baseball-ball', 'buildr' ),
                'fas fa-basketball-ball' => __( 'basketball-ball', 'buildr' ),
                'fas fa-bath' => __( 'bath', 'buildr' ),
                'fas fa-battery-empty' => __( 'battery-empty', 'buildr' ),
                'fas fa-battery-full' => __( 'battery-full', 'buildr' ),
                'fas fa-battery-half' => __( 'battery-half', 'buildr' ),
                'fas fa-battery-quarter' => __( 'battery-quarter', 'buildr' ),
                'fas fa-battery-three-quarters' => __( 'battery-three-quarters', 'buildr' ),
                'fas fa-bed' => __( 'bed', 'buildr' ),
                'fas fa-beer' => __( 'beer', 'buildr' ),
                'fab fa-behance' => __( 'behance', 'buildr' ),
                'fab fa-behance-square' => __( 'behance-square', 'buildr' ),
                'fas fa-bell' => __( 'bell', 'buildr' ),
                'far fa-bell' => __( 'bell', 'buildr' ),
                'fas fa-bell-slash' => __( 'bell-slash', 'buildr' ),
                'far fa-bell-slash' => __( 'bell-slash', 'buildr' ),
                'fas fa-bicycle' => __( 'bicycle', 'buildr' ),
                'fab fa-bimobject' => __( 'bimobject', 'buildr' ),
                'fas fa-binoculars' => __( 'binoculars', 'buildr' ),
                'fas fa-birthday-cake' => __( 'birthday-cake', 'buildr' ),
                'fab fa-bitbucket' => __( 'bitbucket', 'buildr' ),
                'fab fa-bitcoin' => __( 'bitcoin', 'buildr' ),
                'fab fa-bity' => __( 'bity', 'buildr' ),
                'fab fa-black-tie' => __( 'black-tie', 'buildr' ),
                'fab fa-blackberry' => __( 'blackberry', 'buildr' ),
                'fas fa-blind' => __( 'blind', 'buildr' ),
                'fab fa-blogger' => __( 'blogger', 'buildr' ),
                'fab fa-blogger-b' => __( 'blogger-b', 'buildr' ),
                'fab fa-bluetooth' => __( 'bluetooth', 'buildr' ),
                'fab fa-bluetooth-b' => __( 'bluetooth-b', 'buildr' ),
                'fas fa-bold' => __( 'bold', 'buildr' ),
                'fas fa-bolt' => __( 'bolt', 'buildr' ),
                'fas fa-bomb' => __( 'bomb', 'buildr' ),
                'fas fa-book' => __( 'book', 'buildr' ),
                'fas fa-bookmark' => __( 'bookmark', 'buildr' ),
                'far fa-bookmark' => __( 'bookmark', 'buildr' ),
                'fas fa-bowling-ball' => __( 'bowling-ball', 'buildr' ),
                'fas fa-box' => __( 'box', 'buildr' ),
                'fas fa-box-open' => __( 'box-open', 'buildr' ),
                'fas fa-boxes' => __( 'boxes', 'buildr' ),
                'fas fa-braille' => __( 'braille', 'buildr' ),
                'fas fa-briefcase' => __( 'briefcase', 'buildr' ),
                'fas fa-briefcase-medical' => __( 'briefcase-medical', 'buildr' ),
                'fab fa-btc' => __( 'btc', 'buildr' ),
                'fas fa-bug' => __( 'bug', 'buildr' ),
                'fas fa-building' => __( 'building', 'buildr' ),
                'far fa-building' => __( 'building', 'buildr' ),
                'fas fa-bullhorn' => __( 'bullhorn', 'buildr' ),
                'fas fa-bullseye' => __( 'bullseye', 'buildr' ),
                'fas fa-burn' => __( 'burn', 'buildr' ),
                'fab fa-buromobelexperte' => __( 'buromobelexperte', 'buildr' ),
                'fas fa-bus' => __( 'bus', 'buildr' ),
                'fab fa-buysellads' => __( 'buysellads', 'buildr' ),
                'fas fa-calculator' => __( 'calculator', 'buildr' ),
                'fas fa-calendar' => __( 'calendar', 'buildr' ),
                'far fa-calendar' => __( 'calendar', 'buildr' ),
                'fas fa-calendar-alt' => __( 'calendar-alt', 'buildr' ),
                'far fa-calendar-alt' => __( 'calendar-alt', 'buildr' ),
                'fas fa-calendar-check' => __( 'calendar-check', 'buildr' ),
                'far fa-calendar-check' => __( 'calendar-check', 'buildr' ),
                'fas fa-calendar-minus' => __( 'calendar-minus', 'buildr' ),
                'far fa-calendar-minus' => __( 'calendar-minus', 'buildr' ),
                'fas fa-calendar-plus' => __( 'calendar-plus', 'buildr' ),
                'far fa-calendar-plus' => __( 'calendar-plus', 'buildr' ),
                'fas fa-calendar-times' => __( 'calendar-times', 'buildr' ),
                'far fa-calendar-times' => __( 'calendar-times', 'buildr' ),
                'fas fa-camera' => __( 'camera', 'buildr' ),
                'fas fa-camera-retro' => __( 'camera-retro', 'buildr' ),
                'fas fa-capsules' => __( 'capsules', 'buildr' ),
                'fas fa-car' => __( 'car', 'buildr' ),
                'fas fa-caret-down' => __( 'caret-down', 'buildr' ),
                'fas fa-caret-left' => __( 'caret-left', 'buildr' ),
                'fas fa-caret-right' => __( 'caret-right', 'buildr' ),
                'fas fa-caret-square-down' => __( 'caret-square-down', 'buildr' ),
                'far fa-caret-square-down' => __( 'caret-square-down', 'buildr' ),
                'fas fa-caret-square-left' => __( 'caret-square-left', 'buildr' ),
                'far fa-caret-square-left' => __( 'caret-square-left', 'buildr' ),
                'fas fa-caret-square-right' => __( 'caret-square-right', 'buildr' ),
                'far fa-caret-square-right' => __( 'caret-square-right', 'buildr' ),
                'fas fa-caret-square-up' => __( 'caret-square-up', 'buildr' ),
                'far fa-caret-square-up' => __( 'caret-square-up', 'buildr' ),
                'fas fa-caret-up' => __( 'caret-up', 'buildr' ),
                'fas fa-cart-arrow-down' => __( 'cart-arrow-down', 'buildr' ),
                'fas fa-cart-plus' => __( 'cart-plus', 'buildr' ),
                'fab fa-cc-amazon-pay' => __( 'cc-amazon-pay', 'buildr' ),
                'fab fa-cc-amex' => __( 'cc-amex', 'buildr' ),
                'fab fa-cc-apple-pay' => __( 'cc-apple-pay', 'buildr' ),
                'fab fa-cc-diners-club' => __( 'cc-diners-club', 'buildr' ),
                'fab fa-cc-discover' => __( 'cc-discover', 'buildr' ),
                'fab fa-cc-jcb' => __( 'cc-jcb', 'buildr' ),
                'fab fa-cc-mastercard' => __( 'cc-mastercard', 'buildr' ),
                'fab fa-cc-paypal' => __( 'cc-paypal', 'buildr' ),
                'fab fa-cc-stripe' => __( 'cc-stripe', 'buildr' ),
                'fab fa-cc-visa' => __( 'cc-visa', 'buildr' ),
                'fab fa-centercode' => __( 'centercode', 'buildr' ),
                'fas fa-certificate' => __( 'certificate', 'buildr' ),
                'fas fa-chart-area' => __( 'chart-area', 'buildr' ),
                'fas fa-chart-bar' => __( 'chart-bar', 'buildr' ),
                'far fa-chart-bar' => __( 'chart-bar', 'buildr' ),
                'fas fa-chart-line' => __( 'chart-line', 'buildr' ),
                'fas fa-chart-pie' => __( 'chart-pie', 'buildr' ),
                'fas fa-check' => __( 'check', 'buildr' ),
                'fas fa-check-circle' => __( 'check-circle', 'buildr' ),
                'far fa-check-circle' => __( 'check-circle', 'buildr' ),
                'fas fa-check-square' => __( 'check-square', 'buildr' ),
                'far fa-check-square' => __( 'check-square', 'buildr' ),
                'fas fa-chess' => __( 'chess', 'buildr' ),
                'fas fa-chess-bishop' => __( 'chess-bishop', 'buildr' ),
                'fas fa-chess-board' => __( 'chess-board', 'buildr' ),
                'fas fa-chess-king' => __( 'chess-king', 'buildr' ),
                'fas fa-chess-knight' => __( 'chess-knight', 'buildr' ),
                'fas fa-chess-pawn' => __( 'chess-pawn', 'buildr' ),
                'fas fa-chess-queen' => __( 'chess-queen', 'buildr' ),
                'fas fa-chess-rook' => __( 'chess-rook', 'buildr' ),
                'fas fa-chevron-circle-down' => __( 'chevron-circle-down', 'buildr' ),
                'fas fa-chevron-circle-left' => __( 'chevron-circle-left', 'buildr' ),
                'fas fa-chevron-circle-right' => __( 'chevron-circle-right', 'buildr' ),
                'fas fa-chevron-circle-up' => __( 'chevron-circle-up', 'buildr' ),
                'fas fa-chevron-down' => __( 'chevron-down', 'buildr' ),
                'fas fa-chevron-left' => __( 'chevron-left', 'buildr' ),
                'fas fa-chevron-right' => __( 'chevron-right', 'buildr' ),
                'fas fa-chevron-up' => __( 'chevron-up', 'buildr' ),
                'fas fa-child' => __( 'child', 'buildr' ),
                'fab fa-chrome' => __( 'chrome', 'buildr' ),
                'fas fa-circle' => __( 'circle', 'buildr' ),
                'far fa-circle' => __( 'circle', 'buildr' ),
                'fas fa-circle-notch' => __( 'circle-notch', 'buildr' ),
                'fas fa-clipboard' => __( 'clipboard', 'buildr' ),
                'far fa-clipboard' => __( 'clipboard', 'buildr' ),
                'fas fa-clipboard-check' => __( 'clipboard-check', 'buildr' ),
                'fas fa-clipboard-list' => __( 'clipboard-list', 'buildr' ),
                'fas fa-clock' => __( 'clock', 'buildr' ),
                'far fa-clock' => __( 'clock', 'buildr' ),
                'fas fa-clone' => __( 'clone', 'buildr' ),
                'far fa-clone' => __( 'clone', 'buildr' ),
                'fas fa-closed-captioning' => __( 'closed-captioning', 'buildr' ),
                'far fa-closed-captioning' => __( 'closed-captioning', 'buildr' ),
                'fas fa-cloud' => __( 'cloud', 'buildr' ),
                'fas fa-cloud-download-alt' => __( 'cloud-download-alt', 'buildr' ),
                'fas fa-cloud-upload-alt' => __( 'cloud-upload-alt', 'buildr' ),
                'fab fa-cloudscale' => __( 'cloudscale', 'buildr' ),
                'fab fa-cloudsmith' => __( 'cloudsmith', 'buildr' ),
                'fab fa-cloudversify' => __( 'cloudversify', 'buildr' ),
                'fas fa-code' => __( 'code', 'buildr' ),
                'fas fa-code-branch' => __( 'code-branch', 'buildr' ),
                'fab fa-codepen' => __( 'codepen', 'buildr' ),
                'fab fa-codiepie' => __( 'codiepie', 'buildr' ),
                'fas fa-coffee' => __( 'coffee', 'buildr' ),
                'fas fa-cog' => __( 'cog', 'buildr' ),
                'fas fa-cogs' => __( 'cogs', 'buildr' ),
                'fas fa-columns' => __( 'columns', 'buildr' ),
                'fas fa-comment' => __( 'comment', 'buildr' ),
                'far fa-comment' => __( 'comment', 'buildr' ),
                'fas fa-comment-alt' => __( 'comment-alt', 'buildr' ),
                'far fa-comment-alt' => __( 'comment-alt', 'buildr' ),
                'fas fa-comment-dots' => __( 'comment-dots', 'buildr' ),
                'fas fa-comment-slash' => __( 'comment-slash', 'buildr' ),
                'fas fa-comments' => __( 'comments', 'buildr' ),
                'far fa-comments' => __( 'comments', 'buildr' ),
                'fas fa-compass' => __( 'compass', 'buildr' ),
                'far fa-compass' => __( 'compass', 'buildr' ),
                'fas fa-compress' => __( 'compress', 'buildr' ),
                'fab fa-connectdevelop' => __( 'connectdevelop', 'buildr' ),
                'fab fa-contao' => __( 'contao', 'buildr' ),
                'fas fa-copy' => __( 'copy', 'buildr' ),
                'far fa-copy' => __( 'copy', 'buildr' ),
                'fas fa-copyright' => __( 'copyright', 'buildr' ),
                'far fa-copyright' => __( 'copyright', 'buildr' ),
                'fas fa-couch' => __( 'couch', 'buildr' ),
                'fab fa-cpanel' => __( 'cpanel', 'buildr' ),
                'fab fa-creative-commons' => __( 'creative-commons', 'buildr' ),
                'fas fa-credit-card' => __( 'credit-card', 'buildr' ),
                'far fa-credit-card' => __( 'credit-card', 'buildr' ),
                'fas fa-crop' => __( 'crop', 'buildr' ),
                'fas fa-crosshairs' => __( 'crosshairs', 'buildr' ),
                'fab fa-css3' => __( 'css3', 'buildr' ),
                'fab fa-css3-alt' => __( 'css3-alt', 'buildr' ),
                'fas fa-cube' => __( 'cube', 'buildr' ),
                'fas fa-cubes' => __( 'cubes', 'buildr' ),
                'fas fa-cut' => __( 'cut', 'buildr' ),
                'fab fa-cuttlefish' => __( 'cuttlefish', 'buildr' ),
                'fab fa-d-and-d' => __( 'd-and-d', 'buildr' ),
                'fab fa-dashcube' => __( 'dashcube', 'buildr' ),
                'fas fa-database' => __( 'database', 'buildr' ),
                'fas fa-deaf' => __( 'deaf', 'buildr' ),
                'fab fa-delicious' => __( 'delicious', 'buildr' ),
                'fab fa-deploydog' => __( 'deploydog', 'buildr' ),
                'fab fa-deskpro' => __( 'deskpro', 'buildr' ),
                'fas fa-desktop' => __( 'desktop', 'buildr' ),
                'fab fa-deviantart' => __( 'deviantart', 'buildr' ),
                'fas fa-diagnoses' => __( 'diagnoses', 'buildr' ),
                'fab fa-digg' => __( 'digg', 'buildr' ),
                'fab fa-digital-ocean' => __( 'digital-ocean', 'buildr' ),
                'fab fa-discord' => __( 'discord', 'buildr' ),
                'fab fa-discourse' => __( 'discourse', 'buildr' ),
                'fas fa-dna' => __( 'dna', 'buildr' ),
                'fab fa-dochub' => __( 'dochub', 'buildr' ),
                'fab fa-docker' => __( 'docker', 'buildr' ),
                'fas fa-dollar-sign' => __( 'dollar-sign', 'buildr' ),
                'fas fa-dolly' => __( 'dolly', 'buildr' ),
                'fas fa-dolly-flatbed' => __( 'dolly-flatbed', 'buildr' ),
                'fas fa-donate' => __( 'donate', 'buildr' ),
                'fas fa-dot-circle' => __( 'dot-circle', 'buildr' ),
                'far fa-dot-circle' => __( 'dot-circle', 'buildr' ),
                'fas fa-dove' => __( 'dove', 'buildr' ),
                'fas fa-download' => __( 'download', 'buildr' ),
                'fab fa-draft2digital' => __( 'draft2digital', 'buildr' ),
                'fab fa-dribbble' => __( 'dribbble', 'buildr' ),
                'fab fa-dribbble-square' => __( 'dribbble-square', 'buildr' ),
                'fab fa-dropbox' => __( 'dropbox', 'buildr' ),
                'fab fa-drupal' => __( 'drupal', 'buildr' ),
                'fab fa-dyalog' => __( 'dyalog', 'buildr' ),
                'fab fa-earlybirds' => __( 'earlybirds', 'buildr' ),
                'fab fa-edge' => __( 'edge', 'buildr' ),
                'fas fa-edit' => __( 'edit', 'buildr' ),
                'far fa-edit' => __( 'edit', 'buildr' ),
                'fas fa-eject' => __( 'eject', 'buildr' ),
                'fab fa-elementor' => __( 'elementor', 'buildr' ),
                'fas fa-ellipsis-h' => __( 'ellipsis-h', 'buildr' ),
                'fas fa-ellipsis-v' => __( 'ellipsis-v', 'buildr' ),
                'fab fa-ember' => __( 'ember', 'buildr' ),
                'fab fa-empire' => __( 'empire', 'buildr' ),
                'fas fa-envelope' => __( 'envelope', 'buildr' ),
                'far fa-envelope' => __( 'envelope', 'buildr' ),
                'fas fa-envelope-open' => __( 'envelope-open', 'buildr' ),
                'far fa-envelope-open' => __( 'envelope-open', 'buildr' ),
                'fas fa-envelope-square' => __( 'envelope-square', 'buildr' ),
                'fab fa-envira' => __( 'envira', 'buildr' ),
                'fas fa-eraser' => __( 'eraser', 'buildr' ),
                'fab fa-erlang' => __( 'erlang', 'buildr' ),
                'fab fa-ethereum' => __( 'ethereum', 'buildr' ),
                'fab fa-etsy' => __( 'etsy', 'buildr' ),
                'fas fa-euro-sign' => __( 'euro-sign', 'buildr' ),
                'fas fa-exchange-alt' => __( 'exchange-alt', 'buildr' ),
                'fas fa-exclamation' => __( 'exclamation', 'buildr' ),
                'fas fa-exclamation-circle' => __( 'exclamation-circle', 'buildr' ),
                'fas fa-exclamation-triangle' => __( 'exclamation-triangle', 'buildr' ),
                'fas fa-expand' => __( 'expand', 'buildr' ),
                'fas fa-expand-arrows-alt' => __( 'expand-arrows-alt', 'buildr' ),
                'fab fa-expeditedssl' => __( 'expeditedssl', 'buildr' ),
                'fas fa-external-link-alt' => __( 'external-link-alt', 'buildr' ),
                'fas fa-external-link-square-alt' => __( 'external-link-square-alt', 'buildr' ),
                'fas fa-eye' => __( 'eye', 'buildr' ),
                'fas fa-eye-dropper' => __( 'eye-dropper', 'buildr' ),
                'fas fa-eye-slash' => __( 'eye-slash', 'buildr' ),
                'far fa-eye-slash' => __( 'eye-slash', 'buildr' ),
                'fab fa-facebook' => __( 'facebook', 'buildr' ),
                'fab fa-facebook-f' => __( 'facebook-f', 'buildr' ),
                'fab fa-facebook-messenger' => __( 'facebook-messenger', 'buildr' ),
                'fab fa-facebook-square' => __( 'facebook-square', 'buildr' ),
                'fas fa-fast-backward' => __( 'fast-backward', 'buildr' ),
                'fas fa-fast-forward' => __( 'fast-forward', 'buildr' ),
                'fas fa-fax' => __( 'fax', 'buildr' ),
                'fas fa-female' => __( 'female', 'buildr' ),
                'fas fa-fighter-jet' => __( 'fighter-jet', 'buildr' ),
                'fas fa-file' => __( 'file', 'buildr' ),
                'far fa-file' => __( 'file', 'buildr' ),
                'fas fa-file-alt' => __( 'file-alt', 'buildr' ),
                'far fa-file-alt' => __( 'file-alt', 'buildr' ),
                'fas fa-file-archive' => __( 'file-archive', 'buildr' ),
                'far fa-file-archive' => __( 'file-archive', 'buildr' ),
                'fas fa-file-audio' => __( 'file-audio', 'buildr' ),
                'far fa-file-audio' => __( 'file-audio', 'buildr' ),
                'fas fa-file-code' => __( 'file-code', 'buildr' ),
                'far fa-file-code' => __( 'file-code', 'buildr' ),
                'fas fa-file-excel' => __( 'file-excel', 'buildr' ),
                'far fa-file-excel' => __( 'file-excel', 'buildr' ),
                'fas fa-file-image' => __( 'file-image', 'buildr' ),
                'far fa-file-image' => __( 'file-image', 'buildr' ),
                'fas fa-file-medical' => __( 'file-medical', 'buildr' ),
                'fas fa-file-medical-alt' => __( 'file-medical-alt', 'buildr' ),
                'fas fa-file-pdf' => __( 'file-pdf', 'buildr' ),
                'far fa-file-pdf' => __( 'file-pdf', 'buildr' ),
                'fas fa-file-powerpoint' => __( 'file-powerpoint', 'buildr' ),
                'far fa-file-powerpoint' => __( 'file-powerpoint', 'buildr' ),
                'fas fa-file-video' => __( 'file-video', 'buildr' ),
                'far fa-file-video' => __( 'file-video', 'buildr' ),
                'fas fa-file-word' => __( 'file-word', 'buildr' ),
                'far fa-file-word' => __( 'file-word', 'buildr' ),
                'fas fa-film' => __( 'film', 'buildr' ),
                'fas fa-filter' => __( 'filter', 'buildr' ),
                'fas fa-fire' => __( 'fire', 'buildr' ),
                'fas fa-fire-extinguisher' => __( 'fire-extinguisher', 'buildr' ),
                'fab fa-firefox' => __( 'firefox', 'buildr' ),
                'fas fa-first-aid' => __( 'first-aid', 'buildr' ),
                'fab fa-first-order' => __( 'first-order', 'buildr' ),
                'fab fa-firstdraft' => __( 'firstdraft', 'buildr' ),
                'fas fa-flag' => __( 'flag', 'buildr' ),
                'far fa-flag' => __( 'flag', 'buildr' ),
                'fas fa-flag-checkered' => __( 'flag-checkered', 'buildr' ),
                'fas fa-flask' => __( 'flask', 'buildr' ),
                'fab fa-flickr' => __( 'flickr', 'buildr' ),
                'fab fa-flipboard' => __( 'flipboard', 'buildr' ),
                'fab fa-fly' => __( 'fly', 'buildr' ),
                'fas fa-folder' => __( 'folder', 'buildr' ),
                'far fa-folder' => __( 'folder', 'buildr' ),
                'fas fa-folder-open' => __( 'folder-open', 'buildr' ),
                'far fa-folder-open' => __( 'folder-open', 'buildr' ),
                'fas fa-font' => __( 'font', 'buildr' ),
                'fab fa-font-awesome' => __( 'font-awesome', 'buildr' ),
                'fab fa-font-awesome-alt' => __( 'font-awesome-alt', 'buildr' ),
                'fab fa-font-awesome-flag' => __( 'font-awesome-flag', 'buildr' ),
                'fab fa-fonticons' => __( 'fonticons', 'buildr' ),
                'fab fa-fonticons-fi' => __( 'fonticons-fi', 'buildr' ),
                'fas fa-football-ball' => __( 'football-ball', 'buildr' ),
                'fab fa-fort-awesome' => __( 'fort-awesome', 'buildr' ),
                'fab fa-fort-awesome-alt' => __( 'fort-awesome-alt', 'buildr' ),
                'fab fa-forumbee' => __( 'forumbee', 'buildr' ),
                'fas fa-forward' => __( 'forward', 'buildr' ),
                'fab fa-foursquare' => __( 'foursquare', 'buildr' ),
                'fab fa-free-code-camp' => __( 'free-code-camp', 'buildr' ),
                'fab fa-freebsd' => __( 'freebsd', 'buildr' ),
                'fas fa-frown' => __( 'frown', 'buildr' ),
                'far fa-frown' => __( 'frown', 'buildr' ),
                'fas fa-futbol' => __( 'futbol', 'buildr' ),
                'far fa-futbol' => __( 'futbol', 'buildr' ),
                'fas fa-gamepad' => __( 'gamepad', 'buildr' ),
                'fas fa-gavel' => __( 'gavel', 'buildr' ),
                'fas fa-gem' => __( 'gem', 'buildr' ),
                'far fa-gem' => __( 'gem', 'buildr' ),
                'fas fa-genderless' => __( 'genderless', 'buildr' ),
                'fab fa-get-pocket' => __( 'get-pocket', 'buildr' ),
                'fab fa-gg' => __( 'gg', 'buildr' ),
                'fab fa-gg-circle' => __( 'gg-circle', 'buildr' ),
                'fas fa-gift' => __( 'gift', 'buildr' ),
                'fab fa-git' => __( 'git', 'buildr' ),
                'fab fa-git-square' => __( 'git-square', 'buildr' ),
                'fab fa-github' => __( 'github', 'buildr' ),
                'fab fa-github-alt' => __( 'github-alt', 'buildr' ),
                'fab fa-github-square' => __( 'github-square', 'buildr' ),
                'fab fa-gitkraken' => __( 'gitkraken', 'buildr' ),
                'fab fa-gitlab' => __( 'gitlab', 'buildr' ),
                'fab fa-gitter' => __( 'gitter', 'buildr' ),
                'fas fa-glass-martini' => __( 'glass-martini', 'buildr' ),
                'fab fa-glide' => __( 'glide', 'buildr' ),
                'fab fa-glide-g' => __( 'glide-g', 'buildr' ),
                'fas fa-globe' => __( 'globe', 'buildr' ),
                'fab fa-gofore' => __( 'gofore', 'buildr' ),
                'fas fa-golf-ball' => __( 'golf-ball', 'buildr' ),
                'fab fa-goodreads' => __( 'goodreads', 'buildr' ),
                'fab fa-goodreads-g' => __( 'goodreads-g', 'buildr' ),
                'fab fa-google' => __( 'google', 'buildr' ),
                'fab fa-google-drive' => __( 'google-drive', 'buildr' ),
                'fab fa-google-play' => __( 'google-play', 'buildr' ),
                'fab fa-google-plus' => __( 'google-plus', 'buildr' ),
                'fab fa-google-plus-g' => __( 'google-plus-g', 'buildr' ),
                'fab fa-google-plus-square' => __( 'google-plus-square', 'buildr' ),
                'fab fa-google-wallet' => __( 'google-wallet', 'buildr' ),
                'fas fa-graduation-cap' => __( 'graduation-cap', 'buildr' ),
                'fab fa-gratipay' => __( 'gratipay', 'buildr' ),
                'fab fa-grav' => __( 'grav', 'buildr' ),
                'fab fa-gripfire' => __( 'gripfire', 'buildr' ),
                'fab fa-grunt' => __( 'grunt', 'buildr' ),
                'fab fa-gulp' => __( 'gulp', 'buildr' ),
                'fas fa-h-square' => __( 'h-square', 'buildr' ),
                'fab fa-hacker-news' => __( 'hacker-news', 'buildr' ),
                'fab fa-hacker-news-square' => __( 'hacker-news-square', 'buildr' ),
                'fas fa-hand-holding' => __( 'hand-holding', 'buildr' ),
                'fas fa-hand-holding-heart' => __( 'hand-holding-heart', 'buildr' ),
                'fas fa-hand-holding-usd' => __( 'hand-holding-usd', 'buildr' ),
                'fas fa-hand-lizard' => __( 'hand-lizard', 'buildr' ),
                'far fa-hand-lizard' => __( 'hand-lizard', 'buildr' ),
                'fas fa-hand-paper' => __( 'hand-paper', 'buildr' ),
                'far fa-hand-paper' => __( 'hand-paper', 'buildr' ),
                'fas fa-hand-peace' => __( 'hand-peace', 'buildr' ),
                'far fa-hand-peace' => __( 'hand-peace', 'buildr' ),
                'fas fa-hand-point-down' => __( 'hand-point-down', 'buildr' ),
                'far fa-hand-point-down' => __( 'hand-point-down', 'buildr' ),
                'fas fa-hand-point-left' => __( 'hand-point-left', 'buildr' ),
                'far fa-hand-point-left' => __( 'hand-point-left', 'buildr' ),
                'fas fa-hand-point-right' => __( 'hand-point-right', 'buildr' ),
                'far fa-hand-point-right' => __( 'hand-point-right', 'buildr' ),
                'fas fa-hand-point-up' => __( 'hand-point-up', 'buildr' ),
                'far fa-hand-point-up' => __( 'hand-point-up', 'buildr' ),
                'fas fa-hand-pointer' => __( 'hand-pointer', 'buildr' ),
                'far fa-hand-pointer' => __( 'hand-pointer', 'buildr' ),
                'fas fa-hand-rock' => __( 'hand-rock', 'buildr' ),
                'far fa-hand-rock' => __( 'hand-rock', 'buildr' ),
                'fas fa-hand-scissors' => __( 'hand-scissors', 'buildr' ),
                'far fa-hand-scissors' => __( 'hand-scissors', 'buildr' ),
                'fas fa-hand-spock' => __( 'hand-spock', 'buildr' ),
                'far fa-hand-spock' => __( 'hand-spock', 'buildr' ),
                'fas fa-hands' => __( 'hands', 'buildr' ),
                'fas fa-hands-helping' => __( 'hands-helping', 'buildr' ),
                'fas fa-handshake' => __( 'handshake', 'buildr' ),
                'far fa-handshake' => __( 'handshake', 'buildr' ),
                'fas fa-hashtag' => __( 'hashtag', 'buildr' ),
                'fas fa-hdd' => __( 'hdd', 'buildr' ),
                'far fa-hdd' => __( 'hdd', 'buildr' ),
                'fas fa-heading' => __( 'heading', 'buildr' ),
                'fas fa-headphones' => __( 'headphones', 'buildr' ),
                'fas fa-heart' => __( 'heart', 'buildr' ),
                'far fa-heart' => __( 'heart', 'buildr' ),
                'fas fa-heartbeat' => __( 'heartbeat', 'buildr' ),
                'fab fa-hips' => __( 'hips', 'buildr' ),
                'fab fa-hire-a-helper' => __( 'hire-a-helper', 'buildr' ),
                'fas fa-history' => __( 'history', 'buildr' ),
                'fas fa-hockey-puck' => __( 'hockey-puck', 'buildr' ),
                'fas fa-home' => __( 'home', 'buildr' ),
                'fab fa-hooli' => __( 'hooli', 'buildr' ),
                'fas fa-hospital' => __( 'hospital', 'buildr' ),
                'far fa-hospital' => __( 'hospital', 'buildr' ),
                'fas fa-hospital-alt' => __( 'hospital-alt', 'buildr' ),
                'fas fa-hospital-symbol' => __( 'hospital-symbol', 'buildr' ),
                'fab fa-hotjar' => __( 'hotjar', 'buildr' ),
                'fas fa-hourglass' => __( 'hourglass', 'buildr' ),
                'far fa-hourglass' => __( 'hourglass', 'buildr' ),
                'fas fa-hourglass-end' => __( 'hourglass-end', 'buildr' ),
                'fas fa-hourglass-half' => __( 'hourglass-half', 'buildr' ),
                'fas fa-hourglass-start' => __( 'hourglass-start', 'buildr' ),
                'fab fa-houzz' => __( 'houzz', 'buildr' ),
                'fab fa-html5' => __( 'html5', 'buildr' ),
                'fab fa-hubspot' => __( 'hubspot', 'buildr' ),
                'fas fa-i-cursor' => __( 'i-cursor', 'buildr' ),
                'fas fa-id-badge' => __( 'id-badge', 'buildr' ),
                'far fa-id-badge' => __( 'id-badge', 'buildr' ),
                'fas fa-id-card' => __( 'id-card', 'buildr' ),
                'far fa-id-card' => __( 'id-card', 'buildr' ),
                'fas fa-id-card-alt' => __( 'id-card-alt', 'buildr' ),
                'fas fa-image' => __( 'image', 'buildr' ),
                'far fa-image' => __( 'image', 'buildr' ),
                'fas fa-images' => __( 'images', 'buildr' ),
                'far fa-images' => __( 'images', 'buildr' ),
                'fab fa-imdb' => __( 'imdb', 'buildr' ),
                'fas fa-inbox' => __( 'inbox', 'buildr' ),
                'fas fa-indent' => __( 'indent', 'buildr' ),
                'fas fa-industry' => __( 'industry', 'buildr' ),
                'fas fa-info' => __( 'info', 'buildr' ),
                'fas fa-info-circle' => __( 'info-circle', 'buildr' ),
                'fab fa-instagram' => __( 'instagram', 'buildr' ),
                'fab fa-internet-explorer' => __( 'internet-explorer', 'buildr' ),
                'fab fa-ioxhost' => __( 'ioxhost', 'buildr' ),
                'fas fa-italic' => __( 'italic', 'buildr' ),
                'fab fa-itunes' => __( 'itunes', 'buildr' ),
                'fab fa-itunes-note' => __( 'itunes-note', 'buildr' ),
                'fab fa-java' => __( 'java', 'buildr' ),
                'fab fa-jenkins' => __( 'jenkins', 'buildr' ),
                'fab fa-joget' => __( 'joget', 'buildr' ),
                'fab fa-joomla' => __( 'joomla', 'buildr' ),
                'fab fa-js' => __( 'js', 'buildr' ),
                'fab fa-js-square' => __( 'js-square', 'buildr' ),
                'fab fa-jsfiddle' => __( 'jsfiddle', 'buildr' ),
                'fas fa-key' => __( 'key', 'buildr' ),
                'fas fa-keyboard' => __( 'keyboard', 'buildr' ),
                'far fa-keyboard' => __( 'keyboard', 'buildr' ),
                'fab fa-keycdn' => __( 'keycdn', 'buildr' ),
                'fab fa-kickstarter' => __( 'kickstarter', 'buildr' ),
                'fab fa-kickstarter-k' => __( 'kickstarter-k', 'buildr' ),
                'fab fa-korvue' => __( 'korvue', 'buildr' ),
                'fas fa-language' => __( 'language', 'buildr' ),
                'fas fa-laptop' => __( 'laptop', 'buildr' ),
                'fab fa-laravel' => __( 'laravel', 'buildr' ),
                'fab fa-lastfm' => __( 'lastfm', 'buildr' ),
                'fab fa-lastfm-square' => __( 'lastfm-square', 'buildr' ),
                'fas fa-leaf' => __( 'leaf', 'buildr' ),
                'fab fa-leanpub' => __( 'leanpub', 'buildr' ),
                'fas fa-lemon' => __( 'lemon', 'buildr' ),
                'far fa-lemon' => __( 'lemon', 'buildr' ),
                'fab fa-less' => __( 'less', 'buildr' ),
                'fas fa-level-down-alt' => __( 'level-down-alt', 'buildr' ),
                'fas fa-level-up-alt' => __( 'level-up-alt', 'buildr' ),
                'fas fa-life-ring' => __( 'life-ring', 'buildr' ),
                'far fa-life-ring' => __( 'life-ring', 'buildr' ),
                'fas fa-lightbulb' => __( 'lightbulb', 'buildr' ),
                'far fa-lightbulb' => __( 'lightbulb', 'buildr' ),
                'fab fa-line' => __( 'line', 'buildr' ),
                'fas fa-link' => __( 'link', 'buildr' ),
                'fab fa-linkedin' => __( 'linkedin', 'buildr' ),
                'fab fa-linkedin-in' => __( 'linkedin-in', 'buildr' ),
                'fab fa-linode' => __( 'linode', 'buildr' ),
                'fab fa-linux' => __( 'linux', 'buildr' ),
                'fas fa-lira-sign' => __( 'lira-sign', 'buildr' ),
                'fas fa-list' => __( 'list', 'buildr' ),
                'fas fa-list-alt' => __( 'list-alt', 'buildr' ),
                'far fa-list-alt' => __( 'list-alt', 'buildr' ),
                'fas fa-list-ol' => __( 'list-ol', 'buildr' ),
                'fas fa-list-ul' => __( 'list-ul', 'buildr' ),
                'fas fa-location-arrow' => __( 'location-arrow', 'buildr' ),
                'fas fa-lock' => __( 'lock', 'buildr' ),
                'fas fa-lock-open' => __( 'lock-open', 'buildr' ),
                'fas fa-long-arrow-alt-down' => __( 'long-arrow-alt-down', 'buildr' ),
                'fas fa-long-arrow-alt-left' => __( 'long-arrow-alt-left', 'buildr' ),
                'fas fa-long-arrow-alt-right' => __( 'long-arrow-alt-right', 'buildr' ),
                'fas fa-long-arrow-alt-up' => __( 'long-arrow-alt-up', 'buildr' ),
                'fas fa-low-vision' => __( 'low-vision', 'buildr' ),
                'fab fa-lyft' => __( 'lyft', 'buildr' ),
                'fab fa-magento' => __( 'magento', 'buildr' ),
                'fas fa-magic' => __( 'magic', 'buildr' ),
                'fas fa-magnet' => __( 'magnet', 'buildr' ),
                'fas fa-male' => __( 'male', 'buildr' ),
                'fas fa-map' => __( 'map', 'buildr' ),
                'far fa-map' => __( 'map', 'buildr' ),
                'fas fa-map-marker' => __( 'map-marker', 'buildr' ),
                'fas fa-map-marker-alt' => __( 'map-marker-alt', 'buildr' ),
                'fas fa-map-pin' => __( 'map-pin', 'buildr' ),
                'fas fa-map-signs' => __( 'map-signs', 'buildr' ),
                'fas fa-mars' => __( 'mars', 'buildr' ),
                'fas fa-mars-double' => __( 'mars-double', 'buildr' ),
                'fas fa-mars-stroke' => __( 'mars-stroke', 'buildr' ),
                'fas fa-mars-stroke-h' => __( 'mars-stroke-h', 'buildr' ),
                'fas fa-mars-stroke-v' => __( 'mars-stroke-v', 'buildr' ),
                'fab fa-maxcdn' => __( 'maxcdn', 'buildr' ),
                'fab fa-medapps' => __( 'medapps', 'buildr' ),
                'fab fa-medium' => __( 'medium', 'buildr' ),
                'fab fa-medium-m' => __( 'medium-m', 'buildr' ),
                'fas fa-medkit' => __( 'medkit', 'buildr' ),
                'fab fa-medrt' => __( 'medrt', 'buildr' ),
                'fab fa-meetup' => __( 'meetup', 'buildr' ),
                'fas fa-meh' => __( 'meh', 'buildr' ),
                'far fa-meh' => __( 'meh', 'buildr' ),
                'fas fa-mercury' => __( 'mercury', 'buildr' ),
                'fas fa-microchip' => __( 'microchip', 'buildr' ),
                'fas fa-microphone' => __( 'microphone', 'buildr' ),
                'fas fa-microphone-slash' => __( 'microphone-slash', 'buildr' ),
                'fab fa-microsoft' => __( 'microsoft', 'buildr' ),
                'fas fa-minus' => __( 'minus', 'buildr' ),
                'fas fa-minus-circle' => __( 'minus-circle', 'buildr' ),
                'fas fa-minus-square' => __( 'minus-square', 'buildr' ),
                'far fa-minus-square' => __( 'minus-square', 'buildr' ),
                'fab fa-mix' => __( 'mix', 'buildr' ),
                'fab fa-mixcloud' => __( 'mixcloud', 'buildr' ),
                'fab fa-mizuni' => __( 'mizuni', 'buildr' ),
                'fas fa-mobile' => __( 'mobile', 'buildr' ),
                'fas fa-mobile-alt' => __( 'mobile-alt', 'buildr' ),
                'fab fa-modx' => __( 'modx', 'buildr' ),
                'fab fa-monero' => __( 'monero', 'buildr' ),
                'fas fa-money-bill-alt' => __( 'money-bill-alt', 'buildr' ),
                'far fa-money-bill-alt' => __( 'money-bill-alt', 'buildr' ),
                'fas fa-moon' => __( 'moon', 'buildr' ),
                'far fa-moon' => __( 'moon', 'buildr' ),
                'fas fa-motorcycle' => __( 'motorcycle', 'buildr' ),
                'fas fa-mouse-pointer' => __( 'mouse-pointer', 'buildr' ),
                'fas fa-music' => __( 'music', 'buildr' ),
                'fab fa-napster' => __( 'napster', 'buildr' ),
                'fas fa-neuter' => __( 'neuter', 'buildr' ),
                'fas fa-newspaper' => __( 'newspaper', 'buildr' ),
                'far fa-newspaper' => __( 'newspaper', 'buildr' ),
                'fab fa-nintendo-switch' => __( 'nintendo-switch', 'buildr' ),
                'fab fa-node' => __( 'node', 'buildr' ),
                'fab fa-node-js' => __( 'node-js', 'buildr' ),
                'fas fa-notes-medical' => __( 'notes-medical', 'buildr' ),
                'fab fa-npm' => __( 'npm', 'buildr' ),
                'fab fa-ns8' => __( 'ns8', 'buildr' ),
                'fab fa-nutritionix' => __( 'nutritionix', 'buildr' ),
                'fas fa-object-group' => __( 'object-group', 'buildr' ),
                'far fa-object-group' => __( 'object-group', 'buildr' ),
                'fas fa-object-ungroup' => __( 'object-ungroup', 'buildr' ),
                'far fa-object-ungroup' => __( 'object-ungroup', 'buildr' ),
                'fab fa-odnoklassniki' => __( 'odnoklassniki', 'buildr' ),
                'fab fa-odnoklassniki-square' => __( 'odnoklassniki-square', 'buildr' ),
                'fab fa-opencart' => __( 'opencart', 'buildr' ),
                'fab fa-openid' => __( 'openid', 'buildr' ),
                'fab fa-opera' => __( 'opera', 'buildr' ),
                'fab fa-optin-monster' => __( 'optin-monster', 'buildr' ),
                'fab fa-osi' => __( 'osi', 'buildr' ),
                'fas fa-outdent' => __( 'outdent', 'buildr' ),
                'fab fa-page4' => __( 'page4', 'buildr' ),
                'fab fa-pagelines' => __( 'pagelines', 'buildr' ),
                'fas fa-paint-brush' => __( 'paint-brush', 'buildr' ),
                'fab fa-palfed' => __( 'palfed', 'buildr' ),
                'fas fa-pallet' => __( 'pallet', 'buildr' ),
                'fas fa-paper-plane' => __( 'paper-plane', 'buildr' ),
                'far fa-paper-plane' => __( 'paper-plane', 'buildr' ),
                'fas fa-paperclip' => __( 'paperclip', 'buildr' ),
                'fas fa-parachute-box' => __( 'parachute-box', 'buildr' ),
                'fas fa-paragraph' => __( 'paragraph', 'buildr' ),
                'fas fa-paste' => __( 'paste', 'buildr' ),
                'fab fa-patreon' => __( 'patreon', 'buildr' ),
                'fas fa-pause' => __( 'pause', 'buildr' ),
                'fas fa-pause-circle' => __( 'pause-circle', 'buildr' ),
                'far fa-pause-circle' => __( 'pause-circle', 'buildr' ),
                'fas fa-paw' => __( 'paw', 'buildr' ),
                'fab fa-paypal' => __( 'paypal', 'buildr' ),
                'fas fa-pen-square' => __( 'pen-square', 'buildr' ),
                'fas fa-pencil-alt' => __( 'pencil-alt', 'buildr' ),
                'fas fa-people-carry' => __( 'people-carry', 'buildr' ),
                'fas fa-percent' => __( 'percent', 'buildr' ),
                'fab fa-periscope' => __( 'periscope', 'buildr' ),
                'fab fa-phabricator' => __( 'phabricator', 'buildr' ),
                'fab fa-phoenix-framework' => __( 'phoenix-framework', 'buildr' ),
                'fas fa-phone' => __( 'phone', 'buildr' ),
                'fas fa-phone-slash' => __( 'phone-slash', 'buildr' ),
                'fas fa-phone-square' => __( 'phone-square', 'buildr' ),
                'fas fa-phone-volume' => __( 'phone-volume', 'buildr' ),
                'fab fa-php' => __( 'php', 'buildr' ),
                'fab fa-pied-piper' => __( 'pied-piper', 'buildr' ),
                'fab fa-pied-piper-alt' => __( 'pied-piper-alt', 'buildr' ),
                'fab fa-pied-piper-hat' => __( 'pied-piper-hat', 'buildr' ),
                'fab fa-pied-piper-pp' => __( 'pied-piper-pp', 'buildr' ),
                'fas fa-piggy-bank' => __( 'piggy-bank', 'buildr' ),
                'fas fa-pills' => __( 'pills', 'buildr' ),
                'fab fa-pinterest' => __( 'pinterest', 'buildr' ),
                'fab fa-pinterest-p' => __( 'pinterest-p', 'buildr' ),
                'fab fa-pinterest-square' => __( 'pinterest-square', 'buildr' ),
                'fas fa-plane' => __( 'plane', 'buildr' ),
                'fas fa-play' => __( 'play', 'buildr' ),
                'fas fa-play-circle' => __( 'play-circle', 'buildr' ),
                'far fa-play-circle' => __( 'play-circle', 'buildr' ),
                'fab fa-playstation' => __( 'playstation', 'buildr' ),
                'fas fa-plug' => __( 'plug', 'buildr' ),
                'fas fa-plus' => __( 'plus', 'buildr' ),
                'fas fa-plus-circle' => __( 'plus-circle', 'buildr' ),
                'fas fa-plus-square' => __( 'plus-square', 'buildr' ),
                'far fa-plus-square' => __( 'plus-square', 'buildr' ),
                'fas fa-podcast' => __( 'podcast', 'buildr' ),
                'fas fa-poo' => __( 'poo', 'buildr' ),
                'fas fa-pound-sign' => __( 'pound-sign', 'buildr' ),
                'fas fa-power-off' => __( 'power-off', 'buildr' ),
                'fas fa-prescription-bottle' => __( 'prescription-bottle', 'buildr' ),
                'fas fa-prescription-bottle-alt' => __( 'prescription-bottle-alt', 'buildr' ),
                'fas fa-print' => __( 'print', 'buildr' ),
                'fas fa-procedures' => __( 'procedures', 'buildr' ),
                'fab fa-product-hunt' => __( 'product-hunt', 'buildr' ),
                'fab fa-pushed' => __( 'pushed', 'buildr' ),
                'fas fa-puzzle-piece' => __( 'puzzle-piece', 'buildr' ),
                'fab fa-python' => __( 'python', 'buildr' ),
                'fab fa-qq' => __( 'qq', 'buildr' ),
                'fas fa-qrcode' => __( 'qrcode', 'buildr' ),
                'fas fa-question' => __( 'question', 'buildr' ),
                'fas fa-question-circle' => __( 'question-circle', 'buildr' ),
                'far fa-question-circle' => __( 'question-circle', 'buildr' ),
                'fas fa-quidditch' => __( 'quidditch', 'buildr' ),
                'fab fa-quinscape' => __( 'quinscape', 'buildr' ),
                'fab fa-quora' => __( 'quora', 'buildr' ),
                'fas fa-quote-left' => __( 'quote-left', 'buildr' ),
                'fas fa-quote-right' => __( 'quote-right', 'buildr' ),
                'fas fa-random' => __( 'random', 'buildr' ),
                'fab fa-ravelry' => __( 'ravelry', 'buildr' ),
                'fab fa-react' => __( 'react', 'buildr' ),
                'fab fa-readme' => __( 'readme', 'buildr' ),
                'fab fa-rebel' => __( 'rebel', 'buildr' ),
                'fas fa-recycle' => __( 'recycle', 'buildr' ),
                'fab fa-red-river' => __( 'red-river', 'buildr' ),
                'fab fa-reddit' => __( 'reddit', 'buildr' ),
                'fab fa-reddit-alien' => __( 'reddit-alien', 'buildr' ),
                'fab fa-reddit-square' => __( 'reddit-square', 'buildr' ),
                'fas fa-redo' => __( 'redo', 'buildr' ),
                'fas fa-redo-alt' => __( 'redo-alt', 'buildr' ),
                'fas fa-registered' => __( 'registered', 'buildr' ),
                'far fa-registered' => __( 'registered', 'buildr' ),
                'fab fa-rendact' => __( 'rendact', 'buildr' ),
                'fab fa-renren' => __( 'renren', 'buildr' ),
                'fas fa-reply' => __( 'reply', 'buildr' ),
                'fas fa-reply-all' => __( 'reply-all', 'buildr' ),
                'fab fa-replyd' => __( 'replyd', 'buildr' ),
                'fab fa-resolving' => __( 'resolving', 'buildr' ),
                'fas fa-retweet' => __( 'retweet', 'buildr' ),
                'fas fa-ribbon' => __( 'ribbon', 'buildr' ),
                'fas fa-road' => __( 'road', 'buildr' ),
                'fas fa-rocket' => __( 'rocket', 'buildr' ),
                'fab fa-rocketchat' => __( 'rocketchat', 'buildr' ),
                'fab fa-rockrms' => __( 'rockrms', 'buildr' ),
                'fas fa-rss' => __( 'rss', 'buildr' ),
                'fas fa-rss-square' => __( 'rss-square', 'buildr' ),
                'fas fa-ruble-sign' => __( 'ruble-sign', 'buildr' ),
                'fas fa-rupee-sign' => __( 'rupee-sign', 'buildr' ),
                'fab fa-safari' => __( 'safari', 'buildr' ),
                'fab fa-sass' => __( 'sass', 'buildr' ),
                'fas fa-save' => __( 'save', 'buildr' ),
                'far fa-save' => __( 'save', 'buildr' ),
                'fab fa-schlix' => __( 'schlix', 'buildr' ),
                'fab fa-scribd' => __( 'scribd', 'buildr' ),
                'fas fa-search' => __( 'search', 'buildr' ),
                'fas fa-search-minus' => __( 'search-minus', 'buildr' ),
                'fas fa-search-plus' => __( 'search-plus', 'buildr' ),
                'fab fa-searchengin' => __( 'searchengin', 'buildr' ),
                'fas fa-seedling' => __( 'seedling', 'buildr' ),
                'fab fa-sellcast' => __( 'sellcast', 'buildr' ),
                'fab fa-sellsy' => __( 'sellsy', 'buildr' ),
                'fas fa-server' => __( 'server', 'buildr' ),
                'fab fa-servicestack' => __( 'servicestack', 'buildr' ),
                'fas fa-share' => __( 'share', 'buildr' ),
                'fas fa-share-alt' => __( 'share-alt', 'buildr' ),
                'fas fa-share-alt-square' => __( 'share-alt-square', 'buildr' ),
                'fas fa-share-square' => __( 'share-square', 'buildr' ),
                'far fa-share-square' => __( 'share-square', 'buildr' ),
                'fas fa-shekel-sign' => __( 'shekel-sign', 'buildr' ),
                'fas fa-shield-alt' => __( 'shield-alt', 'buildr' ),
                'fas fa-ship' => __( 'ship', 'buildr' ),
                'fas fa-shipping-fast' => __( 'shipping-fast', 'buildr' ),
                'fab fa-shirtsinbulk' => __( 'shirtsinbulk', 'buildr' ),
                'fas fa-shopping-bag' => __( 'shopping-bag', 'buildr' ),
                'fas fa-shopping-basket' => __( 'shopping-basket', 'buildr' ),
                'fas fa-shopping-cart' => __( 'shopping-cart', 'buildr' ),
                'fas fa-shower' => __( 'shower', 'buildr' ),
                'fas fa-sign' => __( 'sign', 'buildr' ),
                'fas fa-sign-in-alt' => __( 'sign-in-alt', 'buildr' ),
                'fas fa-sign-language' => __( 'sign-language', 'buildr' ),
                'fas fa-sign-out-alt' => __( 'sign-out-alt', 'buildr' ),
                'fas fa-signal' => __( 'signal', 'buildr' ),
                'fab fa-simplybuilt' => __( 'simplybuilt', 'buildr' ),
                'fab fa-sistrix' => __( 'sistrix', 'buildr' ),
                'fas fa-sitemap' => __( 'sitemap', 'buildr' ),
                'fab fa-skyatlas' => __( 'skyatlas', 'buildr' ),
                'fab fa-skype' => __( 'skype', 'buildr' ),
                'fab fa-slack' => __( 'slack', 'buildr' ),
                'fab fa-slack-hash' => __( 'slack-hash', 'buildr' ),
                'fas fa-sliders-h' => __( 'sliders-h', 'buildr' ),
                'fab fa-slideshare' => __( 'slideshare', 'buildr' ),
                'fas fa-smile' => __( 'smile', 'buildr' ),
                'far fa-smile' => __( 'smile', 'buildr' ),
                'fas fa-smoking' => __( 'smoking', 'buildr' ),
                'fab fa-snapchat' => __( 'snapchat', 'buildr' ),
                'fab fa-snapchat-ghost' => __( 'snapchat-ghost', 'buildr' ),
                'fab fa-snapchat-square' => __( 'snapchat-square', 'buildr' ),
                'fas fa-snowflake' => __( 'snowflake', 'buildr' ),
                'far fa-snowflake' => __( 'snowflake', 'buildr' ),
                'fas fa-sort' => __( 'sort', 'buildr' ),
                'fas fa-sort-alpha-down' => __( 'sort-alpha-down', 'buildr' ),
                'fas fa-sort-alpha-up' => __( 'sort-alpha-up', 'buildr' ),
                'fas fa-sort-amount-down' => __( 'sort-amount-down', 'buildr' ),
                'fas fa-sort-amount-up' => __( 'sort-amount-up', 'buildr' ),
                'fas fa-sort-down' => __( 'sort-down', 'buildr' ),
                'fas fa-sort-numeric-down' => __( 'sort-numeric-down', 'buildr' ),
                'fas fa-sort-numeric-up' => __( 'sort-numeric-up', 'buildr' ),
                'fas fa-sort-up' => __( 'sort-up', 'buildr' ),
                'fab fa-soundcloud' => __( 'soundcloud', 'buildr' ),
                'fas fa-space-shuttle' => __( 'space-shuttle', 'buildr' ),
                'fab fa-speakap' => __( 'speakap', 'buildr' ),
                'fas fa-spinner' => __( 'spinner', 'buildr' ),
                'fab fa-spotify' => __( 'spotify', 'buildr' ),
                'fas fa-square' => __( 'square', 'buildr' ),
                'far fa-square' => __( 'square', 'buildr' ),
                'fas fa-square-full' => __( 'square-full', 'buildr' ),
                'fab fa-stack-exchange' => __( 'stack-exchange', 'buildr' ),
                'fab fa-stack-overflow' => __( 'stack-overflow', 'buildr' ),
                'fas fa-star' => __( 'star', 'buildr' ),
                'far fa-star' => __( 'star', 'buildr' ),
                'fas fa-star-half' => __( 'star-half', 'buildr' ),
                'far fa-star-half' => __( 'star-half', 'buildr' ),
                'fab fa-staylinked' => __( 'staylinked', 'buildr' ),
                'fab fa-steam' => __( 'steam', 'buildr' ),
                'fab fa-steam-square' => __( 'steam-square', 'buildr' ),
                'fab fa-steam-symbol' => __( 'steam-symbol', 'buildr' ),
                'fas fa-step-backward' => __( 'step-backward', 'buildr' ),
                'fas fa-step-forward' => __( 'step-forward', 'buildr' ),
                'fas fa-stethoscope' => __( 'stethoscope', 'buildr' ),
                'fab fa-sticker-mule' => __( 'sticker-mule', 'buildr' ),
                'fas fa-sticky-note' => __( 'sticky-note', 'buildr' ),
                'far fa-sticky-note' => __( 'sticky-note', 'buildr' ),
                'fas fa-stop' => __( 'stop', 'buildr' ),
                'fas fa-stop-circle' => __( 'stop-circle', 'buildr' ),
                'far fa-stop-circle' => __( 'stop-circle', 'buildr' ),
                'fas fa-stopwatch' => __( 'stopwatch', 'buildr' ),
                'fab fa-strava' => __( 'strava', 'buildr' ),
                'fas fa-street-view' => __( 'street-view', 'buildr' ),
                'fas fa-strikethrough' => __( 'strikethrough', 'buildr' ),
                'fab fa-stripe' => __( 'stripe', 'buildr' ),
                'fab fa-stripe-s' => __( 'stripe-s', 'buildr' ),
                'fab fa-studiovinari' => __( 'studiovinari', 'buildr' ),
                'fab fa-stumbleupon' => __( 'stumbleupon', 'buildr' ),
                'fab fa-stumbleupon-circle' => __( 'stumbleupon-circle', 'buildr' ),
                'fas fa-subscript' => __( 'subscript', 'buildr' ),
                'fas fa-subway' => __( 'subway', 'buildr' ),
                'fas fa-suitcase' => __( 'suitcase', 'buildr' ),
                'fas fa-sun' => __( 'sun', 'buildr' ),
                'far fa-sun' => __( 'sun', 'buildr' ),
                'fab fa-superpowers' => __( 'superpowers', 'buildr' ),
                'fas fa-superscript' => __( 'superscript', 'buildr' ),
                'fab fa-supple' => __( 'supple', 'buildr' ),
                'fas fa-sync' => __( 'sync', 'buildr' ),
                'fas fa-sync-alt' => __( 'sync-alt', 'buildr' ),
                'fas fa-syringe' => __( 'syringe', 'buildr' ),
                'fas fa-table' => __( 'table', 'buildr' ),
                'fas fa-table-tennis' => __( 'table-tennis', 'buildr' ),
                'fas fa-tablet' => __( 'tablet', 'buildr' ),
                'fas fa-tablet-alt' => __( 'tablet-alt', 'buildr' ),
                'fas fa-tablets' => __( 'tablets', 'buildr' ),
                'fas fa-tachometer-alt' => __( 'tachometer-alt', 'buildr' ),
                'fas fa-tag' => __( 'tag', 'buildr' ),
                'fas fa-tags' => __( 'tags', 'buildr' ),
                'fas fa-tape' => __( 'tape', 'buildr' ),
                'fas fa-tasks' => __( 'tasks', 'buildr' ),
                'fas fa-taxi' => __( 'taxi', 'buildr' ),
                'fab fa-telegram' => __( 'telegram', 'buildr' ),
                'fab fa-telegram-plane' => __( 'telegram-plane', 'buildr' ),
                'fab fa-tencent-weibo' => __( 'tencent-weibo', 'buildr' ),
                'fas fa-terminal' => __( 'terminal', 'buildr' ),
                'fas fa-text-height' => __( 'text-height', 'buildr' ),
                'fas fa-text-width' => __( 'text-width', 'buildr' ),
                'fas fa-th' => __( 'th', 'buildr' ),
                'fas fa-th-large' => __( 'th-large', 'buildr' ),
                'fas fa-th-list' => __( 'th-list', 'buildr' ),
                'fab fa-themeisle' => __( 'themeisle', 'buildr' ),
                'fas fa-thermometer' => __( 'thermometer', 'buildr' ),
                'fas fa-thermometer-empty' => __( 'thermometer-empty', 'buildr' ),
                'fas fa-thermometer-full' => __( 'thermometer-full', 'buildr' ),
                'fas fa-thermometer-half' => __( 'thermometer-half', 'buildr' ),
                'fas fa-thermometer-quarter' => __( 'thermometer-quarter', 'buildr' ),
                'fas fa-thermometer-three-quarters' => __( 'thermometer-three-quarters', 'buildr' ),
                'fas fa-thumbs-down' => __( 'thumbs-down', 'buildr' ),
                'far fa-thumbs-down' => __( 'thumbs-down', 'buildr' ),
                'fas fa-thumbs-up' => __( 'thumbs-up', 'buildr' ),
                'far fa-thumbs-up' => __( 'thumbs-up', 'buildr' ),
                'fas fa-thumbtack' => __( 'thumbtack', 'buildr' ),
                'fas fa-ticket-alt' => __( 'ticket-alt', 'buildr' ),
                'fas fa-times' => __( 'times', 'buildr' ),
                'fas fa-times-circle' => __( 'times-circle', 'buildr' ),
                'far fa-times-circle' => __( 'times-circle', 'buildr' ),
                'fas fa-tint' => __( 'tint', 'buildr' ),
                'fas fa-toggle-off' => __( 'toggle-off', 'buildr' ),
                'fas fa-toggle-on' => __( 'toggle-on', 'buildr' ),
                'fas fa-trademark' => __( 'trademark', 'buildr' ),
                'fas fa-train' => __( 'train', 'buildr' ),
                'fas fa-transgender' => __( 'transgender', 'buildr' ),
                'fas fa-transgender-alt' => __( 'transgender-alt', 'buildr' ),
                'fas fa-trash' => __( 'trash', 'buildr' ),
                'fas fa-trash-alt' => __( 'trash-alt', 'buildr' ),
                'far fa-trash-alt' => __( 'trash-alt', 'buildr' ),
                'fas fa-tree' => __( 'tree', 'buildr' ),
                'fab fa-trello' => __( 'trello', 'buildr' ),
                'fab fa-tripadvisor' => __( 'tripadvisor', 'buildr' ),
                'fas fa-trophy' => __( 'trophy', 'buildr' ),
                'fas fa-truck' => __( 'truck', 'buildr' ),
                'fas fa-truck-loading' => __( 'truck-loading', 'buildr' ),
                'fas fa-truck-moving' => __( 'truck-moving', 'buildr' ),
                'fas fa-tty' => __( 'tty', 'buildr' ),
                'fab fa-tumblr' => __( 'tumblr', 'buildr' ),
                'fab fa-tumblr-square' => __( 'tumblr-square', 'buildr' ),
                'fas fa-tv' => __( 'tv', 'buildr' ),
                'fab fa-twitch' => __( 'twitch', 'buildr' ),
                'fab fa-twitter' => __( 'twitter', 'buildr' ),
                'fab fa-twitter-square' => __( 'twitter-square', 'buildr' ),
                'fab fa-typo3' => __( 'typo3', 'buildr' ),
                'fab fa-uber' => __( 'uber', 'buildr' ),
                'fab fa-uikit' => __( 'uikit', 'buildr' ),
                'fas fa-umbrella' => __( 'umbrella', 'buildr' ),
                'fas fa-underline' => __( 'underline', 'buildr' ),
                'fas fa-undo' => __( 'undo', 'buildr' ),
                'fas fa-undo-alt' => __( 'undo-alt', 'buildr' ),
                'fab fa-uniregistry' => __( 'uniregistry', 'buildr' ),
                'fas fa-universal-access' => __( 'universal-access', 'buildr' ),
                'fas fa-university' => __( 'university', 'buildr' ),
                'fas fa-unlink' => __( 'unlink', 'buildr' ),
                'fas fa-unlock' => __( 'unlock', 'buildr' ),
                'fas fa-unlock-alt' => __( 'unlock-alt', 'buildr' ),
                'fab fa-untappd' => __( 'untappd', 'buildr' ),
                'fas fa-upload' => __( 'upload', 'buildr' ),
                'fab fa-usb' => __( 'usb', 'buildr' ),
                'fas fa-user' => __( 'user', 'buildr' ),
                'far fa-user' => __( 'user', 'buildr' ),
                'fas fa-user-circle' => __( 'user-circle', 'buildr' ),
                'far fa-user-circle' => __( 'user-circle', 'buildr' ),
                'fas fa-user-md' => __( 'user-md', 'buildr' ),
                'fas fa-user-plus' => __( 'user-plus', 'buildr' ),
                'fas fa-user-secret' => __( 'user-secret', 'buildr' ),
                'fas fa-user-times' => __( 'user-times', 'buildr' ),
                'fas fa-users' => __( 'users', 'buildr' ),
                'fab fa-ussunnah' => __( 'ussunnah', 'buildr' ),
                'fas fa-utensil-spoon' => __( 'utensil-spoon', 'buildr' ),
                'fas fa-utensils' => __( 'utensils', 'buildr' ),
                'fab fa-vaadin' => __( 'vaadin', 'buildr' ),
                'fas fa-venus' => __( 'venus', 'buildr' ),
                'fas fa-venus-double' => __( 'venus-double', 'buildr' ),
                'fas fa-venus-mars' => __( 'venus-mars', 'buildr' ),
                'fab fa-viacoin' => __( 'viacoin', 'buildr' ),
                'fab fa-viadeo' => __( 'viadeo', 'buildr' ),
                'fab fa-viadeo-square' => __( 'viadeo-square', 'buildr' ),
                'fas fa-vial' => __( 'vial', 'buildr' ),
                'fas fa-vials' => __( 'vials', 'buildr' ),
                'fab fa-viber' => __( 'viber', 'buildr' ),
                'fas fa-video' => __( 'video', 'buildr' ),
                'fas fa-video-slash' => __( 'video-slash', 'buildr' ),
                'fab fa-vimeo' => __( 'vimeo', 'buildr' ),
                'fab fa-vimeo-square' => __( 'vimeo-square', 'buildr' ),
                'fab fa-vimeo-v' => __( 'vimeo-v', 'buildr' ),
                'fab fa-vine' => __( 'vine', 'buildr' ),
                'fab fa-vk' => __( 'vk', 'buildr' ),
                'fab fa-vnv' => __( 'vnv', 'buildr' ),
                'fas fa-volleyball-ball' => __( 'volleyball-ball', 'buildr' ),
                'fas fa-volume-down' => __( 'volume-down', 'buildr' ),
                'fas fa-volume-off' => __( 'volume-off', 'buildr' ),
                'fas fa-volume-up' => __( 'volume-up', 'buildr' ),
                'fab fa-vuejs' => __( 'vuejs', 'buildr' ),
                'fas fa-warehouse' => __( 'warehouse', 'buildr' ),
                'fab fa-weibo' => __( 'weibo', 'buildr' ),
                'fas fa-weight' => __( 'weight', 'buildr' ),
                'fab fa-weixin' => __( 'weixin', 'buildr' ),
                'fab fa-whatsapp' => __( 'whatsapp', 'buildr' ),
                'fab fa-whatsapp-square' => __( 'whatsapp-square', 'buildr' ),
                'fas fa-wheelchair' => __( 'wheelchair', 'buildr' ),
                'fab fa-whmcs' => __( 'whmcs', 'buildr' ),
                'fas fa-wifi' => __( 'wifi', 'buildr' ),
                'fab fa-wikipedia-w' => __( 'wikipedia-w', 'buildr' ),
                'fas fa-window-close' => __( 'window-close', 'buildr' ),
                'far fa-window-close' => __( 'window-close', 'buildr' ),
                'fas fa-window-maximize' => __( 'window-maximize', 'buildr' ),
                'far fa-window-maximize' => __( 'window-maximize', 'buildr' ),
                'fas fa-window-minimize' => __( 'window-minimize', 'buildr' ),
                'far fa-window-minimize' => __( 'window-minimize', 'buildr' ),
                'fas fa-window-restore' => __( 'window-restore', 'buildr' ),
                'far fa-window-restore' => __( 'window-restore', 'buildr' ),
                'fab fa-windows' => __( 'windows', 'buildr' ),
                'fas fa-wine-glass' => __( 'wine-glass', 'buildr' ),
                'fas fa-won-sign' => __( 'won-sign', 'buildr' ),
                'fab fa-wordpress' => __( 'wordpress', 'buildr' ),
                'fab fa-wordpress-simple' => __( 'wordpress-simple', 'buildr' ),
                'fab fa-wpbeginner' => __( 'wpbeginner', 'buildr' ),
                'fab fa-wpexplorer' => __( 'wpexplorer', 'buildr' ),
                'fab fa-wpforms' => __( 'wpforms', 'buildr' ),
                'fas fa-wrench' => __( 'wrench', 'buildr' ),
                'fas fa-x-ray' => __( 'x-ray', 'buildr' ),
                'fab fa-xbox' => __( 'xbox', 'buildr' ),
                'fab fa-xing' => __( 'xing', 'buildr' ),
                'fab fa-xing-square' => __( 'xing-square', 'buildr' ),
                'fab fa-y-combinator' => __( 'y-combinator', 'buildr' ),
                'fab fa-yahoo' => __( 'yahoo', 'buildr' ),
                'fab fa-yandex' => __( 'yandex', 'buildr' ),
                'fab fa-yandex-international' => __( 'yandex-international', 'buildr' ),
                'fab fa-yelp' => __( 'yelp', 'buildr' ),
                'fas fa-yen-sign' => __( 'yen-sign', 'buildr' ),
                'fab fa-yoast' => __( 'yoast', 'buildr' ),
                'fab fa-youtube' => __( 'youtube', 'buildr' ),
                'fab fa-youtube-square' => __( 'youtube-square', 'buildr' ),
            );
            return apply_filters( 'FONTAWESOME_ARRAY', $fonts_arr );
        }
    }
    global $wbtmcore;
    $wbtmcore = new RbfwFormFieldsGenerator();
}