<?php
/**
 * Happy Coders MSG91 Transactional SMS Handler
 */

defined( 'ABSPATH' ) || exit;
use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

/**
 * Sends a transactional SMS using MSG91 Flow API.
 *
 * @param string $mobile Recipient mobile number (with country code, e.g., 91XXXXXXXXXX).
 * @param string $flow_id MSG91 Flow ID (Template ID).
 * @param array  $vars Associative array of variables for the template (e.g., ['VAR1' => 'Value1', 'VAR2' => 'Value2']).
 * @param bool   $force_send If true, bypasses time window check (e.g., for OTPs if you reuse this function). Defaults to false.
 * @return bool|WP_Error True on success, WP_Error on failure.
 */
function hcotp_send_transactional_sms( $mobile, $flow_id, $vars = array(), $force_send = false ) {
	$authkey   = get_option( 'msg91_auth_key' );
	$sender_id = get_option( 'msg91_sender_id' );

	if ( empty( $authkey ) || empty( $sender_id ) || empty( $flow_id ) || empty( $mobile ) ) {
		// error_log('MSG91 SMS: Missing authkey, sender ID, flow ID, or mobile.');
		return new WP_Error( 'config_missing', 'MSG91 SMS configuration or recipient mobile is missing.' );
	}

	// Ensure mobile is in format 91XXXXXXXXXX (without +)
	$mobile_cleaned = str_replace( '+', '', $mobile );
	if ( ! ctype_digit( $mobile_cleaned ) || strlen( $mobile_cleaned ) < 10 ) { // Basic validation
		// error_log('MSG91 SMS: Invalid mobile number format: ' . $mobile);
		return new WP_Error( 'invalid_mobile', 'Invalid mobile number format for MSG91 SMS.' );
	}

	$api_url = 'https://control.msg91.com/api/v5/flow/';

	$payload = array(
		'template_id' => $flow_id,
		'sender'      => $sender_id,
		'short_url'   => '1', // 1 for on, 0 for off - typically '1' for tracking if desired
		'mobiles'     => $mobile_cleaned,
	);

	// Add variables like "VAR1", "VAR2"
	if ( ! empty( $vars ) && is_array( $vars ) ) {
		foreach ( $vars as $key => $value ) {
			// MSG91 expects variables like VAR1, VAR2. Ensure $key matches this.
			// Or if $vars is like ['customer_name' => 'Test'], map it here.
			// For simplicity, this example assumes $vars already contains keys like 'VAR1'.
			// The calling functions will need to prepare $vars correctly.
			$payload[ $key ] = $value;
		}
	}

	$args = array(
		'method'  => 'POST',
		'headers' => array(
			'authkey'      => $authkey,
			'Content-Type' => 'application/json',
		),
		'body'    => json_encode( $payload ),
		'timeout' => 15, // seconds
	);

	$response = wp_remote_post( $api_url, $args );

	if ( is_wp_error( $response ) ) {
		// error_log('MSG91 SMS API Error: ' . $response->get_error_message());
		return $response;
	}

	$body   = wp_remote_retrieve_body( $response );
	$result = json_decode( $body, true );

	if ( isset( $result['type'] ) && $result['type'] === 'success' ) {
		// error_log('MSG91 SMS sent successfully to ' . $mobile_cleaned . ' with Flow ID ' . $flow_id);
		return true;
	} else {
		$error_message = isset( $result['message'] ) ? $result['message'] : 'Unknown error sending MSG91 SMS.';
		// error_log('MSG91 SMS failed: ' . $error_message . ' - Payload: ' . json_encode($payload) . ' - Response: ' . $body);
		return new WP_Error( 'api_error', $error_message );
	}
}

/**
 * Helper function to get customer's phone number.
 * Priority: Order billing phone, User meta 'billing_phone', User login (if it's a phone).
 *
 * @param int|WC_Order $order_or_user_id Order object, Order ID, or User ID.
 * @return string|null Phone number with country code, or null if not found.
 */
