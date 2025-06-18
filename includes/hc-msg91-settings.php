<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'admin_menu',
	function () {
		add_menu_page(
			__( 'MSG91 OTP Settings', 'hc-msg91-plugin' ),
			__( 'MSG91 OTP & SMS', 'hc-msg91-plugin' ),
			'manage_options',
			'msg91-otp-settings',
			'hcotp_settings_page',
			'dashicons-smartphone',
			56
		);
	}
);
function hcotp_admin_enqueue_scripts() {
	wp_enqueue_script( 'msg91-otp-js', HCOTP_PLUGIN_URL . 'assets/js/hc-msg91-otp.js', array( 'jquery' ), time(), true );
	wp_enqueue_style( 'msg91-otp-css', HCOTP_PLUGIN_URL . 'assets/css/hc-msg91-otp.css', array(), time() );

	wp_localize_script(
		'msg91-otp-js',
		'msg91_ajax_obj',
		array(
			'ajax_url'                 => admin_url( 'admin-ajax.php' ),
			'nonce'                    => wp_create_nonce( 'msg91_ajax_nonce_action' ),
			'resend_timer'             => (int) get_option( 'msg91_resend_timer', 60 ),
			'redirect_page'            => get_option( 'msg91_redirect_page' ),
			'sendotp_validation_msg'   => get_option( 'msg91_sendotp_validation_msg', 'Please enter a valid mobile number (between 5 and 12 digits).' ),
			'verifyotp_validation_msg' => get_option( 'msg91_verifyotp_validation_msg', 'Please enter the otp' ),
		)
	);
}

add_action( 'wp_enqueue_scripts', 'hcotp_admin_enqueue_scripts' );
add_action( 'admin_enqueue_scripts', 'hcotp_admin_enqueue_scripts' );

