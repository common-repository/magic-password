<?php

namespace TwoFAS\MagicPassword\Http\Controllers;

use TwoFAS\Core\Http\Controller as Base_Controller;
use TwoFAS\Core\Http\JSON_Response;

abstract class Controller extends Base_Controller {

	/**
	 * @param string $message
	 * @param int    $status_code
	 *
	 * @return JSON_Response
	 */
	protected function json_error( $message, $status_code ) {
		return $this->json( array( 'error' => $message ), $status_code );
	}
}
