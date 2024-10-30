<?php

namespace TwoFAS\MagicPassword\Http;

use TwoFAS\Core\Http\Request as Base_Request;
use TwoFAS\MagicPassword\Http\Session\Session;

class Request extends Base_Request {

	/**
	 * @var Session
	 */
	private $session;

	/**
	 * @param array   $get
	 * @param array   $post
	 * @param array   $server
	 * @param Cookie  $cookie
	 * @param Session $session
	 */
	public function __construct( array $get, array $post, array $server, Cookie $cookie, Session $session ) {
		parent::__construct( $get, $post, $server, $cookie );

		$this->session = $session;
	}

	/**
	 * @return Session
	 */
	public function session() {
		return $this->session;
	}

	/**
	 * @return string
	 */
	public function action() {
		$action = $this->get( Action_Index::ACTION );

		if ( is_string( $action ) ) {
			return $action;
		}

		return '';
	}

	/**
	 * @return Nonce|null
	 */
	public function nonce() {
		$token = $this->post( '_wpnonce' );

		if ( empty( $token ) ) {
			return null;
		}

		return new Nonce( $token );
	}

	/**
	 * @param string $action
	 *
	 * @return bool
	 */
	public function validate_nonce( $action ) {
		$nonce = $this->nonce();

		if ( $nonce ) {
			return $nonce->validate( $action );
		}

		return false;
	}
}
