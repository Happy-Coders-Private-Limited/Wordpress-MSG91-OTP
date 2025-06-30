<?php
/**
 * Plugin Name: Happy Coders OTP Login
 * Text Domain: happy-coders-otp-login
 * Description: Seamless OTP-based login for WordPress/WooCommerce using MSG91. Supports mobile OTP login, and automatic SMS alerts for user registration, order placed, order shipped, order completed, and cart reminder via cronjob.
 * Version: 1.6
 * Author: Happy Coders
 * Author URI: https://www.happycoders.in/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package happy-coders-otp-login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HCOTP_PLUGIN_FILE', __FILE__ );
define( 'HCOTP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'HCOTP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'HCOTP_VERSION', '1.5' );

require_once HCOTP_PLUGIN_DIR . 'includes/hc-msg91-settings.php';
require_once HCOTP_PLUGIN_DIR . 'includes/hc-countries.php';
require_once HCOTP_PLUGIN_DIR . 'includes/hc-msg91-transactional-sms.php';

/**
 * Initialize WooCommerce-specific hooks if WooCommerce is active.
 *
 * @since 1.5
 */
function hcotp_init_woocommerce_hooks() {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	if ( is_plugin_active( 'woocommerce/woocommerce.php' ) && class_exists( 'WooCommerce' ) ) {
		if ( function_exists( 'hc_msg91_register_wc_sms_hooks' ) ) {
			hc_msg91_register_wc_sms_hooks();
		}
	}
}

add_action( 'plugins_loaded', 'hcotp_init_woocommerce_hooks', 20 );

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'hcotp_plugin_action_links' );

/**
 * Run on plugin activation. Creates tables and sets default options.
 *
 * @since 1.5
 */
function hcotp_activate_plugin() {
	hcotp_create_blocked_numbers_table();

	// Default OTP form texts (if not already set).
	$options_to_set = array(
		'hcotp_msg91_sendotp_lable'            => 'Mobile Number',
		'hcotp_msg91_sendotp_dec'              => 'we will send you an OTP',
		'hcotp_msg91_sendotp_button_text'      => 'Send OTP',
		'hcotp_msg91_sendotp_validation_msg'   => 'Please enter the valid mobile number',
		'hcotp_msg91_verifyotp_lable'          => 'Enter OTP',
		'hcotp_msg91_verifyotp_dec'            => 'Enter your 4-digit OTP',
		'hcotp_msg91_verifyotp_button_text'    => 'Verify OTP',
		'hcotp_msg91_verifyotp_validation_msg' => 'Please enter the OTP',
		'hcotp_msg91_perday_otplimit'          => 5,
		'hcotp_msg91_resend_timer'             => 60, // Default resend timer.
	);

	foreach ( $options_to_set as $option_name => $default_value ) {
		if ( false === get_option( $option_name ) ) { // Check if option does not exist.
			update_option( $option_name, $default_value );
		}
	}

	// Default values for new SMS settings (set only if they don't exist).
	$sms_defaults = array(
		'hcotp_msg91_sms_ncr_enable'      => 0,
		'hcotp_msg91_sms_ncr_template_id' => '',
		'hcotp_msg91_sms_ncr_notes'       => 'New Customer: VAR1=CustomerName, VAR2=SiteName, VAR3=ShopURL',
		'hcotp_msg91_sms_npo_enable'      => 0,
		'hcotp_msg91_sms_npo_template_id' => '',
		'hcotp_msg91_sms_npo_notes'       => 'New Order: VAR1=CustomerName, VAR2=OrderID, VAR3=OrderTotal, VAR4=SiteName, VAR5=ShopURL',
		'hcotp_msg91_sms_osh_enable'      => 0,
		'hcotp_msg91_sms_osh_template_id' => '',
		'hcotp_msg91_sms_osh_status_slug' => 'shipped',
		'hcotp_msg91_sms_osh_notes'       => 'Order Shipped: VAR1=CustomerName, VAR2=OrderID, VAR3=TrackingID, VAR4=ShippingProvider, VAR5=TrackingLink, VAR6=SiteName',
		'hcotp_msg91_sms_odl_enable'      => 0,
		'hcotp_msg91_sms_odl_template_id' => '',
		'hcotp_msg91_sms_odl_status_slug' => 'delivered',
		'hcotp_msg91_sms_odl_notes'       => 'Order Delivered: VAR1=CustomerName, VAR2=OrderID, VAR3=SiteName',
		'hcotp_msg91_sms_oac_enable'      => 0,
		'hcotp_msg91_sms_oac_template_id' => '',
		'hcotp_msg91_sms_oac_delay_hours' => 1,
		'hcotp_msg91_sms_oac_notes'       => 'Abandoned Cart: VAR1=CustomerName, VAR2=CartItemsCount, VAR3=CartTotal, VAR4=SiteName, VAR5=CartURL',
	);

	foreach ( $sms_defaults as $key => $value ) {
		if ( false === get_option( $key ) ) {
			update_option( $key, $value );
		}
	}
}
register_activation_hook( __FILE__, 'hcotp_activate_plugin' );

/**
 * Run on plugin deactivation. Cleans up tables and scheduled hooks.
 *
 * @since 1.5
 */
function hcotp_deactivate_plugin() {
	hcotp_delete_blocked_numbers_table();
	wp_clear_scheduled_hook( 'hcotp_trigger_abandoned_cart_sms' );
}
register_deactivation_hook( __FILE__, 'hcotp_deactivate_plugin' );

/**
 * Add settings link to the plugin list.
 *
 * @param array $links The existing links for the plugin.
 * @return array The modified links with the settings link added.
 */
