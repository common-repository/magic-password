<?php

namespace TwoFAS\MagicPassword\Http\Middleware;

use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Middleware\Middleware;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\MagicPassword\Helpers\Flash;
use TwoFAS\MagicPassword\Http\Action_URL;
use TwoFAS\MagicPassword\Http\Request;

class Check_Nonce extends Middleware {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Flash
	 */
	private $flash;

	/**
	 * @param Request $request
	 * @param Flash   $flash
	 */
	public function __construct( Request $request, Flash $flash ) {
		$this->request = $request;
		$this->flash   = $flash;
	}

	/**
	 * @return JSON_Response|Redirect_Response|View_Response
	 */
	public function handle() {
		$page   = $this->request->page();
		$action = $this->request->action();
		$nonce  = $this->request->post( '_wpnonce' );

		if ( empty( $nonce ) ) {
			$nonce = $this->request->get( '_wpnonce' );
		}

		if ( ! $this->check( $nonce, $action ) ) {
			if ( $this->request->is_ajax() ) {
				return $this->json( array(
					'error' => 'Security token is invalid.',
				), 403 );
			}

			$this->flash->add_message( 'error', 'Invalid nonce' );

			return $this->redirect( new Action_URL( $page ) );
		}

		return $this->next->handle();
	}

	/**
	 * @param string $nonce
	 * @param string $action
	 *
	 * @return bool
	 */
	private function check( $nonce, $action ) {
		return false !== wp_verify_nonce( $nonce, $action );
	}
}
