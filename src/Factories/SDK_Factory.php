<?php

namespace TwoFAS\MagicPassword\Factories;

use TwoFAS\Core\Http\Browser;
use TwoFAS\MagicPassword\Helpers\Config;
use TwoFAS\MagicPassword\Storage\Account_Storage;

abstract class SDK_Factory {

	/**
	 * @var Account_Storage
	 */
	protected $account_storage;

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @param Account_Storage $account_storage
	 * @param Config          $config
	 */
	public function __construct( Account_Storage $account_storage, Config $config ) {
		$this->account_storage = $account_storage;
		$this->config          = $config;
	}

	/**
	 * @return array
	 */
	protected function get_headers() {
		$browser     = new Browser();
		$app_name    = $this->account_storage->get_wp_name();
		$app_version = $this->account_storage->get_wp_version();
		$app_url     = $this->account_storage->get_wp_url();

		return array(
			'Plugin-Version'  => MPWD_PLUGIN_VERSION,
			'App-Version'     => $app_version,
			'App-Name'        => $app_name,
			'App-Url'         => $app_url,
			'Browser-Version' => $browser->describe(),
			'Php-Version'     => PHP_VERSION,
		);
	}
}
