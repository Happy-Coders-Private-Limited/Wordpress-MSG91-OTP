=== Happy Coders OTP Login for WooCommerce ===
* Contributors: happycoders, kombiahrk, muthupandi2002, imgopi2002, sureshkumar22
* Tags: otp, woocommerce, msg91, passwordless, whatsapp otp
* Requires at least: 5.0
* Tested up to: 6.8
* Requires PHP: 7.4
* Stable tag: 1.9
* License: GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html

Secure OTP login for WordPress & WooCommerce using SMS and WhatsApp. Send automated order alerts with the MSG91 API. Be passwordless!

== Description ==

Happy Coders OTP Login is a simple, secure, and customizable OTP login plugin for WordPress and WooCommerce sites. It enables users to log in using their mobile number via one-time password (OTP) verification, using the MSG91 SMS API.

The plugin supports full-screen and popup login forms, integrates smoothly with WooCommerce, and improves user experience by replacing traditional email/password logins with secure phone-based authentication.

**Watch our [quick video tutorial](https://www.youtube.com/watch?v=JTToziAf5gM) to see how easy it is to set up!**

[youtube https://www.youtube.com/watch?v=JTToziAf5gM]

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

This plugin uses the MSG91 SMS and WhatsApp gateway (https://msg91.com) to send and verify OTPs, and also to send order-related notifications. You must have a valid MSG91 account and approved SMS/WhatsApp templates.  You can [sign up here](https://msg91.com/signup?utm_source=happycoders)

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
3. Go to **MSG91 OTP & SMS** in the admin menu to configure the settings.
4. Enter your MSG91 credentials and setup options.
5. Add shortcodes to posts/pages/widgets for login.

== Configuration ==
1.  **Get an MSG91 Account:** This plugin requires an MSG91 account. If you don't have one, you can **[sign up here](https://msg91.com/signup?utm_source=happycoders)**.
2.  **Enter Credentials:** In the plugin settings, enter your MSG91 Auth Key, Sender ID, and DLT-approved Template IDs.
3.  **Display the Form:** Use the shortcode `[msg91_otp_form]` on any page or add the CSS class `otp-popup-trigger` to a button/link to show the login form.

== Support ==

We are committed to helping you succeed. To get you the fastest and most accurate help, please direct your query to the correct team.

== For Plugin Issues & Configuration (Happy Coders Support) ==
If you need help with installing the plugin, configuring its settings in WordPress, encounter a bug, or have a feature request for the plugin itself, please use our official support channel.
**Primary Support Channel:** [WordPress.org Support Forum](https://wordpress.org/support/plugin/happy-coders-otp-login/)

== For MSG91 Service & Delivery Issues (MSG91 Support) ==
If your question is about the MSG91 service itself—such as your account, API key, billing, Sender ID approval, DLT templates, or SMS/WhatsApp delivery reports—you must contact the MSG91 support team directly. They are the experts on their platform and can assist you with all service-related inquiries.
**Contact MSG91 Support:** [Visit the MSG91 Contact Page](https://msg91.com/in/contact-us)

== Frequently Asked Questions ==
= Do I need an MSG91 account? =
Yes, this plugin is a connector for the MSG91 service. You must have an active MSG91 account. **[Sign up for MSG91 here](https://msg91.com/signup?utm_source=happycoders)**.

= How do I display the login form? =
You have two easy options:
1.  **Shortcode:** Place `[msg91_otp_form]` on any page, post, or text widget.
2.  **Popup/Modal:** Add the CSS class `otp-popup-trigger` to any button or link. Example: `<a href="#" class="otp-popup-trigger">Login here</a>`.

= Is this compatible with WooCommerce? =
Yes, it works with WooCommerce login and sends order status notifications via SMS/WhatsApp.

= Can I disable certain SMS notifications? =
Yes. In the "Transactional SMS Settings" tab, each notification type (new order, shipped, etc.) can be individually enabled or disabled with a simple toggle.

== Screenshots ==

1. Admin settings screen (1/4)
2. Admin settings screen (2/4)
3. Admin settings screen (3/4)
4. Admin settings screen (4/4)
5. OTP popup login
6. OTP full-screen login
7. OTP verification screen

== Changelog ==

= 1.8 =
* Fix: General bug fixes and performance improvements.

= 1.8 =
* Fix: General bug fixes and performance improvements.

= 1.7 =
* Feature: Added support for sending OTPs via WhatsApp.
* Tweak: Improved UI and clarity on the settings pages.
* Fix: General bug fixes and performance improvements.

= 1.6 =
* Fix: Minor bug fixes and overall improvements for better performance and stability.

= 1.5 =
* Feature: Added automated SMS notifications for New User Registration, Order Placed, Order Shipped, Order Completed, and Abandoned Cart.
* Tweak: Enhanced admin settings UI for managing new transactional SMS features.
* Fix: Minor bug fixes and improvements.

= 1.0.0 =
* Initial release with OTP login features (full-screen and popup) and core MSG91 integration.

== Upgrade Notice ==

= 1.9 =
General bug fixes and performance improvements.