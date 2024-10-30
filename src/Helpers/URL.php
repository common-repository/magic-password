<?php

namespace TwoFAS\MagicPassword\Helpers;

use TwoFAS\MagicPassword\Http\Action_Index;

class URL {

	/**
	 * @param string $action
	 * @param string $page
	 *
	 * @return string
	 */
	public function make( $action = '', $page = Action_Index::PAGE_CONFIGURATION ) {
		$url = admin_url( 'admin.php' );
		$url = add_query_arg( Action_Index::PAGE, $page, $url );

		if ( $action ) {
			$url = add_query_arg( Action_Index::ACTION, $action, $url );
		}

		return $url;
	}

	/**
	 * @param string $action
	 *
	 * @return string
	 */
	public function make_with_nonce( $action = '' ) {
		$url = $this->make( $action );

		return wp_nonce_url( $url, $action );
	}

	/**
	 * @param string $action
	 *
	 * @return string
	 */
	public function make_form_nonce( $action ) {
		return wp_nonce_field( $action, '_wpnonce', true, false );
	}
}
