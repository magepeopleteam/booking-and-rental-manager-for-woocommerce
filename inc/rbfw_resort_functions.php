<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

    function check_seasonal_price_resort( $Book_date, $rbfw_sp_prices, $room_type = '' , $active_tab ='') {
        foreach ( $rbfw_sp_prices as $rbfw_sp_price ) {
            if(isset($rbfw_sp_price['start_date']) && isset($rbfw_sp_price['end_date'])){
                $rbfw_sp_start_date = $rbfw_sp_price['start_date'];
                $rbfw_sp_end_date   = $rbfw_sp_price['end_date'];
                $sp_dates_array     = getAllDates( $rbfw_sp_start_date, $rbfw_sp_end_date );


                if ( in_array( $Book_date, $sp_dates_array ) ) {
                    foreach ($rbfw_sp_price['room_price'] as $room_price){
                        if($room_type == $room_price['room_type']){

                            set_transient("pricing_applied", "sessional", 3600);

                            if($active_tab=='daylong'){
                                return $room_price['day_long_price'];
                            }else{
                                return $room_price['price'];
                            }
                        }
                    }
                }
            }
        }
        return 'not_found';
    }

    function check_seasonal_price_resort_mds( $day_number, $rbfw_sp_prices, $room_type = '' , $active_tab ='', $minStartDay = '') {
        $price = 0;
        foreach ($rbfw_sp_prices as $rbfw_sp_price) {
            if ($day_number >= $rbfw_sp_price['start_day']) {
                foreach ($rbfw_sp_price['room_price'] as $key=>$room_price){
                    if($room_type == $room_price['room_type']){
                        set_transient("pricing_applied", "mds", 3600);
                        if($active_tab=='daylong'){
                            $price = $room_price['day_long_price'];
                        }else{
                            $price = $room_price['price'];
                        }
                    }
                }
            } else {
                break;
            }
        }
        return $price;
    }

    function check_price_resort_tp( $day_number, $rbfw_resort_data_tp, $room_type = '' , $active_tab ='', $default_price='') {
        $day_number = $day_number+1;
        foreach ($rbfw_resort_data_tp as $rbfw_tp_price) {
            $rbfw_start_day = $rbfw_tp_price['start_day'];
            $rbfw_end_day   = $rbfw_tp_price['end_day'];

            if ( $day_number >= $rbfw_start_day  &&  $day_number <= $rbfw_end_day) {
                foreach ($rbfw_tp_price['room_price'] as $key=>$room_price){
                    if($room_type == $room_price['room_type']){
                        set_transient("pricing_applied", "tp", 3600);
                        if($active_tab=='daylong'){
                            return $room_price['day_long_price'];
                        }else{
                            return $room_price['price'];
                        }
                    }
                }
            }
        }
        return $default_price;
    }