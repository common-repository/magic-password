<?php

namespace TwoFAS\MagicPassword\Http\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Middleware\Middleware;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\MagicPassword\Exceptions\User_Not_Found_Exception;
use TwoFAS\MagicPassword\Helpers\Flash;
use TwoFAS\MagicPassword\Integration\User_Configuration;
use UnexpectedValueException;

class Check_Passwordless_Role extends Middleware {

	/**
	 * @var User_Configuration
	 */
	private $user_configuration;

	/**
	 * @var Flash
	 */
	private $flash;

	/**
	 * @param User_Configuration $user_configuration
	 * @param Flash              $flash
	 */
	public function __construct( User_Configuration $user_configuration, Flash $flash ) {
		$this->user_configuration = $user_configuration;
		$this->flash              = $flash;
	}

	/**
	 * @return View_Response|JSON_Response|Redirect_Response
	 *
	 * @throws User_Not_Found_Exception
	 * @throws UnexpectedValueException
	 */
	public function handle() {
		if ( $this->check() ) {
			$message = 'You have a role with obligatory Magic Password. ';
			$message .= 'Please enable Magic Password and set it as the only authentication method. ';
			$message .= 'Otherwise you will be obligated to configure Magic Password during the login process.';
			$this->flash->add_message_now( 'warning', $message );
		}

		return $this->next->handle();
	}

	/**
	 * @return bool
	 *
	 * @throws User_Not_Found_Exception
	 * @throws UnexpectedValueException
	 */
	private function check() {
		return $this->user_configuration->is_passwordless_role_assigned()
			&& ( $this->user_configuration->is_passwordless_login_disabled() || $this->user_configuration->is_passwordless_login_optional() );
	}
}
