<?php

namespace TwoFAS\MagicPassword\Hooks;

use TwoFAS\Core\Hooks\Hook_Interface;
use TwoFAS\MagicPassword\Http\Session\Session;

class Regenerate_Session_Action implements Hook_Interface {

	/**
	 * @var Session
	 */
	private $session;

	/**
	 * @param Session $session
	 */
	public function __construct( Session $session ) {
		$this->session = $session;
	}

	public function register_hook() {
		add_action( 'wp_login', array( $this->session, 'regenerate' ) );
	}
}
