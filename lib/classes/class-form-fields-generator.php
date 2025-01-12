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






if( ! class_exists( 'FormFieldsGenerator' ) ) {

    class FormFieldsGenerator {




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




        public function field_text_multi( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $remove_text 	= isset( $option['remove_text'] ) ? $option['remove_text'] : '<i class="fas fa-trash"></i>';
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
                <span class="ppof-button add-item"><?php esc_html_e('Add','booking-and-rental-manager-for-woocommerce'); ?></span>
                <div class="field-list <?php if($sortable){ echo 'sortable'; }?>" id="<?php echo esc_attr($field_id); ?>">
                    <?php
                    if(!empty($values)):
                        foreach ($values as $value):
                            ?>
                            <div class="item">
                                <input type='text' name='<?php echo esc_attr($field_name); ?>[]'  placeholder='<?php
                                echo esc_attr($placeholder); ?>' value="<?php echo esc_attr($value); ?>" />

                                <span class="ppof-button clone"><i class="far fa-clone"></i></span>

                                <?php if($sortable):?>
                                <span class="ppof-button sort"><i class="fas fa-arrows-alt"></i></span>
                                <?php endif; ?>

                                <span class="ppof-button remove" onclick="jQuery(this).parent().remove()"><?php echo wp_kses_post($remove_text); ?></span>
                            </div>
                        <?php
                        endforeach;
                    else:
                        ?>
                        <div class="item">
                            <input type='text' name='<?php echo esc_attr($field_name); ?>[]'  placeholder='<?php echo
                            esc_attr($placeholder); ?>'
                                   value='' /><span class="button remove" onclick="jQuery(this).parent().remove()
"><?php echo wp_kses_post($remove_text); ?></span>
                            <?php if($sortable):?>
                                <span class="button sort"><i class="fas fa-arrows-alt"></i></span>
                            <?php endif; ?>
                            <span class="button clone"><i class="far fa-clone"></i></span>
                        </div>
                    <?php
                    endif;
                    ?>
                </div>
                <div class="error-mgs"></div>
                <script>
                    jQuery(document).ready(function($) {
                        jQuery(document).on('click', '.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .clone',function(){


                            <?php
                            if(!empty($limit)):
                            ?>
                            var limit = <?php  echo esc_attr($limit); ?>;
                            var node_count = $( ".field-text-multi-wrapper-<?php echo esc_attr($id); ?> .field-list .item" ).size();
                            if(limit > node_count){
                                $( this ).parent().clone().appendTo('.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .field-list' );
                            }else{
                                jQuery('.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .error-mgs').html('Sorry! you can add max '+limit+' item').stop().fadeIn(400).delay(3000).fadeOut(400);
                            }
                            <?php
                            else:
                            ?>
                            $( this ).parent().clone().appendTo('.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .field-list' );
                            <?php
                            endif;
                            ?>

                            //$( this ).parent().appendTo( '.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .field-list' );


                        })
                    jQuery(document).on('click', '.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .add-item',function(){


                        html_<?php echo esc_attr($id); ?> = '<div class="item">';
                        html_<?php echo esc_attr($id); ?> += '<input type="text" name="<?php echo esc_attr($field_name); ?>[]" placeholder="<?php
                            echo esc_attr($placeholder); ?>" />';
                        html_<?php echo esc_attr($id); ?> += '<span class="button remove" onclick="jQuery(this).parent().remove()' +
                            '"><?php echo wp_kses_post($remove_text); ?></span>';
                        html_<?php echo esc_attr($id); ?> += '<span class="button clone"><i class="far fa-clone"></i></span>';
                        <?php if($sortable):?>
                        html_<?php echo esc_attr($id); ?> += ' <span class="button sort" ><i class="fas fa-arrows-alt"></i></span>';
                        <?php endif; ?>
                        html_<?php echo esc_attr($id); ?> += '</div>';


                        <?php
                        if(!empty($limit)):
                            ?>
                            var limit = <?php  echo esc_attr($limit); ?>;
                            var node_count = $( ".field-text-multi-wrapper-<?php echo esc_attr($id); ?> .field-list .item" ).size();
                            if(limit > node_count){
                                jQuery('.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .field-list').append(html_<?php echo esc_attr($id); ?>);
                            }else{
                                jQuery('.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .error-mgs').html('Sorry! you can add max '+limit+' item').stop().fadeIn(400).delay(3000).fadeOut(400);
                            }
                            <?php
                        else:
                            ?>
                            jQuery('.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .field-list').append(html_<?php echo esc_attr($id); ?>);
                            <?php
                        endif;
                        ?>




                    })

                })
                </script>

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
                <textarea name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' cols='40' rows='5' placeholder='<?php echo esc_attr($placeholder); ?>'><?php echo wp_kses_post($__value); ?></textarea>
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
                    id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-checkbox-wrapper
            field-checkbox-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $args as $key => $argName ):
                    $checked = (  $key == $value ) ? "checked" : "";
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
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-switch-wrapper
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
            field-switch-multi-wrapper-<?php echo esc_html($id); ?>">
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
                                    <span class="name"><?php echo esc_html(date($item)); ?></span></label>
                                <span class="format"><code><?php echo esc_attr($item); ?></code></span>
                            </div>
                        <?php
                        endforeach;
                        ?>
                        <div class="format-value">
                            <span class="format"><input value="<?php echo esc_attr($value); ?>" name="<?php echo esc_attr($field_name); ?>"></span>
                            <div class="">Preview: <?php echo esc_html(date($value)); ?></div>
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
                                <label><input type="radio" <?php echo esc_attr($checked); ?> name="preset_<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($item); ?>"><span class="name"><?php echo esc_html(date($item)); ?></span></label>
                                <span class="format"><code><?php echo esc_html($item); ?></code></span>
                            </div>
                            <?php
                        endforeach;
                        ?>
                        <div class="format-value">
                            <span class="format"><input value="<?php echo esc_attr($value); ?>" name="<?php echo esc_attr($field_name); ?>"></span>
                            <div class="">Preview: <?php echo esc_html(date($value)); ?></div>
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
                    $('#<?php echo esc_attr($field_id); ?>').datepicker({dateFormat : '<?php echo esc_attr($date_format); ?>'})});
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
                    <div class="ppof-button add"><?php echo esc_html_e('Add','booking-and-rental-manager-for-woocommerce'); ?></div>
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
                    <div class="ppof-button select-user" ><?php echo esc_html_e('Choose User','booking-and-rental-manager-for-woocommerce');?></div>
                    <div class="search-user" ><input class="" type="text" placeholder="<?php echo esc_html_e('Start typing...','booking-and-rental-manager-for-woocommerce');?>"></div>
                    <ul>
                        <?php
                        if(!empty($icons)):
                            foreach ($icons as $user_id=>$iconTitle):
                                $user_data = get_user_by('ID',$user_id);
                                $get_avatar_url = get_avatar_url($user_id,array('size'=>'60'));
                                ?>
                                <li title="<?php echo esc_attr($user_data->display_name); ?>(#<?php echo esc_attr($user_id); ?>)"
                                    userSrc="<?php echo
                                esc_url($get_avatar_url); ?>"
                                    iconData="<?php echo esc_attr($user_id); ?>"><img src="<?php echo esc_url($get_avatar_url); ?>" />
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
                    <div class="ppof-button select-icon" ><?php echo esc_html_e('Choose Icon','booking-and-rental-manager-for-woocommerce'); ?></div>
                    <div class="search-icon" ><input class="" type="text" placeholder="<?php echo esc_html_e('Start typing...','booking-and-rental-manager-for-woocommerce'); ?>"></div>
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
                    <div class="ppof-button select-icon" ><?php echo esc_html_e('Choose Icon','booking-and-rental-manager-for-woocommerce'); ?></div>
                    <div class="search-icon" ><input class="" type="text" placeholder="<?php echo esc_html_e('Start typing...','booking-and-rental-manager-for-woocommerce'); ?>"></div>
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
            
            
            // $value = preg_replace('#^<\/p>|<p>&nbsp;$#', '', $value);
