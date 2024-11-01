<?php
/**
 * Common class
 *
 * @package USERLOGS
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * Class Userlogs_Common
 *
 * @package USERLOGS
 */
class Userlogs_Common {

	/**
	 * Return %d,%d,.. format for an array.
	 *
	 * @param array $array array list of ids.
	 * @return string
	 */
	public static function get_format( $array ) {
		if ( empty( $array ) || ! is_array( $array ) ) {
			return '';
		}

		$array          = array_filter( array_map( 'intval', $array ) );
		$total_elements = count( $array );
		$placeholders   = array_fill( 0, $total_elements, '%d' );
		$format         = implode( ', ', $placeholders );

		return $format;
	}
}
