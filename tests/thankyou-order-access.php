<?php
/**
 * Run on a development site: wp eval-file wp-content/plugins/booking-and-rental-manager-for-woocommerce/tests/thankyou-order-access.php
 *
 * The [rbfw_thankyou] page rendered any rbfw_order for any logged-in user: rbfw_token is
 * never written for WooCommerce orders, and a token that sanitizes to '' (token[]=x, %20,
 * <x>) loosely matched the empty stored value. The paymentId branch had no ownership check
 * and wrote payment/order status from the URL. Order records were also front-end searchable.
 */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    exit;
}
require_once ABSPATH . 'wp-admin/includes/user.php';

$checks = 0;
$assert = static function ( $condition, $message ) use ( &$checks ) {
    if ( ! $condition ) {
        throw new RuntimeException( $message );
    }
    ++$checks;
    WP_CLI::log( 'PASS ' . $message );
};

$saved_rbfw      = $GLOBALS['rbfw'] ?? null;
$GLOBALS['rbfw'] = $saved_rbfw ?: ( new ReflectionClass( 'MageRBFWClass' ) )->newInstanceWithoutConstructor();

$posts  = array();
$users  = array();
$orders = array();

// Read-only override of the login gate (nothing is saved).
$account    = 'on';
$pay_filter = static function ( $value ) use ( &$account ) {
    $value                              = is_array( $value ) ? $value : array();
    $value['rbfw_mps_checkout_account'] = $account;
    return $value;
};
add_filter( 'option_rbfw_basic_payment_settings', $pay_filter );
add_filter( 'default_option_rbfw_basic_payment_settings', $pay_filter );

$user = static function ( $role ) use ( &$users ) {
    $id      = wp_insert_user( array(
        'user_login' => 'rbfw_ty_' . $role . '_' . wp_generate_password( 6, false ),
        'user_pass'  => wp_generate_password(),
        'user_email' => 'rbfw_ty_' . $role . '_' . wp_generate_password( 6, false ) . '@example.test',
        'role'       => $role,
    ) );
    $users[] = $id;
    return $id;
};

// rbfw_get_option() returns null under WP-CLI, so the shortcode's "is this the Thank You
// page" check compares the queried object id with null: render with none (id 0) to pass it.
$render = static function ( $viewer, $get ) {
    wp_set_current_user( $viewer );
    $_GET                    = $get;
    $GLOBALS['wp_query']     = new WP_Query();
    $GLOBALS['wp_the_query'] = $GLOBALS['wp_query'];
    $html                    = (string) do_shortcode( '[rbfw_thankyou]' );
    $_GET                    = array();
    wp_set_current_user( 0 );
    return $html;
};

