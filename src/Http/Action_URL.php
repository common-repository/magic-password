<?php

namespace TwoFAS\MagicPassword\Http;

use TwoFAS\Core\Http\URL_Interface;

class Action_URL implements URL_Interface {

	/**
	 * @var string
	 */
	private $page;

	/**
	 * @var string
	 */
	private $action;

	/**
	 * @param string $page
	 * @param string $action
	 */
	public function __construct( $page, $action = '' ) {
		$this->page   = $page;
		$this->action = $action;
	}

	/**
	 * @return string
	 */
	public function get() {
		$url = admin_url( 'admin.php' );
		$url = add_query_arg( Action_Index::PAGE, $this->page, $url );

		if ( ! empty( $this->action ) ) {
			$url = add_query_arg( Action_Index::ACTION, $this->action, $url );
		}

		return $url;
	}
}