function hcotp_get_customer_phone( $order_or_user_id ) {
	$phone = null;

	if ( is_a( $order_or_user_id, 'WC_Order' ) ) {
		$order = $order_or_user_id;
		$phone = $order->get_billing_phone();
	} elseif ( is_numeric( $order_or_user_id ) ) {
		// Could be an order ID or user ID. Try order first if WooCommerce is active.
		if ( class_exists( 'WooCommerce' ) && wc_get_order( $order_or_user_id ) ) {
			$order = wc_get_order( $order_or_user_id );
			$phone = $order->get_billing_phone();
		} else {
			// Assume it's a user ID
			$user_id = $order_or_user_id;
			$phone   = get_user_meta( $user_id, 'billing_phone', true );
			if ( empty( $phone ) ) {
				$user_data = get_userdata( $user_id );
				if ( $user_data && preg_match( '/^\+?[0-9]{10,15}$/', $user_data->user_login ) ) { // Check if user_login looks like a phone
					// Ensure it has country code, the OTP login might store it as username
					// This part might need adjustment based on how OTP login saves numbers
					// For now, assume user_login IS the phone number if it matches pattern and billing_phone is empty
					if ( strpos( $user_data->user_login, '+' ) !== 0 && strlen( $user_data->user_login ) <= 10 ) {
						// If no country code, try to prepend default from plugin settings
						$default_country_code = get_option( 'msg91_default_country', '+91' );
						$phone                = str_replace( '+', '', $default_country_code ) . $user_data->user_login;
					} else {
						$phone = $user_data->user_login;
					}
				}
			}
		}
	}

	// Normalize: remove non-digits except leading +
	if ( $phone ) {
		$phone = preg_replace( '/[^\d+]/', '', $phone );
		// Ensure it starts with country code, not +. MSG91 flow API wants 91XXXXXXXXXX.
		// The hcotp_send_transactional_sms function handles removing '+'.
		// Here, we just ensure it's a plausible phone number.
		// If it doesn't have a +, assume it needs the default country code.
		if ( strpos( $phone, '+' ) !== 0 && strlen( $phone ) <= 10 ) { // Simple check for local number
			$default_country_code = get_option( 'msg91_default_country', '+91' ); // e.g. +91
			$phone                = $default_country_code . $phone;
		}
	}

	return $phone ? $phone : null;
}

/**
 * Registers WooCommerce specific hooks for SMS notifications.
 * This function is called from the main plugin file after 'plugins_loaded'.
 */
function hcotp_register_wc_sms_hooks() {
	// 2. New Order Placed
	// This hook provides 3 arguments: $order_id, $posted_data, $order
	// add_action('woocommerce_checkout_order_processed', 'happycoders_msg91_sms_on_new_order_placed', 10, 3);

	// Or use 'woocommerce_thankyou' which is also common
	add_action( 'woocommerce_thankyou', 'hcotp_sms_on_thankyou_page', 10, 1 );

	// 3. Order Shipped & 4. Order Delivered (via status change)
	add_action( 'woocommerce_order_status_changed', 'hcotp_sms_on_order_status_change', 10, 3 );

	// 5. Order on Cart (Abandoned Cart) - Basic Implementation
	add_action( 'woocommerce_cart_updated', 'hcotp_schedule_abandoned_cart_check' );
	add_action( 'hc_msg91_trigger_abandoned_cart_sms', 'hcotp_send_abandoned_cart_sms', 10, 2 );
	add_action( 'woocommerce_checkout_order_processed', 'happycoders_msg91_clear_abandoned_cart_check_on_order', 10, 1 );
}

// --- HOOKS ---

// 1. New Customer Registration
// add_action( 'user_register', 'hcotp_sms_on_new_customer_registration', 10, 1 );
function hcotp_sms_on_new_customer_registration( $user_id ) {
	error_log( 'hcotp_sms_on_new_customer_registration - Fired. User ID: ' . $user_id );
	if ( ! get_option( 'msg91_sms_ncr_enable', 0 ) ) {
		return;
	}
	$template_id = get_option( 'msg91_sms_ncr_template_id' );
	error_log( 'hcotp_sms_on_new_customer_registration - Template ID: ' . $template_id );
	if ( empty( $template_id ) ) {
		return;
	}

	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return;
	}

	$phone = hcotp_get_customer_phone( $user_id );
	if ( ! $phone ) {
		return;
	}

	$vars = array(
		'var1' => $user->display_name ?: $user->user_login, // Customer Name
		'var2' => get_bloginfo( 'name' ),                   // Site Name
		// 'VAR3' => home_url(),                             // Shop URL
	);
	// Documented: VAR1=CustomerName, VAR2=SiteName, VAR3=ShopURL

	hcotp_send_transactional_sms( $phone, $template_id, $vars );
}

