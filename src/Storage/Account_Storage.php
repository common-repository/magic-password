<?php

namespace TwoFAS\MagicPassword\Storage;

use TwoFAS\Account\OAuth\Interfaces\TokenStorage;
use TwoFAS\Account\OAuth\Token;
use TwoFAS\Account\OAuth\TokenNotFoundException;
use TwoFAS\Account\OAuth\TokenType;
use TwoFAS\Encryption\AESKey;
use TwoFAS\Encryption\Interfaces\Key;
use TwoFAS\Encryption\Interfaces\ReadKey;
use TwoFAS\Encryption\Interfaces\WriteKey;
use TwoFAS\MagicPassword\Integration\Blog_Name_Decoder;
use UnexpectedValueException;

class Account_Storage implements ReadKey, WriteKey, TokenStorage {

	/**
	 * @since 1.0.0
	 */
	const VERSION = 'mpwd_version';

	/**
	 * @since 1.0.0
	 */
	const ENCRYPTION_KEY = 'mpwd_encryption_key';

	/**
	 * @since 1.0.0
	 */
	const OAUTH_TOKEN_BASE = 'mpwd_oauth_token_';

	/**
	 * @since 1.0.0
	 */
	const ACCESS_TOKEN_ARRAY_KEY = 'access_token';

	/**
	 * @since 1.0.0
	 */
	const INTEGRATION_ID_ARRAY_KEY = 'integration_id';

	/**
	 * @since 1.0.0
	 */
	const INTEGRATION_LOGIN = 'mpwd_integration_login';

	/**
	 * @since 1.0.0
	 */
	const KEY_TOKEN = 'mpwd_key_token';

	/**
	 * @since 1.0.0
	 */
	const EMAIL = 'mpwd_email';

	/**
	 * @since 1.2.0
	 */
	const PASSWORDLESS_ROLES = 'mpwd_passwordless_roles';

	/**
	 * @since 1.3.0
	 */
	const LOGGING_ALLOWED = 'mpwd_logging_allowed';

	/**
	 * @since 1.3.3
	 */
	const REVIEW_NOTICE = 'mpwd_review_notice_data';

	/**
	 * @since 1.3.3
	 */
	const PLUGIN_DISABLED = 'mpwd_plugin_disabled';

	/**
	 * @var Blog_Name_Decoder
	 */
	private $blog_name_decoder;

	/**
	 * @param Blog_Name_Decoder $blog_name_decoder
	 */
	public function __construct( Blog_Name_Decoder $blog_name_decoder ) {
		$this->blog_name_decoder = $blog_name_decoder;
	}

	/**
	 * Retrieve stored Key object.
	 *
	 * @return Key
	 */
	public function retrieve() {
		$encryption_key = get_option( self::ENCRYPTION_KEY );
		$encryption_key = base64_decode( $encryption_key );

		return new AESKey( $encryption_key );
	}

	/**
	 * Store key in key storage so it can be retrieved for future use
	 *
	 * @param Key $key
	 */
	public function store( Key $key ) {
		update_option( self::ENCRYPTION_KEY, base64_encode( $key->getValue() ) );
	}

	/**
	 * @param Token $token
	 */
	public function storeToken( Token $token ) {
		$option_name = $this->create_oauth_token_option_name( $token->getType() );

		update_option( $option_name, array(
			self::ACCESS_TOKEN_ARRAY_KEY   => $token->getAccessToken(),
			self::INTEGRATION_ID_ARRAY_KEY => $token->getIntegrationId(),
		) );
	}

	/**
	 * @param string $type
	 *
	 * @return Token
	 *
	 * @throws TokenNotFoundException
	 */
	public function retrieveToken( $type ) {
		$option_name = $this->create_oauth_token_option_name( $type );
		$token_array = get_option( $option_name );

		if ( is_array( $token_array ) ) {
			return new Token( $type, $token_array[ self::ACCESS_TOKEN_ARRAY_KEY ], $token_array[ self::INTEGRATION_ID_ARRAY_KEY ] );
		}

		throw new TokenNotFoundException();
	}

	/**
	 * @return int
	 *
	 * @throws TokenNotFoundException
	 */
	public function retrieve_integration_id() {
		$token = $this->retrieveToken( TokenType::passwordlessWordpress()->getType() );

		return $token->getIntegrationId();
	}

	/**
	 * @param string $login
	 */
	public function store_integration_login( $login ) {
		update_option( self::INTEGRATION_LOGIN, $login );
	}

	/**
	 * @return null|string
	 */
	public function retrieve_integration_login() {
		return get_option( self::INTEGRATION_LOGIN, null );
	}

	/**
	 * @param string $token
	 */
	public function store_key_token( $token ) {
		update_option( self::KEY_TOKEN, $token );
	}

