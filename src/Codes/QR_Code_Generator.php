<?php

namespace TwoFAS\MagicPassword\Codes;

use TwoFAS\Api\QrCodeGenerator;

class QR_Code_Generator {

	const CONFIG_PREFIX = 'twofas_c://';
	const LOGIN_PREFIX  = 'twofas_l://';

	/**
	 * @var QrCodeGenerator
	 */
	private $qr_code_generator;

	/**
	 * @param QrCodeGenerator $qr_code_generator
	 */
	public function __construct( QrCodeGenerator $qr_code_generator ) {
		$this->qr_code_generator = $qr_code_generator;
	}

	/**
	 * @param string $integration_id
	 * @param string $session_id
	 * @param string $totp_secret
	 * @param string $mobile_secret
	 * @param string $login
	 *
	 * @return string
	 */
	public function generate_config_code( $integration_id, $session_id, $totp_secret, $mobile_secret, $login ) {
		return $this->create_qr_code(
			self::CONFIG_PREFIX,
			$this->get_channel_name( $integration_id, $session_id ),
			array(
				's' => $totp_secret,
				'm' => $mobile_secret,
				'u' => $login,
			)
		);
	}

	/**
	 * @param string $integration_id
	 * @param string $session_id
	 *
	 * @return string
	 */
	public function generate_login_code( $integration_id, $session_id ) {
		return $this->create_qr_code(
			self::LOGIN_PREFIX,
			$this->get_channel_name( $integration_id, $session_id )
		);
	}

	/**
	 * @param string $integration_id
	 * @param string $session_id
	 *
	 * @return string
	 */
	private function get_channel_name( $integration_id, $session_id ) {
		return 'private-wp_' . $integration_id . '_' . $session_id;
	}

	/**
	 * @param string $prefix
	 * @param string $channel_name
	 * @param array  $args
	 *
	 * @return string
	 */
	private function create_qr_code( $prefix, $channel_name, array $args = array() ) {
		$message = $prefix . $channel_name;
		$message = add_query_arg( $args, $message );

		return $this->qr_code_generator->generateBase64( $message );
	}
}
