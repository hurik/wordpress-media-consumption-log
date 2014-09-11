<?php

add_action( 'admin_menu', 'mcl_admin_menu' );

function mcl_admin_menu() {
    // Add menu
    add_menu_page( 'MCL', 'MCL', 'manage_options', 'mcl-quick-post' );
    add_submenu_page( 'mcl-quick-post', 'MCL - Quick Post', 'Quick Post', 'manage_options', 'mcl-quick-post', 'mcl_quick_post' );
    add_submenu_page( 'mcl-quick-post', 'MCL - Complete', 'Complete', 'manage_options', 'mcl-complete', 'mcl_complete' );
    add_submenu_page( 'mcl-quick-post', 'MCL - Settings', 'Settings', 'manage_options', 'mcl-settings', 'mcl_settings' );

    // Register settings
    add_action( 'admin_init', 'mcl_settings_register' );
}

add_action( 'admin_bar_menu', 'mcl_admin_bar_button', 75 );

function mcl_admin_bar_button( $wp_admin_bar ) {
    $args = array(
        'id' => 'mcl_admin_bar_button',
        'title' => 'Quick Post',
        'href' => admin_url( "admin.php?page=mcl-quick-post" ),
        'meta' => array(
            'class' => 'mcl_admin_bar_button_class'
        )
    );

    $wp_admin_bar->add_node( $args );
}

?>