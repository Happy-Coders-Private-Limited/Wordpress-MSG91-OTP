<?php
/**
 * Happy Coders MSG91 Transactional SMS Handler.
 *
 * @package happy-coders-otp-login
 */

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

/**
 * Prepares SMS variables for MSG91 and replaces placeholders in the message.
 *
 * @param string $message_template The raw message template with ##placeholders##.
 * @param array  $data             Associative array of standardized variables (e.g., ['customer_name' => 'John Doe']).
 * @return array An array containing 'message' (with placeholders replaced) and 'msg91_vars' (VARx array).
 */
function hcotp_prepare_sms_variables( $message_template, $data ) {
	$msg91_vars        = array();
	$processed_message = $message_template;
	$var_counter       = 1;

	// This is a simple example, you might want a more robust mapping.
	$variable_map = array(
		'customer_name'    => 'customer_name',
		'order_id'         => 'order_id',
		'site_name'        => 'site_name',
		'tracking_id'      => 'tracking_id',
		'tracking_url'     => 'tracking_url',
		'cart_items_count' => 'cart_items_count',
		'cart_total'       => 'cart_total',
		// Add more as needed.
	);

	foreach ( $data as $key => $value ) {
		$processed_message = str_replace( '##' . $key . '##', $value, $processed_message );

		if ( isset( $variable_map[ $key ] ) ) {
			$msg91_vars[ $variable_map[ $key ] ] = $value;
		} else {
			$msg91_vars[ 'VAR' . $var_counter ] = $value;
			++$var_counter;
		}
	}

	// Handle any ##placeholders## that were not replaced (e.g., if data was missing).
	$processed_message = preg_replace( '/##(.*?)##/', '', $processed_message );

	return array(
		'message'    => $processed_message,
		'msg91_vars' => $msg91_vars,
	);
}

/**
 * Sends a transactional SMS using MSG91 Flow API.
 *
 * @param string $mobile Recipient mobile number (with country code, e.g., 91XXXXXXXXXX).
 * @param string $flow_id MSG91 Flow ID (Template ID).
 * @param array  $message_template The raw message template with ##placeholders##.
 * @param array  $data Associative array of standardized variables (e.g., ['customer_name' => 'John Doe']).
 * @return bool|WP_Error True on success, WP_Error on failure.
 */