// Callback for New Order Placed
function happycoders_msg91_sms_on_new_order_placed( $order_id, $posted_data, $order ) {
	// Expects 3 args
	error_log( 'happycoders_msg91_sms_on_new_order_placed - Fired. Order ID: ' . $order_id ); // First log
	if ( ! get_option( 'msg91_sms_npo_enable', 0 ) ) {
		error_log( 'happycoders_msg91_sms_on_new_order_placed - NPO SMS not enabled.' );
		return;
	}
	$template_id = get_option( 'msg91_sms_npo_template_id' );
	error_log( 'happycoders_msg91_sms_on_new_order_placed - Template ID: ' . $template_id );
	if ( empty( $template_id ) ) {
		error_log( 'happycoders_msg91_sms_on_new_order_placed - Template ID is empty.' );
		return;
	}
	if ( ! $order ) {
		error_log( 'happycoders_msg91_sms_on_new_order_placed - Order object is invalid/null.' );
		// Attempt to get order object if not passed correctly, though it should be.
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			error_log( 'happycoders_msg91_sms_on_new_order_placed - Could not retrieve order object with wc_get_order.' );
			return;
		}
	}

	$phone = hcotp_get_customer_phone( $order );
	error_log( 'happycoders_msg91_sms_on_new_order_placed - Phone: ' . $phone );
	if ( ! $phone ) {
		error_log( 'happycoders_msg91_sms_on_new_order_placed - Phone number not found.' );
		return;
	}

	$customer_name = $order->get_billing_first_name() ?: $order->get_billing_last_name();
	if ( ! $customer_name && $order->get_customer_id() ) {
		$user          = get_userdata( $order->get_customer_id() );
		$customer_name = $user ? ( $user->display_name ?: $user->user_login ) : 'Valued Customer';
	} elseif ( ! $customer_name ) {
		$customer_name = 'Valued Customer';
	}
	error_log( 'happycoders_msg91_sms_on_new_order_placed - Customer Name: ' . $customer_name );

	$vars = array(
		'var1' => $customer_name,
		'var2' => $order->get_order_number(),
		// 'VAR3' => $order->get_formatted_order_total(),
		// 'VAR4' => get_bloginfo('name'),
		// 'VAR5' => home_url(),
	);
	error_log( 'happycoders_msg91_sms_on_new_order_placed - Variables for SMS: ' . print_r( $vars, true ) );

	$result = hcotp_send_transactional_sms( $phone, $template_id, $vars );
	if ( is_wp_error( $result ) ) {
		error_log( 'happycoders_msg91_sms_on_new_order_placed - SMS sending failed: ' . $result->get_error_message() );
	} else {
		error_log( 'happycoders_msg91_sms_on_new_order_placed - SMS send attempt successful.' );
	}
}

function hcotp_sms_on_thankyou_page( $order_id ) {
	// Expects 1 arg
	error_log( 'hcotp_sms_on_thankyou_page - Fired. Order ID: ' . $order_id );
	if ( ! get_option( 'msg91_sms_npo_enable', 0 ) ) {
		error_log( 'hcotp_sms_on_thankyou_page - NPO SMS not enabled.' );
		return;
	}
	$template_id = get_option( 'msg91_sms_npo_template_id' );
	error_log( 'hcotp_sms_on_thankyou_page - Template ID: ' . $template_id );
	if ( empty( $template_id ) ) {
		error_log( 'hcotp_sms_on_thankyou_page - Template ID is empty.' );
		return;
	}
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		error_log( 'hcotp_sms_on_thankyou_page - Could not get order object.' );
		return;
	}

	$phone = hcotp_get_customer_phone( $order );
	error_log( 'hcotp_sms_on_thankyou_page - Phone: ' . $phone );
	if ( ! $phone ) {
		error_log( 'hcotp_sms_on_thankyou_page - Phone number not found.' );
		return;
	}

	$customer_name = $order->get_billing_first_name() ?: $order->get_billing_last_name();
	if ( ! $customer_name && $order->get_customer_id() ) {
		$user          = get_userdata( $order->get_customer_id() );
		$customer_name = $user ? ( $user->display_name ?: $user->user_login ) : 'Valued Customer';
	} elseif ( ! $customer_name ) {
		$customer_name = 'Valued Customer';
	}
	error_log( 'hcotp_sms_on_thankyou_page - Customer Name: ' . $customer_name );

	$vars = array(
		'var1' => $customer_name,
		'var2' => $order->get_order_number(),
		// 'VAR3' => $order->get_formatted_order_total(),
		// 'VAR4' => get_bloginfo('name'),
		// 'VAR5' => home_url(),
	);
	error_log( 'hcotp_sms_on_thankyou_page - Variables for SMS: ' . print_r( $vars, true ) );

	$result = hcotp_send_transactional_sms( $phone, $template_id, $vars );
	if ( is_wp_error( $result ) ) {
		error_log( 'hcotp_sms_on_thankyou_page - SMS sending failed: ' . $result->get_error_message() );
	} else {
		error_log( 'hcotp_sms_on_thankyou_page - SMS send attempt successful.' );
	}
}

