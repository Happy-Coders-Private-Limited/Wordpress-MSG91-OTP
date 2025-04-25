=== MSG91 OTP for woocommerce ===
* Contributors: happycoders, muthupandi2002, gopiananthc, sureshkumar22
Tags: otp login, msg91, mobile login, phone number login, woocommerce otp
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A simple and secure OTP login system for WordPress/WooCommerce using MSG91 SMS API with full-screen and popup login options.

== Description ==

MSG91 OTP Login is a powerful and lightweight plugin that enables OTP-based login and verification for your WordPress site using the MSG91 SMS gateway.

🔥 **Features:**
- Screen Based or popup OTP login form
- WooCommerce compatibility
- Customizable resend timer
- Country code and flag selection
- Multi-language ready
- Shortcodes for embedding login forms anywhere
- Admin panel for easy configuration


🎯 **Shortcodes:**
- `[msg91_otp_popup_form]` – Display popup OTP login. You can use this shortcode anywhere to open a popup-based OTP login form.
- `[msg91_otp_form]` – Display full-screen OTP login form. You can use this shortcode anywhere (pages, posts, or widgets) to allow users to log in via OTP.


🔧 **Admin Settings:**
- MSG91 Auth Key, Sender ID, and Template ID
- Selectable default and available countries
- OTP resend timer configuration
- Button and label colors
- Redirect URL after login
- Limit OTP sends per user per day

🌐 **Translation Ready:**
This plugin supports the following languages:
- English
- Spanish (es_ES)
- Tamil (ta_IN)

Translations are automatically loaded based on the WordPress site language settings. You can change the site language from **Settings → General → Site Language**, and the plugin will switch to the corresponding language.

== Installation ==
1. Upload the plugin to the `/wp-content/plugins/msg91-otp` directory.
2. Activate the plugin through the ‘Plugins’ menu in WordPress.
3. Navigate to **MSG91 OTP Settings** under the WordPress admin menu.
4. Enter your MSG91 credentials and configure options.
5. Use the shortcodes in posts/pages/widgets to enable OTP login.

== Frequently Asked Questions ==
= Do I need an MSG91 account? =
Yes, you need an MSG91 account and access to their API (Auth Key and Template ID).

= Can I use this with WooCommerce? =
Yes, it's fully compatible with WooCommerce login page.

= How can I translate the plugin? =
You can change the site language from **Settings → General → Site Language**, and the plugin will switch to the corresponding language.

== Screenshots ==

1. Admin settings screen
   ![Admin Settings Screen](assets/images/admin-settings.png)

2. Admin settings screen
![Admin Settings Screen](assets/images/admin-settings-2.png)

3. OTP popup login
   ![OTP Popup Login](assets/images/otp-popup-login.png)

4. OTP full-screen login
   ![OTP Full-Screen Login](assets/images/otp-full-screen-login.png)

4. OTP full-screen login
![OTP Full-Screen Login](assets/images/otp-full-screen-verifyotp.png)


== Changelog ==

= 1.0.0 =
* Initial release with full OTP login features and MSG91 integration.

== Upgrade Notice ==

= 1.0.0 =
First stable version of MSG91 OTP Login plugin.
