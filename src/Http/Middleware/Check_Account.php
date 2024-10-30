<?php

namespace TwoFAS\MagicPassword\Http\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Middleware\Middleware;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\MagicPassword\Helpers\Flash;
use TwoFAS\MagicPassword\Helpers\URL;
use TwoFAS\MagicPassword\Http\Request;
use TwoFAS\MagicPassword\Integration\API_Wrapper;

class Check_Account extends Middleware {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var API_Wrapper
	 */
	private $api_wrapper;

	/**
	 * @var Flash
	 */
	private $flash;

	/**
	 * @var URL
	 */
	private $url;

	/**
	 * @param Request     $request
	 * @param API_Wrapper $api_wrapper
	 * @param Flash       $flash
	 * @param URL         $url
	 */
	public function __construct( Request $request, API_Wrapper $api_wrapper, Flash $flash, URL $url ) {
		$this->request     = $request;
		$this->api_wrapper = $api_wrapper;
		$this->flash       = $flash;
		$this->url         = $url;
	}

	/**
	 * @return JSON_Response|Redirect_Response|View_Response
	 */
	public function handle() {
		if ( ! $this->check() ) {
			$message = 'Account has not been created yet.';

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

	/**
	 * @return bool
	 */
	private function check() {
		return $this->api_wrapper->is_account_created();
	}
}