function hcotp_plugin_action_links( $links ) {
	$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=msg91-otp-settings' ) ) . '">' . esc_html__( 'Settings', 'happy-coders-otp-login' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}
add_filter( 'plugin_row_meta', 'hcotp_plugin_row_meta', 10, 3 );

/**
 * Adds custom meta links to the plugin row in the plugins list table.
 *
 * @param array  $plugin_meta  An array of the plugin's meta data.
 * @param string $plugin_file  Path to the plugin file relative to the plugins directory.
 * @param array  $plugin_data  An array of plugin data.
 * @return array The modified array of plugin meta data with additional links.
 */
function hcotp_plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data ) {
	if ( plugin_basename( __FILE__ ) === $plugin_file ) {
		if ( false !== stripos( $plugin_data['Author'], 'Happy Coders' ) ) {
			$plugin_meta[] = sprintf( '<a href="%s" target="_blank">%s</a>', 'https://www.happycoders.in/msg91-plugin-documentation/', esc_html__( 'Documentation', 'happy-coders-otp-login' ) );
			$plugin_meta[] = sprintf( '<a href="%s" target="_blank">%s</a>', 'https://www.happycoders.in/', esc_html__( 'Support', 'happy-coders-otp-login' ) );
			$plugin_meta[] = sprintf( '<a href="%s" target="_blank">%s</a>', 'https://github.com/Happy-Coders-Private-Limited', esc_html__( 'GitHub', 'happy-coders-otp-login' ) );
		}
	}
	return $plugin_meta;
}



/**
 * Enqueues scripts and styles for the frontend.
 */