// $value = preg_replace('/^[ \t]*[\r\n]+/m', '', $value);

            
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
                wp_editor( htmlspecialchars_decode($value), $id, $settings = $editor_settings);
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


        public function field_option_group( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            $options			= isset( $option['options'] ) ? $option['options'] : array();
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $values			= isset( $option['value'] ) ? $option['value'] : '';
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $FormFieldsGenerator = new FormFieldsGenerator();

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
                                                echo wp_kses_post(mep_field_generator($option['type'], $option));
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
            $FormFieldsGenerator = new FormFieldsGenerator();

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
                                                            echo wp_kses_post(mep_field_generator($option['type'], $option));
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
            $FormFieldsGenerator = new FormFieldsGenerator();

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
                                                                echo wp_kses_post(mep_field_generator($option['type'], $option));
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
                    <input class="link-val" name='<?php echo esc_attr($field_name); ?>' type="text" value="<?php echo esc_attr($value); ?>"> <span class='ppof-button upload' id='media_upload_<?php echo esc_attr($id); ?>'><?php echo esc_html_e('Upload','booking-and-rental-manager-for-woocommerce');?></span> <span class="ppof-button clear">Clear</span>
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
                <div class='ppof-button upload' id='media_upload_<?php echo esc_attr($id); ?>'><?php echo esc_html_e('Upload','booking-and-rental-manager-for-woocommerce');?></div><div class='ppof-button clear' id='media_clear_<?php echo esc_attr($id); ?>'><?php echo esc_html_e('Clear','booking-and-rental-manager-for-woocommerce');?></div>
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
            $remove_text			= isset( $option['remove_text'] ) ? $option['remove_text'] : '<i class="fas fa-times"></i>';
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
                <div class='ppof-button upload' id='media_upload_<?php echo esc_attr($id); ?>'><?php echo esc_html_e('Upload','booking-and-rental-manager-for-woocommerce');?></div><div class='ppof-button clear'
                                                                                          id='media_clear_<?php echo
                                                                                          esc_attr($id);
                                                                                          ?>'><?php echo esc_html_e('Clear','booking-and-rental-manager-for-woocommerce');?></div>
                <div class="media-list media-list-<?php echo esc_attr($id); ?> sortable">
                    <?php
                    if(!empty($values) && is_array($values)):
                        foreach ($values as $value ):
                            $media_url	= wp_get_attachment_url( $value );
                            $media_type	= get_post_mime_type( $value );
                            $media_title= get_the_title( $value );
                            ?>
                            <div class="item">
                                <span class="remove" onclick="jQuery(this).parent().remove()"><?php echo esc_html($remove_text); ?></span>
                                <span class="sort" >sort</span>
                                <img id='media_preview_<?php echo esc_attr($id); ?>' src='<?php echo esc_attr($media_url); ?>' style='width:100%'/>
                                <div class="item-title"><?php echo esc_html($media_title); ?></div>
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
            $remove_text 	= isset( $option['remove_text'] ) ? $option['remove_text'] : '<i class="fas fa-times"></i>';
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
                        }else{
                            jQuery(this).parent().parent().addClass('active');
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
                            //jQuery('.field-list').append(html_new);
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
                        html  = '<div class="item-wrap collapsible"><div class="header">';
                        html += '<span class="button title-text"><i class="fas fa-angle-double-down"></i> Expand</span> ';
                        html += '<span class="button remove" ' +
                            'onclick="jQuery(this).parent().parent().remove()"><?php echo wp_kses_post($remove_text); ?></span> ';
                        
                        <?php if($sortable):?>
                        html += '<span class="button sort" ><i class="fas fa-grip-vertical"></i></span>';
                        <?php endif; ?>
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

                                html+='<textarea id="<?php echo esc_attr($field_name); ?>'+now+'" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"></textarea>';
                               
                                jQuery(function(){
                                    tinymce.init({
                                        selector:"#<?php echo esc_attr($field_name); ?>"+now,
                                        menubar: true,
                                        relative_urls : 0,
                                        remove_script_host : 0,
                                        toolbar: 'undo redo link formatselect bold italic backcolor alignleft aligncenter alignright alignjustify bullist numlist outdent indent removeformat fullscreen',
                                                        plugins: 'fullscreen link'
                                    });
                                });                          
                                
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
                                jQuery('.<?php echo esc_html('field-repeatable-wrapper-'.$id); ?> .field-list').append(html);
                            }else{
                                jQuery('.field-repeatable-wrapper-<?php echo esc_attr($id); ?> .error-mgs').html('Sorry! you can add max '+limit+' item').stop().fadeIn(400).delay(3000).fadeOut(400);
                            }

                            <?php
                        else:
                            ?>
                            jQuery('.<?php echo esc_attr('field-repeatable-wrapper-'.$id); ?> .field-list').append(html);
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
                                <?php if($collapsible):?>
                                <div class="header">
                                    <?php endif; ?>                                  
                                    <?php if($sortable):?>
                                        <span class="button sort"><i class="fas fa-arrows-alt"></i></span>
                                    <?php endif; ?>
                                    <span class="title-text" style="cursor:pointer;display: inline-block;width: 84%;"><?php echo wp_kses_post($title_field_val); ?></span>
                                    <span class="button remove" onclick="jQuery(this).parent().parent().remove()"><?php echo wp_kses_post($remove_text); ?></span>
                                    <?php if($collapsible):?>
                                </div>
                            <?php endif; ?>
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
                                                
                                                <?php $rnd = wp_rand(); ?>
                                                <textarea id="<?php echo esc_attr($field_name.$rnd); ?>" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]"><?php echo wp_kses_post($__value); ?></textarea>
                                                <script>
                                                jQuery(function(){
                                                    tinymce.init({
                                                        selector: "#<?php echo esc_attr($field_name.$rnd); ?>",
                                                        menubar: true,
                                                        relative_urls : 0,
                                                        remove_script_host : 0,                                                        
                                                        toolbar: 'undo redo link formatselect bold italic backcolor alignleft aligncenter alignright alignjustify bullist numlist outdent indent removeformat fullscreen',
                                                        plugins: 'fullscreen link'
                                                    });
                                                });
                                                </script>
                                              
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
                <div class="ppof-button add-item"><i class="fas fa-plus-circle"></i> <?php echo esc_html($btntext); ?></div>
            </div>

            <?php
            return ob_get_clean();
        }

        public function get_tax_data($args){
            foreach ($this->get_rep_taxonomies_array( $args ) as $argIndex => $argName):
            $selected = ($argIndex == $argName) ? 'selected' : ''; ?><option <?php echo esc_attr($selected); ?>  value="<?php echo esc_attr($argIndex); ?>"><?php echo esc_html($argName); ?></option> <?php endforeach;
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

           // if( ! taxonomy_exists( $taxonomy ) ) throw new Pick_error("Taxonomy <strong>$taxonomy</strong> doesn't exists !");

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
            //if( ! taxonomy_exists( $taxonomy ) ) throw new Pick_error("Taxonomy <strong>$taxonomy</strong> doesn't exists !");
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
                'fab fa-500px' => __( '500px', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-accessible-icon' => __( 'accessible-icon', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-accusoft' => __( 'accusoft', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-address-book' => __( 'address-book', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-address-book' => __( 'address-book', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-address-card' => __( 'address-card', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-address-card' => __( 'address-card', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-adjust' => __( 'adjust', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-adn' => __( 'adn', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-adversal' => __( 'adversal', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-affiliatetheme' => __( 'affiliatetheme', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-algolia' => __( 'algolia', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-align-center' => __( 'align-center', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-align-justify' => __( 'align-justify', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-align-left' => __( 'align-left', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-align-right' => __( 'align-right', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-allergies' => __( 'allergies', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-amazon' => __( 'amazon', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-amazon-pay' => __( 'amazon-pay', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-ambulance' => __( 'ambulance', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-american-sign-language-interpreting' => __( 'american-sign-language-interpreting', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-amilia' => __( 'amilia', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-anchor' => __( 'anchor', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-android' => __( 'android', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-angellist' => __( 'angellist', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-angle-double-down' => __( 'angle-double-down', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-angle-double-left' => __( 'angle-double-left', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-angle-double-right' => __( 'angle-double-right', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-angle-double-up' => __( 'angle-double-up', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-angle-down' => __( 'angle-down', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-angle-left' => __( 'angle-left', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-angle-right' => __( 'angle-right', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-angle-up' => __( 'angle-up', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-angrycreative' => __( 'angrycreative', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-angular' => __( 'angular', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-app-store' => __( 'app-store', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-app-store-ios' => __( 'app-store-ios', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-apper' => __( 'apper', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-apple' => __( 'apple', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-apple-pay' => __( 'apple-pay', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-archive' => __( 'archive', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-arrow-alt-circle-down' => __( 'arrow-alt-circle-down', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-arrow-alt-circle-down' => __( 'arrow-alt-circle-down', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-arrow-alt-circle-left' => __( 'arrow-alt-circle-left', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-arrow-alt-circle-left' => __( 'arrow-alt-circle-left', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-arrow-alt-circle-right' => __( 'arrow-alt-circle-right', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-arrow-alt-circle-right' => __( 'arrow-alt-circle-right', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-arrow-alt-circle-up' => __( 'arrow-alt-circle-up', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-arrow-alt-circle-up' => __( 'arrow-alt-circle-up', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-arrow-circle-down' => __( 'arrow-circle-down', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-arrow-circle-left' => __( 'arrow-circle-left', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-arrow-circle-right' => __( 'arrow-circle-right', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-arrow-circle-up' => __( 'arrow-circle-up', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-arrow-down' => __( 'arrow-down', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-arrow-left' => __( 'arrow-left', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-arrow-right' => __( 'arrow-right', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-arrow-up' => __( 'arrow-up', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-arrows-alt' => __( 'arrows-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-arrows-alt-h' => __( 'arrows-alt-h', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-arrows-alt-v' => __( 'arrows-alt-v', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-assistive-listening-systems' => __( 'assistive-listening-systems', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-asterisk' => __( 'asterisk', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-asymmetrik' => __( 'asymmetrik', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-at' => __( 'at', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-audible' => __( 'audible', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-audio-description' => __( 'audio-description', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-autoprefixer' => __( 'autoprefixer', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-avianex' => __( 'avianex', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-aviato' => __( 'aviato', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-aws' => __( 'aws', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-backward' => __( 'backward', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-balance-scale' => __( 'balance-scale', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-ban' => __( 'ban', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-band-aid' => __( 'band-aid', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-bandcamp' => __( 'bandcamp', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-barcode' => __( 'barcode', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-bars' => __( 'bars', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-baseball-ball' => __( 'baseball-ball', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-basketball-ball' => __( 'basketball-ball', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-bath' => __( 'bath', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-battery-empty' => __( 'battery-empty', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-battery-full' => __( 'battery-full', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-battery-half' => __( 'battery-half', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-battery-quarter' => __( 'battery-quarter', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-battery-three-quarters' => __( 'battery-three-quarters', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-bed' => __( 'bed', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-beer' => __( 'beer', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-behance' => __( 'behance', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-behance-square' => __( 'behance-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-bell' => __( 'bell', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-bell' => __( 'bell', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-bell-slash' => __( 'bell-slash', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-bell-slash' => __( 'bell-slash', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-bicycle' => __( 'bicycle', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-bimobject' => __( 'bimobject', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-binoculars' => __( 'binoculars', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-birthday-cake' => __( 'birthday-cake', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-bitbucket' => __( 'bitbucket', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-bitcoin' => __( 'bitcoin', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-bity' => __( 'bity', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-black-tie' => __( 'black-tie', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-blackberry' => __( 'blackberry', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-blind' => __( 'blind', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-blogger' => __( 'blogger', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-blogger-b' => __( 'blogger-b', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-bluetooth' => __( 'bluetooth', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-bluetooth-b' => __( 'bluetooth-b', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-bold' => __( 'bold', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-bolt' => __( 'bolt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-bomb' => __( 'bomb', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-book' => __( 'book', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-bookmark' => __( 'bookmark', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-bookmark' => __( 'bookmark', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-bowling-ball' => __( 'bowling-ball', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-box' => __( 'box', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-box-open' => __( 'box-open', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-boxes' => __( 'boxes', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-braille' => __( 'braille', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-briefcase' => __( 'briefcase', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-briefcase-medical' => __( 'briefcase-medical', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-btc' => __( 'btc', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-bug' => __( 'bug', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-building' => __( 'building', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-building' => __( 'building', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-bullhorn' => __( 'bullhorn', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-bullseye' => __( 'bullseye', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-burn' => __( 'burn', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-buromobelexperte' => __( 'buromobelexperte', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-bus' => __( 'bus', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-buysellads' => __( 'buysellads', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-calculator' => __( 'calculator', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-calendar' => __( 'calendar', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-calendar' => __( 'calendar', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-calendar-alt' => __( 'calendar-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-calendar-alt' => __( 'calendar-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-calendar-check' => __( 'calendar-check', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-calendar-check' => __( 'calendar-check', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-calendar-minus' => __( 'calendar-minus', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-calendar-minus' => __( 'calendar-minus', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-calendar-plus' => __( 'calendar-plus', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-calendar-plus' => __( 'calendar-plus', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-calendar-times' => __( 'calendar-times', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-calendar-times' => __( 'calendar-times', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-camera' => __( 'camera', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-camera-retro' => __( 'camera-retro', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-capsules' => __( 'capsules', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-car' => __( 'car', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-caret-down' => __( 'caret-down', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-caret-left' => __( 'caret-left', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-caret-right' => __( 'caret-right', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-caret-square-down' => __( 'caret-square-down', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-caret-square-down' => __( 'caret-square-down', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-caret-square-left' => __( 'caret-square-left', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-caret-square-left' => __( 'caret-square-left', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-caret-square-right' => __( 'caret-square-right', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-caret-square-right' => __( 'caret-square-right', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-caret-square-up' => __( 'caret-square-up', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-caret-square-up' => __( 'caret-square-up', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-caret-up' => __( 'caret-up', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-cart-arrow-down' => __( 'cart-arrow-down', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-cart-plus' => __( 'cart-plus', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-cc-amazon-pay' => __( 'cc-amazon-pay', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-cc-amex' => __( 'cc-amex', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-cc-apple-pay' => __( 'cc-apple-pay', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-cc-diners-club' => __( 'cc-diners-club', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-cc-discover' => __( 'cc-discover', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-cc-jcb' => __( 'cc-jcb', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-cc-mastercard' => __( 'cc-mastercard', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-cc-paypal' => __( 'cc-paypal', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-cc-stripe' => __( 'cc-stripe', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-cc-visa' => __( 'cc-visa', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-centercode' => __( 'centercode', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-certificate' => __( 'certificate', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-chart-area' => __( 'chart-area', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-chart-bar' => __( 'chart-bar', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-chart-bar' => __( 'chart-bar', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-chart-line' => __( 'chart-line', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-chart-pie' => __( 'chart-pie', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-check' => __( 'check', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-check-circle' => __( 'check-circle', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-check-circle' => __( 'check-circle', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-check-square' => __( 'check-square', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-check-square' => __( 'check-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-chess' => __( 'chess', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-chess-bishop' => __( 'chess-bishop', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-chess-board' => __( 'chess-board', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-chess-king' => __( 'chess-king', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-chess-knight' => __( 'chess-knight', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-chess-pawn' => __( 'chess-pawn', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-chess-queen' => __( 'chess-queen', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-chess-rook' => __( 'chess-rook', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-chevron-circle-down' => __( 'chevron-circle-down', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-chevron-circle-left' => __( 'chevron-circle-left', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-chevron-circle-right' => __( 'chevron-circle-right', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-chevron-circle-up' => __( 'chevron-circle-up', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-chevron-down' => __( 'chevron-down', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-chevron-left' => __( 'chevron-left', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-chevron-right' => __( 'chevron-right', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-chevron-up' => __( 'chevron-up', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-child' => __( 'child', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-chrome' => __( 'chrome', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-circle' => __( 'circle', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-circle' => __( 'circle', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-circle-notch' => __( 'circle-notch', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-clipboard' => __( 'clipboard', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-clipboard' => __( 'clipboard', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-clipboard-check' => __( 'clipboard-check', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-clipboard-list' => __( 'clipboard-list', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-clock' => __( 'clock', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-clock' => __( 'clock', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-clone' => __( 'clone', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-clone' => __( 'clone', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-closed-captioning' => __( 'closed-captioning', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-closed-captioning' => __( 'closed-captioning', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-cloud' => __( 'cloud', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-cloud-download-alt' => __( 'cloud-download-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-cloud-upload-alt' => __( 'cloud-upload-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-cloudscale' => __( 'cloudscale', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-cloudsmith' => __( 'cloudsmith', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-cloudversify' => __( 'cloudversify', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-code' => __( 'code', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-code-branch' => __( 'code-branch', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-codepen' => __( 'codepen', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-codiepie' => __( 'codiepie', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-coffee' => __( 'coffee', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-cog' => __( 'cog', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-cogs' => __( 'cogs', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-columns' => __( 'columns', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-comment' => __( 'comment', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-comment' => __( 'comment', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-comment-alt' => __( 'comment-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-comment-alt' => __( 'comment-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-comment-dots' => __( 'comment-dots', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-comment-slash' => __( 'comment-slash', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-comments' => __( 'comments', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-comments' => __( 'comments', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-compass' => __( 'compass', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-compass' => __( 'compass', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-compress' => __( 'compress', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-connectdevelop' => __( 'connectdevelop', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-contao' => __( 'contao', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-copy' => __( 'copy', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-copy' => __( 'copy', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-copyright' => __( 'copyright', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-copyright' => __( 'copyright', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-couch' => __( 'couch', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-cpanel' => __( 'cpanel', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-creative-commons' => __( 'creative-commons', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-credit-card' => __( 'credit-card', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-credit-card' => __( 'credit-card', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-crop' => __( 'crop', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-crosshairs' => __( 'crosshairs', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-css3' => __( 'css3', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-css3-alt' => __( 'css3-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-cube' => __( 'cube', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-cubes' => __( 'cubes', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-cut' => __( 'cut', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-cuttlefish' => __( 'cuttlefish', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-d-and-d' => __( 'd-and-d', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-dashcube' => __( 'dashcube', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-database' => __( 'database', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-deaf' => __( 'deaf', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-delicious' => __( 'delicious', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-deploydog' => __( 'deploydog', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-deskpro' => __( 'deskpro', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-desktop' => __( 'desktop', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-deviantart' => __( 'deviantart', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-diagnoses' => __( 'diagnoses', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-digg' => __( 'digg', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-digital-ocean' => __( 'digital-ocean', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-discord' => __( 'discord', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-discourse' => __( 'discourse', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-dna' => __( 'dna', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-dochub' => __( 'dochub', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-docker' => __( 'docker', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-dollar-sign' => __( 'dollar-sign', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-dolly' => __( 'dolly', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-dolly-flatbed' => __( 'dolly-flatbed', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-donate' => __( 'donate', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-dot-circle' => __( 'dot-circle', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-dot-circle' => __( 'dot-circle', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-dove' => __( 'dove', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-download' => __( 'download', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-draft2digital' => __( 'draft2digital', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-dribbble' => __( 'dribbble', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-dribbble-square' => __( 'dribbble-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-dropbox' => __( 'dropbox', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-drupal' => __( 'drupal', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-dyalog' => __( 'dyalog', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-earlybirds' => __( 'earlybirds', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-edge' => __( 'edge', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-edit' => __( 'edit', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-edit' => __( 'edit', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-eject' => __( 'eject', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-elementor' => __( 'elementor', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-ellipsis-h' => __( 'ellipsis-h', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-ellipsis-v' => __( 'ellipsis-v', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-ember' => __( 'ember', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-empire' => __( 'empire', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-envelope' => __( 'envelope', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-envelope' => __( 'envelope', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-envelope-open' => __( 'envelope-open', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-envelope-open' => __( 'envelope-open', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-envelope-square' => __( 'envelope-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-envira' => __( 'envira', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-eraser' => __( 'eraser', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-erlang' => __( 'erlang', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-ethereum' => __( 'ethereum', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-etsy' => __( 'etsy', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-euro-sign' => __( 'euro-sign', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-exchange-alt' => __( 'exchange-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-exclamation' => __( 'exclamation', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-exclamation-circle' => __( 'exclamation-circle', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-exclamation-triangle' => __( 'exclamation-triangle', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-expand' => __( 'expand', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-expand-arrows-alt' => __( 'expand-arrows-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-expeditedssl' => __( 'expeditedssl', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-external-link-alt' => __( 'external-link-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-external-link-square-alt' => __( 'external-link-square-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-eye' => __( 'eye', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-eye-dropper' => __( 'eye-dropper', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-eye-slash' => __( 'eye-slash', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-eye-slash' => __( 'eye-slash', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-facebook' => __( 'facebook', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-facebook-f' => __( 'facebook-f', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-facebook-messenger' => __( 'facebook-messenger', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-facebook-square' => __( 'facebook-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-fast-backward' => __( 'fast-backward', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-fast-forward' => __( 'fast-forward', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-fax' => __( 'fax', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-female' => __( 'female', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-fighter-jet' => __( 'fighter-jet', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-file' => __( 'file', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-file' => __( 'file', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-file-alt' => __( 'file-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-file-alt' => __( 'file-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-file-archive' => __( 'file-archive', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-file-archive' => __( 'file-archive', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-file-audio' => __( 'file-audio', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-file-audio' => __( 'file-audio', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-file-code' => __( 'file-code', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-file-code' => __( 'file-code', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-file-excel' => __( 'file-excel', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-file-excel' => __( 'file-excel', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-file-image' => __( 'file-image', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-file-image' => __( 'file-image', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-file-medical' => __( 'file-medical', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-file-medical-alt' => __( 'file-medical-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-file-pdf' => __( 'file-pdf', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-file-pdf' => __( 'file-pdf', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-file-powerpoint' => __( 'file-powerpoint', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-file-powerpoint' => __( 'file-powerpoint', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-file-video' => __( 'file-video', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-file-video' => __( 'file-video', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-file-word' => __( 'file-word', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-file-word' => __( 'file-word', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-film' => __( 'film', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-filter' => __( 'filter', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-fire' => __( 'fire', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-fire-extinguisher' => __( 'fire-extinguisher', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-firefox' => __( 'firefox', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-first-aid' => __( 'first-aid', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-first-order' => __( 'first-order', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-firstdraft' => __( 'firstdraft', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-flag' => __( 'flag', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-flag' => __( 'flag', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-flag-checkered' => __( 'flag-checkered', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-flask' => __( 'flask', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-flickr' => __( 'flickr', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-flipboard' => __( 'flipboard', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-fly' => __( 'fly', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-folder' => __( 'folder', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-folder' => __( 'folder', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-folder-open' => __( 'folder-open', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-folder-open' => __( 'folder-open', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-font' => __( 'font', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-font-awesome' => __( 'font-awesome', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-font-awesome-alt' => __( 'font-awesome-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-font-awesome-flag' => __( 'font-awesome-flag', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-fonticons' => __( 'fonticons', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-fonticons-fi' => __( 'fonticons-fi', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-football-ball' => __( 'football-ball', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-fort-awesome' => __( 'fort-awesome', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-fort-awesome-alt' => __( 'fort-awesome-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-forumbee' => __( 'forumbee', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-forward' => __( 'forward', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-foursquare' => __( 'foursquare', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-free-code-camp' => __( 'free-code-camp', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-freebsd' => __( 'freebsd', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-frown' => __( 'frown', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-frown' => __( 'frown', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-futbol' => __( 'futbol', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-futbol' => __( 'futbol', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-gamepad' => __( 'gamepad', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-gavel' => __( 'gavel', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-gem' => __( 'gem', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-gem' => __( 'gem', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-genderless' => __( 'genderless', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-get-pocket' => __( 'get-pocket', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-gg' => __( 'gg', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-gg-circle' => __( 'gg-circle', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-gift' => __( 'gift', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-git' => __( 'git', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-git-square' => __( 'git-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-github' => __( 'github', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-github-alt' => __( 'github-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-github-square' => __( 'github-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-gitkraken' => __( 'gitkraken', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-gitlab' => __( 'gitlab', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-gitter' => __( 'gitter', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-glass-martini' => __( 'glass-martini', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-glide' => __( 'glide', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-glide-g' => __( 'glide-g', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-globe' => __( 'globe', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-gofore' => __( 'gofore', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-golf-ball' => __( 'golf-ball', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-goodreads' => __( 'goodreads', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-goodreads-g' => __( 'goodreads-g', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-google' => __( 'google', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-google-drive' => __( 'google-drive', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-google-play' => __( 'google-play', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-google-plus' => __( 'google-plus', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-google-plus-g' => __( 'google-plus-g', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-google-plus-square' => __( 'google-plus-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-google-wallet' => __( 'google-wallet', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-graduation-cap' => __( 'graduation-cap', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-gratipay' => __( 'gratipay', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-grav' => __( 'grav', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-gripfire' => __( 'gripfire', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-grunt' => __( 'grunt', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-gulp' => __( 'gulp', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-h-square' => __( 'h-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-hacker-news' => __( 'hacker-news', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-hacker-news-square' => __( 'hacker-news-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hand-holding' => __( 'hand-holding', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hand-holding-heart' => __( 'hand-holding-heart', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hand-holding-usd' => __( 'hand-holding-usd', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hand-lizard' => __( 'hand-lizard', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-hand-lizard' => __( 'hand-lizard', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hand-paper' => __( 'hand-paper', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-hand-paper' => __( 'hand-paper', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hand-peace' => __( 'hand-peace', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-hand-peace' => __( 'hand-peace', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hand-point-down' => __( 'hand-point-down', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-hand-point-down' => __( 'hand-point-down', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hand-point-left' => __( 'hand-point-left', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-hand-point-left' => __( 'hand-point-left', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hand-point-right' => __( 'hand-point-right', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-hand-point-right' => __( 'hand-point-right', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hand-point-up' => __( 'hand-point-up', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-hand-point-up' => __( 'hand-point-up', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hand-pointer' => __( 'hand-pointer', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-hand-pointer' => __( 'hand-pointer', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hand-rock' => __( 'hand-rock', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-hand-rock' => __( 'hand-rock', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hand-scissors' => __( 'hand-scissors', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-hand-scissors' => __( 'hand-scissors', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hand-spock' => __( 'hand-spock', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-hand-spock' => __( 'hand-spock', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hands' => __( 'hands', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hands-helping' => __( 'hands-helping', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-handshake' => __( 'handshake', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-handshake' => __( 'handshake', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hashtag' => __( 'hashtag', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hdd' => __( 'hdd', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-hdd' => __( 'hdd', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-heading' => __( 'heading', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-headphones' => __( 'headphones', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-heart' => __( 'heart', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-heart' => __( 'heart', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-heartbeat' => __( 'heartbeat', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-hips' => __( 'hips', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-hire-a-helper' => __( 'hire-a-helper', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-history' => __( 'history', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hockey-puck' => __( 'hockey-puck', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-home' => __( 'home', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-hooli' => __( 'hooli', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hospital' => __( 'hospital', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-hospital' => __( 'hospital', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hospital-alt' => __( 'hospital-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hospital-symbol' => __( 'hospital-symbol', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-hotjar' => __( 'hotjar', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hourglass' => __( 'hourglass', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-hourglass' => __( 'hourglass', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hourglass-end' => __( 'hourglass-end', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hourglass-half' => __( 'hourglass-half', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-hourglass-start' => __( 'hourglass-start', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-houzz' => __( 'houzz', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-html5' => __( 'html5', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-hubspot' => __( 'hubspot', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-i-cursor' => __( 'i-cursor', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-id-badge' => __( 'id-badge', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-id-badge' => __( 'id-badge', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-id-card' => __( 'id-card', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-id-card' => __( 'id-card', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-id-card-alt' => __( 'id-card-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-image' => __( 'image', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-image' => __( 'image', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-images' => __( 'images', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-images' => __( 'images', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-imdb' => __( 'imdb', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-inbox' => __( 'inbox', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-indent' => __( 'indent', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-industry' => __( 'industry', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-info' => __( 'info', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-info-circle' => __( 'info-circle', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-instagram' => __( 'instagram', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-internet-explorer' => __( 'internet-explorer', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-ioxhost' => __( 'ioxhost', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-italic' => __( 'italic', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-itunes' => __( 'itunes', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-itunes-note' => __( 'itunes-note', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-java' => __( 'java', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-jenkins' => __( 'jenkins', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-joget' => __( 'joget', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-joomla' => __( 'joomla', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-js' => __( 'js', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-js-square' => __( 'js-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-jsfiddle' => __( 'jsfiddle', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-key' => __( 'key', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-keyboard' => __( 'keyboard', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-keyboard' => __( 'keyboard', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-keycdn' => __( 'keycdn', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-kickstarter' => __( 'kickstarter', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-kickstarter-k' => __( 'kickstarter-k', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-korvue' => __( 'korvue', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-language' => __( 'language', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-laptop' => __( 'laptop', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-laravel' => __( 'laravel', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-lastfm' => __( 'lastfm', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-lastfm-square' => __( 'lastfm-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-leaf' => __( 'leaf', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-leanpub' => __( 'leanpub', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-lemon' => __( 'lemon', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-lemon' => __( 'lemon', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-less' => __( 'less', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-level-down-alt' => __( 'level-down-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-level-up-alt' => __( 'level-up-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-life-ring' => __( 'life-ring', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-life-ring' => __( 'life-ring', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-lightbulb' => __( 'lightbulb', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-lightbulb' => __( 'lightbulb', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-line' => __( 'line', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-link' => __( 'link', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-linkedin' => __( 'linkedin', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-linkedin-in' => __( 'linkedin-in', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-linode' => __( 'linode', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-linux' => __( 'linux', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-lira-sign' => __( 'lira-sign', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-list' => __( 'list', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-list-alt' => __( 'list-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-list-alt' => __( 'list-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-list-ol' => __( 'list-ol', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-list-ul' => __( 'list-ul', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-location-arrow' => __( 'location-arrow', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-lock' => __( 'lock', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-lock-open' => __( 'lock-open', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-long-arrow-alt-down' => __( 'long-arrow-alt-down', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-long-arrow-alt-left' => __( 'long-arrow-alt-left', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-long-arrow-alt-right' => __( 'long-arrow-alt-right', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-long-arrow-alt-up' => __( 'long-arrow-alt-up', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-low-vision' => __( 'low-vision', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-lyft' => __( 'lyft', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-magento' => __( 'magento', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-magic' => __( 'magic', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-magnet' => __( 'magnet', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-male' => __( 'male', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-map' => __( 'map', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-map' => __( 'map', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-map-marker' => __( 'map-marker', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-map-marker-alt' => __( 'map-marker-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-map-pin' => __( 'map-pin', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-map-signs' => __( 'map-signs', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-mars' => __( 'mars', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-mars-double' => __( 'mars-double', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-mars-stroke' => __( 'mars-stroke', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-mars-stroke-h' => __( 'mars-stroke-h', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-mars-stroke-v' => __( 'mars-stroke-v', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-maxcdn' => __( 'maxcdn', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-medapps' => __( 'medapps', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-medium' => __( 'medium', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-medium-m' => __( 'medium-m', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-medkit' => __( 'medkit', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-medrt' => __( 'medrt', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-meetup' => __( 'meetup', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-meh' => __( 'meh', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-meh' => __( 'meh', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-mercury' => __( 'mercury', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-microchip' => __( 'microchip', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-microphone' => __( 'microphone', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-microphone-slash' => __( 'microphone-slash', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-microsoft' => __( 'microsoft', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-minus' => __( 'minus', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-minus-circle' => __( 'minus-circle', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-minus-square' => __( 'minus-square', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-minus-square' => __( 'minus-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-mix' => __( 'mix', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-mixcloud' => __( 'mixcloud', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-mizuni' => __( 'mizuni', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-mobile' => __( 'mobile', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-mobile-alt' => __( 'mobile-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-modx' => __( 'modx', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-monero' => __( 'monero', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-money-bill-alt' => __( 'money-bill-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-money-bill-alt' => __( 'money-bill-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-moon' => __( 'moon', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-moon' => __( 'moon', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-motorcycle' => __( 'motorcycle', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-mouse-pointer' => __( 'mouse-pointer', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-music' => __( 'music', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-napster' => __( 'napster', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-neuter' => __( 'neuter', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-newspaper' => __( 'newspaper', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-newspaper' => __( 'newspaper', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-nintendo-switch' => __( 'nintendo-switch', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-node' => __( 'node', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-node-js' => __( 'node-js', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-notes-medical' => __( 'notes-medical', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-npm' => __( 'npm', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-ns8' => __( 'ns8', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-nutritionix' => __( 'nutritionix', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-object-group' => __( 'object-group', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-object-group' => __( 'object-group', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-object-ungroup' => __( 'object-ungroup', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-object-ungroup' => __( 'object-ungroup', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-odnoklassniki' => __( 'odnoklassniki', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-odnoklassniki-square' => __( 'odnoklassniki-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-opencart' => __( 'opencart', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-openid' => __( 'openid', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-opera' => __( 'opera', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-optin-monster' => __( 'optin-monster', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-osi' => __( 'osi', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-outdent' => __( 'outdent', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-page4' => __( 'page4', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-pagelines' => __( 'pagelines', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-paint-brush' => __( 'paint-brush', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-palfed' => __( 'palfed', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-pallet' => __( 'pallet', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-paper-plane' => __( 'paper-plane', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-paper-plane' => __( 'paper-plane', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-paperclip' => __( 'paperclip', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-parachute-box' => __( 'parachute-box', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-paragraph' => __( 'paragraph', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-paste' => __( 'paste', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-patreon' => __( 'patreon', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-pause' => __( 'pause', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-pause-circle' => __( 'pause-circle', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-pause-circle' => __( 'pause-circle', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-paw' => __( 'paw', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-paypal' => __( 'paypal', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-pen-square' => __( 'pen-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-pencil-alt' => __( 'pencil-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-people-carry' => __( 'people-carry', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-percent' => __( 'percent', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-periscope' => __( 'periscope', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-phabricator' => __( 'phabricator', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-phoenix-framework' => __( 'phoenix-framework', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-phone' => __( 'phone', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-phone-slash' => __( 'phone-slash', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-phone-square' => __( 'phone-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-phone-volume' => __( 'phone-volume', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-php' => __( 'php', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-pied-piper' => __( 'pied-piper', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-pied-piper-alt' => __( 'pied-piper-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-pied-piper-hat' => __( 'pied-piper-hat', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-pied-piper-pp' => __( 'pied-piper-pp', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-piggy-bank' => __( 'piggy-bank', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-pills' => __( 'pills', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-pinterest' => __( 'pinterest', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-pinterest-p' => __( 'pinterest-p', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-pinterest-square' => __( 'pinterest-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-plane' => __( 'plane', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-play' => __( 'play', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-play-circle' => __( 'play-circle', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-play-circle' => __( 'play-circle', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-playstation' => __( 'playstation', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-plug' => __( 'plug', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-plus' => __( 'plus', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-plus-circle' => __( 'plus-circle', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-plus-square' => __( 'plus-square', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-plus-square' => __( 'plus-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-podcast' => __( 'podcast', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-poo' => __( 'poo', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-pound-sign' => __( 'pound-sign', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-power-off' => __( 'power-off', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-prescription-bottle' => __( 'prescription-bottle', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-prescription-bottle-alt' => __( 'prescription-bottle-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-print' => __( 'print', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-procedures' => __( 'procedures', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-product-hunt' => __( 'product-hunt', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-pushed' => __( 'pushed', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-puzzle-piece' => __( 'puzzle-piece', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-python' => __( 'python', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-qq' => __( 'qq', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-qrcode' => __( 'qrcode', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-question' => __( 'question', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-question-circle' => __( 'question-circle', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-question-circle' => __( 'question-circle', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-quidditch' => __( 'quidditch', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-quinscape' => __( 'quinscape', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-quora' => __( 'quora', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-quote-left' => __( 'quote-left', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-quote-right' => __( 'quote-right', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-random' => __( 'random', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-ravelry' => __( 'ravelry', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-react' => __( 'react', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-readme' => __( 'readme', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-rebel' => __( 'rebel', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-recycle' => __( 'recycle', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-red-river' => __( 'red-river', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-reddit' => __( 'reddit', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-reddit-alien' => __( 'reddit-alien', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-reddit-square' => __( 'reddit-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-redo' => __( 'redo', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-redo-alt' => __( 'redo-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-registered' => __( 'registered', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-registered' => __( 'registered', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-rendact' => __( 'rendact', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-renren' => __( 'renren', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-reply' => __( 'reply', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-reply-all' => __( 'reply-all', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-replyd' => __( 'replyd', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-resolving' => __( 'resolving', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-retweet' => __( 'retweet', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-ribbon' => __( 'ribbon', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-road' => __( 'road', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-rocket' => __( 'rocket', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-rocketchat' => __( 'rocketchat', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-rockrms' => __( 'rockrms', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-rss' => __( 'rss', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-rss-square' => __( 'rss-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-ruble-sign' => __( 'ruble-sign', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-rupee-sign' => __( 'rupee-sign', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-safari' => __( 'safari', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-sass' => __( 'sass', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-save' => __( 'save', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-save' => __( 'save', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-schlix' => __( 'schlix', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-scribd' => __( 'scribd', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-search' => __( 'search', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-search-minus' => __( 'search-minus', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-search-plus' => __( 'search-plus', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-searchengin' => __( 'searchengin', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-seedling' => __( 'seedling', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-sellcast' => __( 'sellcast', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-sellsy' => __( 'sellsy', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-server' => __( 'server', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-servicestack' => __( 'servicestack', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-share' => __( 'share', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-share-alt' => __( 'share-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-share-alt-square' => __( 'share-alt-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-share-square' => __( 'share-square', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-share-square' => __( 'share-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-shekel-sign' => __( 'shekel-sign', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-shield-alt' => __( 'shield-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-ship' => __( 'ship', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-shipping-fast' => __( 'shipping-fast', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-shirtsinbulk' => __( 'shirtsinbulk', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-shopping-bag' => __( 'shopping-bag', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-shopping-basket' => __( 'shopping-basket', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-shopping-cart' => __( 'shopping-cart', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-shower' => __( 'shower', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-sign' => __( 'sign', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-sign-in-alt' => __( 'sign-in-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-sign-language' => __( 'sign-language', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-sign-out-alt' => __( 'sign-out-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-signal' => __( 'signal', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-simplybuilt' => __( 'simplybuilt', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-sistrix' => __( 'sistrix', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-sitemap' => __( 'sitemap', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-skyatlas' => __( 'skyatlas', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-skype' => __( 'skype', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-slack' => __( 'slack', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-slack-hash' => __( 'slack-hash', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-sliders-h' => __( 'sliders-h', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-slideshare' => __( 'slideshare', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-smile' => __( 'smile', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-smile' => __( 'smile', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-smoking' => __( 'smoking', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-snapchat' => __( 'snapchat', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-snapchat-ghost' => __( 'snapchat-ghost', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-snapchat-square' => __( 'snapchat-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-snowflake' => __( 'snowflake', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-snowflake' => __( 'snowflake', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-sort' => __( 'sort', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-sort-alpha-down' => __( 'sort-alpha-down', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-sort-alpha-up' => __( 'sort-alpha-up', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-sort-amount-down' => __( 'sort-amount-down', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-sort-amount-up' => __( 'sort-amount-up', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-sort-down' => __( 'sort-down', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-sort-numeric-down' => __( 'sort-numeric-down', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-sort-numeric-up' => __( 'sort-numeric-up', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-sort-up' => __( 'sort-up', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-soundcloud' => __( 'soundcloud', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-space-shuttle' => __( 'space-shuttle', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-speakap' => __( 'speakap', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-spinner' => __( 'spinner', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-spotify' => __( 'spotify', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-square' => __( 'square', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-square' => __( 'square', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-square-full' => __( 'square-full', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-stack-exchange' => __( 'stack-exchange', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-stack-overflow' => __( 'stack-overflow', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-star' => __( 'star', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-star' => __( 'star', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-star-half' => __( 'star-half', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-star-half' => __( 'star-half', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-staylinked' => __( 'staylinked', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-steam' => __( 'steam', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-steam-square' => __( 'steam-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-steam-symbol' => __( 'steam-symbol', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-step-backward' => __( 'step-backward', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-step-forward' => __( 'step-forward', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-stethoscope' => __( 'stethoscope', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-sticker-mule' => __( 'sticker-mule', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-sticky-note' => __( 'sticky-note', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-sticky-note' => __( 'sticky-note', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-stop' => __( 'stop', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-stop-circle' => __( 'stop-circle', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-stop-circle' => __( 'stop-circle', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-stopwatch' => __( 'stopwatch', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-strava' => __( 'strava', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-street-view' => __( 'street-view', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-strikethrough' => __( 'strikethrough', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-stripe' => __( 'stripe', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-stripe-s' => __( 'stripe-s', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-studiovinari' => __( 'studiovinari', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-stumbleupon' => __( 'stumbleupon', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-stumbleupon-circle' => __( 'stumbleupon-circle', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-subscript' => __( 'subscript', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-subway' => __( 'subway', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-suitcase' => __( 'suitcase', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-sun' => __( 'sun', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-sun' => __( 'sun', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-superpowers' => __( 'superpowers', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-superscript' => __( 'superscript', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-supple' => __( 'supple', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-sync' => __( 'sync', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-sync-alt' => __( 'sync-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-syringe' => __( 'syringe', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-table' => __( 'table', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-table-tennis' => __( 'table-tennis', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-tablet' => __( 'tablet', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-tablet-alt' => __( 'tablet-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-tablets' => __( 'tablets', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-tachometer-alt' => __( 'tachometer-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-tag' => __( 'tag', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-tags' => __( 'tags', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-tape' => __( 'tape', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-tasks' => __( 'tasks', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-taxi' => __( 'taxi', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-telegram' => __( 'telegram', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-telegram-plane' => __( 'telegram-plane', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-tencent-weibo' => __( 'tencent-weibo', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-terminal' => __( 'terminal', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-text-height' => __( 'text-height', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-text-width' => __( 'text-width', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-th' => __( 'th', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-th-large' => __( 'th-large', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-th-list' => __( 'th-list', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-themeisle' => __( 'themeisle', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-thermometer' => __( 'thermometer', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-thermometer-empty' => __( 'thermometer-empty', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-thermometer-full' => __( 'thermometer-full', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-thermometer-half' => __( 'thermometer-half', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-thermometer-quarter' => __( 'thermometer-quarter', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-thermometer-three-quarters' => __( 'thermometer-three-quarters', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-thumbs-down' => __( 'thumbs-down', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-thumbs-down' => __( 'thumbs-down', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-thumbs-up' => __( 'thumbs-up', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-thumbs-up' => __( 'thumbs-up', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-thumbtack' => __( 'thumbtack', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-ticket-alt' => __( 'ticket-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-times' => __( 'times', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-times-circle' => __( 'times-circle', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-times-circle' => __( 'times-circle', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-tint' => __( 'tint', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-toggle-off' => __( 'toggle-off', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-toggle-on' => __( 'toggle-on', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-trademark' => __( 'trademark', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-train' => __( 'train', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-transgender' => __( 'transgender', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-transgender-alt' => __( 'transgender-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-trash' => __( 'trash', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-trash-alt' => __( 'trash-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-trash-alt' => __( 'trash-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-tree' => __( 'tree', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-trello' => __( 'trello', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-tripadvisor' => __( 'tripadvisor', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-trophy' => __( 'trophy', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-truck' => __( 'truck', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-truck-loading' => __( 'truck-loading', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-truck-moving' => __( 'truck-moving', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-tty' => __( 'tty', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-tumblr' => __( 'tumblr', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-tumblr-square' => __( 'tumblr-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-tv' => __( 'tv', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-twitch' => __( 'twitch', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-twitter' => __( 'twitter', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-twitter-square' => __( 'twitter-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-typo3' => __( 'typo3', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-uber' => __( 'uber', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-uikit' => __( 'uikit', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-umbrella' => __( 'umbrella', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-underline' => __( 'underline', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-undo' => __( 'undo', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-undo-alt' => __( 'undo-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-uniregistry' => __( 'uniregistry', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-universal-access' => __( 'universal-access', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-university' => __( 'university', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-unlink' => __( 'unlink', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-unlock' => __( 'unlock', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-unlock-alt' => __( 'unlock-alt', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-untappd' => __( 'untappd', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-upload' => __( 'upload', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-usb' => __( 'usb', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-user' => __( 'user', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-user' => __( 'user', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-user-circle' => __( 'user-circle', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-user-circle' => __( 'user-circle', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-user-md' => __( 'user-md', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-user-plus' => __( 'user-plus', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-user-secret' => __( 'user-secret', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-user-times' => __( 'user-times', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-users' => __( 'users', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-ussunnah' => __( 'ussunnah', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-utensil-spoon' => __( 'utensil-spoon', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-utensils' => __( 'utensils', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-vaadin' => __( 'vaadin', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-venus' => __( 'venus', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-venus-double' => __( 'venus-double', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-venus-mars' => __( 'venus-mars', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-viacoin' => __( 'viacoin', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-viadeo' => __( 'viadeo', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-viadeo-square' => __( 'viadeo-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-vial' => __( 'vial', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-vials' => __( 'vials', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-viber' => __( 'viber', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-video' => __( 'video', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-video-slash' => __( 'video-slash', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-vimeo' => __( 'vimeo', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-vimeo-square' => __( 'vimeo-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-vimeo-v' => __( 'vimeo-v', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-vine' => __( 'vine', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-vk' => __( 'vk', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-vnv' => __( 'vnv', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-volleyball-ball' => __( 'volleyball-ball', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-volume-down' => __( 'volume-down', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-volume-off' => __( 'volume-off', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-volume-up' => __( 'volume-up', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-vuejs' => __( 'vuejs', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-warehouse' => __( 'warehouse', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-weibo' => __( 'weibo', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-weight' => __( 'weight', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-weixin' => __( 'weixin', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-whatsapp' => __( 'whatsapp', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-whatsapp-square' => __( 'whatsapp-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-wheelchair' => __( 'wheelchair', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-whmcs' => __( 'whmcs', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-wifi' => __( 'wifi', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-wikipedia-w' => __( 'wikipedia-w', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-window-close' => __( 'window-close', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-window-close' => __( 'window-close', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-window-maximize' => __( 'window-maximize', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-window-maximize' => __( 'window-maximize', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-window-minimize' => __( 'window-minimize', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-window-minimize' => __( 'window-minimize', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-window-restore' => __( 'window-restore', 'booking-and-rental-manager-for-woocommerce' ),
                'far fa-window-restore' => __( 'window-restore', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-windows' => __( 'windows', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-wine-glass' => __( 'wine-glass', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-won-sign' => __( 'won-sign', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-wordpress' => __( 'wordpress', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-wordpress-simple' => __( 'wordpress-simple', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-wpbeginner' => __( 'wpbeginner', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-wpexplorer' => __( 'wpexplorer', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-wpforms' => __( 'wpforms', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-wrench' => __( 'wrench', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-x-ray' => __( 'x-ray', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-xbox' => __( 'xbox', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-xing' => __( 'xing', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-xing-square' => __( 'xing-square', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-y-combinator' => __( 'y-combinator', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-yahoo' => __( 'yahoo', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-yandex' => __( 'yandex', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-yandex-international' => __( 'yandex-international', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-yelp' => __( 'yelp', 'booking-and-rental-manager-for-woocommerce' ),
                'fas fa-yen-sign' => __( 'yen-sign', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-yoast' => __( 'yoast', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-youtube' => __( 'youtube', 'booking-and-rental-manager-for-woocommerce' ),
                'fab fa-youtube-square' => __( 'youtube-square', 'booking-and-rental-manager-for-woocommerce' ),
            );
            return apply_filters( 'FONTAWESOME_ARRAY', $fonts_arr );
        }
    }
    global $wbtmcore;
    $wbtmcore = new FormFieldsGenerator();
}
