<?php

namespace TwoFAS\MagicPassword\Exceptions\Handler;

use Exception;
use LogicException;
use RuntimeException;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;
use TwoFAS\Account\Exception\AuthorizationException as Account_Authorization_Exception;
use TwoFAS\Account\Exception\Exception as Account_Exception;
use TwoFAS\Account\Exception\NotFoundException;
use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Api\Exception\AuthorizationException as API_Authorization_Exception;
use TwoFAS\Api\Exception\Exception as API_Exception;
use TwoFAS\Core\Exceptions\Download_Exception;
use TwoFAS\Core\Exceptions\Handler\Error_Handler_Interface;
use TwoFAS\Core\Exceptions\Handler\Logger_Interface;
use TwoFAS\Core\Exceptions\Http_Exception;
use TwoFAS\Core\Http\JSON_Response;
use TwoFAS\Core\Http\View_Response;
use TwoFAS\Encryption\Exceptions\RandomBytesGenerateException;
use TwoFAS\Encryption\Exceptions\RsaDecryptException;
use TwoFAS\MagicPassword\Exceptions\Date_Time_Exception;
use TwoFAS\MagicPassword\Exceptions\DB_Exception;
use TwoFAS\MagicPassword\Exceptions\Failed_Pairing_Exception;
use TwoFAS\MagicPassword\Exceptions\Forbidden_Action_Exception;
use TwoFAS\MagicPassword\Exceptions\Invalid_Nonce_Exception;
use TwoFAS\MagicPassword\Exceptions\Login_Restriction_Exception;
use TwoFAS\MagicPassword\Exceptions\Migration_Exception;
use TwoFAS\MagicPassword\Exceptions\User_ID_Not_Found_Exception;
use TwoFAS\MagicPassword\Exceptions\User_Not_Found_Exception;
use TwoFAS\MagicPassword\Exceptions\User_Not_Set_Exception;
use TwoFAS\MagicPassword\Exceptions\Validation_Exception;
use TwoFAS\ValidationRules\ValidationExceptionInterface;
use TwoFAS\ValidationRules\ValidationRules;
use UnexpectedValueException;
use WP_Error;

class Error_Handler implements Error_Handler_Interface {

	const DEFAULT_ERROR               = 'General error.';
	const TEMPLATE_ERROR              = 'Magic Password could not load the template.';
	const CREDENTIALS_ERROR           = 'Something went wrong with your credentials.';
	const DB_ERROR                    = 'Something went wrong with database.';
	const NONCE_ERROR                 = 'Security token is invalid.';
	const USER_ID_NOT_FOUND_ERROR     = 'User ID has not been found.';
	const USER_NOT_FOUND_ERROR        = 'User has not been found.';
	const USER_NOT_SET_ERROR          = 'User has not been set.';
	const OAUTH_TOKEN_NOT_FOUND_ERROR = 'OAuth token not found.';
	const INTEGRATION_NOT_FOUND_ERROR = 'Integration has not been found.';
	const RANDOM_BYTES_ERROR          = 'Error occurred during generating a random string.';

	/**
	 * @var Logger_Interface
	 */
	private $logger;

	/**
	 * @var bool
	 */
	private $logging_allowed;

	/**
	 * @var array
	 */
	private $dont_log = array(
		'TwoFAS\Api\Exception\AuthorizationException',
		'TwoFAS\Account\Exception\AuthorizationException',
		'TwoFAS\Core\Exceptions\Not_Found_Http_Exception',
		'TwoFAS\Core\Exceptions\Method_Not_Allowed_Http_Exception',
		'TwoFAS\MagicPassword\Exceptions\Login_Restriction_Exception',
	);

	/**
	 * @param Logger_Interface $logger
	 * @param bool             $logging_allowed
	 */
	public function __construct( Logger_Interface $logger, $logging_allowed ) {
		$this->logger          = $logger;
		$this->logging_allowed = $logging_allowed;
	}

	/**
	 * @param Exception $e
	 * @param array     $options
	 *
	 * @return Error_Handler
	 */
	public function capture_exception( Exception $e, array $options = array() ) {
		if ( $this->logging_allowed && $this->can_log( $e ) ) {
			$this->logger->capture_exception( $e, $options );
		}

		return $this;
	}

	/**
	 * @param Exception $e
	 *
	 * @return JSON_Response
	 */
	public function to_json( Exception $e ) {
		$response = $this->create_response( $e );

		return new JSON_Response( array( 'error' => $response['message'] ), $response['status'] );
	}

	/**
	 * @param Exception $e
	 *
	 * @return View_Response
	 */
	public function to_view( Exception $e ) {
		$response = $this->create_response( $e );

		return new View_Response( 'dashboard/error.html.twig', array( 'description' => $response['message'] ) );
	}

	/**
	 * @param Exception $e
	 * @param string    $class
	 *
	 * @return string
	 */
	public function to_notification( Exception $e, $class = 'notice notice-error mf-notice-error' ) {
		$response = $this->create_response( $e );

		$html = "
		<div class='{$class}'>
			<p>{$response['message']}</p>
		</div>";

		return $html;
	}

