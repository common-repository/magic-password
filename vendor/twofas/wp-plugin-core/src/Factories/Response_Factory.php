<?php

namespace TwoFAS\Core\Factories;

use Exception;
use TwoFAS\Core\Exceptions\Handler\Error_Handler_Interface;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\Middleware\Create_Response;
use TwoFAS\Core\Http\No_Content;
use TwoFAS\Core\Http\Redirect_Response;
use TwoFAS\Core\Http\Request;
use TwoFAS\Core\Http\Route;
use TwoFAS\Core\Http\View_Response;

class Response_Factory {

	/**
	 * @var Route $route
	 */
	private $route;

	/**
	 * @var Controller_Factory $controller_factory
	 */
	private $controller_factory;

	/**
	 * @var Middleware_Factory $middleware_factory
	 */
	private $middleware_factory;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Error_Handler_Interface
	 */
	private $error_handler;

	/**
	 * @param Route                   $route
	 * @param Controller_Factory      $controller_factory
	 * @param Middleware_Factory      $middleware_factory
	 * @param Request                 $request
	 * @param Error_Handler_Interface $error_handler
	 */
	public function __construct(
		Route $route,
		Controller_Factory $controller_factory,
		Middleware_Factory $middleware_factory,
		Request $request,
		Error_Handler_Interface $error_handler
	) {
		$this->route              = $route;
		$this->controller_factory = $controller_factory;
		$this->middleware_factory = $middleware_factory;
		$this->request            = $request;
		$this->error_handler      = $error_handler;
	}

	/**
	 * @param Request $request
	 *
	 * @return JSON_Response|Redirect_Response|View_Response
	 */
	public function create_response( Request $request ) {
		if ( ! $this->can_create( $request ) ) {
			return new No_Content();
		}

		$route           = $this->route->create_route();
		$controller      = $this->controller_factory->create( $route['controller'] );
		$create_response = new Create_Response( $request, $controller, $route['action'] );
		$middleware      = $this->middleware_factory->create( $route['middleware'], $create_response );

		return $middleware->handle();
	}

	/**
	 * @param Exception $e
	 *
	 * @return JSON_Response|View_Response
	 */
	public function create_error_response( Exception $e ) {
		$handler = $this->error_handler->capture_exception( $e );

		if ( $this->request->is_ajax() ) {
			return $handler->to_json( $e );
		}

		return $handler->to_view( $e );
	}

	/**
	 * @param Request $request
	 *
	 * @return bool
	 */
	private function can_create( Request $request ) {
		return array_key_exists( $request->page(), $this->route->get_routes() );
	}
}