function hcotp_sms_on_order_status_change( $order_id, $old_status, $new_status ) {
	error_log( 'hcotp_sms_on_order_status_change - Fired. Order ID: ' . $order_id );
	$order = wc_get_order( $order_id );

	$phone = hcotp_get_customer_phone( $order );
	if ( ! $phone ) {
		return;
	}

	$customer_name = $order->get_billing_first_name() ?: $order->get_billing_last_name();
	if ( ! $customer_name && $order->get_customer_id() ) {
		$user          = get_userdata( $order->get_customer_id() );
		$customer_name = $user ? $user->display_name : 'Valued Customer';
	} elseif ( ! $customer_name ) {
		$customer_name = 'Valued Customer';
	}
	$site_url = get_site_url();
	// Order Shipped
	$shipped_enabled       = get_option( 'msg91_sms_osh_enable', 0 );
	$shipped_template_id   = get_option( 'msg91_sms_osh_template_id' );
	$shipped_target_status = get_option( 'msg91_sms_osh_status_slug', 'shipped' ); // Default 'shipped'

	if ( $shipped_enabled && ! empty( $shipped_template_id ) && $new_status === $shipped_target_status ) {
		error_log( 'hcotp_sms_on_order_status_change - Order Shipped SMS enabled. Template ID: ' . $shipped_template_id );

		$tracking_id       = get_post_meta( $order_id, '_hc_msg91_tracking_id', true );
		$tracking_url      = get_post_meta( $order_id, '_hc_msg91_tracking_url', true );
		$shipping_provider = get_post_meta( $order_id, '_hc_msg91_shipping_provider', true );
		// If using another plugin, the meta key for tracking number might be different.

		$vars = array(
			'var1' => $tracking_id,             // Customer Name
			'var2' => $tracking_url ?: $site_url,
			// 'VAR3' => $tracking_id ?: 'N/A',
			// 'VAR4' => $shipping_provider ?: 'your courier',
			// 'VAR5' => $tracking_url ?: 'N/A', // This is the tracking URL or ID
			// 'VAR6' => get_bloginfo('name'),
		);
		// Documented: VAR1=CustomerName, VAR2=OrderID, VAR3=TrackingNumber, VAR4=SiteName, VAR5=ShopURL
		hcotp_send_transactional_sms( $phone, $shipped_template_id, $vars );
	}

	// Order Delivered
	$delivered_enabled       = get_option( 'msg91_sms_odl_enable', 0 );
	$delivered_template_id   = get_option( 'msg91_sms_odl_template_id' );
	$delivered_target_status = get_option( 'msg91_sms_odl_status_slug', 'delivered' ); // Default 'delivered'

	if ( $delivered_enabled && ! empty( $delivered_template_id ) && $new_status === $delivered_target_status ) {
		error_log( 'hcotp_sms_on_order_status_change - Order Delivered SMS enabled. Template ID: ' . $shipped_template_id );
		$vars = array(
			'var1' => $customer_name,
			'var2' => $order->get_order_number(),  // Order ID
			// 'VAR3' => get_bloginfo('name'),       // Site Name
		);
		// Documented: VAR1=CustomerName, VAR2=OrderID, VAR3=SiteName
		hcotp_send_transactional_sms( $phone, $delivered_template_id, $vars );
	}
}


function hcotp_schedule_abandoned_cart_check() {
	error_log( 'hcotp_schedule_abandoned_cart_check - Fired.' );
	if ( is_admin() || ! get_option( 'msg91_sms_oac_enable', 0 ) ) {
		return;
	}

	if ( WC()->cart->is_empty() ) {
		error_log( 'hcotp_schedule_abandoned_cart_check - Cart is now empty.' );
			$user_id = get_current_user_id();
		if ( $user_id ) {
			error_log( "HC MSG91 Schedule: Cart is empty for user $user_id. Attempting to clear scheduled tasks." );
			$cron_array = _get_cron_array();
			if ( ! empty( $cron_array ) ) {
				foreach ( $cron_array as $timestamp => $cron ) {
					if ( isset( $cron['hc_msg91_trigger_abandoned_cart_sms'] ) ) {
						foreach ( $cron['hc_msg91_trigger_abandoned_cart_sms'] as $hook_instance_hash => $details ) {
							if ( isset( $details['args'] ) && ! empty( $details['args'] ) && $details['args'][0] == $user_id ) {
								wp_unschedule_event( $timestamp, 'hc_msg91_trigger_abandoned_cart_sms', $details['args'] );
								error_log( "HC MSG91 Schedule: Cleared task for user $user_id at timestamp $timestamp with args: " . print_r( $details['args'], true ) );
							}
						}
					}
				}
			}
		}
		return;
	}

	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return; // Only for logged-in users for simplicity
	}

	$phone = hcotp_get_customer_phone( $user_id );
	if ( ! $phone ) {
		return;
	}

	$delay_hours = (float) get_option( 'msg91_sms_oac_delay_hours', 1 );
	error_log( 'hcotp_schedule_abandoned_cart_check - Delay hours: ' . $delay_hours );
	if ( $delay_hours <= 0 ) {
		$delay_hours = 1;
	}

	// Use a hash of cart contents to avoid sending if cart changes slightly
	// This is very basic. A proper solution tracks cart items and quantities.
	$cart_contents = WC()->cart->get_cart();
	$cart_hash     = md5( json_encode( $cart_contents ) );

	// Clear previous schedule for this user to avoid multiple SMS for same abandonment period
	wp_clear_scheduled_hook( 'hc_msg91_trigger_abandoned_cart_sms', array( $user_id, $cart_hash ) ); // Old hash might be different
	$existing_tasks = _get_cron_array();
	if ( ! empty( $existing_tasks ) ) {
		foreach ( $existing_tasks as $time => $cron ) {
			if ( isset( $cron['hc_msg91_trigger_abandoned_cart_sms'] ) ) {
				foreach ( $cron['hc_msg91_trigger_abandoned_cart_sms'] as $hash => $details ) {
					if ( isset( $details['args'][0] ) && $details['args'][0] == $user_id ) {
						error_log( 'hcotp_schedule_abandoned_cart_check - Clearing previous schedule for User ID: ' . $user_id );
						wp_unschedule_event( $time, 'hc_msg91_trigger_abandoned_cart_sms', $details['args'] );
					}
				}
			}
		}
	}

	if ( ! wp_next_scheduled( 'hc_msg91_trigger_abandoned_cart_sms', array( $user_id, $cart_hash ) ) ) {
		wp_schedule_single_event( time() + ( $delay_hours * HOUR_IN_SECONDS ), 'hc_msg91_trigger_abandoned_cart_sms', array( $user_id, $cart_hash ) );
	}
}

