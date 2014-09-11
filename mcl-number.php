<?php

add_filter( 'load-post-new.php', 'mcl_load_mcl_number_in_new_post' );

function mcl_load_mcl_number_in_new_post() {
    add_action( 'wp_insert_post', 'mcl_insert_mcl_number_in_new_post' );
}

function mcl_insert_mcl_number_in_new_post( $post_id ) {
    add_post_meta( $post_id, 'mcl_number', '', true );
}

add_action( 'save_post', 'mcl_check_mcl_number_after_saving' );

function mcl_check_mcl_number_after_saving( $post_id ) {
    if ( get_post_status( $post_id ) == 'publish' ) {
        $mcl_number = get_post_meta( $post_id, 'mcl_number', true );

        // Check if already set
        if ( !empty( $mcl_number ) ) {
            return;
        }

        // Set it to one
        $mcl_number = 1;

        $post = get_post( $post_id );
        $title_ecplode = explode( " " . get_option( 'mcl_settings_other_separator' ) . " ", $post->post_title );
        $current_number = end( $title_ecplode );

        if ( count( $title_ecplode ) < 2 ) {
            // Do nothing
        } else if ( strpos( $current_number, " " . get_option( 'mcl_settings_other_mcl_number_and' ) . " " ) !== false ) {
            $mcl_number = 2;
        } else if ( strpos( $current_number, " " . get_option( 'mcl_settings_other_mcl_number_to' ) . " " ) !== false ) {
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

?>