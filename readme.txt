=== Happy Coders OTP Login ===
* Contributors: happycoders, kombiahrk, muthupandi2002, imgopi2002, sureshkumar22
* Tags: otp login, msg91, mobile login, phone number login, sms notifications
* Requires at least: 5.0
* Tested up to: 6.8
* Requires PHP: 7.4
* Stable tag: 1.7
* License: GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html

OTP login for WordPress/WooCommerce using MSG91 API with full-screen and popup forms, automatic SMS alerts for orders and registrations — and now WhatsApp OTP support for login.

== Description ==

Happy Coders OTP Login is a simple, secure, and customizable OTP login plugin for WordPress and WooCommerce sites. It enables users to log in using their mobile number via one-time password (OTP) verification, using the MSG91 SMS API.

The plugin supports full-screen and popup login forms, integrates smoothly with WooCommerce, and improves user experience by replacing traditional email/password logins with secure phone-based authentication.

*NEW in version 1.7:*  
- Automatic SMS notifications for:
  - New user registrations
  - WooCommerce order placed
  - Order shipped
  - Order completed
  - Cart cronjob reminders
- *WhatsApp Send OTP for login*: users can now receive OTP via WhatsApp instead of (or along with) SMS.

This keeps your customers engaged and ensures they never miss important alerts.

=== MSG91 Integration ===

This plugin uses the MSG91 SMS and WhatsApp gateway (https://msg91.com) to send and verify OTPs, and also to send order-related notifications. You must have a valid MSG91 account and approved SMS/WhatsApp templates.

Visit [MSG91's Terms of Service](https://msg91.com/legal/terms) and [Privacy Policy](https://msg91.com/legal/privacy) for more details about how they handle data

=== Data Handling and Privacy ===

- Only the **phone number** is sent to MSG91 for OTP and transactional SMS/WhatsApp delivery.
- No personal or sensitive user data is stored or tracked by this plugin.
- Plugin does **not collect analytics** or track users without consent.
- All configurable from the plugin settings page.

🔥 **Features:**
- Full-screen or popup OTP login form
- WooCommerce login compatibility
- OTP verification via MSG91 (SMS & WhatsApp)
- WhatsApp Send OTP support
- Automatic SMS/WhatsApp alerts for:
  - New user registration
  - Order placed
  - Order shipped
  - Order completed
  - Cart cronjob (abandoned cart reminders)
- Customizable resend timer
- Country code and flag selection
- Shortcodes for embedding login anywhere
- Admin panel for MSG91 and plugin settings
- Language translation support

🎯 **Shortcodes:**
- `[msg91_otp_form]` – Display full-screen OTP login form anywhere (pages, posts, widgets).

🔧 **Admin Settings:**
- MSG91 Auth Key, Sender ID, Template IDs
- Enable/disable WhatsApp OTP option
- Country code options
- OTP resend timer settings
- Button/text color customization
- Post-login redirect URL
- OTP send limit per user/day
- Enable/disable specific SMS/WhatsApp features (registration, order, cart)

== Installation ==
1. Upload the plugin to the `/wp-content/plugins/happy-coders-otp-login` directory.
2. Activate it from the ‘Plugins’ menu in WordPress.
3. Go to **MSG91 OTP & SMS** in the admin menu.
4. Enter your MSG91 credentials and setup options.
5. Add shortcodes to posts/pages/widgets for login.

== Frequently Asked Questions ==
= Do I need an MSG91 account? =
Yes. You must have an MSG91 account with active API access and approved SMS templates.

= Is this compatible with WooCommerce? =
Yes, it works with WooCommerce login and sends order status notifications via SMS/WhatsApp.

= Can I disable certain SMS notifications? =
Yes. Each SMS/WhatsApp type (registration, order, etc.) can be toggled on/off in the settings.

== Screenshots ==

1. Admin settings screen (1/4)
2. Admin settings screen (2/4)
3. Admin settings screen (3/4)
4. Admin settings screen (4/4)
5. OTP popup login
6. OTP full-screen login
7. OTP verification screen

== Changelog ==

= 1.7 =
* Added WhatsApp Send OTP support
* Minor bug fixes and improvements

= 1.6 =
* Minor bug fixes and improvements

= 1.5 =
* Added SMS notification for:
  - New user registration
  - Order placed
  - Order shipped
  - Order completed
  - Cart cronjob (abandoned cart)
* Enhanced admin settings UI
* Minor bug fixes and improvements

= 1.0.0 =
* Initial release with OTP login features and MSG91 integration

== Upgrade Notice ==

= 1.6 =
Recommended update: Minor bug fixes and overall improvements for better performance and stability.

