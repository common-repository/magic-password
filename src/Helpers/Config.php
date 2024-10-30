<?php

namespace TwoFAS\MagicPassword\Helpers;

class Config {

	/**
	 * @var array
	 */
	private $config;

	public function __construct() {
		$this->config = require MPWD_PLUGIN_PATH . 'config.php';
	}

	/**
	 * @return string
	 */
	public function get_api_url() {
		return $this->config['api_url'];
	}

	/**
	 * @return string
	 */
	public function get_account_url() {
		return $this->config['account_url'];
	}

	/**
	 * @return string
	 */
	public function get_pusher_key() {
		return $this->config['pusher_key'];
	}

	/**
	 * @return array
	 */
	public function get_sentry_dsn() {
		return $this->config['sentry_dsn'];
	}

	/**
	 * @return string
	 */
	public function get_readme_url() {
		return $this->config['readme_url'];
	}
}
