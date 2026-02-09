=== Happy Coders OTP Login for WooCommerce ===
* Contributors: happycoders, kombiahrk, muthupandi2002, imgopi2002, sureshkumar22
* Tags: otp, woocommerce, msg91, whatsapp otp, email otp
* Requires at least: 5.0
* Tested up to: 6.9
* Requires PHP: 7.4
* Stable tag: 2.6
* License: GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html

Secure OTP login for WordPress & WooCommerce using SMS, WhatsApp, and Email. Send automated order alerts with the MSG91 API. Be passwordless!

== Description ==

Happy Coders OTP Login is a simple, secure, and customizable OTP login plugin for WordPress and WooCommerce sites. It enables users to log in using their mobile number via one-time password (OTP) verification, using the MSG91 SMS API, and also supports email-based OTP login.

The plugin supports full-screen and popup login forms, integrates smoothly with WooCommerce, and improves user experience by replacing traditional email/password logins with secure phone-based authentication.

Now, you can fully customize your transactional SMS messages using dynamic variables like `##customer_name##`, `##order_id##`, and more, directly from the plugin settings.

**Watch our [quick video tutorial](https://www.youtube.com/watch?v=JTToziAf5gM) to see how easy it is to set up!**

[youtube https://www.youtube.com/watch?v=JTToziAf5gM]

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
- Email OTP login option
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
- Customizable transactional SMS templates with dynamic variables (e.g., `##customer_name##`, `##order_id##`).
- Dynamic OTP length (4 or 6 digits).

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
- Customizable SMS message templates with dynamic variables.

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

= How does Email OTP work? =
Users must first register using Mobile OTP. On the first login after registration, they must verify their email address with an Email OTP. After this one-time email verification, they can use Email OTP for future logins.

== Screenshots ==

1. Admin settings screen (1/5)
2. Admin settings screen (2/5)
3. Admin settings screen (3/5)
4. Admin settings screen (4/5)
5. Admin settings screen (5/5)
6. OTP popup login
7. OTP full-screen login
8. OTP verification screen
9. Email OTP Verfication Screen
10. Email OTP Verfication Screen
11. Email OTP Verfication Screen


== Changelog ==

= 2.6 =
* Tweak: Added default Email OTP subject/body in settings when fields are empty.

= 2.4 =
* Feature: Added Email OTP login option.
* Tweak: Updated plugin version to 2.4.

= 2.3 =
* Feature: Added 'otp_length' parameter to the MSG91 API call for dynamic OTP length.
* Tweak: Updated plugin version to 2.3.

= 2.2 =
* Feature: Added setting to configure OTP length dynamically (4 or 6 digits).
* Tweak: Updated plugin version to 2.2.
* Fix: Minor bug fixes and improvements.

= 2.1 =
* Feature: Introduced customizable transactional SMS message templates with dynamic variable support (e.g., ##customer_name##, ##order_id##).
* Tweak: Enhanced settings page to allow direct input of SMS message templates using descriptive variables.
* Fix: Ensured backward compatibility for existing SMS notes by repurposing the field for message templates.

= 2.0 =
* Fix: Corrected an issue where SMS settings were not being saved properly.
* Feature: Added a migration function to move old settings to a new format.

= 1.9 =
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

= 2.6 =
Adds default Email OTP subject/body in settings when fields are empty.

= 2.4 =
This version adds Email OTP login. Review your OTP settings after updating.

= 2.3 =
This version adds the 'otp_length' parameter to the MSG91 API call. If you have customized the OTP length, ensure your settings are correct.

= 2.2 =
This version introduces the ability to configure OTP length dynamically (4 or 6 digits). Please review your OTP settings after updating.

= 2.1 =
This version introduces customizable transactional SMS message templates with dynamic variables. Your existing SMS notes will now be used as message templates. Please review your settings after updating.

= 2.0 =
This version includes important fixes for saving SMS settings and migrates your old settings to a new format. Please update to ensure all features work correctly.

= 1.9 =
General bug fixes and performance improvements.
