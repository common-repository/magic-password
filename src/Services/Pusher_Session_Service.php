<?php

namespace TwoFAS\MagicPassword\Services;

use TwoFAS\Encryption\Exceptions\RandomBytesGenerateException;
use TwoFAS\MagicPassword\Helpers\Hash;
use TwoFAS\MagicPassword\Http\Session\Session;

class Pusher_Session_Service {

	const PUSHER_SESSION_KEY = 'pusher_session_id';

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

	/**
	 * @return string
	 *
	 * @throws RandomBytesGenerateException
	 */
	public function get_session_id() {
		if ( $this->session->exists( self::PUSHER_SESSION_KEY ) ) {
			return $this->session->get( self::PUSHER_SESSION_KEY );
		}

		$session_id = Hash::generate();
		$this->session->set( self::PUSHER_SESSION_KEY, $session_id );

		return $session_id;
	}
}
