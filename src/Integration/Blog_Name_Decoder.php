<?php

namespace TwoFAS\MagicPassword\Integration;

class Blog_Name_Decoder {

	/**
	 * @param string $name
	 *
	 * @return string
	 */
	public function decode( $name ) {
		return html_entity_decode( $name, ENT_QUOTES, 'UTF-8' );
	}
}
