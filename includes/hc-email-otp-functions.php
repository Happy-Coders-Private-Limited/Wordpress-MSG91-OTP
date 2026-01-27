/**
 * Generate a numeric OTP for email login.
 *
 * @return string
 */
function hcotp_generate_email_otp() {
	$length = absint( get_option( 'hcotp_email_otp_length', 4 ) );
	$length = ( $length < 4 || $length > 8 ) ? 6 : $length;

	$min = (int) pow( 10, $length - 1 );
	$max = (int) pow( 10, $length ) - 1;

	return (string) wp_rand( $min, $max );
}

/**
 * Store email OTP securely for a user.
 *
 * @param int    $user_id User ID.
 * @param string $otp     Plain OTP.
 * @return void
 */
function hcotp_store_email_otp( $user_id, $otp ) {
	$expiry_minutes = absint( get_option( 'hcotp_email_otp_expiry', 5 ) );
	$expiry_minutes = ( $expiry_minutes < 1 ) ? 5 : $expiry_minutes;

	update_user_meta( $user_id, 'hcotp_email_otp_hash', wp_hash_password( $otp ) );
	update_user_meta( $user_id, 'hcotp_email_otp_expiry', time() + ( $expiry_minutes * MINUTE_IN_SECONDS ) );
}

/**
 * Verify an email OTP for a user.
 *
 * @param int    $user_id User ID.
 * @param string $otp     OTP entered by user.
 * @return bool
 */
function hcotp_verify_email_otp( $user_id, $otp ) {
	$hash   = get_user_meta( $user_id, 'hcotp_email_otp_hash', true );
	$expiry = absint( get_user_meta( $user_id, 'hcotp_email_otp_expiry', true ) );

	if ( empty( $hash ) || empty( $expiry ) ) {
		return false;
	}

	if ( time() > $expiry ) {
		return false;
	}

	return wp_check_password( $otp, $hash );
}

/**
 * Replace placeholders in email templates.
 *
 * @param string $content Template content.
 * @param array  $data    Replacement data.
 * @return string
 */
function hcotp_replace_email_placeholders( $content, $data ) {
	$replacements = array(
		'{{otp}}'         => isset( $data['otp'] ) ? $data['otp'] : '',
		'{{expiry}}'      => isset( $data['expiry'] ) ? $data['expiry'] : '',
		'{{site_name}}'   => get_bloginfo( 'name' ),
		'{{site_url}}'    => home_url(),
		'{{user_email}}'  => isset( $data['user_email'] ) ? $data['user_email'] : '',
		'{{user_mobile}}' => isset( $data['user_mobile'] ) ? $data['user_mobile'] : '',
		'{{date}}'        => gmdate( 'Y-m-d' ),
	);

	return str_replace( array_keys( $replacements ), array_values( $replacements ), $content );
}

/**
 * Send Email OTP to user.
 *
 * @param int    $user_id User ID.
 * @param string $email   Email address.
 * @param string $mobile  Mobile number.
 * @return bool
 */
function hcotp_send_email_otp( $user_id, $email, $mobile ) {
	if ( ! is_email( $email ) ) {
		return false;
	}

	$otp = hcotp_generate_email_otp();
	hcotp_store_email_otp( $user_id, $otp );

	$expiry  = absint( get_option( 'hcotp_email_otp_expiry', 5 ) );
	$subject = get_option( 'hcotp_email_otp_subject', '' );
	$body    = get_option( 'hcotp_email_otp_body', '' );

	$data = array(
		'otp'         => $otp,
		'expiry'      => $expiry,
		'user_email'  => $email,
		'user_mobile' => $mobile,
	);

	$subject = hcotp_replace_email_placeholders( $subject, $data );
	$body    = hcotp_replace_email_placeholders( $body, $data );

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

	return wp_mail( $email, $subject, $body, $headers );
}

/**
 * Mark user's email as verified.
 *
 * @param int $user_id User ID.
 * @return void
 */
function hcotp_mark_email_verified( $user_id ) {
	update_user_meta( $user_id, 'hcotp_email_verified', 1 );
	delete_user_meta( $user_id, 'hcotp_email_otp_hash' );
	delete_user_meta( $user_id, 'hcotp_email_otp_expiry' );
}

/**
 * Check whether Email OTP login is enabled.
 *
 * @return bool
 */
function hcotp_is_email_otp_enabled() {
	return (bool) absint( get_option( 'hcotp_email_otp_enabled', 0 ) );
}

