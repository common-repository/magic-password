<?php

namespace TwoFAS\MagicPassword\Helpers;

use TwoFAS\Core\Environment_Interface;

class Environment implements Environment_Interface {

	/**
	 * @return string
	 */
	public function get_wordpress_version() {
		return get_bloginfo( 'version' );
	}

	/**
	 * @return string
	 */
	public function get_php_version() {
		return PHP_VERSION;
	}
}