add_action(
	'admin_init',
	function () {
		register_setting( 'msg91_otp_settings_group', 'whatsapp_auth_enabled', isset( $_POST['whatsapp_auth_enabled'] ) ? 1 : 0 );
		register_setting( 'msg91_otp_settings_group', 'whatsapp_integrated_number', 'sanitize_text_field' );
		register_setting( 'msg91_otp_settings_group', 'whatsapp_template_name', 'sanitize_text_field' );
		register_setting( 'msg91_otp_settings_group', 'whatsapp_template_namespace', 'sanitize_text_field' );
		register_setting( 'msg91_otp_settings_group', 'whatsapp_language_code', 'sanitize_text_field' );
		register_setting( 'msg91_otp_settings_group', 'whatsapp_button_text', 'sanitize_text_field' );

		register_setting( 'msg91_otp_settings_group', 'msg91_active_tab', 'sanitize_text_field' );
		register_setting( 'msg91_otp_settings_group', 'msg91_auth_key', 'sanitize_text_field' );
		register_setting( 'msg91_otp_settings_group', 'msg91_sender_id', 'sanitize_text_field' );
		register_setting( 'msg91_otp_settings_group', 'msg91_template_id', 'sanitize_text_field' );
		register_setting( 'msg91_otp_settings_group', 'msg91_resend_timer', 'intval' );
		register_setting( 'msg91_otp_settings_group', 'msg91_default_country', 'sanitize_text_field' );
		register_setting(
			'msg91_otp_settings_group',
			'msg91_selected_countries',
			function ( $input ) {
				return array_map( 'sanitize_text_field', (array) $input );
			}
		);
		register_setting( 'msg91_otp_settings_group', 'msg91_top_image', 'esc_url_raw' );
		register_setting( 'msg91_otp_settings_group', 'msg91_top_verify_image', 'esc_url_raw' );
		register_setting( 'msg91_otp_settings_group', 'msg91_perday_otplimit', 'intval' );
		register_setting(
			'msg91_otp_settings_group',
			'msg91_flag_show',
			function ( $value ) {
				return $value === '1' ? 1 : 0;
			}
		);
		register_setting( 'msg91_otp_settings_group', 'msg91_redirect_page', 'esc_url_raw' );
		register_setting( 'msg91_otp_settings_group', 'msg91_sendotp_lable', 'sanitize_text_field' );
		register_setting( 'msg91_otp_settings_group', 'msg91_sendotp_lable_color', 'sanitize_hex_color' );
		register_setting( 'msg91_otp_settings_group', 'msg91_sendotp_dec', 'sanitize_text_field' );
		register_setting( 'msg91_otp_settings_group', 'msg91_sendotp_dec_color', 'sanitize_hex_color' );
		register_setting( 'msg91_otp_settings_group', 'msg91_sendotp_validation_msg', 'sanitize_text_field' );
		register_setting( 'msg91_otp_settings_group', 'msg91_sendotp_button_text', 'sanitize_text_field' );
		register_setting( 'msg91_otp_settings_group', 'msg91_sendotp_button_color', 'sanitize_hex_color' );
		register_setting( 'msg91_otp_settings_group', 'msg91_verifyotp_lable', 'sanitize_text_field' );
		register_setting( 'msg91_otp_settings_group', 'msg91_verifyotp_lable_color', 'sanitize_hex_color' );
		register_setting( 'msg91_otp_settings_group', 'msg91_verifyotp_dec', 'sanitize_text_field' );
		register_setting( 'msg91_otp_settings_group', 'msg91_verifyotp_desc_color', 'sanitize_hex_color' );
		register_setting( 'msg91_otp_settings_group', 'msg91_verifyotp_validation_msg', 'sanitize_text_field' );
		register_setting( 'msg91_otp_settings_group', 'msg91_verifyotp_button_text', 'sanitize_text_field' );
		register_setting( 'msg91_otp_settings_group', 'msg91_verifyotp_button_color', 'sanitize_hex_color' );

		// Code for SMS added by Kombiah
		$sms_event_types = array(
			'ncr' => 'New Customer Registration',
			'npo' => 'New Order Placed',
			'osh' => 'Order Shipped',
			'odl' => 'Order Delivered',
			'oac' => 'Order on Cart (Abandoned)',
		);

		foreach ( $sms_event_types as $key => $label ) {
			register_setting( 'msg91_otp_settings_group', "msg91_sms_{$key}_enable", 'absint' );
			register_setting( 'msg91_otp_settings_group', "msg91_sms_{$key}_template_id", 'sanitize_text_field' );
			register_setting( 'msg91_otp_settings_group', "msg91_sms_{$key}_notes", 'wp_kses_post' ); // Allows some HTML for notes
			if ( $key === 'osh' || $key === 'odl' ) {
				register_setting( 'msg91_otp_settings_group', "msg91_sms_{$key}_status_slug", 'sanitize_text_field' );
			}
			if ( $key === 'oac' ) {
				register_setting( 'msg91_otp_settings_group', "msg91_sms_{$key}_delay_hours", 'hcotp_sanitize_positive_float' );
			}
		}
	}
);

function hcotp_sanitize_positive_float( $input ) {
	$value = floatval( str_replace( ',', '.', $input ) );
	return ( $value > 0 ) ? $value : 0.01;
}

