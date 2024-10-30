<?php
/**
 * Plugin Name: Magic Password
 * Plugin URI:  https://magicpassword.io
 * Description: Magic Password is a free security plugin, which allows you to log in by scanning QR code. It's simple, quick, and highly secure—like magic!
 * Version:     2.0.0
 * Author:      Two Factor Authentication Service Inc.
 * Author URI:  https://2fas.com
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mpwd_deactivation() {
	wp_clear_scheduled_hook( 'mpwd_delete_expired_sessions' );
}

function mpwd_start() {
	require_once 'constants.php';
	require_once 'start.php';
}

register_deactivation_hook( __FILE__, 'mpwd_deactivation' );

add_action( 'init', 'mpwd_start' );