function hcotp_enqueue_scripts() {
	wp_enqueue_script(
		'hcotp-main-js',
		HCOTP_PLUGIN_URL . 'assets/js/hc-msg91-otp.js',
		array( 'jquery' ),
		HCOTP_VERSION,
		true
	);
	wp_enqueue_style(
		'hcotp-main-css',
		HCOTP_PLUGIN_URL . 'assets/css/hc-msg91-otp.css',
		array(),
		HCOTP_VERSION
	);
	wp_localize_script(
		'hcotp-main-js',
		'hcotp_params',
		array(
			'ajax_url'                 => admin_url( 'admin-ajax.php' ),
			'nonce'                    => wp_create_nonce( 'msg91_ajax_nonce_action' ),
			'resend_timer'             => (int) get_option( 'hcotp_msg91_resend_timer', 60 ),
			'redirect_page'            => get_option( 'hcotp_msg91_redirect_page' ),
			'sendotp_validation_msg'   => get_option( 'hcotp_msg91_sendotp_validation_msg', 'Please enter a valid mobile number (between 5 and 12 digits).' ),
			'verifyotp_validation_msg' => get_option( 'hcotp_msg91_verifyotp_validation_msg', 'Please enter the otp' ),
			'sending_text'             => __( 'Sending...', 'happy-coders-otp-login' ),
			'verifying_text'           => __( 'Verifying...', 'happy-coders-otp-login' ),
			'error_text'               => __( 'Something went wrong. Please try again.', 'happy-coders-otp-login' ),
			'verified_text'            => __( 'OTP Verified!', 'happy-coders-otp-login' ),
			'invalid_otp_text'         => __( 'Invalid OTP. Please try again.', 'happy-coders-otp-login' ),
			'server_error_text'        => __( 'Server error. Please try again.', 'happy-coders-otp-login' ),
			'send_otp_text'            => get_option( 'hcotp_msg91_sendotp_button_text', __( 'Send OTP', 'happy-coders-otp-login' ) ),
			'verify_otp_text'          => get_option( 'hcotp_msg91_verifyotp_button_text', __( 'Verify OTP', 'happy-coders-otp-login' ) ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'hcotp_enqueue_scripts' );


register_activation_hook( __FILE__, 'hcotp_create_blocked_numbers_table' );
register_activation_hook( __FILE__, 'hcotp_add_otp_column_to_users_table' );
register_deactivation_hook( __FILE__, 'hcotp_delete_blocked_numbers_table' );

/**
 * Creates the database table for storing blocked mobile numbers.
 *
 * This function is triggered during plugin activation and creates a
 * database table with the following columns:
 *
 * - id: An auto-incrementing primary key.
 * - mobile_number: A 20-character string representing the mobile number.
 * - ip_address: A 45-character string representing the user's IP address.
 * - created_at: A timestamp indicating when the number was added to the table.
 *
 * The table is created using the dbDelta function, which ensures that
 * the table is only created if it does not already exist.
 */
function hcotp_create_blocked_numbers_table() {
	global $wpdb;
	$table_name      = $wpdb->prefix . 'hcotp_blocked_numbers';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id INT(11) NOT NULL AUTO_INCREMENT,
		mobile_number VARCHAR(20) NOT NULL,
		ip_address VARCHAR(45) NOT NULL,
		created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id)
	) $charset_collate;";
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}

/**
 * Adds a custom column to the users table for storing OTP codes.
 *
 * Note: Modifying core tables is generally discouraged.
 * User meta is a better alternative for storing user-specific data.
 */
function hcotp_add_otp_column_to_users_table() {
	global $wpdb;
	$table_name  = $wpdb->users;
	$column_name = 'otp_code';

	// Define a cache key for this specific check.
	$cache_key = 'hcotp_otp_column_exists';

	// Try to get the cached result first.
	$column_exists = wp_cache_get( $cache_key );

	if ( false === $column_exists ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$column_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW COLUMNS FROM %s LIKE %s', $table_name, $column_name ) );
		wp_cache_set( $cache_key, $column_exists, 'hcotp' );
	}

	if ( empty( $column_exists ) ) {

		$sql = "ALTER TABLE $table_name ADD COLUMN $column_name VARCHAR(10) DEFAULT NULL;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}

/**
 * Drops the database table for storing blocked mobile numbers.
 *
 * This function is triggered during plugin deactivation and drops the
 * database table created by hcotp_create_blocked_numbers_table.
 */
function hcotp_delete_blocked_numbers_table() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'hcotp_blocked_numbers';

	// Define a cache key for this specific check.
	$cache_key = 'hcotp_blocked_numbers_table_exists';

	// Try to get the cached result first.
	$table_exists = wp_cache_get( $cache_key );

	if ( false === $table_exists ) {

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );
		wp_cache_set( $cache_key, $table_exists );
	}

	if ( $table_exists ) {
		$sql = "DROP TABLE IF EXISTS $table_name;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
add_action( 'wp_ajax_hcotp_send_otp_ajax', 'hcotp_send_otp_ajax' );
add_action( 'wp_ajax_nopriv_hcotp_send_otp_ajax', 'hcotp_send_otp_ajax' );


/**
 * Retrieves the MSG91 OTP plugin options.
 *
 * This function fetches various configuration settings for the MSG91 OTP plugin
 * from the WordPress options table. These settings control the appearance and
 * text of the OTP send and verify interfaces, including labels, descriptions,
 * button text, and colors, as well as images for the top of the forms.
 *
 * @return array An associative array containing the plugin options:
 * - 'send_otp_label': The label for the send OTP input field.
 * - 'send_otp_label_color': The color of the send OTP label.
 * - 'send_otp_desc': The description text for the send OTP section.
 * - 'send_otp_desc_color': The color of the send OTP description text.
 * - 'send_otp_button_text': The text on the send OTP button.
 * - 'send_otp_button_color': The background color of the send OTP button.
 * - 'top_image': URL of the image displayed at the top of the send OTP form.
 * - 'verify_otp_lable': The label for the verify OTP input field.
 * - 'verify_otp_lable_color': The color of the verify OTP label.
 * - 'verify_otp_dec': The description text for the verify OTP section.
 * - 'verify_otp_dec_color': The color of the verify OTP description text.
 * - 'verify_otp_buttontext': The text on the verify OTP button.
 * - 'verify_otp_button_color': The background color of the verify OTP button.
 * - 'top_verify_image': URL of the image displayed at the top of the verify OTP form.
 */
function hcotp_get_options() {
	return array(
		'send_otp_label'              => hcotp_get_option_with_default( 'hcotp_msg91_sendotp_lable', 'Mobile Number' ),
		'send_otp_label_color'        => hcotp_get_option_with_default( 'hcotp_msg91_sendotp_lable_color', '#000000' ),
		'send_otp_desc'               => hcotp_get_option_with_default( 'hcotp_msg91_sendotp_dec', 'We will send you an OTP' ),
		'send_otp_desc_color'         => hcotp_get_option_with_default( 'hcotp_msg91_sendotp_dec_color', '#000000' ),
		'send_otp_button_text'        => hcotp_get_option_with_default( 'hcotp_msg91_sendotp_button_text', 'Send OTP' ),
		'send_otp_button_color'       => hcotp_get_option_with_default( 'hcotp_msg91_sendotp_button_color', '#0073aa' ),
		'top_image'                   => hcotp_get_option_with_default( 'hcotp_msg91_top_image', HCOTP_PLUGIN_URL . 'assets/images/send-otp.png' ),
		'verify_otp_lable'            => hcotp_get_option_with_default( 'hcotp_msg91_verifyotp_lable', 'Enter Mobile' ),
		'verify_otp_lable_color'      => hcotp_get_option_with_default( 'hcotp_msg91_verifyotp_lable_color', '#000000' ),
		'verify_otp_dec'              => hcotp_get_option_with_default( 'hcotp_msg91_verifyotp_dec', 'Enter your 4-digit OTP' ),
		'verify_otp_dec_color'        => hcotp_get_option_with_default( 'hcotp_msg91_verifyotp_desc_color', '#000000' ),
		'verify_otp_buttontext'       => hcotp_get_option_with_default( 'hcotp_msg91_verifyotp_button_text', 'Verify OTP' ),
		'verify_otp_button_color'     => hcotp_get_option_with_default( 'hcotp_msg91_verifyotp_button_color', '#0073aa' ),
		'top_verify_image'            => hcotp_get_option_with_default( 'hcotp_msg91_top_verify_image', HCOTP_PLUGIN_URL . 'assets/images/verify-otp.png' ),
		'hcotp_whatsapp_auth_enabled' => hcotp_get_option_with_default( 'hcotp_whatsapp_auth_enabled', 0 ),
		'hcotp_whatsapp_button_text'  => hcotp_get_option_with_default( 'hcotp_whatsapp_button_text', 'Send OTP via Whatsapp' ),

	);
}

/**
 * Gets a plugin option with a default value if it's not set.
 *
 * @param string $option_name   The name of the option.
 * @param mixed  $default_value The default value.
 * @return mixed The option value or the default.
 */
function hcotp_get_option_with_default( $option_name, $default_value ) {
	$value = get_option( $option_name );
	return ( false === $value || '' === $value ) ? $default_value : $value;
}


/**
 * Generates a country select dropdown based on the plugin settings.
 *
 * This function retrieves the plugin settings from the WordPress options table,
 * including the list of selected countries and whether or not to show the flag
 * icons. It then filters the list of all countries to only include the selected
 * countries, and generates the HTML for the select dropdown. The HTML includes
 * the flag icon for each country, if the flag show setting is enabled.
 *
 * @return string The HTML for the country select dropdown.
 */
function hcotp_country_select() {
	$html          = '';
	$all_countries = hcotp_get_countries_with_iso();

	$selected_countries = get_option( 'hcotp_msg91_selected_countries', array( '+91' ) );

	if ( ! is_array( $selected_countries ) ) {
		$selected_countries = array( '+91' );
	}
	$show_flag = get_option( 'hcotp_msg91_flag_show', 0 );

	$filtered_countries = array_filter(
		$all_countries,
		function ( $country ) use ( $selected_countries ) {
			return in_array( $country['code'], $selected_countries, true );
		}
	);

	foreach ( $filtered_countries as $country ) {
		$selected  = 'selected';
		$flag      = hcotp_iso_to_flag( $country['iso'] );
		$flag_html = $show_flag ? $flag : '';
		$html     .= sprintf(
			/* translators: 1: Country code, 2: Flag icon, 3: Selected attribute, 4: Country name, 5: Country code */
			'<option value="%s" data-flag="%s" %s>%s %s</option>',
			esc_attr( $country['code'] ),
			esc_attr( $flag ),
			esc_attr( $selected ),
			esc_html( $flag_html ),
			esc_html( $country['code'] )
		);
	}

	return "<select name='msg91_country_code' id='msg91_country_code' class='country-select'>{$html}</select>";
}


add_shortcode(
	'msg91_otp_form',
	function () {
		$options = hcotp_get_options();

		if ( empty( $options['top_image'] ) ) {
			$options['top_image'] = HCOTP_PLUGIN_URL . 'assets/images/send-otp.png';
		}
		if ( empty( $options['top_verify_image'] ) ) {
			$options['top_verify_image'] = HCOTP_PLUGIN_URL . 'assets/images/verify-otp.png';
		}

		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			return '<div style="text-align: center;">
						<h3>Welcome, ' . esc_html( $user->display_name ) . '!</h3>
					
					</div>';
		}

		return hcotp_otp_form( $options, false );
	}
);


add_action(
	'wp_footer',
	function () {
		$options = hcotp_get_options();
		if ( empty( $options['top_image'] ) ) {
			$options['top_image'] = HCOTP_PLUGIN_URL . 'assets/images/send-otp.png';
		}
		if ( empty( $options['top_verify_image'] ) ) {
			$options['top_verify_image'] = HCOTP_PLUGIN_URL . 'assets/images/verify-otp.png';
		}
		if ( ! is_user_logged_in() ) {
			// Nonce is for image which is not enqueued.
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo hcotp_otp_form( $options, true );
		}
	}
);

/**
 * Renders the MSG91 OTP form
 *
 * @param array $options The options array. Possible options are:
 *     - top_image: The image to be displayed on top of the form.
 *     - top_verify_image: The image to be displayed on top of the verification form.
 *     - send_otp_label: The label for the send OTP section.
 *     - send_otp_label_color: The color for the send OTP label.
 *     - send_otp_desc: The description for the send OTP section.
 *     - send_otp_desc_color: The color for the send OTP description.
 *     - send_otp_button_text: The text for the send OTP button.
 *     - send_otp_button_color: The background color for the send OTP button.
 *     - verify_otp_lable: The label for the verify OTP section.
 *     - verify_otp_lable_color: The color for the verify OTP label.
 *     - verify_otp_dec: The description for the verify OTP section.
 *     - verify_otp_dec_color: The color for the verify OTP description.
 *     - verify_otp_buttontext: The text for the verify OTP button.
 *     - verify_otp_button_color: The background color for the verify OTP button.
 * @param bool  $is_popup Whether the form should be rendered as a popup.
 *
 * @return string The rendered form.
 */
function hcotp_otp_form( $options, $is_popup = false ) {
	ob_start();

	?>
	<?php if ( $is_popup ) : ?>
	
		<div id="otp-popup-modal" style="display: none;">
			
	<?php endif; ?>

	<div id="otp-form-wrap">
	<?php if ( $is_popup ) : ?>
		<div style="width: 100%; text-align: right; height: 0;">
				<button onclick="document.getElementById( 'otp-popup-modal' ).style.display='none';" style="background: none; border: none; font-size: 24px; cursor: pointer; outline: none;">&times;</button>
			</div>
	<?php endif; ?>
   
		<div id="send_otp_section">
			<?php if ( ! empty( $options['top_image'] ) ) : ?>
				<div style="text-align:center;">
				<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
					<img src="<?php echo esc_url( $options['top_image'] ); ?>" class="popup-image" alt="Send OTP" />
				</div>
			<?php endif; ?>
			<div style="text-align:center;">
					<label class="lable-style" style="color: <?php echo esc_attr( $options['send_otp_label_color'] ); ?>;"><?php echo esc_html( $options['send_otp_label'] ); ?></label>
			</div>

			<div style="text-align:center;">
				<label class="descripition" style="color: <?php echo esc_attr( $options['send_otp_desc_color'] ); ?>;">
					<?php echo esc_html( $options['send_otp_desc'] ); ?>
				</label>
			</div>

			<div class="mobile-input-wrap">
				<?php
				echo hcotp_country_select(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
				<input type="hidden" id="otpprocess" value="">
				<input type="tel" id="msg91_mobile" maxlength="10" pattern="\d*" placeholder="Mobile Number" oninput="this.value = this.value.replace(/[^0-9]/g, '' );" />
			</div>
			<div id="otp-send-status" class="otp-send-status"></div>
			
			<button id="msg91_send_otp" class="common-width" style="background-color: <?php echo esc_attr( $options['send_otp_button_color'] ); ?>; color: #fff;"><?php echo esc_html( $options['send_otp_button_text'] ); ?></button>

				<?php if ( ! empty( $options['hcotp_whatsapp_auth_enabled'] ) ) : ?>
					<button id="msg91_send_otp_whatsapp" class="common-width">
						<?php echo esc_html( $options['hcotp_whatsapp_button_text'] ); ?>
					</button>
				<?php endif; ?>

			</div>
				<div id="otp_input_wrap" style="display: none;">
					<?php if ( ! empty( $options['top_verify_image'] ) ) : ?>
						<div style="text-align:center;">
						<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
							<img src="<?php echo esc_url( $options['top_verify_image'] ); ?>" class="popup-image" />
						</div>
					<?php endif; ?>
					<div style="text-align:center;">
						<label class="lable-style" style="color: <?php echo esc_attr( $options['verify_otp_lable_color'] ); ?>;"><?php echo esc_html( $options['verify_otp_lable'] ); ?>
						</label>
					</div>
					<div style="text-align:center;">
						<label class="descripition" style="color: <?php echo esc_attr( $options['verify_otp_dec_color'] ); ?>;"><?php echo esc_html( $options['verify_otp_dec'] ); ?></label>
					</div>
					<div class="otp-inputs">
						<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
							<input type="number" class="otp-field" id="otp<?php echo esc_attr( $i ); ?>" maxlength="1" />
						<?php endfor; ?>
					</div>
					<div id="otp-verify-status" class="otp-verify-status"></div>
					<div class="verify-otp">
						<button id="msg91_verify_otp" style="background-color: <?php echo esc_attr( $options['verify_otp_button_color'] ); ?>; color: #fff;"><?php echo esc_html( $options['verify_otp_buttontext'] ); ?></button>
					</div>
					<div style="text-align:center;">
						<h4 id="resend_otp"><?php esc_html_e( 'Didn\'t receive an OTP? Resend OTP', 'happy-coders-otp-login' ); ?></h4>
						<div class="row" id="otp_method_buttons">
							<a id="msg91_send_otp" class="send-button sms-button" disabled><?php esc_html_e( 'SMS', 'happy-coders-otp-login' ); ?></a>
						<a id="msg91_send_otp_whatsapp" class="send-button whatsapp-button" disabled><?php esc_html_e( 'Whatsapp', 'happy-coders-otp-login' ); ?></a>
						</div>
						<div id="resend_timer_text"></div>
					</div>
				</div>
	</div>

	<?php if ( $is_popup ) : ?>
		</div> 
		<?php
	endif;

	return ob_get_clean();
}


/**
 * AJAX handler for sending OTP to user.
 *
 * This function sends an OTP to the user if the daily limit has not been exceeded.
 * If the limit has been exceeded, it returns an error message.
 *
 * @since 1.0.0
 */
function hcotp_send_otp_ajax() {
	global $wpdb;
	check_ajax_referer( 'msg91_ajax_nonce_action', 'security_nonce' );

	$mobile        = sanitize_text_field( wp_unslash( $_POST['mobile'] ?? '' ) );
	$otpprocess    = sanitize_text_field( wp_unslash( $_POST['otpprocess'] ?? '' ) );
	$ip_address    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$table_name    = $wpdb->prefix . 'hcotp_blocked_numbers';
	$per_day_limit = intval( get_option( 'hcotp_msg91_perday_otplimit', 5 ) );
	$today         = gmdate( 'Y-m-d' );

	// Define a cache key for this specific check.
	$cache_key = 'hcotp_blocked_numbers_table_exists';

	$table_exists = wp_cache_get( $cache_key );

	if ( false === $table_exists ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );
		wp_cache_set( $cache_key, $table_exists );
	}

	if ( $table_exists ) {
		$otp_count_today = 0;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$otp_count_today = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM %s WHERE mobile_number = %s AND DATE(created_at) = %s',
				$table_name,
				$mobile,
				$today
			)
		);
	}

	if ( $otp_count_today >= $per_day_limit ) {
		wp_send_json_error( array( 'message' => 'You have reached the OTP request limit for today.' ) );
	}

	$authkey     = get_option( 'hcotp_msg91_auth_key' );
	$sender      = get_option( 'hcotp_msg91_sender_id' );
	$template_id = get_option( 'hcotp_msg91_template_id' );
	$wa_template = get_option( 'hcotp_whatsapp_template_name', 'login' );

	$wa_namespace = get_option( 'hcotp_whatsapp_template_namespace' );

	$wa_number = get_option( 'hcotp_whatsapp_integrated_number' );

	$hcotp_whatsapp_language_code = get_option( 'hcotp_whatsapp_language_code' );

	if ( 'sms' === $otpprocess ) {

		$url = "https://control.msg91.com/api/v5/otp?authkey=$authkey&otp_expiry=5&template_id=$template_id&mobile=$mobile&realTimeResponse";

		$response = wp_remote_get( $url );
		$body     = wp_remote_retrieve_body( $response );
		$result   = json_decode( $body, true );

		if ( isset( $result['type'] ) && 'success' === $result['type'] ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->query(
				$wpdb->prepare(
					'INSERT INTO  %s (mobile_number, ip_address, created_at) VALUES (%s, %s, %s)',
					$table_name,
					$mobile,
					$ip_address,
					current_time( 'mysql' )
				)
			);
			wp_send_json_success(
				array(
					'message'    => 'OTP sent successfully via SMS.',
					'request_id' => $result['request_id'] ?? null,
				)
			);
		} else {
			wp_send_json_error( array( 'message' => $result['message'] ?? 'Failed to send OTP via SMS.' ) );
		}
	} else {
		$otp_code = wp_rand( 1000, 9999 );

		$wa_url     = 'https://api.msg91.com/api/v5/whatsapp/whatsapp-outbound-message/bulk/';
		$wa_payload = array(
			'integrated_number' => $wa_number,
			'content_type'      => 'template',
			'payload'           => array(
				'messaging_product' => 'whatsapp',
				'type'              => 'template',
				'template'          => array(
					'name'              => $wa_template,
					'language'          => array(
						'code'   => $hcotp_whatsapp_language_code,
						'policy' => 'deterministic',
					),
					'namespace'         => $wa_namespace,
					'to_and_components' => array(
						array(
							'to'         => array( $mobile ),
							'components' => array(
								'body_1'   => array(
									'type'  => 'text',
									'value' => $otp_code,
								),
								'button_1' => array(
									'type'    => 'text',
									'subtype' => 'url',
									'value'   => $otp_code,
								),
							),
						),
					),
				),
			),
		);

		$response = wp_remote_post(
			$wa_url,
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'Authkey'      => $authkey,
				),
				'body'    => wp_json_encode( $wa_payload ),
			)
		);

		$body   = wp_remote_retrieve_body( $response );
		$result = json_decode( $body, true );

		if ( isset( $result['status'] ) && 'success' === $result['status'] ) {

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->query(
					$wpdb->prepare(
						'INSERT INTO %s (mobile_number, ip_address, created_at) VALUES (%s, %s, %s)',
						$table_name,
						$mobile,
						$ip_address,
						current_time( 'mysql' )
					)
				);
				$clean_mobile = preg_replace( '/\D/', '', $mobile );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$user = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT * FROM {$wpdb->users} WHERE user_login = %s",
						$clean_mobile
					)
				);
			if ( $user ) {

				update_user_meta( $user->ID, 'otp_code', $otp_code );
				update_user_meta( $user->ID, 'mobile_number', $clean_mobile );

			} else {
				$password = wp_generate_password( 8, false );
				$user_id  = wp_insert_user(
					array(
						'user_login' => $clean_mobile,
						'user_pass'  => $password,
						'user_email' => $clean_mobile . '@example.com',
						'role'       => 'subscriber',
					)
				);

				if ( ! is_wp_error( $user_id ) ) {
						update_user_meta( $user_id, 'otp_code', $otp_code );
						update_user_meta( $user_id, 'mobile_number', $clean_mobile );
				}
			}

				wp_send_json_success( array( 'message' => 'OTP sent successfully via WhatsApp.' ) );
		} else {
			wp_send_json_error( array( 'message' => $result['message'] ?? 'Failed to send OTP via WhatsApp.' ) );
		}
	}
}

