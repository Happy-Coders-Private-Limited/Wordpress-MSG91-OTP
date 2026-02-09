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
	if ( 'toplevel_page_msg91-otp-settings' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script( 'hcotp-admin-tabs', HCOTP_PLUGIN_URL . 'assets/js/hc-msg91-otp.js', array( 'jquery' ), time(), true );
	wp_enqueue_script( 'hcotp-admin-js', HCOTP_PLUGIN_URL . 'assets/js/hcotp-admin.js', array( 'jquery', 'hcotp-admin-tabs' ), time(), true );
	wp_enqueue_style( 'hcotp-admin-css', HCOTP_PLUGIN_URL . 'assets/css/hc-msg91-otp.css', array(), time() );
}
add_action( 'admin_enqueue_scripts', 'hcotp_admin_enqueue_scripts' );

/**
 * Sanitizes HTML email templates while allowing common email markup.
 *
 * @param string $input Raw HTML.
 * @return string
 */
function hcotp_sanitize_email_template_html( $input ) {
	$allowed = wp_kses_allowed_html( 'post' );

	$allowed['html']  = array( 'lang' => true );
	$allowed['head']  = array();
	$allowed['body']  = array( 'style' => true );
	$allowed['meta']  = array(
		'charset' => true,
		'name'    => true,
		'content' => true,
	);
	$allowed['title'] = array();

	$allowed['table'] = array(
		'role'        => true,
		'width'       => true,
		'cellspacing' => true,
		'cellpadding' => true,
		'border'      => true,
		'align'       => true,
		'style'       => true,
	);
	$allowed['tbody'] = array();
	$allowed['thead'] = array();
	$allowed['tfoot'] = array();
	$allowed['tr']    = array( 'style' => true );
	$allowed['td']    = array(
		'width'  => true,
		'align'  => true,
		'valign' => true,
		'style'  => true,
	);
	$allowed['th']    = array(
		'width'  => true,
		'align'  => true,
		'valign' => true,
		'style'  => true,
	);

	$allowed['div']['style']  = true;
	$allowed['span']['style'] = true;
	$allowed['p']['style']    = true;
	$allowed['a']['style']    = true;
	$allowed['img']['style']  = true;

	return wp_kses( $input, $allowed );
}

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
		register_setting( 'hcotp_otp_settings_group', 'hcotp_email_sendotp_label', 'sanitize_text_field' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_email_sendotp_label_color', 'sanitize_hex_color' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_email_sendotp_desc', 'sanitize_text_field' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_email_sendotp_desc_color', 'sanitize_hex_color' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_email_sendotp_button_text', 'sanitize_text_field' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_email_sendotp_button_color', 'sanitize_hex_color' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_email_verifyotp_lable', 'sanitize_text_field' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_email_verifyotp_lable_color', 'sanitize_hex_color' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_email_verifyotp_desc', 'sanitize_text_field' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_email_verifyotp_desc_color', 'sanitize_hex_color' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_email_verifyotp_buttontext', 'sanitize_text_field' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_email_verifyotp_button_color', 'sanitize_hex_color' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_email_verifyotp_validation_msg', 'sanitize_text_field' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_email_resend_timer', 'intval' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_email_top_image', 'esc_url_raw' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_email_top_verify_image', 'esc_url_raw' );
		register_setting( 'hcotp_otp_settings_group', 'hcotp_email_perday_otplimit', 'intval' );
		register_setting(
			'hcotp_otp_settings_group',
			'hcotp_msg91_otp_length',
			function ( $value ) {
				$value = intval( $value );
				return in_array( $value, array( 4, 6 ), true ) ? $value : 4;
			}
		);
		register_setting(
			'hcotp_otp_settings_group',
			'hcotp_email_otp_enabled',
			function ( $value ) {
				return '1' === $value ? 1 : 0;
			}
		);
		register_setting(
			'hcotp_otp_settings_group',
			'hcotp_email_otp_length',
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 6,
			)
		);
		register_setting(
			'hcotp_otp_settings_group',
			'hcotp_email_otp_expiry',
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 5,
			)
		);
		register_setting(
			'hcotp_otp_settings_group',
			'hcotp_force_email_after_login',
			array(
				'type'              => 'boolean',
				'sanitize_callback' => 'absint',
				'default'           => 1,
			)
		);
		register_setting(
			'hcotp_otp_settings_group',
			'hcotp_email_otp_subject',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);
		register_setting(
			'hcotp_otp_settings_group',
			'hcotp_email_otp_header_image',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'esc_url_raw',
				'default'           => '',
			)
		);
		register_setting(
			'hcotp_otp_settings_group',
			'hcotp_email_otp_header_image_width',
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 200,
			)
		);
		register_setting(
			'hcotp_otp_settings_group',
			'hcotp_email_otp_header_image_height',
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 0,
			)
		);
		register_setting(
			'hcotp_otp_settings_group',
			'hcotp_email_otp_footer_image',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'esc_url_raw',
				'default'           => '',
			)
		);
		register_setting(
			'hcotp_otp_settings_group',
			'hcotp_email_otp_footer_image_width',
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 200,
			)
		);
		register_setting(
			'hcotp_otp_settings_group',
			'hcotp_email_otp_footer_image_height',
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 0,
			)
		);
		register_setting(
			'hcotp_otp_settings_group',
			'hcotp_email_otp_body',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'wp_kses_post',
				'default'           => '',
			)
		);
		register_setting(
			'hcotp_otp_settings_group',
			'hcotp_email_template_choice',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'template_1',
			)
		);
		register_setting(
			'hcotp_otp_settings_group',
			'hcotp_email_template_html_1',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'hcotp_sanitize_email_template_html',
				'default'           => '',
			)
		);
		register_setting(
			'hcotp_otp_settings_group',
			'hcotp_email_template_html_2',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'hcotp_sanitize_email_template_html',
				'default'           => '',
			)
		);
		register_setting(
			'hcotp_otp_settings_group',
			'hcotp_email_template_html_3',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'hcotp_sanitize_email_template_html',
				'default'           => '',
			)
		);
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
			register_setting( 'hcotp_otp_settings_group', "hcotp_msg91_sms_{$key}_notes", 'wp_kses_post' );
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
			<a href="#email_settings" class="nav-tab <?php echo 'email_settings' === $active_tab ? 'nav-tab-active' : ''; ?>" data-tab="email_settings"><?php esc_html_e( 'Email OTP', 'happy-coders-otp-login' ); ?></a>
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
						<th scope="row"><?php esc_html_e( 'OTP Length', 'happy-coders-otp-login' ); ?></th>
						<td>
							<select name="hcotp_msg91_otp_length">
								<option value="4" <?php selected( get_option( 'hcotp_msg91_otp_length', 4 ), 4 ); ?>>
									<?php esc_html_e( '4 Digits (Default)', 'happy-coders-otp-login' ); ?>
								</option>
								<option value="6" <?php selected( get_option( 'hcotp_msg91_otp_length', 4 ), 6 ); ?>>
									<?php esc_html_e( '6 Digits', 'happy-coders-otp-login' ); ?>
								</option>
							</select>
							<p class="description">
							<?php esc_html_e( 'Make sure your OTP length is either 4 or 6 digits.', 'happy-coders-otp-login' ); ?>
						</p>
					
						</td>
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
							<span class="description"><?php esc_html_e( 'Show country code in dropdown?', 'happy-coders-otp-login' ); ?></span>

						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Select countries', 'happy-coders-otp-login' ); ?></th>
						<td>
							<select name="hcotp_msg91_selected_countries[]" id="hcotp_msg91_selected_countries" multiple="multiple" style="width: 100%; height: 150px;">
								<?php
									$selected_countries = get_option( 'hcotp_msg91_selected_countries', array( '+91' ) );
									$default_country    = get_option( 'hcotp_msg91_default_country', '+91' );
									$countries          = hcotp_get_countries_with_iso();

								if ( ! is_array( $selected_countries ) ) {
									$selected_countries = array( '+91' );
								}

								if ( ! in_array( $default_country, $selected_countries, true ) ) {
									$selected_countries[] = $default_country;
								}
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
				<p><?php esc_html_e( 'The plugin will pass predefined variables to MSG91. Available variables: ##customer_name##, ##order_id##, ##site_name##, ##tracking_id##, ##tracking_url##, ##cart_items_count##, ##cart_total##.', 'happy-coders-otp-login' ); ?></p>

				<?php
				$sms_event_types = array(
					'ncr' => __( 'New Customer Registration', 'happy-coders-otp-login' ),
					'npo' => __( 'New Order Placed', 'happy-coders-otp-login' ),
					'osh' => __( 'Order Shipped', 'happy-coders-otp-login' ),
					'odl' => __( 'Order Delivered', 'happy-coders-otp-login' ),
					'oac' => __( 'Abandoned Cart', 'happy-coders-otp-login' ),
				);

				// Define default message templates for display in settings.
				$default_message_templates = array(
					'ncr' => 'Hi ##customer_name##, Welcome to ##site_name##!',
					'npo' => 'Hi ##customer_name##, Thank you for choosing ##site_name##! Your order has been confirmed. Your order ID is ##order_id##.',
					'osh' => 'Hi ##customer_name##, Your order ##order_id## has been shipped! Tracking ID: ##tracking_id##. Track here: ##tracking_url##',
					'odl' => 'Hi ##customer_name##, Your order ##order_id## has been delivered! Thank you for shopping with us.',
					'oac' => 'Hi ##customer_name##, You left items in your cart! ##cart_items_count## items worth ##cart_total##. Complete your order now!',
				);

				foreach ( $sms_event_types as $key => $label ) :
					$enable_option            = "hcotp_msg91_sms_{$key}_enable";
					$template_id_option       = "hcotp_msg91_sms_{$key}_template_id";
					$notes_option             = "hcotp_msg91_sms_{$key}_notes";
					$sample_message           = $default_message_templates[ $key ];
					$current_message_template = get_option( $notes_option );
					$current_message_template = ! empty( $current_message_template )
						? $current_message_template
						: $default_message_templates[ $key ];
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
					<tr valign="top">
						<th scope="row"><label for="<?php echo esc_attr( $notes_option ); ?>"><?php esc_html_e( 'SMS Message Template', 'happy-coders-otp-login' ); ?></label></th>
						<td>
							<textarea name="<?php echo esc_attr( $notes_option ); ?>" rows="3" cols="50" class="large-text"><?php echo esc_textarea( $current_message_template ); ?></textarea>
							<p class="description">
								<?php
								printf(
									/* translators: %s: sample message */
									esc_html__( 'Use ##variable_name## for dynamic content. E.g. %s', 'happy-coders-otp-login' ),
									esc_html( $sample_message )
								);
								?>
							</p>
						</td>
					</tr>
					<?php
					if ( 'osh' === $key || 'odl' === $key ) :
						$status_slug_option = "hcotp_msg91_sms_{$key}_status_slug";
						$default_slug       = ( 'osh' === $key ) ? 'shipped' : 'delivered';
						?>
					<tr valign="top">
						<th scope="row"><label for="<?php echo esc_attr( $status_slug_option ); ?>"><?php esc_html_e( 'Target Order Status Slug', 'happy-coders-otp-login' ); ?></label></th>
						<td>
							<input type="text" name="<?php echo esc_attr( $status_slug_option ); ?>" value="<?php echo esc_attr( get_option( $status_slug_option, $default_slug ) ); ?>" size="30" />
							<p class="description"><?php esc_html_e( 'The WooCommerce order status slug that triggers this SMS (e.g. "shipped", "delivered", "completed").', 'happy-coders-otp-login' ); ?></p>
						</td>
					</tr>
					<?php endif; ?>
					<?php
					if ( 'oac' === $key ) :
						$delay_option = "hcotp_msg91_sms_{$key}_delay_hours";
						?>
					<tr valign="top">
						<th scope="row"><label for="<?php echo esc_attr( $delay_option ); ?>"><?php esc_html_e( 'Abandonment Delay (Hours)', 'happy-coders-otp-login' ); ?></label></th>
						<td>
							<input type="number" name="<?php echo esc_attr( $delay_option ); ?>" value="<?php echo esc_attr( get_option( $delay_option, 1 ) ); ?>"  min="0.01" step="0.01" size="5" lang="en" />
							<p class="description"><?php esc_html_e( 'Delay in hours (e.g., 1 for 1 hour, 0.5 for 30 minutes). Affects logged-in users.', 'happy-coders-otp-login' ); ?></p>
						</td>
					</tr>
					<?php endif; ?>
				</table>
				<?php endforeach; ?>
			</div>
			
			<div id="email_settings" class="tab-content <?php echo 'email_settings' === $active_tab ? 'active-tab' : ''; ?>">

				<h2><?php esc_html_e( 'Email OTP Settings', 'happy-coders-otp-login' ); ?></h2>

				<table class="form-table" role="presentation">

				<tr>
					<th scope="row"><?php esc_html_e( 'Enable Email OTP Login', 'happy-coders-otp-login' ); ?></th>
					<td>
						<input type="checkbox" name="hcotp_email_otp_enabled" value="1" <?php checked( 1, get_option( 'hcotp_email_otp_enabled' ), true ); ?> />
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'OTP Length', 'happy-coders-otp-login' ); ?></th>
					<td>
						<select name="hcotp_email_otp_length">
							<option value="4" <?php selected( get_option( 'hcotp_email_otp_length', 4 ), 4 ); ?>>
								<?php esc_html_e( '4 Digits (Default)', 'happy-coders-otp-login' ); ?>
							</option>
							<option value="6" <?php selected( get_option( 'hcotp_email_otp_length', 4 ), 6 ); ?>>
								<?php esc_html_e( '6 Digits', 'happy-coders-otp-login' ); ?>
							</option>
						</select>
						<p class="description">
						<?php esc_html_e( 'Make sure your OTP length is either 4 or 6 digits.', 'happy-coders-otp-login' ); ?>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'OTP Expiry (minutes)', 'happy-coders-otp-login' ); ?></th>
					<td>
						<input type="number" min="1" max="30"
							name="hcotp_email_otp_expiry"
							value="<?php echo esc_attr( get_option( 'hcotp_email_otp_expiry', 5 ) ); ?>" />
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'User OTP Limit per day', 'happy-coders-otp-login' ); ?></th>
					<td>
						<input type="number" name="hcotp_email_perday_otplimit" value="<?php echo esc_attr( get_option( 'hcotp_email_perday_otplimit' ) ); ?>" size="30" />
						<p class="description"><?php esc_html_e( 'Set to 0 for unlimited OTP requests.', 'happy-coders-otp-login' ); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Resend OTP Timer (sec)', 'happy-coders-otp-login' ); ?></th>
					<td>
						<input type="number" name="hcotp_email_resend_timer" 
							value="<?php echo esc_attr( get_option( 'hcotp_email_resend_timer', 60 ) ); ?>" size="30" />
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Force Email After Login', 'happy-coders-otp-login' ); ?></th>
					<td>
						<input type="checkbox" name="hcotp_force_email_after_login" value="1"
							<?php checked( 1, get_option( 'hcotp_force_email_after_login' ) ); ?> />
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Email OTP Label', 'happy-coders-otp-login' ); ?></th>
					<td>
						<input type="text" class="regular-text"
							name="hcotp_email_sendotp_label"
							value="<?php echo esc_attr( get_option( 'hcotp_email_sendotp_label', 'Email Address' ) ); ?>" />
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Email OTP Label Color', 'happy-coders-otp-login' ); ?></th>
					<td>
						<input type="color"
							name="hcotp_email_sendotp_label_color"
							value="<?php echo esc_attr( get_option( 'hcotp_email_sendotp_label_color', '#000000' ) ); ?>" />
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Email OTP Description', 'happy-coders-otp-login' ); ?></th>
					<td>
						<input type="text" class="regular-text"
							name="hcotp_email_sendotp_desc"
							value="<?php echo esc_attr( get_option( 'hcotp_email_sendotp_desc', 'We will send an OTP to your email' ) ); ?>" />
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Email OTP Description Color', 'happy-coders-otp-login' ); ?></th>
					<td>
						<input type="color"
							name="hcotp_email_sendotp_desc_color"
							value="<?php echo esc_attr( get_option( 'hcotp_email_sendotp_desc_color', '#000000' ) ); ?>" />
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Email OTP Button Text', 'happy-coders-otp-login' ); ?></th>
					<td>
						<input type="text" class="regular-text"
							name="hcotp_email_sendotp_button_text"
							value="<?php echo esc_attr( get_option( 'hcotp_email_sendotp_button_text', 'Send Email OTP' ) ); ?>" />
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Email OTP Button Color', 'happy-coders-otp-login' ); ?></th>
					<td>
						<input type="color"
							name="hcotp_email_sendotp_button_color"
							value="<?php echo esc_attr( get_option( 'hcotp_email_sendotp_button_color', '#2271b1' ) ); ?>" />
					</td>
				</tr>

				<tr>
					<th><?php esc_html_e( 'Send OTP Top Image', 'happy-coders-otp-login' ); ?></th>
					<td>
						<input type="text"
							name="hcotp_email_top_image"
							value="<?php echo esc_attr( get_option( 'hcotp_email_top_image', HCOTP_PLUGIN_URL . 'assets/images/email-send-otp.png' ) ); ?>"
							class="regular-text">
						<p class="description"><?php esc_html_e( 'Image shown on email OTP send screen.', 'happy-coders-otp-login' ); ?></p>
					</td>
				</tr>

				<tr>
					<th><?php esc_html_e( 'Verify OTP Top Image', 'happy-coders-otp-login' ); ?></th>
					<td>
						<input type="text"
							name="hcotp_email_top_verify_image"
							value="<?php echo esc_attr( get_option( 'hcotp_email_top_verify_image', HCOTP_PLUGIN_URL . 'assets/images/email-verify-otp.png' ) ); ?>"
							class="regular-text">
						<p class="description"><?php esc_html_e( 'Image shown on email OTP verification screen.', 'happy-coders-otp-login' ); ?></p>
					</td>
				</tr>

				<tr>
					<th><?php esc_html_e( 'Verify OTP Label', 'happy-coders-otp-login' ); ?></th>
					<td>
						<input type="text"
							name="hcotp_email_verifyotp_lable"
							value="<?php echo esc_attr( get_option( 'hcotp_email_verifyotp_lable', 'Enter OTP' ) ); ?>"
							class="regular-text">
					</td>
				</tr>

				<tr>
					<th><?php esc_html_e( 'Verify Label Color', 'happy-coders-otp-login' ); ?></th>
					<td>
						<input type="color"
							name="hcotp_email_verifyotp_lable_color"
							value="<?php echo esc_attr( get_option( 'hcotp_email_verifyotp_lable_color', '#000000' ) ); ?>">
					</td>
				</tr>

				<tr>
					<th><?php esc_html_e( 'Verify OTP Description', 'happy-coders-otp-login' ); ?></th>
					<td>
						<input type="text"
							name="hcotp_email_verifyotp_desc"
							value="<?php echo esc_attr( get_option( 'hcotp_email_verifyotp_desc', 'Enter the OTP sent to your email' ) ); ?>"
							class="regular-text">
					</td>
				</tr>

				<tr>
					<th><?php esc_html_e( 'Verify Description Color', 'happy-coders-otp-login' ); ?></th>
					<td>
						<input type="color"
							name="hcotp_email_verifyotp_desc_color"
							value="<?php echo esc_attr( get_option( 'hcotp_email_verifyotp_desc_color', '#666666' ) ); ?>">
					</td>
				</tr>

				<tr>
					<th><?php esc_html_e( 'Verify Button Text', 'happy-coders-otp-login' ); ?></th>
					<td>
						<input type="text"
							name="hcotp_email_verifyotp_buttontext"
							value="<?php echo esc_attr( get_option( 'hcotp_email_verifyotp_buttontext', 'Verify OTP' ) ); ?>"
							class="regular-text">
					</td>
				</tr>

				<tr>
					<th><?php esc_html_e( 'Verify Button Color', 'happy-coders-otp-login' ); ?></th>
					<td>
						<input type="color"
							name="hcotp_email_verifyotp_button_color"
							value="<?php echo esc_attr( get_option( 'hcotp_email_verifyotp_button_color', '#2271b1' ) ); ?>">
					</td>
				</tr>

				<tr>
					<th><?php esc_html_e( 'Verify Validation Message', 'happy-coders-otp-login' ); ?></th>
					<td>
						<input type="text"
							name="hcotp_email_verifyotp_validation_msg"
							value="<?php echo esc_attr( get_option( 'hcotp_email_verifyotp_validation_msg', 'Please enter the OTP' ) ); ?>"
							class="regular-text">
					</td>
				</tr>

				</table>

				<hr />

				<h2><?php esc_html_e( 'Email Template', 'happy-coders-otp-login' ); ?></h2>

				<?php
				$template_1_default = function_exists( 'hcotp_get_default_email_template_html' )
					? hcotp_get_default_email_template_html( 'template_1' )
					: '';
				$template_2_default = function_exists( 'hcotp_get_default_email_template_html' )
					? hcotp_get_default_email_template_html( 'template_2' )
					: '';
				$template_3_default = function_exists( 'hcotp_get_default_email_template_html' )
					? hcotp_get_default_email_template_html( 'template_3' )
					: '';

				$template_1_value = get_option( 'hcotp_email_template_html_1', '' );
				$template_2_value = get_option( 'hcotp_email_template_html_2', '' );
				$template_3_value = get_option( 'hcotp_email_template_html_3', '' );

				if ( '' === $template_1_value ) {
					$template_1_value = $template_1_default;
				}
				if ( '' === $template_2_value ) {
					$template_2_value = $template_2_default;
				}
				if ( '' === $template_3_value ) {
					$template_3_value = $template_3_default;
				}
				?>
				<textarea id="hcotp-email-template-default-1" style="display:none;"><?php echo esc_textarea( $template_1_default ); ?></textarea>
				<textarea id="hcotp-email-template-default-2" style="display:none;"><?php echo esc_textarea( $template_2_default ); ?></textarea>
				<textarea id="hcotp-email-template-default-3" style="display:none;"><?php echo esc_textarea( $template_3_default ); ?></textarea>

				<table class="form-table" role="presentation">

				<tr>
					<th scope="row"><?php esc_html_e( 'Header Image', 'happy-coders-otp-login' ); ?></th>
					<td>
						<input type="text" class="regular-text"
							name="hcotp_email_otp_header_image"
							value="<?php echo esc_attr( get_option( 'hcotp_email_otp_header_image' ) ); ?>" />
						<button class="button hcotp-upload"><?php esc_html_e( 'Upload', 'happy-coders-otp-login' ); ?></button>
						<p class="description"><?php esc_html_e( 'Optional image URL for header.', 'happy-coders-otp-login' ); ?></p>
						<p class="description">
							<?php esc_html_e( 'Width/Height in pixels. Leave height blank (0) to auto scale.', 'happy-coders-otp-login' ); ?>
						</p>
						<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
							<label>
								<?php esc_html_e( 'Width', 'happy-coders-otp-login' ); ?>
								<input type="number" min="0" max="1000" step="1"
									name="hcotp_email_otp_header_image_width"
									value="<?php echo esc_attr( get_option( 'hcotp_email_otp_header_image_width', 200 ) ); ?>"
									style="width:100px;">
							</label>
							<label>
								<?php esc_html_e( 'Height', 'happy-coders-otp-login' ); ?>
								<input type="number" min="0" max="1000" step="1"
									name="hcotp_email_otp_header_image_height"
									value="<?php echo esc_attr( get_option( 'hcotp_email_otp_header_image_height', 0 ) ); ?>"
									style="width:100px;">
							</label>
						</div>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Footer Image', 'happy-coders-otp-login' ); ?></th>
					<td>
						<input type="text" class="regular-text"
							name="hcotp_email_otp_footer_image"
							value="<?php echo esc_attr( get_option( 'hcotp_email_otp_footer_image' ) ); ?>" />
						<button class="button hcotp-upload"><?php esc_html_e( 'Upload', 'happy-coders-otp-login' ); ?></button>
						<p class="description"><?php esc_html_e( 'Optional image URL for footer.', 'happy-coders-otp-login' ); ?></p>
						<p class="description">
							<?php esc_html_e( 'Width/Height in pixels. Leave height blank (0) to auto scale.', 'happy-coders-otp-login' ); ?>
						</p>
						<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
							<label>
								<?php esc_html_e( 'Width', 'happy-coders-otp-login' ); ?>
								<input type="number" min="0" max="1000" step="1"
									name="hcotp_email_otp_footer_image_width"
									value="<?php echo esc_attr( get_option( 'hcotp_email_otp_footer_image_width', 200 ) ); ?>"
									style="width:100px;">
							</label>
							<label>
								<?php esc_html_e( 'Height', 'happy-coders-otp-login' ); ?>
								<input type="number" min="0" max="1000" step="1"
									name="hcotp_email_otp_footer_image_height"
									value="<?php echo esc_attr( get_option( 'hcotp_email_otp_footer_image_height', 0 ) ); ?>"
									style="width:100px;">
							</label>
						</div>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Email Subject', 'happy-coders-otp-login' ); ?></th>
					<td>
						<input type="text" class="regular-text"
							name="hcotp_email_otp_subject"
							value="<?php echo esc_attr( get_option( 'hcotp_email_otp_subject' ) ); ?>" />
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Email Body', 'happy-coders-otp-login' ); ?></th>
					<td>
						<textarea rows="8" cols="60"
							name="hcotp_email_otp_body"><?php echo esc_textarea( get_option( 'hcotp_email_otp_body' ) ); ?></textarea>
						<p class="description"> <?php esc_html_e( 'This content will be injected into the selected template at {{content}}.', 'happy-coders-otp-login' ); ?><br>
							<?php esc_html_e( 'Available variables:', 'happy-coders-otp-login' ); ?><br>
							<code>{{otp}}</code>,
							<code>{{expiry}}</code>,
							<code>{{site_name}}</code>,							
							<code>{{site_url}}</code>,
							<code>{{user_mobile}}</code>,
							<code>{{user_email}}</code>,
							<code>{{date}}</code>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Template 1 HTML', 'happy-coders-otp-login' ); ?></th>
					<td>
						<textarea rows="10" cols="60" name="hcotp_email_template_html_1" data-default-template="<?php echo esc_attr( $template_1_default ); ?>"><?php echo esc_textarea( $template_1_value ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Use {{content}} to show the Email Body.', 'happy-coders-otp-login' ); ?></p>
						<p class="description"><?php esc_html_e( 'Add {{header_image}} or {{footer_image}} where you want the images to appear.', 'happy-coders-otp-login' ); ?></p>
						<p class="description"><?php esc_html_e( 'Use {{header_image_url}} or {{footer_image_url}} only if you want to place the image manually.', 'happy-coders-otp-login' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Template 2 HTML', 'happy-coders-otp-login' ); ?></th>
					<td>
						<textarea rows="10" cols="60" name="hcotp_email_template_html_2" data-default-template="<?php echo esc_attr( $template_2_default ); ?>"><?php echo esc_textarea( $template_2_value ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Use {{content}} to show the Email Body.', 'happy-coders-otp-login' ); ?></p>
						<p class="description"><?php esc_html_e( 'Add {{header_image}} or {{footer_image}} where you want the images to appear.', 'happy-coders-otp-login' ); ?></p>
						<p class="description"><?php esc_html_e( 'Use {{header_image_url}} or {{footer_image_url}} only if you want to place the image manually.', 'happy-coders-otp-login' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Template 3 HTML', 'happy-coders-otp-login' ); ?></th>
					<td>
						<textarea rows="10" cols="60" name="hcotp_email_template_html_3" data-default-template="<?php echo esc_attr( $template_3_default ); ?>"><?php echo esc_textarea( $template_3_value ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Use {{content}} to show the Email Body.', 'happy-coders-otp-login' ); ?></p>
						<p class="description"><?php esc_html_e( 'Add {{header_image}} or {{footer_image}} where you want the images to appear.', 'happy-coders-otp-login' ); ?></p>
						<p class="description"><?php esc_html_e( 'Use {{header_image_url}} or {{footer_image_url}} only if you want to place the image manually.', 'happy-coders-otp-login' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Template Selection', 'happy-coders-otp-login' ); ?></th>
					<td>
						<select name="hcotp_email_template_choice">
							<option value="template_1" <?php selected( get_option( 'hcotp_email_template_choice', 'template_1' ), 'template_1' ); ?>>
								<?php esc_html_e( 'Template 1 (Default)', 'happy-coders-otp-login' ); ?>
							</option>
							<option value="template_2" <?php selected( get_option( 'hcotp_email_template_choice', 'template_1' ), 'template_2' ); ?>>
								<?php esc_html_e( 'Template 2 (Dark)', 'happy-coders-otp-login' ); ?>
							</option>
							<option value="template_3" <?php selected( get_option( 'hcotp_email_template_choice', 'template_1' ), 'template_3' ); ?>>
								<?php esc_html_e( 'Template 3 (Classic)', 'happy-coders-otp-login' ); ?>
							</option>
						</select>
						<p class="description"><?php esc_html_e( 'The selected template will be used for all Email OTP messages.', 'happy-coders-otp-login' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Template Preview', 'happy-coders-otp-login' ); ?></th>
					<td>
						<button class="button button-secondary" id="hcotp-email-template-preview">
							<?php esc_html_e( 'Preview Selected Template', 'happy-coders-otp-login' ); ?>
						</button>
						<p class="description"><?php esc_html_e( 'Opens a preview using the current Email Body and Template HTML values.', 'happy-coders-otp-login' ); ?></p>
						<div id="hcotp-email-preview-wrap" style="display:none;margin-top:16px;border:1px solid #c3c4c7;border-radius:8px;overflow:hidden;">
							<div style="padding:8px 12px;background:#f6f7f7;border-bottom:1px solid #c3c4c7;display:flex;justify-content:space-between;align-items:center;">
								<strong><?php esc_html_e( 'Email Preview', 'happy-coders-otp-login' ); ?></strong>
								<button class="button button-link-delete" id="hcotp-email-preview-close">
									<?php esc_html_e( 'Close', 'happy-coders-otp-login' ); ?>
								</button>
							</div>
							<iframe id="hcotp-email-preview-iframe" title="<?php esc_attr_e( 'Email Preview', 'happy-coders-otp-login' ); ?>" style="width:100%;height:500px;border:0;"></iframe>
						</div>
					</td>
				</tr>

				</table>

			</div>

			<?php submit_button(); ?>
		</form>
	</div>
	
	<?php
}
