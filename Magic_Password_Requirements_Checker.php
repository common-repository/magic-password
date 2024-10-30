<?php

class Magic_Password_Requirements_Checker {

	const WP_MINIMUM_VERSION  = '4.2';
	const PHP_MINIMUM_VERSION = '5.3.3';

	/**
	 * @var bool
	 */
	private $is_checked = false;

	/**
	 * @var array
	 */
	private $not_satisfied = array();

	/**
	 * @return bool
	 */
	public function are_satisfied() {
		if ( ! $this->is_checked ) {
			$this->check_requirements();
		}

		return empty( $this->not_satisfied );
	}

	/**
	 * @return array
	 */
	public function get_not_satisfied() {
		return $this->not_satisfied;
	}

	/**
	 * @return bool
	 */
	public function check_wp_version() {
		$wp_version = get_bloginfo( 'version' );

		return version_compare( $wp_version, self::WP_MINIMUM_VERSION, '>=' );
	}

	/**
	 * @return bool
	 */
	public function check_php_version() {
		return version_compare( PHP_VERSION, self::PHP_MINIMUM_VERSION, '>=' );
	}

	/**
	 * @return bool
	 */
	public function check_curl() {
		return extension_loaded( 'curl' );
	}

	/**
	 * @return bool
	 */
	public function check_gd() {
		return extension_loaded( 'gd' );
	}

	/**
	 * @return bool
	 */
	public function check_mbstring() {
		return extension_loaded( 'mbstring' );
	}

	/**
	 * @return bool
	 */
	public function check_openssl() {
		return extension_loaded( 'openssl' );
	}

	private function check_requirements() {
		if ( ! $this->check_wp_version() ) {
			$this->not_satisfied( 'Magic Password plugin does not support your WordPress version. Minimum required version is ' . self::WP_MINIMUM_VERSION . '.' );
		}

		if ( ! $this->check_php_version() ) {
			$this->not_satisfied( 'Magic Password plugin does not support your PHP version. Minimum required version is ' . self::PHP_MINIMUM_VERSION . '.' );
		}

		if ( ! $this->check_curl() ) {
			$this->not_satisfied( $this->get_php_extension_message( 'cURL' ) );
		}

		if ( ! $this->check_gd() ) {
			$this->not_satisfied( $this->get_php_extension_message( 'GD' ) );
		}

		if ( ! $this->check_mbstring() ) {
			$this->not_satisfied( $this->get_php_extension_message( 'Multibyte String' ) );
		}

		if ( ! $this->check_openssl() ) {
			$this->not_satisfied( $this->get_php_extension_message( 'OpenSSL' ) );
		}

		$this->is_checked = true;
	}

	/**
	 * @param string $message
	 */
	private function not_satisfied( $message ) {
		$this->not_satisfied[] = $message;
	}

	/**
	 * @param string $extension
	 *
	 * @return string
	 */
	private function get_php_extension_message( $extension ) {
		return 'Magic Password plugin requires ' . $extension . ' extension to work properly.';
	}
}
