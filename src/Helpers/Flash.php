<?php

namespace TwoFAS\MagicPassword\Helpers;

use TwoFAS\MagicPassword\Exceptions\Invalid_Flash_Message_Type_Exception;
use TwoFAS\MagicPassword\Http\Cookie;

class Flash {

	/**
	 * @var Cookie
	 */
	private $cookie;

	/**
	 * @var array
	 */
	private $messages;

	/**
	 * @param Cookie $cookie
	 */
	public function __construct( Cookie $cookie ) {
		$this->cookie   = $cookie;
		$this->messages = $this->fetch_messages();
	}

	/**
	 * @param string $type
	 *
	 * @return array
	 */
	public function get_messages( $type ) {
		if ( ! array_key_exists( $type, $this->messages ) ) {
			return array();
		}

		return $this->messages[ $type ];
	}

	/**
	 * @param string $type
	 * @param string $message
	 *
	 * @throws Invalid_Flash_Message_Type_Exception
	 */
	public function add_message_now( $type, $message ) {
		$this->validate_type( $type );
		$this->messages[ $type ][] = $message;
	}

	/**
	 * @param string $type
	 * @param string $message
	 *
	 * @throws Invalid_Flash_Message_Type_Exception
	 */
	public function add_message( $type, $message ) {
		$this->add_message_now( $type, $message );
		$message_key = array_search( $message, $this->messages[ $type ] );
		$cookie_name = $this->create_cookie_name( $type, $message_key );
		$this->cookie->set_cookie( $cookie_name, $message, Cookie::ONE_MINUTE_IN_SECONDS );
	}

	/**
	 * @return array
	 */
	private function fetch_messages() {
		$cookie_messages = $this->cookie->get_cookie( Cookie::FLASH_MESSAGE_COOKIE_NAME_BASE );

		if ( ! is_array( $cookie_messages ) ) {
			return array();
		}

		return $this->group_messages_by_type( $cookie_messages );
	}

	/**
	 * @param array $cookie_messages
	 *
	 * @return array
	 */
	private function group_messages_by_type( array $cookie_messages ) {
		$messages = array();

		foreach ( $cookie_messages as $type => $group ) {
			foreach ( $group as $key => $message ) {
				$cookie_name = $this->create_cookie_name( $type, $key );
				$this->cookie->delete_cookie( $cookie_name );
				$messages[ $type ][] = $message;
			}
		}

		return $messages;
	}

	/**
	 * @param string $type
	 * @param int    $key
	 *
	 * @return string
	 */
	private function create_cookie_name( $type, $key ) {
		return Cookie::FLASH_MESSAGE_COOKIE_NAME_BASE . '[' . $type . ']' . '[' . $key . ']';
	}

	/**
	 * @param string $type
	 *
	 * @throws Invalid_Flash_Message_Type_Exception
	 */
	private function validate_type( $type ) {
		$allowed_types = array( 'success', 'error', 'warning' );

		if ( ! in_array( $type, $allowed_types ) ) {
			throw new Invalid_Flash_Message_Type_Exception(
				'There are only 2 allowed flash message types: success and error.'
			);
		}
	}
}
