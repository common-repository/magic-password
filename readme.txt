=== Magic Password ===
Contributors: 2fas
Tags: passwordless, password, 2fa, authentication, verification, passwordless wordpress, passwordless authentication, security, token, otp, totp, login, magicpassword
Requires at least: 4.2
Tested up to: 5.2
Requires PHP: 5.3.3
Stable tag: trunk
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Magic Password is a free security plugin, which allows you to log in by scanning QR code. It's simple, quick, and highly secure—like magic!

== Notice ==
**This plugin is no longer maintained and supported!** For more information please check out our website at: [https://magicpassword.io](https://magicpassword.io)

== Description ==

Forget your pass****

Forget your password, forget your username. Make your WordPress log in process completely passwordless. All you need is a phone! Magic Password is a free security Clef-like app which allows you to log in to your WordPress in a flash. Just open your application, scan QR code, and it's done. It's simple, quick and highly secure—like magic! It's even better than two-factor authentication (2FA) because you don't have to enter login, password and 2FA code.

[youtube https://www.youtube.com/watch?v=-2H_2RLSdNk]

We use cutting-edge, hash-based message authentication codes, so you can be sure that the log in process is highly secure. How does it work? Under the hood, a cryptographic hash function combines a secret key with current timestamp. Every 30 seconds it generates a unique code which replaces your password. We use end-to-end (e2e) encryption and do not store personal identifiable information (PII) about you or your users.

And you don't need registration at all! Just download the app, install WordPress plugin, pair your devices, and you are ready to go!

Before installing this plugin, please make sure you don't use any other plugins which modify the login process.

For more information please check out our website at: [https://magicpassword.io](https://magicpassword.io)

If you need our support, please contact us at: support@magicpassword.io

[Download Magic Password Android App](https://play.google.com/store/apps/details?id=io.magicpassword)
[Download Magic Password iOS App](https://itunes.apple.com/us/app/magic-password-forget-your-password/id1240404220?mt=8)

Note that although we do not require any registration, we do use third-party services in order to make this plugin work:

- [https://2fas.com](https://2fas.com) — for authentication requests and communication with a mobile app
- [https://pusher.com](https://pusher.com) — for real-time feedback in a browser

We place great emphasis on security and privacy, thus we don't send or store any personal identifiable information (which includes not sending any e-mail addresses).

== Installation ==

1. From the "Plugins" menu search for "Magic Password", click "Install Now" and then "Activate".
2. Choose Magic Password from menu, download our mobile app.
3. Scan the Magic Code through our app.
4. That's it!

**Plugin requirements**:

- PHP 5.3.3 or newer (PHP 7.3 or newer is recommended)
- PHP extensions: cURL, GD, Multibyte String and OpenSSL
- WordPress 4.2 or newer (WordPress 5.2 or newer is recommended)
- JavaScript enabled
- A database user must have privileges for creating and deleting tables

Important notice: Magic Password is not compatible with a multisite mode.

If you have any problems with the installation, please contact us at support@magicpassword.io

== Frequently Asked Questions ==

= What do I need to start using Magic Password? =
All you need to do is to download mobile app on your smartphone. Currently we support only iOS and Android systems.

[iOS](https://itunes.apple.com/us/app/magic-password-forget-your-password/id1240404220?mt=8)
[Android](https://play.google.com/store/apps/details?id=io.magicpassword)

= Is it really safe? =
It might looks too easy to be secure, but in fact we worked really hard to create this service. Please note that we don't keep any sensitive data (i.e. login or password) on our side. Additionally we pass whole communication through multilevel encryption system.

= Will it always be free? =
Yes! Magic Password will always be free.

= What is your privacy policy? =
We place great emphasis on security and privacy, thus we don't send or store any personal identifiable information (which includes not sending any e-mail addresses). However, Magic Password sends to our API data which is important to provide website security and high quality technical support. Below you can find what kind of data is being sent:

- Website URL with the name and version of the WordPress installation
- PHP version
- Magic Password version
- Browser name

Thanks to this data we can show WordPress site name in our mobile application.

As of version 1.3.0, we also use this data to provide backward compatibility of our plugin, because we've changed the algorithm of generating encrypted data. Old versions use the old algorithm, so we need to know which version of our plugin is installed on a website.

This data is also necessary in order to provide technical support. Otherwise it would be very difficult to find the causes of problems experienced by our clients, because we would have to ask for this data each time someone reports an issue. This data gives us basic knowledge about the environment where our plugin is installed, so we have the ability to try to reproduce the same issue as our client's.

== Screenshots ==

1. All you need to configure the plugin is to scan the QR code.
2. The application has been paired with WordPress account.
3. Login via Magic Password is configured.
4. Logging in with Magic Password.
5. To log in on a mobile device just tap the login QR code.
6. Login via Magic Password can be set as obligatory for specific roles.

== Changelog ==

= 2.0.0 (Apr. 1, 2020) =
* Support for plugin is abandoned
* For each user forced login with magic password is disabled

= 1.5.0 (Sep. 19, 2019) =
* Refresh OAuth Tokens with expiry date of one year
* Fixed send button in deactivation form
* Sets PHP 5.3 version as deprecated
* Upgrade Pusher version (using for web sockets)
* Improved login process

= 1.4.5 (Jun. 13, 2019) =
* Fixed bug with getting user's ID from session

= 1.4.4 (May 22, 2019) =
* Fixed the Settings link
* Fixed autofocus when switching to standard login view
* Handled uncaught exceptions
* Added deactivation survey
* Changed radio buttons order
* Improved notifications performance
* Changed login settings confirmation workflow
* Improved error logging
* Minor frontend improvements

= 1.4.3 (Feb. 7, 2019) =
* Fixed object overriding in JavaScript's file

= 1.4.2 (Oct. 31, 2018) =
* Fixed redundant API requests

= 1.4.1 (Oct. 29, 2018) =
* Deleted empty file
* Updated options which should be deleted during uninstallation
* Added details to error messages
* New account is not created automatically if some data persists in database
* Fixed reported bad coding practice
* Hid "Back to" link on login second step screen
* Updated SDK
* JavaScript fixes
* Fixed AJAX route error

= 1.4.0 (Apr. 9, 2018) =
* Added button to enabling and disabling plugin for all accounts
* Improved overall plugin performance
* Plugin is asking for a voluntary rating
* Fixed second step of a login process
* Fixed interim login
* Fixed compatibility issue with WordPress 4.2
* Fixed console error

== Upgrade Notice ==

= 1.3.2 =
Important notice! Please deactivate plugin before update.
