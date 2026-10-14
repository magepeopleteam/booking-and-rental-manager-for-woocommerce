<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

    /**
     * Seasonal rate of one resort room on one date, or 'not_found'.
     *
     * A season whose rate for this room/package was left blank does not apply:
     * a blank used to come back as '' and be billed as a free night.
     */
    function check_seasonal_price_resort( $Book_date, $rbfw_sp_prices, $room_type = '' , $active_tab ='') {
        if ( ! is_array( $rbfw_sp_prices ) ) {
            return 'not_found';
        }
        $book_ts = strtotime( (string) $Book_date );
        if ( false === $book_ts ) {
            return 'not_found';
        }
        $book_ymd = gmdate( 'Y-m-d', $book_ts );
        foreach ( $rbfw_sp_prices as $rbfw_sp_price ) {
            if ( empty( $rbfw_sp_price['start_date'] ) || empty( $rbfw_sp_price['end_date'] ) || empty( $rbfw_sp_price['room_price'] ) || ! is_array( $rbfw_sp_price['room_price'] ) ) {
                continue;
            }
            $start_ts = strtotime( $rbfw_sp_price['start_date'] );
            $end_ts   = strtotime( $rbfw_sp_price['end_date'] );
            if ( false === $start_ts || false === $end_ts ) {
                continue;
            }
            // Inclusive on both ends, compared as calendar days.
            if ( $book_ymd < gmdate( 'Y-m-d', $start_ts ) || $book_ymd > gmdate( 'Y-m-d', $end_ts ) ) {
                continue;
            }
            foreach ( $rbfw_sp_price['room_price'] as $room_price ) {
                if ( ! is_array( $room_price ) || ! isset( $room_price['room_type'] ) || $room_type != $room_price['room_type'] ) {
                    continue;
                }
                $key   = ( $active_tab == 'daylong' ) ? 'day_long_price' : 'price';
                $value = isset( $room_price[ $key ] ) ? trim( (string) $room_price[ $key ] ) : '';
                if ( '' === $value || ! is_numeric( $value ) ) {
                    break; // No rate for this room in this season; try the next season.
                }
                set_transient( 'pricing_applied', 'sessional', 3600 );
                return $value;
            }
        }
        return 'not_found';
    }

    /**
     * Days a resort stay is billed for. One rule for the booking form, the price
     * summary, the cart and the stored ticket: the nights between the dates, plus
     * one when "count extra day" is on, and never less than one (a same-day
     * day-long stay is one day, not a free one).
     *
     * @param string $checkin_date  Y-m-d.
     * @param string $checkout_date Y-m-d.
     * @return int
     */
    function rbfw_resort_billable_days( $checkin_date, $checkout_date ) {
        global $rbfw;
        $origin = $checkin_date ? date_create( $checkin_date ) : false;
        $target = $checkout_date ? date_create( $checkout_date ) : false;
        $days   = ( $origin && $target ) ? (int) date_diff( $origin, $target )->format( '%a' ) : 0;
        $extra  = is_object( $rbfw ) && method_exists( $rbfw, 'get_option_trans' )
            ? $rbfw->get_option_trans( 'rbfw_count_extra_day_enable', 'rbfw_basic_gen_settings', 'on' )
            : 'on';
        if ( 'on' === $extra || 0 === $days ) {
            $days++;
        }
        return $days;
    }

    /**
     * Price of ONE room of a type for a whole stay, after the pricing addons.
     *
     * Precedence is unchanged from the old inline copies: Multi Day Price Saver,
     * then Tiered Pricing, then Seasonal Pricing (per date), else the room's own
     * day-long / day-night rate.
     *
     * @param int    $post_id       Resort item.
     * @param string $room_type     Room type name.
     * @param string $category      'daynight' or 'daylong'.
     * @param string $checkin_date  Y-m-d.
     * @param string $checkout_date Y-m-d.
     * @return array|null {
     *     @type float  $base_rate Room's own rate per day.
     *     @type float  $total     Stay price for one room.
     *     @type float  $per_day   Average per day ($total / $days).
     *     @type int    $days      Billable days.
     *     @type bool   $varies    True when the daily rate is not the same every day.
     *     @type string $applied   '', 'sessional', 'mds' or 'tp'.
     * }
     *     Null when the item has no such room type.
     */
    function rbfw_resort_room_stay_price( $post_id, $room_type, $category, $checkin_date, $checkout_date ) {
        $rooms = get_post_meta( $post_id, 'rbfw_resort_room_data', true );
        $rooms = is_array( $rooms ) ? $rooms : array();
        $rate_key = ( 'daylong' === $category ) ? 'rbfw_room_daylong_rate' : 'rbfw_room_daynight_rate';
        $base_rate = null;
        foreach ( $rooms as $room ) {
            if ( is_array( $room ) && isset( $room['room_type'] ) && (string) $room['room_type'] === (string) $room_type ) {
                $base_rate = isset( $room[ $rate_key ] ) ? (float) $room[ $rate_key ] : 0.0;
                break;
            }
        }
        if ( null === $base_rate ) {
            return null;
        }

        $days    = rbfw_resort_billable_days( $checkin_date, $checkout_date );
        $daily   = array();
        $applied = '';

        $mds_on = function_exists( 'is_plugin_active' ) && is_plugin_active( 'multi-day-price-saver-addon-for-wprently/additional-day-price.php' );
        $tp_on  = function_exists( 'is_plugin_active' ) && is_plugin_active( 'tiered-pricing-addon-wprently/tiered-pricing-addon.php' );
        $sp_on  = function_exists( 'is_plugin_active' ) && is_plugin_active( 'booking-and-rental-manager-seasonal-pricing/rent-seasonal-pricing.php' );
        $mds    = $mds_on ? get_post_meta( $post_id, 'rbfw_resort_data_mds', true ) : array();
        $tp     = $tp_on ? get_post_meta( $post_id, 'rbfw_resort_data_tp', true ) : array();
        $sp     = $sp_on ? get_post_meta( $post_id, 'rbfw_resort_data_sp', true ) : array();

        if ( $mds_on && ! empty( $mds ) && is_array( $mds ) ) {
            $mds_rate = check_seasonal_price_resort_mds( $days, $mds, $room_type, $category );
            $rate     = ( (float) $mds_rate > 0 ) ? (float) $mds_rate : $base_rate;
            $applied  = ( (float) $mds_rate > 0 ) ? 'mds' : '';
            $daily    = array_fill( 0, $days, $rate );
        } elseif ( $tp_on && ! empty( $tp ) && is_array( $tp ) ) {
            for ( $d = 0; $d < $days; $d++ ) {
                $daily[] = (float) check_price_resort_tp( $d, $tp, $room_type, $category, $base_rate );
            }
            $applied = 'tp';
        } elseif ( $sp_on && ! empty( $sp ) && is_array( $sp ) ) {
            $dates = getAllDates( $checkin_date, $checkout_date );
            for ( $d = 0; $d < $days; $d++ ) {
                // A same-day stay has one date; never read past the stay's dates.
                $date = isset( $dates[ $d ] ) ? $dates[ $d ] : end( $dates );
                $rate = $date ? check_seasonal_price_resort( $date, $sp, $room_type, $category ) : 'not_found';
                if ( 'not_found' !== $rate ) {
                    $applied = 'sessional';
                    $daily[] = (float) $rate;
                } else {
                    $daily[] = $base_rate;
                }
            }
        } else {
            $daily = array_fill( 0, $days, $base_rate );
        }

        $total = array_sum( $daily );
        $min   = $daily ? min( $daily ) : 0;
        $max   = $daily ? max( $daily ) : 0;

        return array(
            'base_rate' => $base_rate,
            'total'     => (float) $total,
            'per_day'   => $days > 0 ? (float) $total / $days : 0.0,
            'days'      => $days,
            'varies'    => abs( $max - $min ) > 0.0001,
            'applied'   => $applied,
        );
    }

    /**
     * The Discount Over X Days addon's discount for a resort booking, or [].
     *
     * Taken off the rental itself (rooms + extra services), never off fees or
     * the refundable security deposit, and a fixed amount is per booking — the
     * same base and rule the booking summary shows.
     *
     * @param int   $post_id      Resort item.
     * @param int   $days         Billable days (rbfw_resort_billable_days()).
     * @param float $rental_total Rooms + extra services.
     * @return array rbfw_get_discount_array() result.
     */
    function rbfw_resort_discount( $post_id, $days, $rental_total ) {
        return rbfw_booking_discount( $post_id, $days, $rental_total, 1 );
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