<?php

namespace TwoFAS\MagicPassword\Integration;

class Integration_Name {

	const MINIMUM_LENGTH = 4;
	const MAXIMUM_LENGTH = 255;
	const DEFAULT_NAME   = 'WordPress';

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
	 * @param string $name
	 *
	 * @return string
	 */
	public function create( $name ) {
		$name = $this->blog_name_decoder->decode( $name );

		if ( $this->check_length( $name ) ) {
			return $name;
		}

		return self::DEFAULT_NAME;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	private function check_length( $name ) {
		$length = mb_strlen( $name, 'UTF-8' );

		return $length >= self::MINIMUM_LENGTH && $length <= self::MAXIMUM_LENGTH;
	}
}
