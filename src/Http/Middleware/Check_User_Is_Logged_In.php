<?php

namespace TwoFAS\MagicPassword\Http\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Middleware\Middleware;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\MagicPassword\Http\Request;

class Check_User_Is_Logged_In extends Middleware {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @param Request $request
	 */
	public function __construct( Request $request ) {
		$this->request = $request;
	}

	/**
	 * @return JSON_Response|Redirect_Response|View_Response
	 */
	public function handle() {
		if ( ! $this->check() ) {
			$message = 'You must be logged in to perform this action.';

			if ( $this->request->is_ajax() ) {
				return $this->json( array(
					'error' => $message,
				), 403 );
			}

			return $this->view( 'dashboard/forbidden.html.twig', array( 'description' => $message ) );
		}

		return $this->next->handle();
	}

	/**
	 * @return bool
	 */
	private function check() {
		return is_user_logged_in();
	}
}