try {
    $owner    = $user( 'customer' );
    $attacker = $user( 'subscriber' );
    $contrib  = $user( 'contributor' );
    $admin    = $user( 'administrator' );

    $item    = wp_insert_post( array( 'post_type' => 'rbfw_item', 'post_status' => 'publish', 'post_title' => 'Thank-you test item' ) );
    $posts[] = $item;
    update_post_meta( $item, 'rbfw_item_type', 'bike_car_sd' );

    $make_order = static function ( $name, $meta ) use ( &$posts, $item ) {
        $id      = wp_insert_post( array( 'post_type' => 'rbfw_order', 'post_status' => 'publish', 'post_title' => '#9' . wp_rand( 100, 999 ) . ' ' . $name ) );
        $posts[] = $id;
        $meta   += array(
            'rbfw_billing_name'   => $name,
            'rbfw_billing_email'  => strtolower( str_replace( ' ', '.', $name ) ) . '@victim.example',
            'rbfw_payment_method' => 'Offline Payment',
            'rbfw_ticket_info'    => array( array(
                'ticket_name'         => 'Vintage Evening Gown',
                'rbfw_id'             => $item,
                'rbfw_rent_type'      => 'bike_car_sd',
                'rbfw_start_datetime' => '2026-11-02 10:00',
                'rbfw_end_datetime'   => '2026-11-05 18:00',
                'duration_cost'       => 240,
                'service_cost'        => 0,
                'ticket_price'        => 240,
            ) ),
        );
        foreach ( $meta as $key => $value ) {
            update_post_meta( $id, $key, $value );
        }
        return $id;
    };

    // A: a WooCommerce booking exactly as the plugin writes it today (no rbfw_token).
    $wc_order = wc_create_order( array( 'customer_id' => $owner ) );
    $orders[] = $wc_order;
    $woo      = $make_order( 'Alicia Woo', array( 'rbfw_order_id' => $wc_order->get_id(), 'rbfw_user_id' => '', 'rbfw_reference' => '' ) );
    // B: a Mage Payment System order placed from the owner's account (token + gateway reference).
    $legacy_token = bin2hex( random_bytes( 16 ) );
    $legacy       = $make_order( 'Bruno Legacy', array( 'rbfw_user_id' => $owner, 'rbfw_token' => $legacy_token, 'rbfw_reference' => 'PAYID-RBFWTEST-B', 'rbfw_payment_status' => '', 'rbfw_order_status' => 'pending' ) );
    // C: a Mage Payment System guest order.
    $guest_token = bin2hex( random_bytes( 16 ) );
    $guest       = $make_order( 'Carla Guest', array( 'rbfw_user_id' => 0, 'rbfw_token' => $guest_token ) );
    // A post of another type that happens to carry the same meta keys.
    $other_token = bin2hex( random_bytes( 16 ) );
    $other       = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'Not an order' ) );
    $posts[]     = $other;
    update_post_meta( $other, 'rbfw_token', $other_token );
    update_post_meta( $other, 'rbfw_billing_email', 'dora.other@victim.example' );
    update_post_meta( $other, 'rbfw_ticket_info', get_post_meta( $woo, 'rbfw_ticket_info', true ) );

    $sees = static function ( $html, $email ) {
        return false !== strpos( $html, $email );
    };

    // 1. Offline link against a WooCommerce order: there is no token, so nothing may open it.
    $bypass = array( 'array' => array( 'x' ), 'space' => ' ', 'tab' => "\t", 'newline' => "\n", 'nul' => "\0", 'tag' => '<x>', 'wrong' => 'deadbeef' );
    foreach ( $bypass as $label => $token ) {
        $html = $render( $attacker, array( 'order_id' => (string) $woo, 'token' => $token ) );
        $assert( ! $sees( $html, 'alicia.woo@' ), "subscriber + token ($label) cannot read a WooCommerce booking" );
    }
    $assert( ! $sees( $render( $contrib, array( 'order_id' => (string) $woo, 'token' => array( 'x' ) ) ), 'alicia.woo@' ), 'contributor + token[] cannot read a WooCommerce booking' );
    $assert( ! $sees( $render( $owner, array( 'order_id' => (string) $woo, 'token' => array( 'x' ) ) ), 'alicia.woo@' ), 'owner + token[] gets nothing: tokenless orders fail closed' );
    $assert( ! $sees( $render( $admin, array( 'order_id' => (string) $woo, 'token' => array( 'x' ) ) ), 'alicia.woo@' ), 'admin + token[] gets nothing either' );
    $assert( ! $sees( $render( $attacker, array( 'order_id' => array( (string) $woo ), 'token' => 'x' ) ), 'alicia.woo@' ), 'order_id[] is rejected' );
    $assert( '' === trim( $render( $attacker, array( 'order_id' => '999999999', 'token' => array( 'x' ) ) ) ), 'nonexistent order renders nothing' );

    // 2. Legacy token links keep working for their owner, and only for them.
    $assert( $sees( $render( $owner, array( 'order_id' => (string) $legacy, 'token' => $legacy_token ) ), 'bruno.legacy@' ), 'owner + valid legacy token still sees their booking' );
    $assert( $sees( $render( $admin, array( 'order_id' => (string) $legacy, 'token' => $legacy_token ) ), 'bruno.legacy@' ), 'admin + valid token sees the booking' );
    $assert( ! $sees( $render( $attacker, array( 'order_id' => (string) $legacy, 'token' => $legacy_token ) ), 'bruno.legacy@' ), 'another account with a leaked token is refused' );
    $assert( ! $sees( $render( $owner, array( 'order_id' => (string) $legacy, 'token' => $legacy_token . 'x' ) ), 'bruno.legacy@' ), 'owner + wrong token is refused' );
    $assert( ! $sees( $render( $owner, array( 'order_id' => (string) $legacy, 'token' => array( $legacy_token ) ) ), 'bruno.legacy@' ), 'owner + token as array is refused' );
    $assert( $sees( $render( $attacker, array( 'order_id' => (string) $guest, 'token' => $guest_token ) ), 'carla.guest@' ), 'guest order opens with its token' );
    $assert( ! $sees( $render( $attacker, array( 'order_id' => (string) $guest, 'token' => ' ' ) ), 'carla.guest@' ), 'guest order refuses a blank token' );
    $assert( ! $sees( $render( $attacker, array( 'order_id' => (string) $other, 'token' => $other_token ) ), 'dora.other@' ), 'a non-order post with a matching token is refused' );

    // 3. Login gate: on by default; off lets a guest use a guest-order token, never an account order's.
    $assert( ! $sees( $render( 0, array( 'order_id' => (string) $guest, 'token' => $guest_token ) ), 'carla.guest@' ), 'logged-out visitor blocked while checkout account is on' );
    $account = 'off';
    $assert( $sees( $render( 0, array( 'order_id' => (string) $guest, 'token' => $guest_token ) ), 'carla.guest@' ), 'logged-out visitor + guest-order token works when checkout account is off' );
    $assert( ! $sees( $render( 0, array( 'order_id' => (string) $legacy, 'token' => $legacy_token ) ), 'bruno.legacy@' ), 'logged-out visitor cannot open an account order even with its token' );
    $assert( ! $sees( $render( 0, array( 'order_id' => (string) $woo, 'token' => array( 'x' ) ) ), 'alicia.woo@' ), 'logged-out visitor + token[] gets nothing' );
    $account = 'on';

    // 4. Payment return branch: ownership checked, and the URL never writes order state.
    $assert( ! $sees( $render( $attacker, array( 'paymentId' => 'PAYID-RBFWTEST-B', 'paymentStatus' => 'succeeded' ) ), 'bruno.legacy@' ), 'paymentId of another account is refused' );
    $assert( ! $sees( $render( $attacker, array( 'paymentId' => ' ' ) ), 'alicia.woo@' ), 'blank paymentId does not match an empty reference' );
    $assert( ! $sees( $render( $attacker, array( 'paymentId' => array( 'x' ) ) ), 'alicia.woo@' ), 'paymentId[] is rejected' );
    $assert( $sees( $render( $owner, array( 'paymentId' => 'PAYID-RBFWTEST-B', 'PayerID' => 'PAYER1', 'paymentStatus' => 'succeeded' ) ), 'bruno.legacy@' ), 'owner still sees the booking on the payment return' );
    $assert( '' === get_post_meta( $legacy, 'rbfw_payment_status', true ), 'paymentStatus from the URL is not saved' );
    $assert( 'pending' === get_post_meta( $legacy, 'rbfw_order_status', true ), 'order status is not forced to processing' );
    $assert( ! metadata_exists( 'post', $legacy, 'rbfw_payer_id' ), 'PayerID from the URL is not saved' );
    $assert( '' === get_post_meta( $woo, 'rbfw_order_status', true ), 'blank paymentId wrote nothing to the WooCommerce booking' );
    // The owner comes from the linked WooCommerce order: rbfw_user_id stays empty under HPOS.
    $wc_order2 = wc_create_order( array( 'customer_id' => $owner ) );
    $orders[]  = $wc_order2;
    $make_order( 'Erin Hpos', array( 'rbfw_order_id' => $wc_order2->get_id(), 'rbfw_user_id' => '', 'rbfw_reference' => 'PAYID-RBFWTEST-E' ) );
    $assert( ! $sees( $render( $attacker, array( 'paymentId' => 'PAYID-RBFWTEST-E' ) ), 'erin.hpos@' ), 'WooCommerce customer owns the booking even with an empty rbfw_user_id' );
    $assert( $sees( $render( $owner, array( 'paymentId' => 'PAYID-RBFWTEST-E' ) ), 'erin.hpos@' ), 'that customer still sees it' );

    // 5. Order records are private: not public, not in front-end search, still in wp-admin.
    foreach ( array( 'rbfw_order', 'rbfw_order_meta' ) as $type ) {
        $object = get_post_type_object( $type );
        $assert( ! $object->public && $object->exclude_from_search && $object->show_ui, "$type is private, unsearchable and keeps its admin screen" );
    }
    $found = wp_list_pluck( ( new WP_Query( array( 's' => 'Legacy', 'posts_per_page' => 50 ) ) )->posts, 'post_type' );
    $assert( ! in_array( 'rbfw_order', $found, true ), 'front-end search does not list booking orders' );

    WP_CLI::success( "$checks checks passed" );
} finally {
    foreach ( $posts as $id ) {
        wp_delete_post( $id, true );
    }
    foreach ( $orders as $order ) {
        $order->delete( true );
    }
    foreach ( $users as $id ) {
        wp_delete_user( $id );
    }
    remove_filter( 'option_rbfw_basic_payment_settings', $pay_filter );
    remove_filter( 'default_option_rbfw_basic_payment_settings', $pay_filter );
    $GLOBALS['rbfw'] = $saved_rbfw;
    $_GET            = array();
}
