<?php

namespace TwoFAS\MagicPassword\Http\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Middleware\Middleware;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\MagicPassword\Http\Request;
use TwoFAS\MagicPassword\Storage\User_Storage;

class Check_User_Is_Admin extends Middleware {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var User_Storage
	 */
	private $user_storage;

	/**
	 * @param Request      $request
	 * @param User_Storage $user_storage
	 */
	public function __construct( Request $request, User_Storage $user_storage ) {
		$this->request      = $request;
		$this->user_storage = $user_storage;
	}

	/**
	 * @return View_Response|JSON_Response|Redirect_Response
	 */
	public function handle() {
		if ( ! $this->user_storage->is_admin() ) {
			$message = 'You do not have sufficient permissions to perform this action.';

			if ( $this->request->is_ajax() ) {
				return $this->json( array(
					'error' => $message,
				), 403 );
			}

			return $this->view( 'dashboard/forbidden.html.twig', array( 'description' => $message ) );
		}

		return $this->next->handle();
	}
}