function hcotp_send_transactional_sms( $mobile, $flow_id, $message_template, $data = array() ) {
	$authkey   = get_option( 'hcotp_msg91_auth_key' );
	$sender_id = get_option( 'hcotp_msg91_sender_id' );

	if ( empty( $authkey ) || empty( $sender_id ) || empty( $flow_id ) || empty( $mobile ) ) {
		return new WP_Error( 'config_missing', 'MSG91 SMS configuration or recipient mobile is missing.' );
	}

	// Ensure mobile is in format 91XXXXXXXXXX (without +).
	$mobile_cleaned = str_replace( '+', '', $mobile );
	if ( ! ctype_digit( $mobile_cleaned ) || strlen( $mobile_cleaned ) < 10 ) {
		return new WP_Error( 'invalid_mobile', 'Invalid mobile number format for MSG91 SMS.' );
	}

	$api_url = 'https://control.msg91.com/api/v5/flow/';

	// Prepare variables using the new helper function.
	$prepared_data = hcotp_prepare_sms_variables( $message_template, $data );
	$msg91_vars    = $prepared_data['msg91_vars'];
	$final_message = $prepared_data['message']; // Not directly used by MSG91 Flow API, but good for logging/future use.

	$payload = array(
		'template_id' => $flow_id,
		'sender'      => $sender_id,
		'short_url'   => '1', // 1 for on, 0 for off - typically '1' for tracking if desired
		'mobiles'     => $mobile_cleaned,
	);

	if ( ! empty( $msg91_vars ) && is_array( $msg91_vars ) ) {
		foreach ( $msg91_vars as $key => $value ) {
			$payload[ $key ] = $value;
		}
	}

	$args = array(
		'method'  => 'POST',
		'headers' => array(
			'authkey'      => $authkey,
			'Content-Type' => 'application/json',
		),
		'body'    => wp_json_encode( $payload ),
		'timeout' => 15, // seconds.
	);

	$response = wp_remote_post( $api_url, $args );

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$body   = wp_remote_retrieve_body( $response );
	$result = json_decode( $body, true );

	if ( isset( $result['type'] ) && 'success' === $result['type'] ) {
		return true;
	} else {
		$error_message = isset( $result['message'] ) ? $result['message'] : 'Unknown error sending MSG91 SMS.';
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
			// Assume it's a user ID.
			$user_id = $order_or_user_id;
			$phone   = get_user_meta( $user_id, 'billing_phone', true );
			if ( empty( $phone ) ) {
				$user_data = get_userdata( $user_id );
				if ( $user_data && preg_match( '/^\+?[0-9]{10,15}$/', $user_data->user_login ) ) { // Check if user_login looks like a phone
					// Ensure it has country code, the OTP login might store it as username
					// This part might need adjustment based on how OTP login saves numbers
					// For now, assume user_login IS the phone number if it matches pattern and billing_phone is empty.
					if ( strpos( $user_data->user_login, '+' ) !== 0 && strlen( $user_data->user_login ) <= 10 ) {
						// If no country code, try to prepend default from plugin settings.
						$default_country_code = get_option( 'hcotp_msg91_default_country', '+91' );
						$phone                = str_replace( '+', '', $default_country_code ) . $user_data->user_login;
					} else {
						$phone = $user_data->user_login;
					}
				}
			}
		}
	}

	// Normalize: remove non-digits except leading +.
	if ( $phone ) {
		$phone = preg_replace( '/[^\d+]/', '', $phone );
		// Ensure it starts with country code, not +. MSG91 flow API wants 91XXXXXXXXXX.
		// The hcotp_send_transactional_sms function handles removing '+'.
		// Here, we just ensure it's a plausible phone number.
		// If it doesn't have a +, assume it needs the default country code.
		if ( strpos( $phone, '+' ) !== 0 && strlen( $phone ) <= 10 ) {
			$default_country_code = get_option( 'hcotp_msg91_default_country', '+91' );
			$phone                = $default_country_code . $phone;
		}
	}

	return $phone ? $phone : null;
}

/**
 * Registers WooCommerce specific hooks for SMS notifications.
 */
function hcotp_register_wc_sms_hooks() {
	// Or use 'woocommerce_thankyou' which is also common.
	add_action( 'woocommerce_thankyou', 'hcotp_sms_on_thankyou_page', 10, 1 );

	// 3. Order Shipped & 4. Order Delivered (via status change).
	add_action( 'woocommerce_order_status_changed', 'hcotp_sms_on_order_status_change', 10, 3 );

	// 5. Order on Cart (Abandoned Cart) - Basic Implementation.
	add_action( 'woocommerce_cart_updated', 'hcotp_schedule_abandoned_cart_check' );
	add_action( 'hcotp_trigger_abandoned_cart_sms', 'hcotp_send_abandoned_cart_sms', 10, 1 );
	add_action( 'woocommerce_checkout_order_processed', 'hcotp_clear_abandoned_cart_check_on_order', 10, 1 );
}

/**
 * Sends SMS on new customer registration.
 *
 * @param int $user_id The new user ID.
 */