function hcotp_send_abandoned_cart_sms( $user_id, $scheduled_cart_hash ) {
	error_log( "HC MSG91 Abandoned Cart SMS: Fired for User ID: $user_id, Scheduled Cart Hash: $scheduled_cart_hash" );

	// --- Ensure WooCommerce is loaded ---
	if ( ! function_exists( 'WC' ) || ! is_object( WC() ) ) {
		error_log( 'HC MSG91 Abandoned Cart SMS: WooCommerce (WC()) is not available. Attempting to load.' );
		if ( defined( 'WP_PLUGIN_DIR' ) && file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ) {
			include_once WP_PLUGIN_DIR . '/woocommerce/woocommerce.php';
			error_log( 'HC MSG91 Abandoned Cart SMS: Included woocommerce.php manually.' );
			// After including, WC() should be available.
			if ( ! function_exists( 'WC' ) || ! is_object( WC() ) ) {
				error_log( 'HC MSG91 Abandoned Cart SMS: Failed to load WC() even after manual include.' );
				return; // Critical failure
			}
		} else {
			error_log( 'HC MSG91 Abandoned Cart SMS: woocommerce.php not found at expected path.' );
			return; // Critical failure
		}
	}
	// --- End Ensure WooCommerce is loaded ---

	if ( ! get_option( 'msg91_sms_oac_enable', 0 ) ) {
		error_log( 'HC MSG91 Abandoned Cart SMS: SMS not enabled.' );
		return;
	}
	$template_id = get_option( 'msg91_sms_oac_template_id' );
	if ( empty( $template_id ) ) {
		error_log( 'HC MSG91 Abandoned Cart SMS: Template ID is empty.' );
		return;
	}

	$user = get_userdata( $user_id );
	if ( ! $user ) {
		error_log( "HC MSG91 Abandoned Cart SMS: User not found for ID: $user_id." );
		return;
	}

	// --- Robust WooCommerce Initialization for Cron ---
	// Try to ensure frontend includes are loaded if not already
	if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
		error_log( 'HC MSG91 Abandoned Cart SMS: DOING_CRON is true. Ensuring frontend includes if method exists.' );
		if ( method_exists( WC(), 'frontend_includes' ) ) { // Check if the method exists on the WC object
			WC()->frontend_includes();
			error_log( 'HC MSG91 Abandoned Cart SMS: WC()->frontend_includes() called.' );

			// After frontend_includes, we often need to manually instantiate/init session and customer for the target user
			if ( class_exists( 'WC_Session_Handler' ) && ( is_null( WC()->session ) || ! WC()->session instanceof WC_Session_Handler ) ) {
				WC()->session = new WC_Session_Handler();
				WC()->session->init();
				error_log( 'HC MSG91 Abandoned Cart SMS: WC_Session_Handler re-initialized after frontend_includes.' );
			}

			if ( class_exists( 'WC_Customer' ) && ( is_null( WC()->customer ) || ( WC()->customer instanceof WC_Customer && WC()->customer->get_id() != $user_id ) ) ) {
				WC()->customer = new WC_Customer( $user_id, true ); // true to force loading for specific user
				error_log( "HC MSG91 Abandoned Cart SMS: WC_Customer re-initialized for user $user_id after frontend_includes." );
			}

			// Cart initialization needs to happen after customer and session are set up for the user
			if ( class_exists( 'WC_Cart' ) && ( is_null( WC()->cart ) || ! WC()->cart instanceof WC_Cart ) ) {
				WC()->cart = new WC_Cart();
				error_log( 'HC MSG91 Abandoned Cart SMS: WC_Cart re-initialized after frontend_includes.' );
			}
			// The cart loading logic below will handle populating it.
		} else {
			error_log( 'HC MSG91 Abandoned Cart SMS: WC()->frontend_includes() method not found, attempting manual setup.' );
			// Fallback: if frontend_includes isn't there or didn't work, try manual instantiation of necessary objects
			if ( is_null( WC()->session ) || ! WC()->session instanceof WC_Session_Handler ) {
				if ( class_exists( 'WC_Session_Handler' ) ) {
					WC()->session = new WC_Session_Handler();
					WC()->session->init();
					error_log( 'HC MSG91 Abandoned Cart SMS: Manual WC_Session_Handler initialized.' );
				}
			}
			if ( is_null( WC()->customer ) || ( WC()->customer instanceof WC_Customer && WC()->customer->get_id() != $user_id ) ) {
				if ( class_exists( 'WC_Customer' ) ) {
					WC()->customer = new WC_Customer( $user_id, true );
					error_log( "HC MSG91 Abandoned Cart SMS: Manual WC_Customer initialized for user $user_id." );
				}
			}
			if ( is_null( WC()->cart ) || ! WC()->cart instanceof WC_Cart ) {
				if ( class_exists( 'WC_Cart' ) ) {
					WC()->cart = new WC_Cart();
					error_log( 'HC MSG91 Abandoned Cart SMS: Manual WC_Cart initialized.' );
				}
			}
		}
	}

	if ( ! isset( WC()->session ) || ! WC()->session instanceof WC_Session_Handler ) {
		error_log( 'HC MSG91 Abandoned Cart SMS: WC()->session is not a valid WC_Session_Handler object after attempts.' );
		// Fallback instantiation if frontend_includes didn't set it up as expected
		if ( class_exists( 'WC_Session_Handler' ) ) {
			WC()->session = new WC_Session_Handler();
			WC()->session->init();
			error_log( 'HC MSG91 Abandoned Cart SMS: Fallback WC_Session initialized.' );
		} else {
			error_log( 'HC MSG91 Abandoned Cart SMS: WC_Session_Handler class STILL not found.' );
			return;
		}
	}

	// Ensure customer ID is set in the session for cart loading
	if ( WC()->session && is_callable( array( WC()->session, 'get_customer_id' ) ) && WC()->session->get_customer_id() != $user_id ) {
		WC()->session->set_customer_session_cookie( true );
		// WC()->session->set('customer_id', $user_id); // This might not be enough, WC_Customer handles this better
		// Instead of directly setting session customer_id, let's ensure WC_Customer is for our user.
		if ( is_null( WC()->customer ) || WC()->customer->get_id() != $user_id ) {
			WC()->customer = new WC_Customer( $user_id, true ); // Force load the correct customer
			error_log( "HC MSG91 Abandoned Cart SMS: WC_Customer object explicitly set/loaded for user_id $user_id." );
		}
		// WooCommerce's cart loading mechanism should then use WC()->customer->get_id()
	}

	if ( ! isset( WC()->cart ) || ! WC()->cart instanceof WC_Cart ) {
		error_log( 'HC MSG91 Abandoned Cart SMS: WC()->cart is not a valid WC_Cart object after attempts.' );
		if ( class_exists( 'WC_Cart' ) ) {
			WC()->cart = new WC_Cart();
			error_log( 'HC MSG91 Abandoned Cart SMS: Fallback WC_Cart instantiated.' );
		} else {
			error_log( 'HC MSG91 Abandoned Cart SMS: WC_Cart class STILL not found.' );
			return;
		}
	}

	// Load cart for the specific user
	// WC_Cart's get_cart_from_session usually relies on cookies or session data,
	// which might not be set for the cron user.
	// We need to ensure the cart is loaded for the $user_id.
	// One way is to fill the cart object after ensuring WC()->customer is set correctly for $user_id
	if ( WC()->cart && WC()->customer && WC()->customer->get_id() == $user_id ) {
		// If WC_Cart's get_cart_from_session relies on a session ID that's not available in cron,
		// we might need a more direct way to load a user's persisted cart.
		// WooCommerce stores persistent carts in user meta 'woocommerce_cart'.
		$persistent_cart = get_user_meta( $user_id, '_woocommerce_persistent_cart_' . get_current_blog_id(), true );
		if ( ! empty( $persistent_cart ) && isset( $persistent_cart['cart'] ) ) {
			// Clear any existing cart items in the WC()->cart object
			if ( method_exists( WC()->cart, 'empty_cart' ) ) {
				WC()->cart->empty_cart( false ); // false to not trigger actions
			}
			// Populate WC()->cart with items from persistent storage
			foreach ( $persistent_cart['cart'] as $key => $item ) {
				WC()->cart->add_to_cart(
					$item['product_id'],
					$item['quantity'],
					isset( $item['variation_id'] ) ? $item['variation_id'] : 0,
					isset( $item['variation'] ) ? $item['variation'] : array(),
					$item // Pass the whole item along for other data
				);
			}
			WC()->cart->set_session(); // This might try to save to session, which is fine.
			error_log( "HC MSG91 Abandoned Cart SMS: Cart loaded from persistent user meta for User ID: $user_id. Items: " . WC()->cart->get_cart_contents_count() );
		} else {
			error_log( "HC MSG91 Abandoned Cart SMS: No persistent cart found in user meta for User ID: $user_id. Trying get_cart_from_session as fallback." );
			// Fallback to the session-based loading if persistent cart is empty
			if ( method_exists( WC()->cart, 'get_cart_from_session' ) ) {
				WC()->cart->get_cart_from_session();
				error_log( 'HC MSG91 Abandoned Cart SMS: Fallback get_cart_from_session called. Items: ' . WC()->cart->get_cart_contents_count() );
			}
		}
	} else {
		error_log( "HC MSG91 Abandoned Cart SMS: WC()->cart or WC()->customer not correctly set up for user $user_id before loading cart items." );
		return;
	}
	// --- End Robust WooCommerce Initialization ---

	// ... (rest of your function: check if cart is empty, check recent orders, get phone, send SMS) ...
	// The checks for WC()->cart->is_empty(), get_cart_contents_count(), get_cart_total() should now work.

	if ( WC()->cart && WC()->cart->is_empty() ) {
		error_log( "HC MSG91 Abandoned Cart SMS: Cart is empty for User ID: $user_id after loading attempts." );
		return;
	}
	error_log( "HC MSG91 Abandoned Cart SMS: Cart is NOT empty for User ID: $user_id. Items: " . ( WC()->cart ? WC()->cart->get_cart_contents_count() : 'N/A' ) );

	// Check current cart hash against scheduled hash
	// $current_cart_contents = WC()->cart->get_cart();
	// $current_cart_hash = md5(json_encode($current_cart_contents));
	// if ($current_cart_hash !== $scheduled_cart_hash) return; // Cart changed significantly

	// Check if user has placed an order since scheduling
	$delay_hours = (float) get_option( 'msg91_sms_oac_delay_hours', 1 );
	$args        = array(
		'customer_id'  => $user_id,
		'date_created' => '>' . ( time() - ( $delay_hours * HOUR_IN_SECONDS ) - ( 5 * MINUTE_IN_SECONDS ) ), // check orders in last X hours + 5 mins buffer
		'status'       => array_keys( wc_get_order_statuses() ), // Any status
	);
	$orders      = wc_get_orders( $args );
	if ( ! empty( $orders ) ) {
		return; // User placed an order
	}

	$phone = hcotp_get_customer_phone( $user_id );
	if ( ! $phone ) {
		return;
	}

	$customer_name    = $user->display_name ?: $user->user_login;
	$cart_items_count = WC()->cart->get_cart_contents_count();
	$cart_total       = WC()->cart->get_cart_total();

	$vars = array(
		'var1' => $customer_name,        // Customer Name
		'var2' => $cart_items_count,     // Cart Items Count
		// 'VAR3' => $cart_total,           // Cart Total
		// 'VAR4' => get_bloginfo('name'),   // Site Name
		// 'VAR5' => wc_get_cart_url(),      // Cart URL
	);
	// Documented: VAR1=CustomerName, VAR2=CartItemsCount, VAR3=CartTotal, VAR4=SiteName, VAR5=CartURL

	hcotp_send_transactional_sms( $phone, $template_id, $vars );
}

