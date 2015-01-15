<?php

if ( is_admin() ) {
    MclAdminMenu::init();
}

class MclAdminMenu {

    public static function init() {
        add_action( 'admin_menu', array( get_called_class(), 'admin_menu' ) );
        add_action( 'admin_bar_menu', array( get_called_class(), 'admin_bar_menu' ), 75 );
        add_action( 'admin_init', array( get_called_class(), 'admin_init' ) );
        add_action( 'wp_insert_post', array( get_called_class(), 'wp_insert_post' ) );
        add_action( 'save_post', array( get_called_class(), 'save_post' ) );
    }

    public static function admin_menu() {
        // Add menu
        add_menu_page( 'MCL', 'MCL', 'manage_options', 'mcl-quick-post' );
        add_submenu_page( 'mcl-quick-post', 'MCL - ' . __( 'Quick Post', 'media-consumption-log' ), __( 'Quick Post', 'media-consumption-log' ), 'manage_options', 'mcl-quick-post', array( 'MclQuickPost', 'create_page' ) );
        add_submenu_page( 'mcl-quick-post', 'MCL - ' . __( 'Complete', 'media-consumption-log' ), __( 'Complete', 'media-consumption-log' ), 'manage_options', 'mcl-complete', array( 'MclComplete', 'create_page' ) );
        add_submenu_page( 'mcl-quick-post', 'MCL - ' . __( 'Units', 'media-consumption-log' ), __( 'Units', 'media-consumption-log' ), 'manage_options', 'mcl-unit', array( 'MclUnit', 'create_page' ) );
        add_submenu_page( 'mcl-quick-post', 'MCL - ' . __( 'Rebuild data', 'media-consumption-log' ), __( 'Rebuild data', 'media-consumption-log' ), 'manage_options', 'mcl-rebuild-data', array( 'MclRebuildData', 'create_page' ) );
        add_submenu_page( 'mcl-quick-post', 'MCL - ' . __( 'Settings', 'media-consumption-log' ), __( 'Settings', 'media-consumption-log' ), 'manage_options', 'mcl-settings', array( 'MclSettings', 'create_page' ) );
    }

    public static function admin_bar_menu( $wp_admin_bar ) {
        $args = array(
            'id' => 'mcl_admin_bar_button',
            'title' => __( 'Quick Post', 'media-consumption-log' ),
            'href' => admin_url( "admin.php?page=mcl-quick-post" ),
            'meta' => array(
                'class' => 'mcl_admin_bar_button_class'
            )
        );

        $wp_admin_bar->add_node( $args );
    }

    public static function admin_init() {
        MclSettings::register_settings();
        MclUnit::register_settings();
    }

    public static function wp_insert_post( $post_id ) {
        // Set category and tag in new post with link
        if ( isset( $_REQUEST['category'] ) ) {
            wp_set_post_categories( $post_id, array( $_REQUEST['category'] ) );
        }

        if ( isset( $_REQUEST['tag'] ) ) {
            wp_set_post_tags( $post_id, get_tag( $_REQUEST['tag'] )->name );
        }

        // Add default custom fields
        MclComplete::add_default_custom_field_in_new_post( $post_id );
        MclNumber::add_default_custom_field_in_new_post( $post_id );
    }

    public static function save_post( $post_id ) {
        MclNumber::check_mcl_number_after_saving( $post_id );
        MclComplete::check_complete_after_saving( $post_id );
    }

}

?>