<?php

namespace TwoFAS\MagicPassword\Helpers;

use Exception;
use Twig_Environment;
use Twig_Loader_Filesystem;
use Twig_SimpleFunction;
use TwoFAS\MagicPassword\Exceptions\Handler\Error_Handler;

class Twig {

	/**
	 * @var Twig_Environment
	 */
	private $twig;

	/**
	 * @var Error_Handler
	 */
	private $error_handler;

	/**
	 * @param Flash         $flash
	 * @param URL           $url
	 * @param Error_Handler $error_handler
	 */
	public function __construct( Flash $flash, URL $url, Error_Handler $error_handler ) {
		$loader              = new Twig_Loader_Filesystem( MPWD_TEMPLATES_PATH );
		$this->twig          = new Twig_Environment( $loader );
		$this->error_handler = $error_handler;
		$this->twig->addGlobal( 'flash', $flash );
		$this->twig->addGlobal( 'url', $url );
		$this->twig->addFunction( new Twig_SimpleFunction( 'login_header', 'login_header' ) );
		$this->twig->addFunction( new Twig_SimpleFunction( 'login_footer', 'login_footer' ) );
		$this->twig->addFunction( new Twig_SimpleFunction( 'wp_login_url', 'wp_login_url' ) );
		$this->twig->addFunction( new Twig_SimpleFunction( 'wp_create_nonce', 'wp_create_nonce' ) );
	}

	/**
	 * @param string $template_name
	 * @param array  $data
	 *
	 * @return string
	 */
	public function render( $template_name, array $data = array() ) {
		try {
			$data['assets_url'] = MPWD_ASSETS_URL;

			return $this->twig->render( $template_name, $data );
		} catch ( Exception $e ) {
			return $this->error_handler->capture_exception( $e )->to_notification( $e );
		}
	}
}