function hcotp_settings_page() {
	$active_tab = get_option( 'msg91_active_tab', 'otp_settings' );
	?>
	<div class="wrap">
		<h1><?php echo msg91_translate( 'Happy Coders MSG91 Settings' ); ?></h1>

		<h2 class="nav-tab-wrapper">   
			<a href="#general_settings" class="nav-tab <?php echo $active_tab == 'general_settings' ? 'nav-tab-active' : ''; ?>" data-tab="general_settings"><?php echo msg91_translate( 'General Settings' ); ?></a>        
			<a href="#otp_settings" class="nav-tab <?php echo $active_tab == 'otp_settings' ? 'nav-tab-active' : ''; ?>" data-tab="otp_settings"><?php echo msg91_translate( 'OTP Login Settings' ); ?></a>
			<a href="#sms_settings" class="nav-tab <?php echo $active_tab == 'sms_settings' ? 'nav-tab-active' : ''; ?>" data-tab="sms_settings"><?php echo msg91_translate( 'Transactional SMS Settings' ); ?></a>
			
		</h2>

		<form method="post" action="options.php">
			<?php settings_fields( 'msg91_otp_settings_group' ); ?>
			<?php do_settings_sections( 'msg91_otp_settings_group' ); ?>

			<input type="hidden" name="msg91_active_tab" id="msg91_active_tab_input" value="<?php echo esc_attr( $active_tab ); ?>">

			<div id="general_settings" class="tab-content <?php echo $active_tab == 'general_settings' ? 'active-tab' : ''; ?>">
				<h2><?php echo msg91_translate( 'MSG91 API Credentials ' ); ?></h2>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php echo msg91_translate( 'MSG91 Auth Key' ); ?></th>
						<td>
							<input type="text" name="msg91_auth_key" value="<?php echo esc_attr( get_option( 'msg91_auth_key' ) ); ?>" size="50" />
							<p class="description"><?php echo esc_html( msg91_translate( 'Your MSG91 Authentication Key. Used for OTP and Transactional SMS.' ) ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php echo msg91_translate( 'Sender ID' ); ?></th>
						<td>
							<input type="text" name="msg91_sender_id" value="<?php echo esc_attr( get_option( 'msg91_sender_id' ) ); ?>" size="30" />
							<p class="description"><?php echo esc_html( msg91_translate( 'Your DLT Approved Sender ID. Used for OTP and Transactional SMS.' ) ); ?></p>
						</td>
					</tr>
<tr valign="top">
	<th scope="row">
		<?php echo msg91_translate( 'Send OTP to users through WhatsApp?' ); ?>
	</th>
	<td>
		<label>
			<input type="checkbox" id="whatsapp_auth_checkbox" name="whatsapp_auth_enabled" value="1" <?php checked( get_option( 'whatsapp_auth_enabled' ), 1 ); ?> />
			<?php echo msg91_translate( 'Yes' ); ?>
		</label>
		<div id="whatsapp_auth_inputs" style="margin-top: 10px; <?php echo get_option( 'whatsapp_auth_enabled' ) ? '' : 'display:none;'; ?>">
			<p class="lable-input-between">
				<label ><strong >Integrated Number:</strong><br>
					<input type="text" class="input-top" size="50" name="whatsapp_integrated_number" value="<?php echo esc_attr( get_option( 'whatsapp_integrated_number' ) ); ?>" class="regular-text" />
				</label>
			</p>
			<p class="lable-input-between"> 
				<label style="margin-bottom: 12px;"><strong>Template Name:</strong><br>
					<input type="text" class="input-top"  size="50" name="whatsapp_template_name" value="<?php echo esc_attr( get_option( 'whatsapp_template_name' ) ); ?>" class="regular-text" />
				</label>
			</p> 
			<p class="lable-input-between">
				<label style="margin-bottom: 12px;"><strong>Template Namespace:</strong><br>
					<input type="text" class="input-top"  size="50" name="whatsapp_template_namespace" value="<?php echo esc_attr( get_option( 'whatsapp_template_namespace' ) ); ?>" class="regular-text" />
				</label>
			</p>
			<p class="lable-input-between">
				<label style="margin-bottom: 12px;"><strong>Language Code:</strong><br>
					<input type="text"class="input-top"  size="50" name="whatsapp_language_code" value="<?php echo esc_attr( get_option( 'whatsapp_language_code' ) ); ?>" class="regular-text" />
				</label>
			</p>

			<p class="lable-input-between">
				<label style="margin-bottom: 12px;"><strong>Button Text (Example : Send OTP via Whatsapp)</strong><br>
					<input type="text"class="input-top"  size="50" name="whatsapp_button_text" value="<?php echo esc_attr( get_option( 'whatsapp_button_text' ) ); ?>" class="regular-text" />
				</label>
			</p>
		</div>
	</td>
</tr>

					


				</table>
			</div>


			<div id="otp_settings" class="tab-content <?php echo $active_tab == 'otp_settings' ? 'active-tab' : ''; ?>">
				<h2><?php echo msg91_translate( 'OTP Login Settings' ); ?></h2>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php echo msg91_translate( 'Template ID' ); ?></th>
						<td><input type="text" name="msg91_template_id" value="<?php echo esc_attr( get_option( 'msg91_template_id' ) ); ?>" size="30" />
						<p class="description"><?php echo esc_html( msg91_translate( 'MSG91 DLT Template ID for sending OTPs.' ) ); ?></p></td>
						
					</tr>
					<tr valign="top">
						<th scope="row"><?php echo msg91_translate( 'User OTP Limit per day' ); ?></th>
						<td><input type="number" name="msg91_perday_otplimit" value="<?php echo esc_attr( get_option( 'msg91_perday_otplimit' ) ); ?>" size="30" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php echo msg91_translate( 'Resend OTP Timer (sec)' ); ?></th>
						<td>
							<input type="number" name="msg91_resend_timer" 
								value="<?php echo esc_attr( get_option( 'msg91_resend_timer', 60 ) ); ?>" size="30" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php echo msg91_translate( 'Popup Login' ); ?></th>
						<td> 
							<p >
								<?php echo msg91_translate( 'You can also use the class name' ); ?> 
								<code style="font-size:16px; color:#0073aa;">otp-popup-trigger</code> 
								<?php echo msg91_translate( 'to trigger the popup on any element' ); ?>.
							</p>
						</td>
					</tr>


					<tr valign="top">
						<th scope="row"><?php echo msg91_translate( 'Screen based Login' ); ?></th>
						<td>
							<p style="margin: 0 0 5px;">
								<?php echo msg91_translate( 'Use this shortcode to display the screen OTP-based login form' ); ?>
							</p>
							<code id="msg91-shortcode" style="font-size:16px; color:#0073aa;">
								<?php echo '[msg91_otp_form]'; ?>
							</code>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php echo msg91_translate( 'Image URL for Send OTP Form' ); ?></th>
						<td>
							<input type="text" name="msg91_top_image" value="<?php echo esc_attr( get_option( 'msg91_top_image', HCOTP_PLUGIN_URL . 'assets/images/send-otp.png' ) ); ?>" size="60" />
							<p class="description"><?php echo esc_html( msg91_translate( 'Paste the full image URL to display above the OTP form (e.g. banner, logo).' ) ); ?></p>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php echo esc_html( msg91_translate( 'Send OTP Form Lable' ) ); ?></th>
						<td><input type="text" name="msg91_sendotp_lable" value="<?php echo esc_attr( get_option( 'msg91_sendotp_lable' ) ); ?>" size="50" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php echo msg91_translate( 'Send OTP Lable Color' ); ?></th>
						<td><input type="color" name="msg91_sendotp_lable_color" value="<?php echo esc_attr( get_option( 'msg91_sendotp_lable_color' ) ); ?>" size="30" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php echo msg91_translate( 'Send OTP Form Decription' ); ?></th>
						<td><input type="text" name="msg91_sendotp_dec" value="<?php echo esc_attr( get_option( 'msg91_sendotp_dec' ) ); ?>" size="50" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php echo msg91_translate( 'Send OTP Decription Color' ); ?></th>
						<td><input type="color" name="msg91_sendotp_dec_color" value="<?php echo esc_attr( get_option( 'msg91_sendotp_dec_color' ) ); ?>" size="30" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php echo msg91_translate( 'Send OTP Form Button Text' ); ?></th>
						<td><input type="text" name="msg91_sendotp_button_text" value="<?php echo esc_attr( get_option( 'msg91_sendotp_button_text' ) ); ?>" size="50" /></td>
					</tr>


					<tr valign="top">
						<th scope="row"><?php echo msg91_translate( 'Send OTP Button Color' ); ?></th>
						<td><input type="color" name="msg91_sendotp_button_color" value="<?php echo esc_attr( get_option( 'msg91_sendotp_button_color' ) ); ?>" size="30" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php echo msg91_translate( 'Send OTP Form validation msg' ); ?></th>
						<td><input type="text" name="msg91_sendotp_validation_msg" value="<?php echo esc_attr( get_option( 'msg91_sendotp_validation_msg' ) ); ?>" size="50" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php echo msg91_translate( 'Image URL for Verify OTP Form' ); ?></th>
						<td>
							<input type="text" name="msg91_top_verify_image" value="<?php echo esc_attr( get_option( 'msg91_top_verify_image', HCOTP_PLUGIN_URL . 'assets/images/verify-otp.png' ) ); ?>" size="60" />
							<p class="description"><?php echo esc_html( msg91_translate( 'Paste the full image URL to display above the OTP form (e.g. banner, logo).' ) ); ?></p>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php echo msg91_translate( 'Verify OTP Form Lable' ); ?></th>
						<td><input type="text" name="msg91_verifyotp_lable" value="<?php echo esc_attr( get_option( 'msg91_verifyotp_lable' ) ); ?>" size="50" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php echo msg91_translate( 'Verify OTP Lable Color' ); ?></th>
						<td><input type="color" name="msg91_verifyotp_lable_color" value="<?php echo esc_attr( get_option( 'msg91_verifyotp_lable_color' ) ); ?>" size="30" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php echo msg91_translate( 'Verify OTP Form Decription' ); ?></th>
						<td><input type="text" name="msg91_verifyotp_dec" value="<?php echo esc_attr( get_option( 'msg91_verifyotp_dec' ) ); ?>" size="50" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php echo esc_html( msg91_translate( 'Verify OTP Decription Color' ) ); ?></th>
						<td><input type="color" name="msg91_verifyotp_dec_color" value="<?php echo esc_attr( get_option( 'msg91_verifyotp_dec_color' ) ); ?>" size="30" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php echo esc_html( msg91_translate( 'Verify OTP Form Button Text' ) ); ?></th>
						<td><input type="text" name="msg91_verifyotp_button_text" value="<?php echo esc_attr( get_option( 'msg91_verifyotp_button_text' ) ); ?>" size="50" /></td>
					</tr>

				
					<tr valign="top">
						<th scope="row"><?php echo esc_html( msg91_translate( 'Verify OTP Button Color' ) ); ?></th>
						<td><input type="color" name="msg91_verifyotp_button_color" value="<?php echo esc_attr( get_option( 'msg91_verifyotp_button_color' ) ); ?>" size="30" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php echo esc_html( msg91_translate( 'Verify OTP Form validation msg' ) ); ?></th>
						<td><input type="text" name="msg91_verifyotp_validation_msg" value="<?php echo esc_attr( get_option( 'msg91_verifyotp_validation_msg' ) ); ?>" size="50" /></td>
					</tr>


					<tr valign="top">
						<th scope="row"><?php echo esc_html( msg91_translate( 'Redirect Page URL' ) ); ?></th>
						<td>
							<input type="text" name="msg91_redirect_page" 
								value="<?php echo esc_attr( get_option( 'msg91_redirect_page', home_url() ) ); ?>" 
								size="60" />
							<p class="description">
							<?php
							echo esc_html( msg91_translate( 'Enter the URL where users should be redirected after login. Example:' ) );
							?>
<code><?php echo home_url( '/dashboard' ); ?></code></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php echo msg91_translate( 'Default Country' ); ?></th>
						<td>
							<select name="msg91_default_country" id="msg91_default_country">
								<?php
									$default_country = get_option( 'msg91_default_country', '+91' );
									$countries       = hcotp_get_countries_with_iso();

								foreach ( $countries as $country ) {
											$dial_code = $country['code'];
											$name      = $country['name'];
											$iso       = $country['iso'];

											$flag = function_exists( 'hcotp_iso_to_flag' ) ? hcotp_iso_to_flag( $iso ) : '';

											printf(
												'<option value="%s" %s>%s %s (%s)</option>',
												esc_attr( $dial_code ),
												selected( $default_country, $dial_code, false ),
												esc_html( $flag ),
												esc_html( $name ),
												esc_html( $dial_code )
											);
								}

								?>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"></th>
						<td>
							<input type="checkbox" name="msg91_flag_show" value="1" <?php checked( 1, get_option( 'msg91_flag_show' ), true ); ?> />
							<span class="description"><?php echo esc_html( msg91_translate( 'Do you want to show country flag?' ) ); ?></span>

						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php echo msg91_translate( 'Selected Countries' ); ?></th>
						<td>
							<select name="msg91_selected_countries[]" id="msg91_selected_countries" multiple="multiple" style="width: 100%; height: 150px;">
								<?php
									$default_countries = get_option( 'msg91_selected_countries', array( '+91' ) );
									$countries         = hcotp_get_countries_with_iso();
								foreach ( $countries as $country ) {
									$dial_code = $country['code'];
									$name      = $country['name'];
									$iso       = $country['iso'];

									$selected = in_array( $dial_code, $default_countries ) ? 'selected' : '';
									$flag     = function_exists( 'hcotp_iso_to_flag' ) ? hcotp_iso_to_flag( $iso ) : '';

									$background_color = ( $selected ) ? 'background-color: #009ee8; color: white;' : '';

									echo "<option value='$dial_code' $selected style='$background_color'>$flag $name ($dial_code)</option>";
								}
								?>
							</select>
						</td>
					</tr>
				</table>
			</div>

			<div id="sms_settings" class="tab-content <?php echo $active_tab == 'sms_settings' ? 'active-tab' : ''; ?>">

				<!-- Code for Transactional SMS Notifications by Kombiah -->
				<h2><?php echo msg91_translate( 'Transactional SMS Notifications' ); ?></h2>
				<p><?php echo msg91_translate( 'Configure SMS notifications for various events. You need to create corresponding Flow templates in your MSG91 dashboard and provide the Flow ID here as Template ID. Ensure your Sender ID is DLT approved for these templates.' ); ?></p>
				<p><?php echo msg91_translate( 'The plugin will pass predefined variables to MSG91 (e.g., VAR1, VAR2). Please refer to the plugin documentation for the list of variables available for each SMS type.' ); ?></p>

				<?php
				$sms_event_types = array(
					'ncr' => msg91_translate( 'New Customer Registration' ),
					'npo' => msg91_translate( 'New Order Placed (WooCommerce)' ),
					'osh' => msg91_translate( 'Order Shipped (WooCommerce)' ),
					'odl' => msg91_translate( 'Order Delivered (WooCommerce)' ),
					'oac' => msg91_translate( 'Order on Cart / Abandoned (WooCommerce)' ),
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
						<th scope="row"><?php echo msg91_translate( 'Enable SMS' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( $enable_option ); ?>" value="1" <?php checked( 1, get_option( $enable_option, 0 ) ); ?> />
								<?php
												printf(
													esc_html( msg91_translate( 'Send SMS when %s' ) ),
													esc_html( strtolower( str_replace( '(WooCommerce)', '', $label ) ) )
												);
								?>

							</label>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php echo msg91_translate( 'MSG91 Flow/Template ID' ); ?></th>
						<td>
							<input type="text" name="<?php echo esc_attr( $template_id_option ); ?>" value="<?php echo esc_attr( get_option( $template_id_option ) ); ?>" size="40" />
							<p class="description"><?php echo esc_html( msg91_translate( 'Enter the Flow ID from your MSG91 panel for this event.' ) ); ?></p>
						</td>
					</tr>
					<?php
					if ( $key === 'osh' || $key === 'odl' ) :
						$status_slug_option = "msg91_sms_{$key}_status_slug";
						$default_slug       = ( $key === 'osh' ) ? 'shipped' : 'delivered';
						?>
					<tr valign="top">
						<th scope="row"><?php echo msg91_translate( 'Target Order Status Slug' ); ?></th>
						<td>
							<input type="text" name="<?php echo esc_attr( $status_slug_option ); ?>" value="<?php echo esc_attr( get_option( $status_slug_option, $default_slug ) ); ?>" size="30" />
							<p class="description">
								<?php echo esc_html( msg91_translate( 'Enter the WooCommerce order status slug that triggers this SMS (e.g., "shipped", "wc-completed", "delivered"). Do not include "wc-" prefix if it\'s a custom status without it.' ) ); ?>
							</p>
						</td>
					</tr>
					<?php endif; ?>
					<?php
					if ( $key === 'oac' ) :
						$delay_option = "msg91_sms_{$key}_delay_hours";
						?>
					<tr valign="top">
						<th scope="row"><?php echo msg91_translate( 'Abandonment Delay (Hours)' ); ?></th>
						<td>
							<input type="number" name="<?php echo esc_attr( $delay_option ); ?>" value="<?php echo esc_attr( get_option( $delay_option, 1 ) ); ?>"  min="0.01" step="0.01" size="5" lang="en" />
							<p class="description"><?php echo esc_html( msg91_translate( 'Enter delay in hours (e.g., 1 for 1 hour, 0.5 for 30 minutes, 0.05 for 3 minutes). Minimum 0.01 (approx 30 seconds). Affects logged-in users.' ) ); ?></p>
						</td>
					</tr>
					<?php endif; ?>
					<tr valign="top">
						<th scope="row"><?php echo msg91_translate( 'Template Notes / Content Preview' ); ?></th>
						<td>
							<textarea name="<?php echo esc_attr( $notes_option ); ?>" rows="3" cols="50" class="large-text"><?php echo esc_textarea( get_option( $notes_option ) ); ?></textarea>
							<?php
							$var_note = msg91_translate( 'For your reference. Paste your MSG91 template content here or add notes about variables used.' );
							if ( $key === 'ncr' ) {
								$var_note .= ' ' . msg91_translate( 'var1 = CustomerName.' );
							} elseif ( $key === 'oac' ) {
								$var_note .= ' ' . msg91_translate( 'var1 = Cart Recovery URL, var2 = Cart Item Count.' );
							} else {
								$var_note .= ' ' . msg91_translate( 'var1 = CustomerName, var2 = OrderID.' );
							}
							?>
							<p class="description"><?php echo $var_note; ?></p>

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
// Helper function to get default notes for SMS templates
function happycoders_msg91_get_default_sms_note( $key ) {
	$notes = array(
		'ncr' => 'New Customer: VAR1=CustomerName, VAR2=SiteName, VAR3=ShopURL',
		'npo' => 'New Order: VAR1=CustomerName, VAR2=OrderID, VAR3=OrderTotal, VAR4=SiteName, VAR5=ShopURL',
		'osh' => 'Order Shipped: VAR1=CustomerName, VAR2=OrderID, VAR3=TrackingID, VAR4=ShippingProvider, VAR5=TrackingLink, VAR6=SiteName',
		'odl' => 'Order Delivered: VAR1=CustomerName, VAR2=OrderID, VAR3=SiteName',
		'oac' => 'Abandoned Cart: VAR1=CustomerName, VAR2=CartItemsCount, VAR3=CartTotal, VAR4=SiteName, VAR5=CartURL',
	);
	return isset( $notes[ $key ] ) ? $notes[ $key ] : '';
}
?>
