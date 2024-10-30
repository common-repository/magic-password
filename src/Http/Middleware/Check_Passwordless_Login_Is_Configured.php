<?php

namespace TwoFAS\MagicPassword\Http\Middleware;

use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Middleware\Middleware;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\MagicPassword\Exceptions\User_Not_Set_Exception;
use TwoFAS\MagicPassword\Helpers\Flash;
use TwoFAS\MagicPassword\Http\Action_URL;
use TwoFAS\MagicPassword\Http\Request;
use TwoFAS\MagicPassword\Integration\User_Configuration;

class Check_Passwordless_Login_Is_Configured extends Middleware {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var User_Configuration
	 */
	private $user_configuration;

	/**
	 * @var Flash
	 */
	private $flash;

	/**
	 * @param Request            $request
	 * @param Flash              $flash
	 * @param User_Configuration $user_configuration
	 */
	public function __construct( Request $request, Flash $flash, User_Configuration $user_configuration ) {
		$this->request            = $request;
		$this->flash              = $flash;
		$this->user_configuration = $user_configuration;
	}

	/**
	 * @return View_Response|JSON_Response|Redirect_Response
	 *
	 * @throws User_Not_Set_Exception
	 * @throws API_Exception
	 */
	public function handle() {
		if ( ! $this->user_configuration->is_passwordless_login_configured() ) {
			$message = 'Plugin has not been configured yet.';

			if ( $this->request->is_ajax() ) {
				return $this->json( array(
					'error' => $message,
				), 403 );
			}

			$this->flash->add_message( 'error', $message );

			return $this->redirect( new Action_URL( $this->request->page() ) );
		}

		return $this->next->handle();
	}
}