add_action( 'wp_ajax_hcotp_send_otp_ajax', 'hcotp_send_otp_ajax' );
add_action( 'wp_ajax_nopriv_hcotp_send_otp_ajax', 'hcotp_send_otp_ajax' );
add_action( 'wp_ajax_hcotp_auto_login_user', 'hcotp_auto_login_user' );
add_action( 'wp_ajax_nopriv_hcotp_auto_login_user', 'hcotp_auto_login_user' );

/**
 * Automatically logs in a user based on their mobile number.
 *
 * This function checks if a user exists based on the provided mobile number.
 * If the user does not exist, it creates a new user with the mobile number
 * as the username and a generated password. The user is then logged in and
 * their session is configured for a duration of 30 days. A cookie is set to
 * remember the verified mobile and user ID for the same duration.
 *
 * Expects the mobile number to be provided in the $_POST data.
 *
 * Sends a JSON response indicating success or failure.
 */
function hcotp_auto_login_user() {
	check_ajax_referer( 'msg91_ajax_nonce_action', 'security_nonce' );
	$mobile = sanitize_text_field( wp_unslash( isset( $_POST['mobile'] ) ? $_POST['mobile'] : '' ) );
	if ( empty( $mobile ) ) {
		wp_send_json_error( array( 'message' => 'Mobile number missing' ) );
	}

	$username = $mobile;
	$email    = $username . '@example.com';

	$user = get_user_by( 'login', $username );

	if ( ! $user ) {
		$user_id = wp_create_user( $username, wp_generate_password(), $email );
		wp_update_user(
			array(
				'ID'           => $user_id,
				'display_name' => $mobile,
			)
		);
		$user = get_user_by( 'ID', $user_id );

			hcotp_sms_on_new_customer_registration( $user->ID );

	} else {
		$created             = strtotime( $user->user_registered );
		$is_very_recent_user = ( time() - $created ) < 60;
		if ( $is_very_recent_user ) {
			hcotp_sms_on_new_customer_registration( $user->ID );
		}
	}
	wp_set_current_user( $user->ID );
	wp_set_auth_cookie( $user->ID, true );

	setcookie( 'msg91_verified_mobile', $mobile, time() + ( 30 * 24 * 60 * 60 ), COOKIEPATH, COOKIE_DOMAIN );
	setcookie( 'msg91_verified_user_id', $user->ID, time() + ( 30 * 24 * 60 * 60 ), COOKIEPATH, COOKIE_DOMAIN );

	wp_send_json_success(
		array(
			'message' => 'User logged in successfully',
			'name'    => $user->display_name,
			'user_id' => $user->ID,

		)
	);
}
add_action( 'wp_ajax_hcotp_verify_otp_ajax', 'hcotp_verify_otp_ajax' );
add_action( 'wp_ajax_nopriv_hcotp_verify_otp_ajax', 'hcotp_verify_otp_ajax' );