function hcotp_sms_on_new_customer_registration( $user_id ) {
	if ( ! get_option( 'hcotp_msg91_sms_ncr_enable', 0 ) ) {
		return;
	}
	$template_id      = get_option( 'hcotp_msg91_sms_ncr_template_id' );
	$message_template = get_option( 'hcotp_msg91_sms_ncr_notes', 'Hi ##customer_name##, Welcome to ##site_name##!' );

	if ( empty( $template_id ) || empty( $message_template ) ) {
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

	$data = array(
		'customer_name' => $user->display_name ? $user->display_name : $user->user_login, // Customer Name.
		'site_name'     => get_bloginfo( 'name' ),                   // Site Name.
	);

	hcotp_send_transactional_sms( $phone, $template_id, $message_template, $data );
}

/**
 * Sends SMS when the order is placed (on thank you page).
 *
 * @param int $order_id The order ID.
 */
function hcotp_sms_on_thankyou_page( $order_id ) {
	if ( ! get_option( 'hcotp_msg91_sms_npo_enable', 0 ) ) {
		return;
	}
	$template_id      = get_option( 'hcotp_msg91_sms_npo_template_id' );
	$message_template = get_option( 'hcotp_msg91_sms_npo_notes', 'Hi ##customer_name##, Thank you for choosing ##site_name##! Your order has been confirmed. Your order ID is ##order_id##.' );

	if ( empty( $template_id ) || empty( $message_template ) ) {
		return;
	}
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	$phone = hcotp_get_customer_phone( $order );
	if ( ! $phone ) {
		return;
	}

	$customer_name = $order->get_billing_first_name() ? $order->get_billing_first_name() : $order->get_billing_last_name();
	if ( ! $customer_name && $order->get_customer_id() ) {
		$user          = get_userdata( $order->get_customer_id() );
		$customer_name = $user ? ( $user->display_name ? $user->display_name : $user->user_login ) : 'Valued Customer';
	} elseif ( ! $customer_name ) {
		$customer_name = 'Valued Customer';
	}

	$data = array(
		'customer_name' => $customer_name,
		'order_id'      => $order->get_order_number(),
		'site_name'     => get_bloginfo( 'name' ),
	);

	// Pass message template and data to send function.
	hcotp_send_transactional_sms( $phone, $template_id, $message_template, $data );
}

/**
 * Sends SMS on order status change (e.g., shipped, delivered).
 *
 * @param int    $order_id   The order ID.
 * @param string $old_status Old order status.
 * @param string $new_status New order status.
 */
function hcotp_sms_on_order_status_change( $order_id, $old_status, $new_status ) {
	$order = wc_get_order( $order_id );

	$phone = hcotp_get_customer_phone( $order );
	if ( ! $phone ) {
		return;
	}

	$customer_name = $order->get_billing_first_name() ? $order->get_billing_first_name() : $order->get_billing_last_name();
	if ( ! $customer_name && $order->get_customer_id() ) {
		$user          = get_userdata( $order->get_customer_id() );
		$customer_name = $user ? $user->display_name : 'Valued Customer';
	} elseif ( ! $customer_name ) {
		$customer_name = 'Valued Customer';
	}
	$site_url = get_site_url();

	// Order Shipped.
	$shipped_enabled          = get_option( 'hcotp_msg91_sms_osh_enable', 0 );
	$shipped_template_id      = get_option( 'hcotp_msg91_sms_osh_template_id' );
	$shipped_message_template = get_option( 'hcotp_msg91_sms_osh_notes', 'Hi ##customer_name##, Your order ##order_id## has been shipped! Tracking ID: ##tracking_id##. Track here: ##tracking_url##' );
	$shipped_target_status    = get_option( 'hcotp_msg91_sms_osh_status_slug', 'shipped' );

	if ( $shipped_enabled && ! empty( $shipped_template_id ) && ! empty( $shipped_message_template ) && $new_status === $shipped_target_status ) {
		$tracking_id       = get_post_meta( $order_id, '_hcotp_tracking_id', true );
		$tracking_url      = get_post_meta( $order_id, '_hcotp_tracking_url', true );
		$shipping_provider = get_post_meta( $order_id, '_hcotp_shipping_provider', true );

		$data = array(
			'customer_name'     => $customer_name,
			'order_id'          => $order->get_order_number(),
			'tracking_id'       => $tracking_id,
			'tracking_url'      => $tracking_url ? $tracking_url : $site_url,
			'shipping_provider' => $shipping_provider,
			'site_name'         => get_bloginfo( 'name' ),
		);
		hcotp_send_transactional_sms( $phone, $shipped_template_id, $shipped_message_template, $data );
	}

	// Order Delivered.
	$delivered_enabled          = get_option( 'hcotp_msg91_sms_odl_enable', 0 );
	$delivered_template_id      = get_option( 'hcotp_msg91_sms_odl_template_id' );
	$delivered_message_template = get_option( 'hcotp_msg91_sms_odl_notes', 'Hi ##customer_name##, Your order ##order_id## has been delivered! Thank you for shopping with us.' );
	$delivered_target_status    = get_option( 'hcotp_msg91_sms_odl_status_slug', 'delivered' );

	if ( $delivered_enabled && ! empty( $delivered_template_id ) && ! empty( $delivered_message_template ) && $new_status === $delivered_target_status ) {
		$data = array(
			'customer_name' => $customer_name,
			'order_id'      => $order->get_order_number(),
			'site_name'     => get_bloginfo( 'name' ),
		);
		hcotp_send_transactional_sms( $phone, $delivered_template_id, $delivered_message_template, $data );
	}
}

/**
 * Schedules a check for abandoned carts when the cart is updated.
 */
function hcotp_schedule_abandoned_cart_check() {
	if ( is_admin() || ! get_option( 'hcotp_msg91_sms_oac_enable', 0 ) ) {
		return;
	}

	if ( WC()->cart->is_empty() ) {
			$user_id = get_current_user_id();
		if ( $user_id ) {
			$cron_array = _get_cron_array();
			if ( ! empty( $cron_array ) ) {
				foreach ( $cron_array as $timestamp => $cron ) {
					if ( isset( $cron['hcotp_trigger_abandoned_cart_sms'] ) ) {
						foreach ( $cron['hcotp_trigger_abandoned_cart_sms'] as $hook_instance_hash => $details ) {
							if ( isset( $details['args'] ) && ! empty( $details['args'] ) && $details['args'][0] === $user_id ) {
								wp_unschedule_event( $timestamp, 'hcotp_trigger_abandoned_cart_sms', $details['args'] );
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
		return; // Only for logged-in users for simplicity.
	}

	$phone = hcotp_get_customer_phone( $user_id );
	if ( ! $phone ) {
		return;
	}

	$delay_hours = (float) get_option( 'hcotp_msg91_sms_oac_delay_hours', 1 );
	if ( $delay_hours <= 0 ) {
		$delay_hours = 1;
	}

	// Use a hash of cart contents to avoid sending if cart changes slightly
	// This is very basic. A proper solution tracks cart items and quantities.
	$cart_contents = WC()->cart->get_cart();
	$cart_hash     = md5( wp_json_encode( $cart_contents ) );

	// Clear previous schedule for this user to avoid multiple SMS for same abandonment period.
	wp_clear_scheduled_hook( 'hcotp_trigger_abandoned_cart_sms', array( $user_id, $cart_hash ) ); // Old hash might be different.
	$existing_tasks = _get_cron_array();
	if ( ! empty( $existing_tasks ) ) {
		foreach ( $existing_tasks as $time => $cron ) {
			if ( isset( $cron['hcotp_trigger_abandoned_cart_sms'] ) ) {
				foreach ( $cron['hcotp_trigger_abandoned_cart_sms'] as $hash => $details ) {
					if ( isset( $details['args'][0] ) && $details['args'][0] === $user_id ) {
						wp_unschedule_event( $time, 'hcotp_trigger_abandoned_cart_sms', $details['args'] );
					}
				}
			}
		}
	}

	if ( ! wp_next_scheduled( 'hcotp_trigger_abandoned_cart_sms', array( $user_id, $cart_hash ) ) ) {
		wp_schedule_single_event( time() + ( $delay_hours * HOUR_IN_SECONDS ), 'hcotp_trigger_abandoned_cart_sms', array( $user_id, $cart_hash ) );
	}
}

/**
 * Sends the abandoned cart SMS.
 *
 * @param int $user_id The user ID.
 */
function hcotp_send_abandoned_cart_sms( $user_id ) {

	// --- Ensure WooCommerce is loaded ---
	if ( ! function_exists( 'WC' ) || ! is_object( WC() ) ) {
		if ( defined( 'WP_PLUGIN_DIR' ) && file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ) {
			include_once WP_PLUGIN_DIR . '/woocommerce/woocommerce.php';
			// After including, WC() should be available.
			if ( ! function_exists( 'WC' ) || ! is_object( WC() ) ) {
				return; // Critical failure.
			}
		} else {
			return; // Critical failure.
		}
	}
	// --- End Ensure WooCommerce is loaded ---

	if ( ! get_option( 'hcotp_msg91_sms_oac_enable', 0 ) ) {
		return;
	}
	$template_id      = get_option( 'hcotp_msg91_sms_oac_template_id' );
	$message_template = get_option( 'hcotp_msg91_sms_oac_notes', 'Hi ##customer_name##, You left items in your cart! ##cart_items_count## items worth ##cart_total##. Complete your order now!' );

	if ( empty( $template_id ) || empty( $message_template ) ) {
		return;
	}

	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return;
	}

	// --- Robust WooCommerce Initialization for Cron ---
	// Try to ensure frontend includes are loaded if not already.
	if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
		if ( method_exists( WC(), 'frontend_includes' ) ) { // Check if the method exists on the WC object.
			WC()->frontend_includes();

			// After frontend_includes, we often need to manually instantiate/init session and customer for the target user.
			if ( class_exists( 'WC_Session_Handler' ) && ( is_null( WC()->session ) || ! WC()->session instanceof WC_Session_Handler ) ) {
				WC()->session = new WC_Session_Handler();
				WC()->session->init();
			}

			if ( class_exists( 'WC_Customer' ) && ( is_null( WC()->customer ) || ( WC()->customer instanceof WC_Customer && WC()->customer->get_id() !== $user_id ) ) ) {
				WC()->customer = new WC_Customer( $user_id, true ); // true to force loading for specific user.
			}

			// Cart initialization needs to happen after customer and session are set up for the user.
			if ( class_exists( 'WC_Cart' ) && ( is_null( WC()->cart ) || ! WC()->cart instanceof WC_Cart ) ) {
				WC()->cart = new WC_Cart();
			}
			// The cart loading logic below will handle populating it.
		} else {
			// Fallback: if frontend_includes isn't there or didn't work, try manual instantiation of necessary objects.
			if ( is_null( WC()->session ) || ! WC()->session instanceof WC_Session_Handler ) {
				if ( class_exists( 'WC_Session_Handler' ) ) {
					WC()->session = new WC_Session_Handler();
					WC()->session->init();
				}
			}
			if ( is_null( WC()->customer ) || ( WC()->customer instanceof WC_Customer && WC()->customer->get_id() !== $user_id ) ) {
				if ( class_exists( 'WC_Customer' ) ) {
					WC()->customer = new WC_Customer( $user_id, true );
				}
			}
			if ( is_null( WC()->cart ) || ! WC()->cart instanceof WC_Cart ) {
				if ( class_exists( 'WC_Cart' ) ) {
					WC()->cart = new WC_Cart();
				}
			}
		}
	}

	if ( ! isset( WC()->session ) || ! WC()->session instanceof WC_Session_Handler ) {
		// Fallback instantiation if frontend_includes didn't set it up as expected.
		if ( class_exists( 'WC_Session_Handler' ) ) {
			WC()->session = new WC_Session_Handler();
			WC()->session->init();
		} else {
			return;
		}
	}

	// Ensure customer ID is set in the session for cart loading.
	if ( WC()->session && is_callable( array( WC()->session, 'get_customer_id' ) ) && WC()->session->get_customer_id() !== $user_id ) {
		WC()->session->set_customer_session_cookie( true );
		// WC()->session->set('customer_id', $user_id); // This might not be enough, WC_Customer handles this better
		// Instead of directly setting session customer_id, let's ensure WC_Customer is for our user.
		if ( is_null( WC()->customer ) || WC()->customer->get_id() !== $user_id ) {
			WC()->customer = new WC_Customer( $user_id, true ); // Force load the correct customer.
		}
	}

	if ( ! isset( WC()->cart ) || ! WC()->cart instanceof WC_Cart ) {
		if ( class_exists( 'WC_Cart' ) ) {
			WC()->cart = new WC_Cart();
		} else {
			return;
		}
	}

	// Load cart for the specific user
	// WC_Cart's get_cart_from_session usually relies on cookies or session data,
	// which might not be set for the cron user.
	// We need to ensure the cart is loaded for the $user_id.
	// One way is to fill the cart object after ensuring WC()->customer is set correctly for $user_id.
	if ( WC()->cart && WC()->customer && WC()->customer->get_id() === $user_id ) {
		// If WC_Cart's get_cart_from_session relies on a session ID that's not available in cron,
		// we might need a more direct way to load a user's persisted cart.
		// WooCommerce stores persistent carts in user meta 'woocommerce_cart'.
		$persistent_cart = get_user_meta( $user_id, '_woocommerce_persistent_cart_' . get_current_blog_id(), true );
		if ( ! empty( $persistent_cart ) && isset( $persistent_cart['cart'] ) ) {
			// Clear any existing cart items in the WC()->cart object.
			if ( method_exists( WC()->cart, 'empty_cart' ) ) {
				WC()->cart->empty_cart( false ); // false to not trigger actions.
			}
			// Populate WC()->cart with items from persistent storage.
			foreach ( $persistent_cart['cart'] as $key => $item ) {
				WC()->cart->add_to_cart(
					$item['product_id'],
					$item['quantity'],
					isset( $item['variation_id'] ) ? $item['variation_id'] : 0,
					isset( $item['variation'] ) ? $item['variation'] : array(),
					$item // Pass the whole item along for other data.
				);
			}
			WC()->cart->set_session(); // This might try to save to session, which is fine.
		} elseif ( method_exists( WC()->cart, 'get_cart_from_session' ) ) {
				WC()->cart->get_cart_from_session();
		}
	}

	// ... (rest of your function: check if cart is empty, check recent orders, get phone, send SMS) ...
	// The checks for WC()->cart->is_empty(), get_cart_contents_count(), get_cart_total() should now work.

	if ( WC()->cart && WC()->cart->is_empty() ) {
		return;
	}

	// Check if user has placed an order since scheduling.
	$delay_hours = (float) get_option( 'hcotp_msg91_sms_oac_delay_hours', 1 );
	$args        = array(
		'customer_id'  => $user_id,
		'date_created' => '>' . ( time() - ( $delay_hours * HOUR_IN_SECONDS ) - ( 5 * MINUTE_IN_SECONDS ) ), // check orders in last X hours + 5 mins buffer.
		'status'       => array_keys( wc_get_order_statuses() ), // Any status.
	);
	$orders      = wc_get_orders( $args );
	if ( ! empty( $orders ) ) {
		return; // User placed an order.
	}

	$phone = hcotp_get_customer_phone( $user_id );
	if ( ! $phone ) {
		return;
	}

	$customer_name    = $user->display_name ? $user->display_name : $user->user_login;
	$cart_items_count = WC()->cart->get_cart_contents_count();
	$cart_total       = WC()->cart->get_cart_total();

	$data = array(
		'customer_name'    => $customer_name,        // Customer Name.
		'cart_items_count' => $cart_items_count,     // Cart Items Count.
		'cart_total'       => $cart_total,           // Cart Total.
		'site_name'        => get_bloginfo( 'name' ),   // Site Name.
		'cart_url'         => wc_get_cart_url(),      // Cart URL.
	);

	hcotp_send_transactional_sms( $phone, $template_id, $message_template, $data );
}

/**
 * Clears scheduled abandoned cart checks when an order is processed.
 *
 * @param int $order_id The order ID.
 */
function hcotp_clear_abandoned_cart_check_on_order( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( $order && $order->get_customer_id() ) {
			$user_id = $order->get_customer_id();
		// We don't have the cart hash here, so we clear any task for this user.
		$timestamp = wp_next_scheduled( 'hcotp_trigger_abandoned_cart_sms', array( $user_id, null ) ); // This won't work directly
		// Need to iterate cron array or store the specific args used for scheduling.
		// Simplified: Clear all for this user.
		$existing_tasks = _get_cron_array();
		if ( ! empty( $existing_tasks ) ) {
			foreach ( $existing_tasks as $time => $cron ) {
				if ( isset( $cron['hcotp_trigger_abandoned_cart_sms'] ) ) {
					foreach ( $cron['hcotp_trigger_abandoned_cart_sms'] as $hash => $details ) {
						if ( isset( $details['args'][0] ) && $details['args'][0] === $user_id ) {
							wp_unschedule_event( $time, 'hcotp_trigger_abandoned_cart_sms', $details['args'] );
						}
					}
				}
			}
		}
	}
}


/**
 * Adds a custom meta box to the order edit page for shipment details.
 */
function hcotp_add_shipment_details_meta_box() {
	$screen = class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) && wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
		? wc_get_page_screen_id( 'shop-order' )
		: 'shop_order';

	$shipped_enabled = get_option( 'hcotp_msg91_sms_osh_enable', 0 );
	if ( $shipped_enabled ) {
		add_meta_box(
			'hc_msg91_shipment_details',
			__( 'Shipment Tracking Details (MSG91)', 'happy-coders-otp-login' ),
			'hcotp_shipment_details_meta_box_html',
			$screen, // Post type for WooCommerce orders.
			'side',       // Context (normal, side, advanced).
			'default'     // Priority.
		);
	}
}
add_action( 'add_meta_boxes', 'hcotp_add_shipment_details_meta_box' );

/**
 * Renders the HTML for the shipment details meta box.
 *
 * @param WP_Post|WC_Order $post_or_order_object The post or order object.
 */
function hcotp_shipment_details_meta_box_html( $post_or_order_object ) {
	$order    = is_a( $post_or_order_object, 'WP_Post' ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;
	$order_id = 0;
	if ( $order instanceof WP_Post ) {
		$order_id = $order->ID;
	} elseif ( $order instanceof WC_Order ) {
		$order_id = $order->get_id();
	} else {
		// Fallback or error if type is unexpected
		// For now, let's assume it's one of the above. If you still get errors, log the type.
		return; // Can't proceed without an order ID.
	}

	if ( ! $order_id ) {
		echo '<p>' . esc_html__( 'Could not determine order ID.', 'happy-coders-otp-login' ) . '</p>';
		return;
	}

	wp_nonce_field( 'hc_msg91_save_shipment_details', 'hc_msg91_shipment_nonce' );

	$tracking_id       = get_post_meta( $order_id, '_hcotp_tracking_id', true );
	$tracking_url      = get_post_meta( $order_id, '_hcotp_tracking_url', true );
	$shipping_provider = get_post_meta( $order_id, '_hcotp_shipping_provider', true );
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

/**
 * Saves the custom shipment meta fields.
 *
 * @param int $order_id The order ID.
 */
function hcotp_save_shipment_details_meta( $order_id ) {
	if (
	! isset( $_POST['hc_msg91_shipment_nonce'] ) ||
	! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hc_msg91_shipment_nonce'] ) ), 'hc_msg91_save_shipment_details' )
	) {
		return $order_id;
	}

	// Check if the current user has permission to save the data.
	if ( ! current_user_can( 'edit_post', $order_id ) ) {
		return $order_id;
	}

	// Check if it's an autosave.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $order_id;
	}

	if ( isset( $_POST['hc_msg91_tracking_id'] ) ) {
		update_post_meta( $order_id, '_hcotp_tracking_id', sanitize_text_field( wp_unslash( $_POST['hc_msg91_tracking_id'] ) ) );
	}
	if ( isset( $_POST['hc_msg91_tracking_url'] ) ) {
		update_post_meta( $order_id, '_hcotp_tracking_url', esc_url_raw( wp_unslash( $_POST['hc_msg91_tracking_url'] ) ) );
	}
	if ( isset( $_POST['hc_msg91_shipping_provider'] ) ) {
		update_post_meta( $order_id, '_hcotp_shipping_provider', sanitize_text_field( wp_unslash( $_POST['hc_msg91_shipping_provider'] ) ) );
	}
}
add_action( 'woocommerce_process_shop_order_meta', 'hcotp_save_shipment_details_meta', 10, 1 );