function happycoders_msg91_clear_abandoned_cart_check_on_order( $order_id ) {
	error_log( 'happycoders_msg91_clear_abandoned_cart_check_on_order - Fired. Order ID: ' . $order_id );
	$order = wc_get_order( $order_id );
	if ( $order && $order->get_customer_id() ) {
			$user_id = $order->get_customer_id();
		// We don't have the cart hash here, so we clear any task for this user.
		$timestamp = wp_next_scheduled( 'hc_msg91_trigger_abandoned_cart_sms', array( $user_id, null ) ); // This won't work directly
		// Need to iterate cron array or store the specific args used for scheduling.
		// Simplified: Clear all for this user.
		$existing_tasks = _get_cron_array();
		if ( ! empty( $existing_tasks ) ) {
			foreach ( $existing_tasks as $time => $cron ) {
				if ( isset( $cron['hc_msg91_trigger_abandoned_cart_sms'] ) ) {
					foreach ( $cron['hc_msg91_trigger_abandoned_cart_sms'] as $hash => $details ) {
						if ( isset( $details['args'][0] ) && $details['args'][0] == $user_id ) {
							wp_unschedule_event( $time, 'hc_msg91_trigger_abandoned_cart_sms', $details['args'] );
						}
					}
				}
			}
		}
	}
}


