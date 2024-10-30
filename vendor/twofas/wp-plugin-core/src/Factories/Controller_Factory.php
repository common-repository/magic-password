<?php

namespace TwoFAS\Core\Factories;

use LogicException;
use Pimple\Container;
use TwoFAS\Core\Http\Controller;

class Controller_Factory {

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @param Container $container
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
	}

	/**
	 * @param string $controller_name
	 *
	 * @return Controller
	 *
	 * @throws LogicException
	 */
	public function create( $controller_name ) {
		$parts = explode( '_', $controller_name );

		$controller_name = implode( '_', array_map( function ( $part ) {
			return strtolower( $part );
		}, $parts ) );

		if ( ! $this->container->offsetExists( $controller_name ) ) {
			throw new LogicException( 'Controller name: ' . $controller_name . ' is not registered in DI container' );
		}

		return $this->container[ $controller_name ];
	}
}
