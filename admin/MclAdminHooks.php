<?php

if ( is_admin() ) {
    MclAdminHooks::on_start();
}

class MclAdminHooks {

    public static function on_start() {
        add_action( 'admin_init', array( get_called_class(), 'admin_init' ) );
        add_action( 'admin_menu', array( get_called_class(), 'admin_menu' ) );

        add_action( 'save_post', array( get_called_class(), 'save_post' ) );
        add_action( 'transition_post_status', array( get_called_class(), 'transition_post_status' ), 10, 3 );
        add_action( 'before_delete_post', array( get_called_class(), 'before_delete_post' ) );

        add_filter( 'load-post-new.php', array( get_called_class(), 'load_post_new_php' ) );
    }

    public static function admin_init() {
        MclSettings::register_settings();
        MclUnits::register_settings();
    }

    public static function admin_menu() {
        // Add menu
        add_menu_page( 'MCL', 'MCL', 'manage_options', 'mcl-quick-post' );
        add_submenu_page( 'mcl-quick-post', 'MCL - ' . __( 'Quick Post', 'media-consumption-log' ), __( 'Quick Post', 'media-consumption-log' ), 'manage_options', 'mcl-quick-post', array( 'MclQuickPost', 'create_page' ) );
        add_submenu_page( 'mcl-quick-post', 'MCL - ' . __( 'Complete', 'media-consumption-log' ), __( 'Complete', 'media-consumption-log' ), 'manage_options', 'mcl-complete', array( 'MclComplete', 'create_page' ) );
        add_submenu_page( 'mcl-quick-post', 'MCL - ' . __( 'Units', 'media-consumption-log' ), __( 'Units', 'media-consumption-log' ), 'manage_options', 'mcl-unit', array( 'MclUnits', 'create_page' ) );
        add_submenu_page( 'mcl-quick-post', 'MCL - ' . __( 'Data', 'media-consumption-log' ), __( 'Data', 'media-consumption-log' ), 'manage_options', 'mcl-rebuild-data', array( 'MclData', 'create_page' ) );
        add_submenu_page( 'mcl-quick-post', 'MCL - ' . __( 'Settings', 'media-consumption-log' ), __( 'Settings', 'media-consumption-log' ), 'manage_options', 'mcl-settings', array( 'MclSettings', 'create_page' ) );
    }

    public static function save_post( $post_id ) {
        $cats = get_the_category( $post_id );

        if ( MclHelper::is_monitored_category( $cats[0]->term_id ) ) {
            MclNumber::check_mcl_number_after_saving( $post_id );

            if ( get_post_status( $post_id ) == 'publish' ) {
                MclData::update_data();
            }
        } else {
            delete_post_meta( $post_id, "mcl_number" );
        }
    }

    public static function transition_post_status( $new_status, $old_status, $post ) {
        $cats = get_the_category( $post->ID );

        if ( MclHelper::is_monitored_category( $cats[0]->term_id ) ) {
            if ( ($old_status == 'publish' && $new_status != 'publish' ) ) {
                MclData::update_data();
            }
        }
    }

    public static function before_delete_post( $post_id ) {
        $cats = get_the_category( $post_id );

        if ( MclHelper::is_monitored_category( $cats[0]->term_id ) ) {
            add_action( 'delete_post', array( get_called_class(), 'delete_post' ) );
        }
    }

    public static function delete_post( $post_id ) {
        MclData::update_data();
    }

    public static function load_post_new_php( $post_id ) {
        add_action( 'wp_insert_post', array( get_called_class(), 'load_post_new_php_to_wp_insert_post' ) );
    }

    public static function load_post_new_php_to_wp_insert_post( $post_id ) {
        // Set category and tag in new post with link
        if ( isset( $_REQUEST['category'] ) ) {
            wp_set_post_categories( $post_id, array( $_REQUEST['category'] ) );
        }

        if ( isset( $_REQUEST['tag'] ) ) {
            wp_set_post_tags( $post_id, get_tag( $_REQUEST['tag'] )->name );
        }

        // Add default custom fields
        MclNumber::add_default_custom_field_in_new_post( $post_id );
    }

    public static function register_activation_hook() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'mcl_complete';

        if ( $wpdb->get_var( "SHOW TABLES LIKE {$table_name}" ) != $table_name ) {
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";

            if ( !empty( $wpdb->collate ) ) {
                $charset_collate .= " COLLATE {$wpdb->collate}";
            }

            $sql = "
            CREATE TABLE {$table_name} (
                `tag_id` bigint(20) unsigned NOT NULL,
                `cat_id` bigint(20) unsigned NOT NULL,
                `complete` tinyint(1) NOT NULL,
                PRIMARY KEY (`tag_id`,`cat_id`)
            ) $charset_collate;
        ";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

            add_option( 'mcl_db_version', 1 );
        }
    }

}

?>