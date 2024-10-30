<?php

namespace TwoFAS\MagicPassword\Http\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Middleware\Middleware;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\MagicPassword\Http\Request;
use TwoFAS\MagicPassword\Storage\Account_Storage;

class Check_If_Plugin_Is_Enabled extends Middleware {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Account_Storage
	 */
	private $account_storage;

	/**
	 * @param Request         $request
	 * @param Account_Storage $account_storage
	 */
	public function __construct( Request $request, Account_Storage $account_storage ) {
		$this->request         = $request;
		$this->account_storage = $account_storage;
	}

	/**
	 * @return View_Response|JSON_Response|Redirect_Response
	 */
	public function handle() {
		if ( $this->account_storage->is_plugin_disabled() ) {
			$message = 'Plugin must be enabled to perform this action.';

			if ( $this->request->is_ajax() ) {
				return $this->json( array(
					'error' => $message,
				), 403 );
			}

			return $this->view( 'dashboard/error.html.twig', array(
				'description' => $message,
			) );
		}

		return $this->next->handle();
	}
}