// Add a custom meta box to the order edit page
add_action( 'add_meta_boxes', 'happycoders_msg91_add_shipment_details_meta_box' );
function happycoders_msg91_add_shipment_details_meta_box() {
	// error_log('HC MSG91: happycoders_msg91_add_shipment_details_meta_box function CALLED');
	$screen = class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) && wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
		? wc_get_page_screen_id( 'shop-order' )
		: 'shop_order';

	$shipped_enabled = get_option( 'msg91_sms_osh_enable', 0 );
	if ( $shipped_enabled ) {
		add_meta_box(
			'hc_msg91_shipment_details',
			__( 'Shipment Tracking Details (MSG91)', 'happy-coders-otp-login' ),
			'happycoders_msg91_shipment_details_meta_box_html',
			$screen, // Post type for WooCommerce orders
			'side',       // Context (normal, side, advanced)
			'default'     // Priority
		);
	}
}

// HTML for the meta box
function happycoders_msg91_shipment_details_meta_box_html( $object ) {
	$order    = is_a( $object, 'WP_Post' ) ? wc_get_order( $object->ID ) : $object;
	$order_id = 0;
	if ( $order instanceof WP_Post ) {
		$order_id = $order->ID;
	} elseif ( $order instanceof WC_Order ) {
		$order_id = $order->get_id();
	} else {
		// Fallback or error if type is unexpected
		// For now, let's assume it's one of the above. If you still get errors, log the type:
		// error_log('HC MSG91 Meta Box HTML: Unexpected object type: ' . get_class($post_or_order_object));
		return; // Can't proceed without an order ID
	}

	if ( ! $order_id ) {
		echo '<p>' . esc_html__( 'Could not determine order ID.', 'happy-coders-otp-login' ) . '</p>';
		return;
	}

	wp_nonce_field( 'hc_msg91_save_shipment_details', 'hc_msg91_shipment_nonce' );

	$tracking_id       = get_post_meta( $order_id, '_hc_msg91_tracking_id', true );
	$tracking_url      = get_post_meta( $order_id, '_hc_msg91_tracking_url', true );
	$shipping_provider = get_post_meta( $order_id, '_hc_msg91_shipping_provider', true );
	?>
	<p>
		<label for="hc_msg91_tracking_id"><?php esc_html_e( 'Tracking ID:', 'happy-coders-otp-login' ); ?></label><br>
		<input type="text" id="hc_msg91_tracking_id" name="hc_msg91_tracking_id" value="<?php echo esc_attr( $tracking_id ); ?>" style="width:100%;">
	</p>
	<p>
		<label for="hc_msg91_tracking_url"><?php esc_html_e( 'Tracking URL (optional):', 'happy-coders-otp-login' ); ?></label><br>
		<input type="url" id="hc_msg91_tracking_url" name="hc_msg91_tracking_url" value="<?php echo esc_attr( $tracking_url ); ?>" style="width:100%;">
	</p>
	<p>
		<label for="hc_msg91_shipping_provider"><?php esc_html_e( 'Shipping Provider (optional):', 'happy-coders-otp-login' ); ?></label><br>
		<input type="text" id="hc_msg91_shipping_provider" name="hc_msg91_shipping_provider" value="<?php echo esc_attr( $shipping_provider ); ?>" style="width:100%;">
	</p>
	<p class="description">
		<?php esc_html_e( 'Enter tracking details for SMS notifications.', 'happy-coders-otp-login' ); ?>
	</p>
	<?php
}

