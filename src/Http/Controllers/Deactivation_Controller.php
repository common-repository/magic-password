<?php

namespace TwoFAS\MagicPassword\Http\Controllers;

use TwoFAS\Account\Exception\Exception;
use TwoFAS\Account\HttpClient\ClientInterface;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\MagicPassword\Http\Request;

class Deactivation_Controller extends Controller {

	/**
	 * @var ClientInterface
	 */
	private $http_client;

	/**
	 * @param ClientInterface $http_client
	 */
	public function __construct( ClientInterface $http_client ) {
		$this->http_client = $http_client;
	}

	/**
	 * @param Request $request
	 *
	 * @return JSON_Response
	 *
	 * @throws Exception
	 */
	public function send_deactivation_reason( Request $request ) {
		$message = trim( $request->post( 'message' ) );

		if ( ! empty( $message ) ) {
			$headers = array( 'Content-Type' => 'application/json' );
			$data    = array(
				'name'    => 'Magic Password Deactivation',
				'email'   => 'noreply@magicpassword.io',
				'message' => stripslashes( $request->post( 'message' ) ),
			);

			$this->http_client->request( 'POST', 'https://magicpassword.io/send-mail', $data, $headers );
		}

		return $this->json( array() );
	}
}
