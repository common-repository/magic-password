<?php

namespace TwoFAS\MagicPassword\Hooks;

use TwoFAS\Core\Hooks\Hook_Interface;
use TwoFAS\MagicPassword\Helpers\URL;
use TwoFAS\MagicPassword\Http\Action_Index;

class Action_Links_Filter implements Hook_Interface {

	/**
	 * @var URL
	 */
	private $url;

	/**
	 * @param URL $url
	 */
	public function __construct( URL $url ) {
		$this->url = $url;
	}

	public function register_hook() {
		add_filter( 'plugin_action_links_' . MPWD_PLUGIN_BASENAME, array( $this, 'add_settings_link' ) );
	}

	/**
	 * @param array $links
	 *
	 * @return array
	 */
	public function add_settings_link( array $links ) {
		$settings = $this->create_settings_link();

		return array_merge( $settings, $links );
	}

	/**
	 * @return array
	 */
	private function create_settings_link() {
		$url  = $this->url->make( '', Action_Index::PAGE_SETTINGS );
		$link = '<a href="' . $url . '">Settings</a>';

		return array(
			'settings' => $link,
		);
	}
}