/**
 * Verify the OTP sent by MSG91 and log in the user if OTP is valid.
 *
 * This function is called via AJAX when the user submits the OTP form.
 *
 * @since 1.0.0
 *
 * @return mixed A JSON response with a success message and the user ID if the OTP is valid, or an error message if the OTP is invalid.
 */
function hcotp_verify_otp_ajax() {
	check_ajax_referer( 'msg91_ajax_nonce_action', 'security_nonce' );
	$mobile     = sanitize_text_field( wp_unslash( isset( $_POST['mobile'] ) ? $_POST['mobile'] : '' ) );
	$otpprocess = sanitize_text_field( wp_unslash( $_POST['otpprocess'] ?? '' ) );
	$otp        = sanitize_text_field( wp_unslash( isset( $_POST['otp'] ) ? $_POST['otp'] : '' ) );

	if ( empty( $mobile ) || empty( $otp ) ) {
		wp_send_json_error( array( 'message' => 'Mobile number and OTP are required.' ) );
		return;
	}

	$mobile = preg_replace( '/[^0-9]/', '', $mobile );

	if ( 'sms' === $otpprocess ) {
		$url = 'https://api.msg91.com/api/verifyRequestOTP.php?authkey=' . get_option( 'hcotp_msg91_auth_key' ) . "&mobile={$mobile}&otp={$otp}";

		$response = wp_remote_get( $url );
		$body     = wp_remote_retrieve_body( $response );
		$result   = json_decode( $body, true );

		if ( isset( $result['type'] ) && 'success' === $result['type'] ) {
			$user = get_user_by( 'login', $mobile );

			$created = strtotime( $user->user_registered );

			$is_very_recent_user = ( time() - $created ) < 120;

			if ( $is_very_recent_user ) {
				hcotp_sms_on_new_customer_registration( $user->ID );
			}
			if ( $user ) {
				wp_set_current_user( $user->ID );
				wp_set_auth_cookie( $user->ID, true );

				setcookie( 'msg91_verified_mobile', $mobile, time() + ( 30 * 24 * 60 * 60 ), COOKIEPATH, COOKIE_DOMAIN );
				setcookie( 'msg91_verified_user_id', $user->ID, time() + ( 30 * 24 * 60 * 60 ), COOKIEPATH, COOKIE_DOMAIN );

				wp_send_json_success(
					array(
						'message' => 'OTP Verified Successfully, User logged in',
						'user_id' => $user->ID,
					)
				);
			} else {
				hcotp_auto_login_user();
			}
			setcookie( 'msg91_verified_mobile', $mobile, time() + ( 30 * 24 * 60 * 60 ), COOKIEPATH, COOKIE_DOMAIN );
			setcookie( 'msg91_verified_user_id', $user->ID, time() + ( 30 * 24 * 60 * 60 ), COOKIEPATH, COOKIE_DOMAIN );

			wp_send_json_success(
				array(
					'message' => 'OTP Verified Successfully',
					'user_id' => get_current_user_id(),
				)
			);
		} else {
			wp_send_json_error( array( 'message' => $result['message'] ?? 'OTP verification failed' ) );
		}
	} elseif ( 'whatsapp' === $otpprocess ) {

		$user    = get_user_by( 'login', $mobile );
		$created = strtotime( $user->user_registered );

		$is_very_recent_user = ( time() - $created ) < 120;

		if ( $is_very_recent_user ) {
			hcotp_sms_on_new_customer_registration( $user->ID );
		}
		$saved_otp = get_user_meta( $user->ID, 'otp_code', true );

		if ( $otp !== $saved_otp ) {
			wp_send_json_error( array( 'message' => 'Invalid OTP for WhatsApp.' ) );
		}

		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID, true );

		setcookie( 'msg91_verified_mobile', $mobile, time() + ( 30 * 24 * 60 * 60 ), COOKIEPATH, COOKIE_DOMAIN );
		setcookie( 'msg91_verified_user_id', $user->ID, time() + ( 30 * 24 * 60 * 60 ), COOKIEPATH, COOKIE_DOMAIN );

		wp_send_json_success(
			array(
				'message' => 'OTP Verified Successfully (WhatsApp)',
				'user_id' => $user->ID,
			)
		);
	} else {
		wp_send_json_error( array( 'message' => 'Invalid OTP process type.' ) );
	}
}



