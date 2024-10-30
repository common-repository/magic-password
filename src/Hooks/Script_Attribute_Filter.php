<?php

namespace TwoFAS\MagicPassword\Hooks;

use TwoFAS\Core\Hooks\Hook_Interface;

class Script_Attribute_Filter implements Hook_Interface {

	public function register_hook() {
		add_filter( 'script_loader_tag', array( $this, 'add_attribute' ), 10, 3 );
	}

	/**
	 * @param string $tag
	 * @param string $handle
	 * @param string $src
	 *
	 * @return string
	 */
	public function add_attribute( $tag, $handle, $src ) {
		if ( 'sentry' === $handle ) {
			$tag = '<script type="text/javascript" src="' . esc_url( $src ) . '" crossorigin="anonymous"></script>';
		}

		return $tag;
	}
}
