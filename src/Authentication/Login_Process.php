<?php

namespace TwoFAS\MagicPassword\Authentication;

use Exception;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\MagicPassword\Authentication\Handler\Login_Handler;
use TwoFAS\MagicPassword\Authentication\Middleware\Middleware_Interface;
use TwoFAS\MagicPassword\Exceptions\Handler\Error_Handler;
use WP_Error;
use WP_User;

class Login_Process {

	/**
	 * @var Login_Handler
	 */
	private $login_handler;

	/**
	 * @var Error_Handler
	 */
	private $error_handler;

	/**
	 * @var Middleware_Interface
	 */
	private $before_middleware;

	/**
	 * @var Middleware_Interface
	 */
	private $after_middleware;

	/**
	 * @param Middleware_Interface $before_middleware
	 * @param Middleware_Interface $after_middleware
	 * @param Login_Handler        $login_handler
	 * @param Error_Handler        $error_handler
	 */
	public function __construct(
		Middleware_Interface $before_middleware,
		Middleware_Interface $after_middleware,
		Login_Handler $login_handler,
		Error_Handler $error_handler
	) {
		$this->before_middleware = $before_middleware;
		$this->after_middleware  = $after_middleware;
		$this->login_handler     = $login_handler;
		$this->error_handler     = $error_handler;
	}

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return bool|JSON_Response|View_Response
	 */
	public function authenticate( $user ) {
		$response = $this->before_middleware->handle( $user );

		if ( $this->should_be_returned( $response ) ) {
			return $response;
		}

		$response = $this->create_response( $user );

		return $this->after_middleware->handle( $user, $response );
	}

	/**
	 * @param null|JSON_Response|View_Response $response
	 *
	 * @return bool
	 */
	private function should_be_returned( $response ) {
		return $response instanceof JSON_Response || $response instanceof View_Response;
	}

	/**
	 * @param WP_Error|WP_User $user
	 *
	 * @return bool|JSON_Response|View_Response
	 */
	private function create_response( $user ) {
		try {
			$response = $this->login_handler->authenticate( $user );
		} catch ( Exception $e ) {
			$response = $this->error_handler->capture_exception( $e )->to_json( $e );
		}

		return $response;
	}
}
