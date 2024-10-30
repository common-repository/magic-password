<?php

namespace TwoFAS\MagicPassword\Http\Controllers;

use TwoFAS\Account\Exception\Exception as Account_Exception;
use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Api\Exception\ValidationException as API_Validation_Exception;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\MagicPassword\Http\Request;
use TwoFAS\MagicPassword\Integration\API_Wrapper;
use TwoFAS\MagicPassword\Storage\Account_Storage;

class Channel_Authentication_Controller extends Controller {

	/**
	 * @var API_Wrapper
	 */
	private $api_wrapper;

	/**
	 * @var Account_Storage
	 */
	private $account_storage;

	/**
	 * @param API_Wrapper     $api_wrapper
	 * @param Account_Storage $account_storage
	 */
	public function __construct( API_Wrapper $api_wrapper, Account_Storage $account_storage ) {
		$this->api_wrapper     = $api_wrapper;
		$this->account_storage = $account_storage;
	}

	/**
	 * @param Request $request
	 *
	 * @return JSON_Response
	 *
	 * @throws TokenNotFoundException
	 * @throws API_Validation_Exception
	 * @throws API_Exception
	 * @throws Account_Exception
	 */
	public function authenticate_channel( Request $request ) {
		$integration_id = $this->account_storage->retrieve_integration_id();
		$session_id     = $request->post( 'session_id' );
		$socket_id      = $request->post( 'socket_id' );
		$result         = $this->api_wrapper->authenticate_channel( $integration_id, $session_id, $socket_id );

		return $this->json( $result );
	}
}
