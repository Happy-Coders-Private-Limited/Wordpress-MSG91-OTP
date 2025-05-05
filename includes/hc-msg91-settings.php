<?php
add_action('admin_menu', function() {
    add_menu_page(
        __('MSG91 OTP Settings'),
        __('MSG91 OTP'),
        'manage_options',
        'msg91-otp-settings',
        'msg91_otp_settings_page',
        'dashicons-smartphone',
        56
    );
});



add_action('admin_init', function() {
    register_setting('msg91_otp_settings_group', 'msg91_auth_key', 'sanitize_text_field');
    register_setting('msg91_otp_settings_group', 'msg91_sender_id', 'sanitize_text_field');
    register_setting('msg91_otp_settings_group', 'msg91_template_id', 'sanitize_text_field');
    register_setting('msg91_otp_settings_group', 'msg91_resend_timer', 'intval');
    register_setting('msg91_otp_settings_group', 'msg91_default_country', 'sanitize_text_field');
    register_setting('msg91_otp_settings_group', 'msg91_selected_countries', function($input) {
        return array_map('sanitize_text_field', (array) $input);
    });
    register_setting('msg91_otp_settings_group', 'msg91_top_image', 'esc_url_raw');
    register_setting('msg91_otp_settings_group', 'msg91_top_verify_image', 'esc_url_raw');
    register_setting('msg91_otp_settings_group', 'msg91_perday_otplimit', 'intval');
    register_setting('msg91_otp_settings_group', 'msg91_flag_show', function($value) {
        return $value === '1' ? 1 : 0;
    });
    register_setting('msg91_otp_settings_group', 'msg91_redirect_page', 'esc_url_raw');
    register_setting('msg91_otp_settings_group', 'msg91_sendotp_lable', 'sanitize_text_field');
    register_setting('msg91_otp_settings_group', 'msg91_sendotp_lable_color', 'sanitize_hex_color');
    register_setting('msg91_otp_settings_group', 'msg91_sendotp_dec', 'sanitize_text_field');
    register_setting('msg91_otp_settings_group', 'msg91_sendotp_dec_color', 'sanitize_hex_color');
    register_setting('msg91_otp_settings_group', 'msg91_sendotp_validation_msg', 'sanitize_text_field');
    register_setting('msg91_otp_settings_group', 'msg91_sendotp_button_text', 'sanitize_text_field');
    register_setting('msg91_otp_settings_group', 'msg91_sendotp_button_color', 'sanitize_hex_color');
    register_setting('msg91_otp_settings_group', 'msg91_verifyotp_lable', 'sanitize_text_field');
    register_setting('msg91_otp_settings_group', 'msg91_verifyotp_lable_color', 'sanitize_hex_color');
    register_setting('msg91_otp_settings_group', 'msg91_verifyotp_dec', 'sanitize_text_field');
    register_setting('msg91_otp_settings_group', 'msg91_verifyotp_desc_color', 'sanitize_hex_color');
    register_setting('msg91_otp_settings_group', 'msg91_verifyotp_validation_msg', 'sanitize_text_field');
    register_setting('msg91_otp_settings_group', 'msg91_verifyotp_button_text', 'sanitize_text_field');
    register_setting('msg91_otp_settings_group', 'msg91_verifyotp_button_color', 'sanitize_hex_color');
});

