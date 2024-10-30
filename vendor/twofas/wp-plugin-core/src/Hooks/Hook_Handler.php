<?php

namespace TwoFAS\Core\Hooks;

use TwoFAS\Core\Http\View_Response;

class Hook_Handler {

	/**
	 * @var Hook_Interface[]
	 */
	private $hooks = array();

	/**
	 * @param Hook_Interface $hook
	 *
	 * @return Hook_Handler
	 */
	public function add_hook( Hook_Interface $hook ) {
		$this->hooks[] = $hook;

		return $this;
	}

	/**
	 * @param View_Response $response
	 */
	public function register_hooks( View_Response $response ) {
		foreach ( $this->hooks as $hook ) {
			if ( $hook instanceof Admin_Menu_Action ) {
				$hook->set_response( $response );
			}

			$hook->register_hook();
		}
	}
}
