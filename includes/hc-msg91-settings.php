<?php
/**
 * Settings page for the Happy Coders OTP Login plugin.
 *
 * @package happy-coders-otp-login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds the admin menu page for the plugin settings.
 */
function hcotp_add_admin_menu() {
	add_menu_page(
		__( 'MSG91 OTP & SMS Settings', 'happy-coders-otp-login' ),
		__( 'MSG91 OTP & SMS', 'happy-coders-otp-login' ),
		'manage_options',
		'msg91-otp-settings',
		'hcotp_settings_page',
		'dashicons-smartphone',
		56
	);
}
add_action( 'admin_menu', 'hcotp_add_admin_menu' );


/**
 * Enqueues scripts and styles for the admin settings page.
 *
 * @param string $hook The current admin page.
 */
function hcotp_admin_enqueue_scripts( $hook ) {
	if ( 'toplevel_page_hcotp-settings' !== $hook ) {
		return;
	}
	wp_enqueue_script( 'hcotp-admin-js', HCOTP_PLUGIN_URL . 'assets/js/hc-msg91-otp.js', array( 'jquery' ), time(), true );
	wp_enqueue_style( 'hcotp-admin-css', HCOTP_PLUGIN_URL . 'assets/css/hc-msg91-otp.css', array(), time() );
}
add_action( 'admin_enqueue_scripts', 'hcotp_admin_enqueue_scripts' );