function msg91_otp_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php echo __msg91('Happy Coders MSG91 OTP Settings'); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields('msg91_otp_settings_group'); ?>
            <?php do_settings_sections('msg91_otp_settings_group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php echo __msg91('MSG91 Auth Key'); ?></th>
                    <td><input type="text" name="msg91_auth_key" value="<?php echo esc_attr(get_option('msg91_auth_key')); ?>" size="50" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo __msg91('Sender ID'); ?></th>
                    <td><input type="text" name="msg91_sender_id" value="<?php echo esc_attr(get_option('msg91_sender_id')); ?>" size="30" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo __msg91('Template ID'); ?></th>
                    <td><input type="text" name="msg91_template_id" value="<?php echo esc_attr(get_option('msg91_template_id')); ?>" size="30" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo __msg91('User OTP Limit per day'); ?></th>
                    <td><input type="number" name="msg91_perday_otplimit" value="<?php echo esc_attr(get_option('msg91_perday_otplimit')); ?>" size="30" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo __msg91('Resend OTP Timer (sec)'); ?></th>
                    <td>
                        <input type="number" name="msg91_resend_timer" 
                            value="<?php echo esc_attr(get_option('msg91_resend_timer', 60)); ?>" size="30" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo __msg91('Popup Login'); ?></th>
                    <td> 
                        <p >
                            <?php echo __msg91('You can also use the class name'); ?> 
                            <code style="font-size:16px; color:#0073aa;">otp-popup-trigger</code> 
                            <?php echo __msg91('to trigger the popup on any element'); ?>.
                        </p>
                    </td>
                </tr>


                <tr valign="top">
                    <th scope="row"><?php echo __msg91('Screen based Login'); ?></th>
                    <td>
                        <p style="margin: 0 0 5px;">
                            <?php echo __msg91('Use this shortcode to display the screen OTP-based login form'); ?>
                        </p>
                        <code id="msg91-shortcode" style="font-size:16px; color:#0073aa;">
                            <?php echo '[msg91_otp_form]'; ?>
                        </code>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo __msg91('Image URL for Send OTP Form'); ?></th>
                    <td>
                        <input type="text" name="msg91_top_image" value="<?php echo esc_attr(get_option('msg91_top_image', plugin_dir_url(__FILE__) . 'assets/images/send-otp.png')); ?>" size="60" />
                        <p class="description"><?php echo __msg91('Paste the full image URL to display above the OTP form (e.g. banner, logo).'); ?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo __msg91('Send OTP Form Lable'); ?></th>
                    <td><input type="text" name="msg91_sendotp_lable" value="<?php echo esc_attr(get_option('msg91_sendotp_lable')); ?>" size="50" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo __msg91('Send OTP Lable Color'); ?></th>
                    <td><input type="color" name="msg91_sendotp_lable_color" value="<?php echo esc_attr(get_option('msg91_sendotp_lable_color')); ?>" size="30" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo __msg91('Send OTP Form Decription'); ?></th>
                    <td><input type="text" name="msg91_sendotp_dec" value="<?php echo esc_attr(get_option('msg91_sendotp_dec')); ?>" size="50" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo __msg91('Send OTP Decription Color'); ?></th>
                    <td><input type="color" name="msg91_sendotp_dec_color" value="<?php echo esc_attr(get_option('msg91_sendotp_dec_color')); ?>" size="30" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo __msg91('Send OTP Form Button Text'); ?></th>
                    <td><input type="text" name="msg91_sendotp_button_text" value="<?php echo esc_attr(get_option('msg91_sendotp_button_text')); ?>" size="50" /></td>
                </tr>


                <tr valign="top">
                    <th scope="row"><?php echo __msg91('Send OTP Button Color'); ?></th>
                    <td><input type="color" name="msg91_sendotp_button_color" value="<?php echo esc_attr(get_option('msg91_sendotp_button_color')); ?>" size="30" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo __msg91('Send OTP Form validation msg'); ?></th>
                    <td><input type="text" name="msg91_sendotp_validation_msg" value="<?php echo esc_attr(get_option('msg91_sendotp_validation_msg')); ?>" size="50" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo __msg91('Image URL for Verify OTP Form'); ?></th>
                    <td>
                        <input type="text" name="msg91_top_verify_image" value="<?php echo esc_attr(get_option('msg91_top_verify_image', plugin_dir_url(__FILE__) . 'assets/images/verify-otp.png')); ?>" size="60" />
                        <p class="description"><?php echo __msg91('Paste the full image URL to display above the OTP form (e.g. banner, logo).'); ?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo __msg91('Verify OTP Form Lable'); ?></th>
                    <td><input type="text" name="msg91_verifyotp_lable" value="<?php echo esc_attr(get_option('msg91_verifyotp_lable')); ?>" size="50" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo __msg91('Verify OTP Lable Color'); ?></th>
                    <td><input type="color" name="msg91_verifyotp_lable_color" value="<?php echo esc_attr(get_option('msg91_verifyotp_lable_color')); ?>" size="30" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo __msg91('Verify OTP Form Decription'); ?></th>
                    <td><input type="text" name="msg91_verifyotp_dec" value="<?php echo esc_attr(get_option('msg91_verifyotp_dec')); ?>" size="50" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo __msg91('Verify OTP Decription Color'); ?></th>
                    <td><input type="color" name="msg91_verifyotp_dec_color" value="<?php echo esc_attr(get_option('msg91_verifyotp_dec_color')); ?>" size="30" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo __msg91('Verify OTP Form Button Text'); ?></th>
                    <td><input type="text" name="msg91_verifyotp_button_text" value="<?php echo esc_attr(get_option('msg91_verifyotp_button_text')); ?>" size="50" /></td>
                </tr>

               
                <tr valign="top">
                    <th scope="row"><?php echo __msg91('Verify OTP Button Color'); ?></th>
                    <td><input type="color" name="msg91_verifyotp_button_color" value="<?php echo esc_attr(get_option('msg91_verifyotp_button_color')); ?>" size="30" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo __msg91('Verify OTP Form validation msg'); ?></th>
                    <td><input type="text" name="msg91_verifyotp_validation_msg" value="<?php echo esc_attr(get_option('msg91_verifyotp_validation_msg')); ?>" size="50" /></td>
                </tr>


                <tr valign="top">
                    <th scope="row"><?php echo __msg91('Redirect Page URL'); ?></th>
                    <td>
                        <input type="text" name="msg91_redirect_page" 
                            value="<?php echo esc_attr(get_option('msg91_redirect_page', home_url())); ?>" 
                            size="60" />
                        <p class="description"><?php echo __msg91('Enter the URL where users should be redirected after login. Example:'); ?> <code><?php echo home_url('/dashboard'); ?></code></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo __msg91('Default Country'); ?></th>
                    <td>
                        <select name="msg91_default_country" id="msg91_default_country">
                            <?php 
                                $default_country = get_option('msg91_default_country', '+91'); 
                                $countries = hc_msg91_get_countries_with_iso();
                                                            
                                foreach ($countries as $country) {
                                    $dial_code = $country['code'];
                                    $name = $country['name'];
                                    $iso = $country['iso'];
                                    $selected = $default_country === $dial_code ? 'selected' : ''; 
                                    $flag = hc_msg91_iso_to_flag($iso);
                                    echo "<option value='$dial_code' $selected>$flag $name ($dial_code)</option>"; 
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"></th>
                    <td>
                        <input type="checkbox" name="msg91_flag_show" value="1" <?php checked(1, get_option('msg91_flag_show'), true); ?> />
                        <span class="description"><?php echo __msg91('Do you want to show country flag?'); ?></span>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo __msg91('Selected Countries'); ?></th>
                    <td>
                        <select name="msg91_selected_countries[]" id="msg91_selected_countries" multiple="multiple" style="width: 100%; height: 150px;">
                            <?php 
                                $default_countries = get_option('msg91_selected_countries', ['+91']); 
                                $countries = hc_msg91_get_countries_with_iso(); 
                                foreach ($countries as $country) {
                                    $dial_code = $country['code'];
                                    $name = $country['name'];
                                    $iso = $country['iso'];
                            
                                    $selected = in_array($dial_code, $default_countries) ? 'selected' : ''; 
                                    $flag = hc_msg91_iso_to_flag($iso);
                                    
                                    $background_color = ($selected) ? 'background-color: #009ee8; color: white;' : '';
                                    
                                    echo "<option value='$dial_code' $selected style='$background_color'>$flag $name ($dial_code)</option>"; 
                                }
                            ?>
                        </select>
                    </td>
                 </tr>
              
                
              
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
