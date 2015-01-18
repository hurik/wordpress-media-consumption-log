<?php

class MclNumber {

    public static function add_default_custom_field_in_new_post( $post_id ) {
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

            $post = get_post( $post_id );
            $title_ecplode = explode( " " . MclSettings::get_other_separator() . " ", $post->post_title );
            $current_number = end( $title_ecplode );

            if ( count( $title_ecplode ) < 2 ) {
                // Do nothing
            } else if ( strpos( $current_number, " " . MclSettings::get_other_and() . " " ) !== false ) {
                $mcl_number = 2;
            } else if ( strpos( $current_number, " " . MclSettings::get_other_to() . " " ) !== false ) {
                preg_match_all( '!\d+(?:\.\d+)?!', $current_number, $matches );

                if ( count( $matches[0] ) == 2 ) {
                    $mcl_number = ceil( floatval( $matches[0][1] ) - floatval( $matches[0][0] ) + 1 );
                } else if ( count( $matches[0] ) == 4 ) {
                    $mcl_number = ceil( floatval( $matches[0][3] ) - floatval( $matches[0][1] ) + 1 );
                }
            }

            update_post_meta( $post_id, 'mcl_number', $mcl_number );
        }
    }

}

?>