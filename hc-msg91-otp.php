<?php
/**
 * Plugin Name: Happy Coders OTP Login
 * Description: Happy Coders OTP Login integrates seamless OTP-based authentication using MSG91. Easily send, verify, and resend OTPs with a customizable timer—enhancing your website’s security and user experience.
 * Version: 1.2
 * Author: Happy Coders
 * Author URI: https://www.happycoders.in/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined('ABSPATH') || exit;
require_once plugin_dir_path(__FILE__) . 'includes/hc-msg91-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/hc-countries.php';

add_action('plugins_loaded', function () {
    $locale = determine_locale();
    $lang_file = plugin_dir_path(__FILE__) . 'languages/msg91-otp-' . $locale . '.php';

    if (file_exists($lang_file)) {
        $GLOBALS['msg91_otp_translations'] = include $lang_file;
    } else {
        $GLOBALS['msg91_otp_translations'] = [];
    }
});
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'msg91_otp_plugin_action_links');

function msg91_otp_plugin_action_links($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=msg91-otp-settings') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

add_filter('plugin_row_meta', 'msg91_otp_plugin_row_meta', 10, 4);
function msg91_otp_plugin_row_meta($plugin_meta, $plugin_file, $plugin_data, $status) {
    if ($plugin_file === plugin_basename(__FILE__)) {
        if (stripos($plugin_data['Author'], 'HAPPY CODERS') !== false) {
            $plugin_meta[] = '<a href="https://www.happycoders.in/msg91-plugin-documentation/" target="_blank">Documentation</a>';
            $plugin_meta[] = '<a href="https://www.happycoders.in/" target="_blank">Support</a>';
            $plugin_meta[] = '<a href="https://github.com/Happy-Coders-Private-Limited" target="_blank">GitHub</a>';
        }
    }
    return $plugin_meta;
}



function __msg91($text) {
    return $GLOBALS['msg91_otp_translations'][$text] ?? $text;
}

add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script('msg91-otp-js', plugin_dir_url(__FILE__) . 'assets/js/hc-msg91-otp.js', ['jquery'], time(), true);
    wp_enqueue_style('msg91-otp-css', plugin_dir_url(__FILE__) . 'assets/css/hc-msg91-otp.css', [], time());
    
    wp_localize_script('msg91-otp-js', 'msg91_ajax_obj', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'resend_timer' => (int) get_option('msg91_resend_timer', 60),
        'redirect_page' => get_option('msg91_redirect_page'),
        'sendotp_validation_msg' => get_option('msg91_sendotp_validation_msg', 'Please enter a valid mobile number (between 5 and 12 digits).'),
        'verifyotp_validation_msg' => get_option('msg91_verifyotp_validation_msg', 'Please enter the otp'),
     
    ]);
});

register_activation_hook(__FILE__, 'msg91_create_blocked_numbers_table');
register_deactivation_hook(__FILE__, 'msg91_delete_blocked_numbers_table');

function msg91_create_blocked_numbers_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'msg91_blocked_number';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        mobile_number VARCHAR(20) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function msg91_delete_blocked_numbers_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'msg91_blocked_number';
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql); 
}

add_action('wp_ajax_send_msg91_otp_ajax', 'send_msg91_otp_ajax');
add_action('wp_ajax_nopriv_send_msg91_otp_ajax', 'send_msg91_otp_ajax');


function msg91_get_options() {
    return [
        'send_otp_label' => get_option('msg91_sendotp_lable', 'Mobile Number'),
        'send_otp_label_color' => get_option('msg91_sendotp_lable_color', '#000000'),
        'send_otp_desc' => get_option('msg91_sendotp_dec', 'We will send you an OTP'),
        'send_otp_desc_color' => get_option('msg91_sendotp_dec_color', '#000000'),
        'send_otp_button_text' => get_option('msg91_sendotp_button_text', 'Send OTP'),
        'send_otp_button_color' => get_option('msg91_sendotp_button_color', '#0073aa'),
        'top_image' => get_option('msg91_top_image', plugin_dir_url(__FILE__) . 'assets/images/send-otp.png'),
        'verify_otp_lable' => get_option('msg91_verifyotp_lable', 'Enter Mobile'),
        'verify_otp_lable_color' => get_option('msg91_verifyotp_lable_color', '#000000'),
        'verify_otp_dec' => get_option('msg91_verifyotp_dec', 'Enter your 4-digit OTP'),
        'verify_otp_dec_color' => get_option('msg91_verifyotp_desc_color', '#000000'),
        'verify_otp_buttontext' => get_option('msg91_verifyotp_button_text', 'Verify OTP'),
        'verify_otp_button_color' => get_option('msg91_verifyotp_button_color', '#0073aa'),
        'top_verify_image' => get_option('msg91_top_verify_image', plugin_dir_url(__FILE__) . 'assets/images/verify-otp.png'),
    ];
}

function hc_msg91_country_select($options) {
    $html = '';
    $all_countries = hc_msg91_get_countries_with_iso();
    $selected_countries = get_option('msg91_selected_countries', ['+91']); 
    $show_flag = get_option('msg91_flag_show', 0);

    $filtered_countries = array_filter($all_countries, function($country) use ($selected_countries) {
        return in_array($country['code'], $selected_countries);
    });

    foreach ($filtered_countries as $country) {
        $selected = 'selected'; 
        $flag = hc_msg91_iso_to_flag($country['iso']); 
        $flag_html = $show_flag ? $flag : '';
        $html .= "<option value='{$country['code']}' data-flag='$flag' $selected>$flag_html {$country['code']}</option>";
    }

    return "<select name='msg91_country_code' id='msg91_country_code' class='country-select'>{$html}</select>";
}


add_shortcode('msg91_otp_form', function () {
    $options = msg91_get_options();
   
    if (empty($options['top_image'])) {
        $options['top_image'] = plugin_dir_url(__FILE__) . 'assets/images/send-otp.png';
    }
 
    if (empty($options['top_verify_image'])) {
        $options['top_verify_image'] = plugin_dir_url(__FILE__) . 'assets/images/verify-otp.png';
    }

    if (is_user_logged_in()) {
        $user = wp_get_current_user(); 
        return '<div style="text-align: center;">
                    <h3>Welcome, ' . esc_html($user->display_name) . '!</h3>
                    <button id="next-to-address" style="margin-top: 20px;">Next</button>
                </div>';
    }

    return render_msg91_otp_form($options, false);
});


add_action('wp_footer', function () {
    $options = msg91_get_options();
    if (empty($options['top_image'])) {
        $options['top_image'] = plugin_dir_url(__FILE__) . 'assets/images/send-otp.png';
    }
    if (empty($options['top_verify_image'])) {
        $options['top_verify_image'] = plugin_dir_url(__FILE__) . 'assets/images/verify-otp.png';
    }
    if (is_user_logged_in()) {
        $user = wp_get_current_user(); 
        return '<div style="text-align: center;">
                    <h3>Welcome, ' . esc_html($user->display_name) . '!</h3>
                    <button id="next-to-address" style="margin-top: 20px;">Next</button>
                </div>';
    }
    echo render_msg91_otp_form($options, true);
});

function render_msg91_otp_form($options, $is_popup = false) {
    ob_start();
    ?>
    <?php if ($is_popup): ?>
    
        <div id="otp-popup-modal" style="display: none;">
            
    <?php endif; ?>

    <div id="otp-form-wrap">
    <?php if ($is_popup): ?>
        <div style="width: 100%; text-align: right; height: 0;">
                <button onclick="document.getElementById('otp-popup-modal').style.display='none';" style="background: none; border: none; font-size: 24px; cursor: pointer; outline: none;">&times;</button>
            </div>
    <?php endif; ?>
   
        <div id="send_otp_section">
            <?php if (!empty($options['top_image'])): ?>
                <div style="text-align:center;">
                    <img src="<?php echo esc_url($options['top_image']); ?>" class="popup-image" />
                </div>
            <?php endif; ?>
            <div style="text-align:center;">
                  <label class="lable-style" style="color: <?php echo esc_attr($options['send_otp_label_color']); ?>;"><?php echo esc_html($options['send_otp_label']); ?></label>
            </div>

            <div style="text-align:center;">
                <label class="descripition" style="color: <?php echo esc_attr($options['send_otp_desc_color']); ?>;">
                    <?php echo esc_html($options['send_otp_desc']); ?>
                </label>
            </div>

            <div class="mobile-input-wrap">
                <?php echo hc_msg91_country_select($options); ?>
                <input type="tel" id="msg91_mobile" maxlength="10" pattern="\d*" placeholder="Mobile Number" oninput="this.value = this.value.replace(/[^0-9]/g, '');" />
            </div>
            <div id="otp-send-status" class="otp-send-status"></div>
            <button id="msg91_send_otp" class="common-width" style="background-color: <?php echo esc_attr($options['send_otp_button_color']); ?>; color: #fff;"><?php echo esc_html($options['send_otp_button_text']); ?></button>
        </div>

        <div id="otp_input_wrap" style="display: none;">
            <?php if (!empty($options['top_verify_image'])): ?>
                <div style="text-align:center;">
                    <img src="<?php echo esc_url($options['top_verify_image']); ?>" class="popup-image" />
                </div>
            <?php endif; ?>
            <div style="text-align:center;">
                 <label class="lable-style" style="color: <?php echo esc_attr($options['verify_otp_lable_color']); ?>;"><?php echo esc_html($options['verify_otp_lable']); ?>
                </label>
            </div>
            <div style="text-align:center;">
                <label class="descripition" style="color: <?php echo esc_attr($options['verify_otp_dec_color']); ?>;"><?php echo esc_html($options['verify_otp_dec']); ?></label>
             </div>
            <div class="otp-inputs">
                <?php for ($i = 1; $i <= 4; $i++): ?>
                    <input type="number" class="otp-field" id="otp<?php echo $i; ?>" maxlength="1" />
                <?php endfor; ?>
            </div>
            <div id="otp-verify-status" class="otp-verify-status"></div>
            <div class="verify-otp">
                <button id="msg91_verify_otp" style="background-color: <?php echo esc_attr($options['verify_otp_button_color']); ?>; color: #fff;"><?php echo esc_html($options['verify_otp_buttontext']); ?></button>
            </div>
            <div style="text-align:center;">
                <button id="resend_otp" disabled><?php echo __msg91('Didn"t receive an OTP? Resend OTP'); ?></button>
                <div id="resend_timer_text"></div>
            </div>
        </div>
    </div>

    <?php if ($is_popup): ?>
        </div> 
    <?php endif;

    return ob_get_clean();
}


function send_msg91_otp_ajax() {
    global $wpdb;
    $mobile = sanitize_text_field($_POST['mobile']);
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $table_name = $wpdb->prefix . 'msg91_blocked_number';
    $per_day_limit = intval(get_option('msg91_perday_otplimit', 5));
    $today = date('Y-m-d');
    $otp_count_today = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE mobile_number = %s AND DATE(created_at) = %s",
        $mobile, $today
    ));

    if ($otp_count_today >= $per_day_limit) {
        wp_send_json_error(['message' => 'You have reached the OTP request limit for today.']);
    }
    $authkey     = get_option('msg91_auth_key');
    $sender      = get_option('msg91_sender_id');
    $template_id = get_option('msg91_template_id');

    $url = "https://control.msg91.com/api/v5/otp?authkey=$authkey&otp_expiry=5&template_id=$template_id&mobile=$mobile&realTimeResponse";

    $response = wp_remote_get($url);
    $body     = wp_remote_retrieve_body($response);
    $result   = json_decode($body, true);
    if (isset($result['type']) && $result['type'] === 'success') {
       
        $wpdb->insert($table_name, [
            'mobile_number' => $mobile,
            'ip_address'    => $ip_address,
            'created_at'    => current_time('mysql')
        ]);

        wp_send_json_success([
            'message'     => 'OTP sent successfully.',
            'request_id'  => $result['request_id'] ?? null
        ]);
    } else {
        wp_send_json_error([
            'message' => $result['message'] ?? 'Failed to send OTP.'
        ]);
    }
}
add_action('wp_ajax_send_msg91_otp_ajax', 'send_msg91_otp_ajax');
add_action('wp_ajax_nopriv_send_msg91_otp_ajax', 'send_msg91_otp_ajax');
add_action('wp_ajax_msg91_auto_login_user', 'msg91_auto_login_user');
add_action('wp_ajax_nopriv_msg91_auto_login_user', 'msg91_auto_login_user');

function msg91_auto_login_user() {
    $mobile = sanitize_text_field($_POST['mobile']);
    if (empty($mobile)) {
        wp_send_json_error(['message' => 'Mobile number missing']);
    }

    $username = $mobile;
    $email = $username . '@example.com';

    $user = get_user_by('login', $username);

    if (!$user) {
        $user_id = wp_create_user($username, wp_generate_password(), $email);
        wp_update_user([
            'ID' => $user_id,
            'display_name' => $mobile,
        ]);
        $user = get_user_by('ID', $user_id);
    }
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, true);
    $session_lifetime = 60 * 60 * 24 * 30; 
    ini_set('session.gc_maxlifetime', $session_lifetime);
    session_set_cookie_params($session_lifetime);
    if (!session_id()) {
        session_start();
    }

    setcookie('msg91_verified_mobile', $mobile, time() + (30 * 24 * 60 * 60), COOKIEPATH, COOKIE_DOMAIN);
    setcookie('msg91_verified_user_id', $user->ID, time() + (30 * 24 * 60 * 60), COOKIEPATH, COOKIE_DOMAIN);


    wp_send_json_success([
        'message' => 'User logged in successfully',
        'name' => $user->display_name,
        'user_id' => $user->ID
        
    ]);
}

add_action('wp_ajax_verify_msg91_otp_ajax', 'verify_msg91_otp_ajax');
add_action('wp_ajax_nopriv_verify_msg91_otp_ajax', 'verify_msg91_otp_ajax');

function verify_msg91_otp_ajax() {
    $mobile = sanitize_text_field($_POST['mobile']);
    $otp    = sanitize_text_field($_POST['otp']);

    $url = "https://api.msg91.com/api/verifyRequestOTP.php?authkey=" . get_option('msg91_auth_key') . "&mobile={$mobile}&otp={$otp}";

    $response = wp_remote_get($url);
    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);

    if (isset($result['type']) && $result['type'] === 'success') {
        $user = get_user_by('login', $mobile);

        if ($user) {
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID, true);

            setcookie('msg91_verified_mobile', $mobile, time() + (30 * 24 * 60 * 60), COOKIEPATH, COOKIE_DOMAIN);
            setcookie('msg91_verified_user_id', $user->ID, time() + (30 * 24 * 60 * 60), COOKIEPATH, COOKIE_DOMAIN);
        
            $session_lifetime = 60 * 60 * 24 * 30; 
            ini_set('session.gc_maxlifetime', $session_lifetime);
            session_set_cookie_params($session_lifetime);
            if (!session_id()) {
                session_start();
            }
        
            wp_send_json_success([
                'message' => 'OTP Verified Successfully, User logged in',
                'user_id' => $user->ID
            ]);
        } else {
            msg91_auto_login_user();
        }
        setcookie('msg91_verified_mobile', $mobile, time() + (30 * 24 * 60 * 60), COOKIEPATH, COOKIE_DOMAIN);
         setcookie('msg91_verified_user_id', $user->ID, time() + (30 * 24 * 60 * 60), COOKIEPATH, COOKIE_DOMAIN);

        wp_send_json_success([
            'message' => 'OTP Verified Successfully',
            'user_id' => get_current_user_id() 
        ]);
    } else {
        wp_send_json_error(['message' => $result['message'] ?? 'OTP verification failed']);
    }
}