	/**
	 * @param Exception $e
	 *
	 * @return WP_Error
	 */
	public function to_wp_error( Exception $e ) {
		$response = $this->create_response( $e );

		return new WP_Error( 'mpwd_error', $response['message'] );
	}

	/**
	 * @param string $message
	 * @param int    $status
	 *
	 * @return array
	 */
	private function to_array( $message, $status ) {
		return array(
			'message' => $message,
			'status'  => $status,
		);
	}

	/**
	 * @param Exception $e
	 *
	 * @return bool
	 */
	private function can_log( Exception $e ) {
		foreach ( $this->dont_log as $excluded_exception ) {
			if ( $e instanceof $excluded_exception ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param Exception $e
	 *
	 * @return array
	 */
	private function create_response( Exception $e ) {
		if ( $e instanceof DB_Exception ) {
			return $this->to_array( self::DB_ERROR, 500 );
		}

		if ( $e instanceof Migration_Exception ) {
			return $this->to_array( $e->getMessage(), 500 );
		}

		if ( $e instanceof API_Authorization_Exception ) {
			return $this->to_array( self::CREDENTIALS_ERROR, 403 );
		}

		if ( $e instanceof Account_Authorization_Exception ) {
			return $this->to_array( self::CREDENTIALS_ERROR, 403 );
		}

		if ( $e instanceof User_ID_Not_Found_Exception ) {
			return $this->to_array( self::USER_ID_NOT_FOUND_ERROR, 404 );
		}

		if ( $e instanceof User_Not_Found_Exception ) {
			return $this->to_array( self::USER_NOT_FOUND_ERROR, 404 );
		}

		if ( $e instanceof User_Not_Set_Exception ) {
			return $this->to_array( self::USER_NOT_SET_ERROR, 404 );
		}

		if ( $e instanceof TokenNotFoundException ) {
			return $this->to_array( self::OAUTH_TOKEN_NOT_FOUND_ERROR, 404 );
		}

		if ( $e instanceof NotFoundException ) {
			return $this->to_array( self::INTEGRATION_NOT_FOUND_ERROR, 404 );
		}

		if ( $e instanceof Failed_Pairing_Exception ) {
			return $this->to_array( $e->getMessage(), $e->getCode() );
		}

		if ( $e instanceof Invalid_Nonce_Exception ) {
			return $this->to_array( self::NONCE_ERROR, 403 );
		}

		if ( $e instanceof ValidationExceptionInterface ) {
			return $this->to_array( $this->create_validation_error( $e ), 400 );
		}

		if ( $e instanceof RsaDecryptException ) {
			return $this->to_array( self::DEFAULT_ERROR, 500 );
		}

		if ( $e instanceof RandomBytesGenerateException ) {
			return $this->to_array( self::RANDOM_BYTES_ERROR, 500 );
		}

		if ( $e instanceof Forbidden_Action_Exception ) {
			return $this->to_array( $e->getMessage(), 403 );
		}

		if ( $e instanceof Http_Exception ) {
			return $this->to_array( $e->getMessage(), $e->getCode() );
		}

		if ( $e instanceof API_Exception ) {
			return $this->to_array( self::DEFAULT_ERROR, 500 );
		}

		if ( $e instanceof Account_Exception ) {
			return $this->to_array( self::DEFAULT_ERROR, 500 );
		}

		if ( $e instanceof Twig_Error_Loader ) {
			return $this->to_array( self::TEMPLATE_ERROR, 500 );
		}

		if ( $e instanceof Twig_Error_Syntax ) {
			return $this->to_array( self::TEMPLATE_ERROR, 500 );
		}

		if ( $e instanceof Twig_Error_Runtime ) {
			return $this->to_array( self::TEMPLATE_ERROR, 500 );
		}

		if ( $e instanceof LogicException ) {
			return $this->to_array( self::DEFAULT_ERROR, 500 );
		}

		if ( $e instanceof UnexpectedValueException ) {
			return $this->to_array( $e->getMessage(), 500 );
		}

		if ( $e instanceof Login_Restriction_Exception ) {
			return $this->to_array( $e->getMessage(), 403 );
		}

		if ( $e instanceof Validation_Exception ) {
			return $this->to_array( $e->getMessage(), 400 );
		}

		if ( $e instanceof Download_Exception ) {
			return $this->to_array( $e->getMessage(), 500 );
		}

		if ( $e instanceof RuntimeException ) {
			return $this->to_array( $e->getMessage(), 500 );
		}

		if ( $e instanceof Date_Time_Exception ) {
			return $this->to_array( $e->getMessage(), 500 );
		}

		return $this->to_array( self::DEFAULT_ERROR, 500 );
	}

	/**
	 * @param ValidationExceptionInterface $e
	 *
	 * @return string
	 */
	private function create_validation_error( ValidationExceptionInterface $e ) {
		if ( $e->hasError( 'socket_id', ValidationRules::REQUIRED ) ) {
			return 'Socket ID has not been sent.';
		}

		if ( $e->hasError( 'channel_name', ValidationRules::REQUIRED ) ) {
			return 'Channel name has not been sent.';
		}

		return 'Some data has not been sent.';
	}
}
