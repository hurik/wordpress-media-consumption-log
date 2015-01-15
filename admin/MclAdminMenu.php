<?php

MclAdminMenu::init();

class MclAdminMenu {

    public static function init() {
        add_action( 'admin_menu', array( get_called_class(), 'mcl_admin_menu' ) );
        add_action( 'admin_bar_menu', array( get_called_class(), 'mcl_admin_bar_button' ), 75 );
    }

    public static function mcl_admin_menu() {
        // Add menu
        add_menu_page( 'MCL', 'MCL', 'manage_options', 'mcl-quick-post' );
        add_submenu_page( 'mcl-quick-post', 'MCL - ' . __( 'Quick Post', 'media-consumption-log' ), __( 'Quick Post', 'media-consumption-log' ), 'manage_options', 'mcl-quick-post', 'mcl_quick_post' );
        add_submenu_page( 'mcl-quick-post', 'MCL - ' . __( 'Complete', 'media-consumption-log' ), __( 'Complete', 'media-consumption-log' ), 'manage_options', 'mcl-complete', 'mcl_complete' );
        add_submenu_page( 'mcl-quick-post', 'MCL - ' . __( 'Units', 'media-consumption-log' ), __( 'Units', 'media-consumption-log' ), 'manage_options', 'mcl-unit', 'mcl_unit' );
        add_submenu_page( 'mcl-quick-post', 'MCL - ' . __( 'Rebuild data', 'media-consumption-log' ), __( 'Rebuild data', 'media-consumption-log' ), 'manage_options', 'mcl-rebuild-data', array( 'MclRebuildData', 'create_page' ) );
        add_submenu_page( 'mcl-quick-post', 'MCL - ' . __( 'Settings', 'media-consumption-log' ), __( 'Settings', 'media-consumption-log' ), 'manage_options', 'mcl-settings', 'mcl_settings' );

        // Register settings
        add_action( 'admin_init', 'mcl_settings_register' );

        // Register settings
        add_action( 'admin_init', 'mcl_unit_register' );
    }

    public static function mcl_admin_bar_button( $wp_admin_bar ) {
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

}

?>