// Save the custom meta fields
add_action( 'woocommerce_process_shop_order_meta', 'happycoders_msg91_save_shipment_details_meta', 10, 1 );
function happycoders_msg91_save_shipment_details_meta( $order_id ) {
	if (
	! isset( $_POST['hc_msg91_shipment_nonce'] ) ||
	! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hc_msg91_shipment_nonce'] ) ), 'hc_msg91_save_shipment_details' )
	) {
		error_log( 'HC MSG91: happycoders_msg91_save_shipment_details_meta nonce failed' );
		return $order_id;
	}

	// Check if the current user has permission to save the data.
	if ( ! current_user_can( 'edit_post', $order_id ) ) {
		return $order_id;
	}

	// Check if it's an autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $order_id;
	}

	if ( isset( $_POST['hc_msg91_tracking_id'] ) ) {
		update_post_meta( $order_id, '_hc_msg91_tracking_id', sanitize_text_field( $_POST['hc_msg91_tracking_id'] ) );
	}
	if ( isset( $_POST['hc_msg91_tracking_url'] ) ) {
		update_post_meta( $order_id, '_hc_msg91_tracking_url', esc_url_raw( $_POST['hc_msg91_tracking_url'] ) );
	}
	if ( isset( $_POST['hc_msg91_shipping_provider'] ) ) {
		update_post_meta( $order_id, '_hc_msg91_shipping_provider', sanitize_text_field( $_POST['hc_msg91_shipping_provider'] ) );
	}
}


?>