add_action(
	'admin_init',
	function () {
		register_setting( 'hcotp_otp_settings_group', 'hcotp_whatsapp_auth_enabled', 'absint' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_whatsapp_integrated_number', 'sanitize_text_field' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_whatsapp_template_name', 'sanitize_text_field' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_whatsapp_template_namespace', 'sanitize_text_field' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_whatsapp_language_code', 'sanitize_text_field' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_whatsapp_button_text', 'sanitize_text_field' );

		register_setting( 'hcotp_otp_settings_group', 'hcotp_msg91_active_tab', 'sanitize_text_field' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_msg91_auth_key', 'sanitize_text_field' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_msg91_sender_id', 'sanitize_text_field' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_msg91_template_id', 'sanitize_text_field' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_msg91_resend_timer', 'intval' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_msg91_default_country', 'sanitize_text_field' );
		register_setting(
			'hcotp_otp_settings_group',
			'hcotp_msg91_selected_countries',
			function ( $input ) {
				return array_map( 'sanitize_text_field', (array) $input );
			}
		);
		register_setting( 'hcotp_otp_settings_group', 'hcotp_msg91_top_image', 'esc_url_raw' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_msg91_top_verify_image', 'esc_url_raw' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_msg91_perday_otplimit', 'intval' );
		register_setting(
			'hcotp_otp_settings_group',
			'hcotp_msg91_flag_show',
			function ( $value ) {
				return '1' === $value ? 1 : 0;
			}
		);
		register_setting( 'hcotp_otp_settings_group', 'hcotp_msg91_redirect_page', 'esc_url_raw' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_msg91_sendotp_lable', 'sanitize_text_field' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_msg91_sendotp_lable_color', 'sanitize_hex_color' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_msg91_sendotp_dec', 'sanitize_text_field' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_msg91_sendotp_dec_color', 'sanitize_hex_color' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_msg91_sendotp_validation_msg', 'sanitize_text_field' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_msg91_sendotp_button_text', 'sanitize_text_field' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_msg91_sendotp_button_color', 'sanitize_hex_color' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_msg91_verifyotp_lable', 'sanitize_text_field' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_msg91_verifyotp_lable_color', 'sanitize_hex_color' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_msg91_verifyotp_dec', 'sanitize_text_field' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_msg91_verifyotp_desc_color', 'sanitize_hex_color' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_msg91_verifyotp_validation_msg', 'sanitize_text_field' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_msg91_verifyotp_button_text', 'sanitize_text_field' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_msg91_verifyotp_button_color', 'sanitize_hex_color' );

		// Code for SMS added by Kombiah.
		$sms_event_types = array(
			'ncr' => 'New Customer Registration',
			'npo' => 'New Order Placed',
			'osh' => 'Order Shipped',
			'odl' => 'Order Delivered',
			'oac' => 'Order on Cart (Abandoned)',
		);

		foreach ( $sms_event_types as $key => $label ) {
			register_setting( 'hcotp_otp_settings_group', "hcotp_msg91_sms_{$key}_enable", 'absint' );
			register_setting( 'hcotp_otp_settings_group', "hcotp_msg91_sms_{$key}_template_id", 'sanitize_text_field' );
			register_setting( 'hcotp_otp_settings_group', "hcotp_msg91_sms_{$key}_notes", 'wp_kses_post' ); // Allows some HTML for notes.
			if ( 'osh' === $key || 'odl' === $key ) {
				register_setting( 'hcotp_otp_settings_group', "hcotp_msg91_sms_{$key}_status_slug", 'sanitize_text_field' );
			}
			if ( 'oac' === $key ) {
				register_setting( 'hcotp_otp_settings_group', "hcotp_msg91_sms_{$key}_delay_hours", 'hcotp_sanitize_positive_float' );
			}
		}
	}
);

/**
 * Sanitization callback for a positive float value.
 *
 * @param mixed $input The input to sanitize.
 * @return float The sanitized positive float.
 */
function hcotp_sanitize_positive_float( $input ) {
	$value = floatval( str_replace( ',', '.', $input ) );
	return ( $value > 0 ) ? $value : 0.01;
}

/**
 * Renders the settings page.
 */
function hcotp_settings_page() {
	$active_tab = get_option( 'hcotp_msg91_active_tab', 'otp_settings' );
	?>
	<div class="wrap" id="hcotp-settings-wrap"> 
		<h1><?php esc_html_e( 'Happy Coders MSG91 Settings', 'happy-coders-otp-login' ); ?></h1>

		<h2 class="nav-tab-wrapper">   
			<a href="#general_settings" class="nav-tab <?php echo 'general_settings' === $active_tab ? 'nav-tab-active' : ''; ?>" data-tab="general_settings"><?php esc_html_e( 'General Settings', 'happy-coders-otp-login' ); ?></a>        
			<a href="#otp_settings" class="nav-tab <?php echo 'otp_settings' === $active_tab ? 'nav-tab-active' : ''; ?>" data-tab="otp_settings"><?php esc_html_e( 'OTP Login Settings', 'happy-coders-otp-login' ); ?></a>
			<a href="#sms_settings" class="nav-tab <?php echo 'sms_settings' === $active_tab ? 'nav-tab-active' : ''; ?>" data-tab="sms_settings"><?php esc_html_e( 'Transactional SMS Settings', 'happy-coders-otp-login' ); ?></a>
			
		</h2>

		<form method="post" action="options.php">
			<?php settings_fields( 'hcotp_otp_settings_group' ); ?>
			<?php do_settings_sections( 'hcotp_otp_settings_group' ); ?>

			<input type="hidden" name="hcotp_msg91_active_tab" id="hcotp_msg91_active_tab_input" value="<?php echo esc_attr( $active_tab ); ?>">

			<div id="general_settings" class="tab-content <?php echo 'general_settings' === $active_tab ? 'active-tab' : ''; ?>">
				<h2><?php esc_html_e( 'MSG91 API Credentials', 'happy-coders-otp-login' ); ?></h2>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'MSG91 Auth Key', 'happy-coders-otp-login' ); ?></th>
						<td>
							<input type="text" name="hcotp_msg91_auth_key" value="<?php echo esc_attr( get_option( 'hcotp_msg91_auth_key' ) ); ?>" size="50" />
							<p class="description"><?php esc_html_e( 'Your MSG91 Authentication Key. Used for OTP and Transactional SMS.', 'happy-coders-otp-login' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Sender ID', 'happy-coders-otp-login' ); ?></th>
						<td>
							<input type="text" name="hcotp_msg91_sender_id" value="<?php echo esc_attr( get_option( 'hcotp_msg91_sender_id' ) ); ?>" size="30" />
							<p class="description"><?php esc_html_e( 'Your DLT Approved Sender ID. Used for OTP and Transactional SMS.', 'happy-coders-otp-login' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<?php esc_html_e( 'Enable WhatsApp OTP', 'happy-coders-otp-login' ); ?>
						</th>
						<td>
							<label>
								<input type="checkbox" id="hcotp_whatsapp_auth_checkbox" name="hcotp_whatsapp_auth_enabled" value="1" <?php checked( get_option( 'hcotp_whatsapp_auth_enabled' ), 1 ); ?> />
								<?php esc_html_e( 'Yes, send OTP to users through WhatsApp.', 'happy-coders-otp-login' ); ?>
							</label>
							<div id="hcotp_whatsapp_auth_inputs" style="margin-top: 10px; <?php echo get_option( 'hcotp_whatsapp_auth_enabled' ) ? '' : 'display:none;'; ?>">
								<p class="lable-input-between">
									<label ><strong >Integrated Number:</strong><br>
										<input type="text" class="input-top" size="50" name="hcotp_whatsapp_integrated_number" value="<?php echo esc_attr( get_option( 'hcotp_whatsapp_integrated_number' ) ); ?>" class="regular-text" />
									</label>
								</p>
								<p class="lable-input-between"> 
									<label style="margin-bottom: 12px;"><strong>Template Name:</strong><br>
										<input type="text" class="input-top"  size="50" name="hcotp_whatsapp_template_name" value="<?php echo esc_attr( get_option( 'hcotp_whatsapp_template_name' ) ); ?>" class="regular-text" />
									</label>
								</p> 
								<p class="lable-input-between">
									<label style="margin-bottom: 12px;"><strong>Template Namespace:</strong><br>
										<input type="text" class="input-top"  size="50" name="hcotp_whatsapp_template_namespace" value="<?php echo esc_attr( get_option( 'hcotp_whatsapp_template_namespace' ) ); ?>" class="regular-text" />
									</label>
								</p>
								<p class="lable-input-between">
									<label style="margin-bottom: 12px;"><strong>Language Code:</strong><br>
										<input type="text"class="input-top"  size="50" name="hcotp_whatsapp_language_code" value="<?php echo esc_attr( get_option( 'hcotp_whatsapp_language_code' ) ); ?>" class="regular-text" />
									</label>
								</p>

								<p class="lable-input-between">
									<label style="margin-bottom: 12px;"><strong>Button Text (Example : Send OTP via Whatsapp)</strong><br>
										<input type="text"class="input-top"  size="50" name="hcotp_whatsapp_button_text" value="<?php echo esc_attr( get_option( 'hcotp_whatsapp_button_text' ) ); ?>" class="regular-text" />
									</label>
								</p>
							</div>
						</td>
					</tr>
				</table>
			</div>


			<div id="otp_settings" class="tab-content <?php echo 'otp_settings' === $active_tab ? 'active-tab' : ''; ?>">
				<h2><?php esc_html_e( 'OTP Login Settings', 'happy-coders-otp-login' ); ?></h2>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Template ID', 'happy-coders-otp-login' ); ?></th>
						<td><input type="text" name="hcotp_msg91_template_id" value="<?php echo esc_attr( get_option( 'hcotp_msg91_template_id' ) ); ?>" size="30" />
						<p class="description"><?php esc_html_e( 'MSG91 DLT Template ID for sending OTPs.', 'happy-coders-otp-login' ); ?></p></td>
						
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'User OTP Limit per day', 'happy-coders-otp-login' ); ?></th>
						<td><input type="number" name="hcotp_msg91_perday_otplimit" value="<?php echo esc_attr( get_option( 'hcotp_msg91_perday_otplimit' ) ); ?>" size="30" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Resend OTP Timer (sec)', 'happy-coders-otp-login' ); ?></th>
						<td>
							<input type="number" name="hcotp_msg91_resend_timer" 
								value="<?php echo esc_attr( get_option( 'hcotp_msg91_resend_timer', 60 ) ); ?>" size="30" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Popup Login', 'happy-coders-otp-login' ); ?></th>
						<td> 
							<p >
								<?php esc_html_e( 'You can also use the class name', 'happy-coders-otp-login' ); ?> 
								<code style="font-size:16px; color:#0073aa;">otp-popup-trigger</code> 
								<?php esc_html_e( 'to trigger the popup on any element', 'happy-coders-otp-login' ); ?>.
							</p>
						</td>
					</tr>


					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Screen based Login', 'happy-coders-otp-login' ); ?></th>
						<td>
							<p style="margin: 0 0 5px;">
								<?php esc_html_e( 'Use this shortcode to display the screen OTP-based login form', 'happy-coders-otp-login' ); ?>
							</p>
							<code id="msg91-shortcode" style="font-size:16px; color:#0073aa;">
								<?php echo '[msg91_otp_form]'; ?>
							</code>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Image URL for Send OTP Form', 'happy-coders-otp-login' ); ?></th>
						<td>
							<input type="text" name="hcotp_msg91_top_image" value="<?php echo esc_attr( get_option( 'hcotp_msg91_top_image', HCOTP_PLUGIN_URL . 'assets/images/send-otp.png' ) ); ?>" size="60" />
							<p class="description"><?php esc_html_e( 'Paste the full image URL to display above the OTP form (e.g. banner, logo).', 'happy-coders-otp-login' ); ?></p>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Send OTP Form Lable', 'happy-coders-otp-login' ); ?></th>
						<td><input type="text" name="hcotp_msg91_sendotp_lable" value="<?php echo esc_attr( get_option( 'hcotp_msg91_sendotp_lable' ) ); ?>" size="50" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Send OTP Lable Color', 'happy-coders-otp-login' ); ?></th>
						<td><input type="color" name="hcotp_msg91_sendotp_lable_color" value="<?php echo esc_attr( get_option( 'hcotp_msg91_sendotp_lable_color' ) ); ?>" size="30" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Send OTP Form Decription', 'happy-coders-otp-login' ); ?></th>
						<td><input type="text" name="hcotp_msg91_sendotp_dec" value="<?php echo esc_attr( get_option( 'hcotp_msg91_sendotp_dec' ) ); ?>" size="50" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Send OTP Decription Color', 'happy-coders-otp-login' ); ?></th>
						<td><input type="color" name="hcotp_msg91_sendotp_dec_color" value="<?php echo esc_attr( get_option( 'hcotp_msg91_sendotp_dec_color' ) ); ?>" size="30" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Send OTP Form Button Text', 'happy-coders-otp-login' ); ?></th>
						<td><input type="text" name="hcotp_msg91_sendotp_button_text" value="<?php echo esc_attr( get_option( 'hcotp_msg91_sendotp_button_text' ) ); ?>" size="50" /></td>
					</tr>


					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Send OTP Button Color', 'happy-coders-otp-login' ); ?></th>
						<td><input type="color" name="hcotp_msg91_sendotp_button_color" value="<?php echo esc_attr( get_option( 'hcotp_msg91_sendotp_button_color' ) ); ?>" size="30" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Send OTP Form validation msg', 'happy-coders-otp-login' ); ?></th>
						<td><input type="text" name="hcotp_msg91_sendotp_validation_msg" value="<?php echo esc_attr( get_option( 'hcotp_msg91_sendotp_validation_msg' ) ); ?>" size="50" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Image URL for Verify OTP Form', 'happy-coders-otp-login' ); ?></th>
						<td>
							<input type="text" name="hcotp_msg91_top_verify_image" value="<?php echo esc_attr( get_option( 'hcotp_msg91_top_verify_image', HCOTP_PLUGIN_URL . 'assets/images/verify-otp.png' ) ); ?>" size="60" />
							<p class="description"><?php esc_html_e( 'Paste the full image URL to display above the OTP form (e.g. banner, logo).', 'happy-coders-otp-login' ); ?></p>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Verify OTP Form Lable', 'happy-coders-otp-login' ); ?></th>
						<td><input type="text" name="hcotp_msg91_verifyotp_lable" value="<?php echo esc_attr( get_option( 'hcotp_msg91_verifyotp_lable' ) ); ?>" size="50" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Verify OTP Lable Color', 'happy-coders-otp-login' ); ?></th>
						<td><input type="color" name="hcotp_msg91_verifyotp_lable_color" value="<?php echo esc_attr( get_option( 'hcotp_msg91_verifyotp_lable_color' ) ); ?>" size="30" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Verify OTP Form Decription', 'happy-coders-otp-login' ); ?></th>
						<td><input type="text" name="hcotp_msg91_verifyotp_dec" value="<?php echo esc_attr( get_option( 'hcotp_msg91_verifyotp_dec' ) ); ?>" size="50" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Verify OTP Decription Color', 'happy-coders-otp-login' ); ?></th>
						<td><input type="color" name="hcotp_msg91_verifyotp_dec_color" value="<?php echo esc_attr( get_option( 'hcotp_msg91_verifyotp_dec_color' ) ); ?>" size="30" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Verify OTP Form Button Text', 'happy-coders-otp-login' ); ?></th>
						<td><input type="text" name="hcotp_msg91_verifyotp_button_text" value="<?php echo esc_attr( get_option( 'hcotp_msg91_verifyotp_button_text' ) ); ?>" size="50" /></td>
					</tr>

				
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Verify OTP Button Color', 'happy-coders-otp-login' ); ?></th>
						<td><input type="color" name="hcotp_msg91_verifyotp_button_color" value="<?php echo esc_attr( get_option( 'hcotp_msg91_verifyotp_button_color' ) ); ?>" size="30" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Verify OTP Form validation msg', 'happy-coders-otp-login' ); ?></th>
						<td><input type="text" name="hcotp_msg91_verifyotp_validation_msg" value="<?php echo esc_attr( get_option( 'hcotp_msg91_verifyotp_validation_msg' ) ); ?>" size="50" /></td>
					</tr>


					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Redirect Page URL', 'happy-coders-otp-login' ); ?></th>
						<td>
							<input type="text" name="hcotp_msg91_redirect_page" 
								value="<?php echo esc_attr( get_option( 'hcotp_msg91_redirect_page', home_url() ) ); ?>" 
								size="60" />
							<p class="description"><?php esc_html_e( 'Enter the URL where users should be redirected after a successful login.', 'happy-coders-otp-login' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Default Country', 'happy-coders-otp-login' ); ?></th>
						<td>
							<select name="hcotp_msg91_default_country" id="hcotp_msg91_default_country">
								<?php
									$default_country = get_option( 'hcotp_msg91_default_country', '+91' );
									$countries       = hcotp_get_countries_with_iso();
								foreach ( $countries as $country ) {
									printf(
										'<option value="%s" %s>%s %s (%s)</option>',
										esc_attr( $country['code'] ),
										selected( $default_country, $country['code'], false ),
										esc_html( hcotp_iso_to_flag( $country['iso'] ) ),
										esc_html( $country['name'] ),
										esc_html( $country['code'] )
									);
								}
								?>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"></th>
						<td>
							<input type="checkbox" name="hcotp_msg91_flag_show" value="1" <?php checked( 1, get_option( 'hcotp_msg91_flag_show' ), true ); ?> />
							<span class="description"><?php esc_html_e( 'Show country flag in dropdown?', 'happy-coders-otp-login' ); ?></span>

						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Select countries', 'happy-coders-otp-login' ); ?></th>
						<td>
							<select name="hcotp_msg91_selected_countries[]" id="hcotp_msg91_selected_countries" multiple="multiple" style="width: 100%; height: 150px;">
								<?php
									$selected_countries = get_option( 'hcotp_msg91_selected_countries', array( '+91' ) );
									$countries          = hcotp_get_countries_with_iso();
								foreach ( $countries as $country ) {
									printf(
										'<option value="%s" %s>%s %s (%s)</option>',
										esc_attr( $country['code'] ),
										in_array( $country['code'], $selected_countries, true ) ? 'selected' : '',
										esc_html( hcotp_iso_to_flag( $country['iso'] ) ),
										esc_html( $country['name'] ),
										esc_html( $country['code'] )
									);
								}
								?>
							</select>
						</td>
					</tr>
				</table>
			</div>

			<div id="sms_settings" class="tab-content <?php echo 'sms_settings' === $active_tab ? 'active-tab' : ''; ?>">

				<!-- Code for Transactional SMS Notifications by Kombiah -->
				<h2><?php esc_html_e( 'Transactional SMS Notifications (WooCommerce)', 'happy-coders-otp-login' ); ?></h2>
				<p><?php esc_html_e( 'Configure SMS notifications for various events. You need to create corresponding Flow templates in your MSG91 dashboard and provide the Flow ID here as Template ID.', 'happy-coders-otp-login' ); ?></p>
				<p><?php esc_html_e( 'The plugin will pass predefined variables to MSG91 (e.g., VAR1, VAR2). Please refer to the plugin documentation for the list of variables available for each SMS type.', 'happy-coders-otp-login' ); ?></p>

				<?php
				$sms_event_types = array(
					'ncr' => __( 'New Customer Registration', 'happy-coders-otp-login' ),
					'npo' => __( 'New Order Placed', 'happy-coders-otp-login' ),
					'osh' => __( 'Order Shipped', 'happy-coders-otp-login' ),
					'odl' => __( 'Order Delivered', 'happy-coders-otp-login' ),
					'oac' => __( 'Abandoned Cart', 'happy-coders-otp-login' ),
				);

				foreach ( $sms_event_types as $key => $label ) :
					$enable_option      = "msg91_sms_{$key}_enable";
					$template_id_option = "msg91_sms_{$key}_template_id";
					$notes_option       = "msg91_sms_{$key}_notes";
					?>
				<hr>
				<h3><?php echo esc_html( $label ); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Enable SMS', 'happy-coders-otp-login' ); ?></th>
						<td>
							<label><input type="checkbox" name="<?php echo esc_attr( $enable_option ); ?>" value="1" <?php checked( 1, get_option( $enable_option, 0 ) ); ?> />
							<?php
							/* translators: %s: event label (e.g. New Order Placed) */
							printf( esc_html__( 'Send SMS for: %s', 'happy-coders-otp-login' ), esc_html( $label ) );
							?>
							</label>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="<?php echo esc_attr( $template_id_option ); ?>"><?php esc_html_e( 'MSG91 Flow/Template ID', 'happy-coders-otp-login' ); ?></label></th>
						<td>
							<input type="text" name="<?php echo esc_attr( $template_id_option ); ?>" value="<?php echo esc_attr( get_option( $template_id_option ) ); ?>" size="40" />
							<p class="description"><?php esc_html_e( 'Enter the Flow ID from your MSG91 panel for this event.', 'happy-coders-otp-login' ); ?></p>
						</td>
					</tr>
					<?php
					if ( 'osh' === $key || 'odl' === $key ) :
						$status_slug_option = "msg91_sms_{$key}_status_slug";
						$default_slug       = ( 'osh' === $key ) ? 'shipped' : 'delivered';
						?>
					<tr valign="top">
						<th scope="row"><label for="<?php echo esc_attr( $status_slug_option ); ?>"><?php esc_html_e( 'Target Order Status Slug', 'happy-coders-otp-login' ); ?></label></th>
						<td>
							<input type="text" name="<?php echo esc_attr( $status_slug_option ); ?>" value="<?php echo esc_attr( get_option( $status_slug_option, $default_slug ) ); ?>" size="30" />
							<p class="description"><?php esc_html_e( 'The WooCommerce order status slug that triggers this SMS (e.g. "shipped", "wc-completed").', 'happy-coders-otp-login' ); ?></p>
						</td>
					</tr>
					<?php endif; ?>
					<?php
					if ( 'oac' === $key ) :
						$delay_option = "msg91_sms_{$key}_delay_hours";
						?>
					<tr valign="top">
						<th scope="row"><label for="<?php echo esc_attr( $delay_option ); ?>"><?php esc_html_e( 'Abandonment Delay (Hours)', 'happy-coders-otp-login' ); ?></label></th>
						<td>
							<input type="number" name="<?php echo esc_attr( $delay_option ); ?>" value="<?php echo esc_attr( get_option( $delay_option, 1 ) ); ?>"  min="0.01" step="0.01" size="5" lang="en" />
							<p class="description"><?php esc_html_e( 'Delay in hours (e.g., 1 for 1 hour, 0.5 for 30 minutes). Affects logged-in users.', 'happy-coders-otp-login' ); ?></p>
						</td>
					</tr>
					<?php endif; ?>
					<tr valign="top">
						<th scope="row"><label for="<?php echo esc_attr( $notes_option ); ?>"><?php esc_html_e( 'Template Notes / Variables', 'happy-coders-otp-login' ); ?></label></th>
						<td>
							<textarea name="<?php echo esc_attr( $notes_option ); ?>" rows="3" cols="50" class="large-text"><?php echo esc_textarea( get_option( $notes_option ) ); ?></textarea>							
							<p class="description"><?php esc_html_e( 'For your reference. Paste your MSG91 template content here or add notes about variables used.', 'happy-coders-otp-login' ); ?></p>
						</td>
					</tr>
				</table>
				<?php endforeach; ?>
			</div>
			<?php submit_button(); ?>
		</form>
	</div>
	
	<?php
}

?>