/**
 * Register custom order statuses for 'Shipped' and 'Delivered'.
 */
function hcotp_register_custom_order_statuses() {
	// Status: Shipped.
	register_post_status(
		'wc-shipped',
		array(
			'label'                     => _x( 'Shipped', 'Order status', 'happy-coders-otp-login' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			/* translators: %s: number of orders */
			'label_count'               => _n_noop( 'Shipped <span class="count">(%s)</span>', 'Shipped <span class="count">(%s)</span>', 'happy-coders-otp-login' ),
		)
	);

	// Status: Delivered.
	register_post_status(
		'wc-delivered',
		array(
			'label'                     => _x( 'Delivered', 'Order status', 'happy-coders-otp-login' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			/* translators: %s: number of orders */
			'label_count'               => _n_noop( 'Delivered <span class="count">(%s)</span>', 'Delivered <span class="count">(%s)</span>', 'happy-coders-otp-login' ),
		)
	);
}
add_action( 'init', 'hcotp_register_custom_order_statuses' );

/**
 * Add custom statuses to the WooCommerce order statuses list.
 *
 * @param array $order_statuses Existing order statuses.
 * @return array Modified order statuses.
 */
function hcotp_add_custom_statuses_to_order_list( $order_statuses ) {
	$new_order_statuses = array();

	// Add new statuses after 'Processing' or 'Completed'.
	foreach ( $order_statuses as $key => $status ) {
		$new_order_statuses[ $key ] = $status;
		if ( 'wc-processing' === $key || 'wc-completed' === $key ) { // Choose where to insert them.
			$new_order_statuses['wc-shipped']   = _x( 'Shipped', 'Order status', 'happy-coders-otp-login' );
			$new_order_statuses['wc-delivered'] = _x( 'Delivered', 'Order status', 'happy-coders-otp-login' );
		}
	}
	// Ensure they are added if the above hooks didn't catch.
	if ( ! isset( $new_order_statuses['wc-shipped'] ) ) {
		$new_order_statuses['wc-shipped'] = _x( 'Shipped', 'Order status', 'happy-coders-otp-login' );
	}
	if ( ! isset( $new_order_statuses['wc-delivered'] ) ) {
		$new_order_statuses['wc-delivered'] = _x( 'Delivered', 'Order status', 'happy-coders-otp-login' );
	}

	return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'hcotp_add_custom_statuses_to_order_list' );

remove_action( 'woocommerce_login_form', 'woocommerce_login_form_start', 10 );
remove_action( 'woocommerce_login_form', 'woocommerce_login_form', 20 );
remove_action( 'woocommerce_login_form', 'woocommerce_login_form_end', 30 );

// Remove WooCommerce register form.
remove_action( 'woocommerce_register_form', 'woocommerce_register_form_start', 10 );
remove_action( 'woocommerce_register_form', 'woocommerce_register_form', 20 );
remove_action( 'woocommerce_register_form', 'woocommerce_register_form_end', 30 );


/**
 * Replaces the default WooCommerce login/register forms with the OTP form.
 *
 * @since 1.5
 */
function hcotp_replace_wc_login_with_otp() {
	if ( ! is_user_logged_in() ) {
		echo do_shortcode( '[msg91_otp_form]' );
	}
}

/**
 * Adds a custom message before the checkout form for non-logged-in users.
 *
 * @since 1.5
 */
function hcotp_message_before_checkout() {
	if ( ! is_user_logged_in() ) {
		echo '<div class="woocommerce-info custom-login-notice">';
		echo 'Please <a href="/my-account">click here</a> to login.';
		echo '</div>';

	}
}

/**
 * Hooks into WooCommerce to replace forms.
 *
 * @since 1.5
 */
function hcotp_override_wc_forms() {
	// Remove default login/register forms from My Account page.
	remove_action( 'woocommerce_before_customer_login_form', 'woocommerce_output_all_notices', 10 );
	remove_action( 'woocommerce_customer_login_form', 'woocommerce_login_form', 10 );
	remove_action( 'woocommerce_customer_registration_form', 'woocommerce_registration_form', 10 );

	// Add our OTP form.
	add_action( 'woocommerce_before_customer_login_form', 'hcotp_replace_wc_login_with_otp' );

	// Remove login form from checkout.
	remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );
	add_action( 'woocommerce_before_checkout_form', 'hcotp_message_before_checkout', 5 );
}
add_action( 'wp_loaded', 'hcotp_override_wc_forms' );

/**
 * Asks for a plugin review after a period of time.
 */
function hcotp_ask_for_review() {
	// Only show to admins who can install plugins.
	if ( ! current_user_can( 'install_plugins' ) ) {
		return;
	}

	// Check if the notice was dismissed.
	$dismissed = get_option( 'hcotp_review_notice_dismissed' );
	if ( $dismissed ) {
		return;
	}

	// Check when the plugin was activated.
	$activation_time = get_option( 'hcotp_activation_time' );
	if ( ! $activation_time ) {
		// If it's not set, set it now.
		update_option( 'hcotp_activation_time', time() );
		return;
	}

	// Wait 7 days before showing the notice.
	if ( time() < $activation_time + ( 7 * DAY_IN_SECONDS ) ) {
		return;
	}

	// Create the links.
	$review_url  = 'https://wordpress.org/support/plugin/happy-coders-otp-login/reviews/?filter=5#new-post';
	$support_url = 'https://wordpress.org/support/plugin/happy-coders-otp-login/';

	$base_dismiss_url = add_query_arg( 'hcotp_dismiss_review_notice', '1' );

	$dismiss_url = wp_nonce_url( $base_dismiss_url, 'hcotp-dismiss-review-action', 'hcotp_review_nonce' );

	?>
	<div class="notice notice-info is-dismissible">
		<p>
			<?php
			printf(
				/* translators: %s: Plugin name */
				esc_html__( 'Hey! You have been using %s for a while now. We hope you are enjoying it!', 'happy-coders-otp-login' ),
				'<strong>Happy Coders OTP Login</strong>'
			);
			?>
		</p>
		<p>
			<a href="<?php echo esc_url( $review_url ); ?>" class="button button-primary" target="_blank" style="margin-right:10px;"><?php esc_html_e( 'Yes, I\'ll leave a 5-star review!', 'happy-coders-otp-login' ); ?></a>
			<a href="<?php echo esc_url( $support_url ); ?>" class="button button-secondary" target="_blank" style="margin-right:10px;"><?php esc_html_e( 'I need some help first', 'happy-coders-otp-login' ); ?></a>
			<a href="<?php echo esc_url( $dismiss_url ); ?>" class="button button-secondary"><?php esc_html_e( 'No, thanks / I already did', 'happy-coders-otp-login' ); ?></a>
		</p>
	</div>
	<script>
	// Make the core dismiss button work with our logic.
	document.addEventListener('click', function(event) {
		if (event.target.matches('.notice-dismiss[data-hcotp-dismiss-url]')) {
			window.location.href = event.target.dataset.hcotpDismissUrl;
		}
	});
	</script>
	<?php
}
add_action( 'admin_notices', 'hcotp_ask_for_review' );

/**
 * Handle the dismissal of the review notice.
 */
function hcotp_handle_dismiss_review_notice() {
	if ( isset( $_GET['hcotp_dismiss_review_notice'] ) && '1' === $_GET['hcotp_dismiss_review_notice'] ) {
		if ( ! isset( $_GET['hcotp_review_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['hcotp_review_nonce'] ), 'hcotp-dismiss-review-action' ) ) {
			// Nonce is missing or invalid. Stop execution and show an error.
			wp_die( esc_html__( 'Security check failed. Please try again.', 'happy-coders-otp-login' ), 'Security Check', array( 'response' => 403 ) );
		}
		update_option( 'hcotp_review_notice_dismissed', true );
		wp_safe_redirect( remove_query_arg( array( 'hcotp_dismiss_review_notice', 'hcotp_review_nonce' ) ) );
		exit;
	}
}
add_action( 'admin_init', 'hcotp_handle_dismiss_review_notice' );

// Add this to your main plugin activation hook `hcotp_activate_plugin()`
// This ensures we have a timestamp to check against.
if ( false === get_option( 'hcotp_activation_time' ) ) {
	update_option( 'hcotp_activation_time', time() );
}
