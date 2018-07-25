<?php

/*
  Copyright (C) 2014-2018 Andreas Giemza <andreas@giemza.net>

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License along
  with this program; if not, write to the Free Software Foundation, Inc.,
  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

class MclNumber {

	public static function add_default_custom_field( $post_id ) {
		add_post_meta( $post_id, 'mcl_number', '', true );
	}

	public static function check_mcl_number_after_saving( $post_id ) {
		if ( get_post_status( $post_id ) == 'publish' ) {
			$mcl_number = get_post_meta( $post_id, 'mcl_number', true );

			// Check if already set
			if ( is_numeric( $mcl_number ) && $mcl_number >= 0 ) {
				return;
			}

			// Set it to one
			$mcl_number = 1;

			$post			 = get_post( $post_id );
			$title_ecplode	 = explode( MclSettings::get_other_separator(), $post->post_title );
			$current_number	 = end( $title_ecplode );

			if ( count( $title_ecplode ) < 2 ) {
				// Do nothing
			} else if ( strpos( $current_number, MclSettings::get_other_and() ) !== false ) {
				$mcl_number = 2;
			} else if ( strpos( $current_number, MclSettings::get_other_to() ) !== false ) {
				preg_match_all( '!\d+(?:\.\d+)?!', $current_number, $matches );

				$mcl_number = ceil( floatval( $matches[ 0 ][ (count( $matches[ 0 ] ) - 1) ] ) - floatval( $matches[ 0 ][ ((count( $matches[ 0 ] ) / 2) - 1) ] ) + 1 );
			}

			if ( $mcl_number < 0 ) {
				$mcl_number = 0;
			}

			update_post_meta( $post_id, 'mcl_number', $mcl_number );
		}
	}

}