	/**
	 * @return null|string
	 */
	public function retrieve_key_token() {
		return get_option( self::KEY_TOKEN, null );
	}

	/**
	 * @param string $email
	 */
	public function store_email( $email ) {
		update_option( self::EMAIL, $email );
	}

	/**
	 * @return null|string
	 */
	public function retrieve_email() {
		return get_option( self::EMAIL, null );
	}

	public function enable_logging() {
		update_option( self::LOGGING_ALLOWED, 1 );
	}

	public function disable_logging() {
		update_option( self::LOGGING_ALLOWED, 0 );
	}

	/**
	 * @return bool
	 */
	public function is_logging_allowed() {
		return (bool) get_option( self::LOGGING_ALLOWED, false );
	}

	/**
	 * @return string
	 */
	public function get_wp_url() {
		return get_bloginfo( 'wpurl' );
	}

	/**
	 * @return string
	 */
	public function get_wp_version() {
		return get_bloginfo( 'version' );
	}

	/**
	 * @return string
	 */
	public function get_bare_wp_name() {
		return get_bloginfo( 'name' );
	}

	/**
	 * @return string
	 */
	public function get_wp_name() {
		return $this->blog_name_decoder->decode( $this->get_bare_wp_name() );
	}

	/**
	 * @param Key    $encryption_key
	 * @param string $integration_login
	 * @param string $key_token
	 * @param string $email
	 */
	public function store_integration( Key $encryption_key, $integration_login, $key_token, $email ) {
		$this->store( $encryption_key );
		$this->store_integration_login( $integration_login );
		$this->store_key_token( $key_token );
		$this->store_email( $email );
	}

	/**
	 * @return bool
	 */
	public function is_account_created() {
		$integration_login = $this->retrieve_integration_login();
		$key_token         = $this->retrieve_key_token();
		$email             = $this->retrieve_email();

		return $this->encryption_key_exists()
			&& $this->oauth_token_exists()
			&& ! is_null( $integration_login )
			&& ! is_null( $key_token )
			&& ! is_null( $email );
	}

	/**
	 * @return bool
	 */
	public function encryption_key_exists() {
		return false !== get_option( self::ENCRYPTION_KEY );
	}

	/**
	 * @return bool
	 */
	public function oauth_token_exists() {
		try {
			$this->retrieveToken( TokenType::PASSWORDLESS_WORDPRESS );
			return true;
		} catch ( TokenNotFoundException $e ) {

			return false;
		}
	}

	/**
	 * @return array
	 *
	 * @throws UnexpectedValueException
	 */
	public function get_passwordless_roles() {
		$roles = get_option( self::PASSWORDLESS_ROLES, array() );

		if ( is_array( $roles ) ) {
			return $roles;
		}

		throw new UnexpectedValueException( 'Could not check whether passwordless authentication is obligatory.' );
	}

	/**
	 * @param array $roles
	 */
	public function set_passwordless_roles( array $roles ) {
		update_option( self::PASSWORDLESS_ROLES, $roles );
	}

	/**
	 * @param array $user_roles
	 *
	 * @return bool
	 *
	 * @throws UnexpectedValueException
	 */
	public function has_passwordless_role( array $user_roles ) {
		$roles        = $this->get_passwordless_roles();
		$intersection = array_intersect( $user_roles, $roles );

		return ! empty( $intersection );
	}

	/**
	 * @return array
	 */
	public function get_review_notice_data() {
		$review_notice = get_option( self::REVIEW_NOTICE, array() );

		if ( ! is_array( $review_notice ) ) {
			$review_notice = array();
		}

		return $review_notice;
	}

	/**
	 * @param array $data
	 */
	public function set_review_notice_data( array $data ) {
		update_option( self::REVIEW_NOTICE, $data );
	}

	public function disable_plugin() {
		update_option( self::PLUGIN_DISABLED, '1' );
	}

	public function enable_plugin() {
		update_option( self::PLUGIN_DISABLED, '0' );
	}

	/**
	 * @return bool
	 */
	public function is_plugin_enabled() {
		return '1' !== get_option( self::PLUGIN_DISABLED );
	}

	/**
	 * @return bool
	 */
	public function is_plugin_disabled() {
		return ! $this->is_plugin_enabled();
	}

	/**
	 * @return string
	 */
	public function get_db_version() {
		return get_option( self::VERSION, '0' );
	}

	/**
	 * @param string $version
	 */
	public function set_db_version( $version ) {
		update_option( self::VERSION, $version );
	}

	/**
	 * @param string $type
	 *
	 * @return string
	 */
	private function create_oauth_token_option_name( $type ) {
		return self::OAUTH_TOKEN_BASE . $type;
	}
}
