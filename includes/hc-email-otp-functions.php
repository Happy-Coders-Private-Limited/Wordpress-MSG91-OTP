<?php
/**
 * Email OTP functionality.
 *
 * @package happy-coders-otp-login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

add_action( 'wp_ajax_hcotp_send_email_otp', 'hcotp_send_email_otp_ajax' );
add_action( 'wp_ajax_nopriv_hcotp_send_email_otp', 'hcotp_send_email_otp_ajax' );

/**
 * AJAX handler to send Email OTP.
 *
 * @return void
 */
function hcotp_send_email_otp_ajax() {
	check_ajax_referer( 'msg91_ajax_nonce_action', 'security_nonce' );

	if ( ! hcotp_is_email_otp_enabled() ) {
		wp_send_json_error(
			array( 'message' => esc_html__( 'Email OTP is disabled.', 'happy-coders-otp-login' ) )
		);
	}

	$user_id = absint( wp_unslash( $_POST['user_id'] ?? 0 ) );
	$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );

	if ( empty( $user_id ) || ! is_email( $email ) ) {
		wp_send_json_error(
			array( 'message' => esc_html__( 'Invalid user or email.', 'happy-coders-otp-login' ) )
		);
	}

	update_user_meta( $user_id, 'hcotp_email', $email );
	update_user_meta( $user_id, 'hcotp_email_verified', 0 );

	$user   = get_user_by( 'ID', $user_id );
	$mobile = get_user_meta( $user_id, 'mobile_number', true );

	if ( ! $user || empty( $mobile ) ) {
		wp_send_json_error(
			array( 'message' => esc_html__( 'User data missing.', 'happy-coders-otp-login' ) )
		);
	}

	$sent = hcotp_send_email_otp( $user_id, $email, $mobile );

	if ( ! $sent ) {
		wp_send_json_error(
			array( 'message' => esc_html__( 'Failed to send Email OTP.', 'happy-coders-otp-login' ) )
		);
	}

	wp_send_json_success(
		array(
			'message' => esc_html__( 'OTP sent to email.', 'happy-coders-otp-login' ),
		)
	);
}

add_action( 'wp_ajax_hcotp_verify_email_otp', 'hcotp_verify_email_otp_ajax' );
add_action( 'wp_ajax_nopriv_hcotp_verify_email_otp', 'hcotp_verify_email_otp_ajax' );

/**
 * AJAX handler to verify Email OTP.
 *
 * @return void
 */
function hcotp_verify_email_otp_ajax() {
	check_ajax_referer( 'msg91_ajax_nonce_action', 'security_nonce' );

	if ( ! hcotp_is_email_otp_enabled() ) {
		wp_send_json_error(
			array( 'message' => esc_html__( 'Email OTP is disabled.', 'happy-coders-otp-login' ) )
		);
	}

	$user_id = absint( wp_unslash( $_POST['user_id'] ?? 0 ) );
	$otp     = sanitize_text_field( wp_unslash( $_POST['otp'] ?? '' ) );

	if ( empty( $user_id ) || empty( $otp ) ) {
		wp_send_json_error(
			array( 'message' => esc_html__( 'Missing OTP or user.', 'happy-coders-otp-login' ) )
		);
	}

	if ( ! hcotp_verify_email_otp( $user_id, $otp ) ) {
		wp_send_json_error(
			array( 'message' => esc_html__( 'Invalid or expired OTP.', 'happy-coders-otp-login' ) )
		);
	}

	hcotp_mark_email_verified( $user_id );

	$email = get_user_meta( $user_id, 'hcotp_email', true );

	if ( is_email( $email ) ) {
		wp_update_user(
			array(
				'ID'         => $user_id,
				'user_email' => $email,
			)
		);
	}

	wp_send_json_success(
		array(
			'message' => esc_html__( 'Email verified successfully.', 'happy-coders-otp-login' ),
		)
	);
}

/**
 * Check whether user must verify email after login.
 *
 * @param int $user_id User ID.
 * @return bool
 */
function hcotp_user_requires_email_verification( $user_id ) {
	if ( ! hcotp_is_email_otp_enabled() ) {
		return false;
	}

	if ( ! absint( get_option( 'hcotp_force_email_after_login', 1 ) ) ) {
		return false;
	}

	return ! absint( get_user_meta( $user_id, 'hcotp_email_verified', true ) );
}

/**
 * Validate whether email login is allowed.
 *
 * @param string $email Email address.
 * @return int|WP_Error
 */
function hcotp_validate_email_login( $email ) {
	if ( ! hcotp_is_email_otp_enabled() ) {
		return new WP_Error(
			'email_otp_disabled',
			esc_html__( 'Email login is disabled.', 'happy-coders-otp-login' )
		);
	}

	$user = get_user_by( 'email', $email );

	if ( ! $user ) {
		return new WP_Error(
			'email_not_found',
			esc_html__( 'No account found with this email.', 'happy-coders-otp-login' )
		);
	}

	if ( ! absint( get_user_meta( $user->ID, 'hcotp_email_verified', true ) ) ) {
		return new WP_Error(
			'email_not_verified',
			esc_html__( 'Please verify your email using mobile OTP login.', 'happy-coders-otp-login' )
		);
	}

	return $user->ID;
}