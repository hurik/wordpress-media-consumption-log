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
        if ( is_numeric( $mcl_number ) && $mcl_number >= 0 ) {
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

function mcl_install() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'mcl_complete';

    if ( $wpdb->get_var( "SHOW TABLES LIKE {$table_name}" ) != $table_name ) {
        $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";

        if ( !empty( $wpdb->collate ) ) {
            $charset_collate .= " COLLATE {$wpdb->collate}";
        }

        $sql = "CREATE TABLE {$table_name} (
            `tag_id` bigint(20) unsigned NOT NULL,
            `cat_id` bigint(20) unsigned NOT NULL,
            `complete` tinyint(1) NOT NULL,
            PRIMARY KEY (`tag_id`,`cat_id`)
	) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        add_option( 'mcl_db_version', 1 );
    }
}

?>