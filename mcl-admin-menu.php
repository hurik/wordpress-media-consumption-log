<?php

add_action( 'admin_menu', 'mcl_admin_menu' );

function mcl_admin_menu() {
    add_menu_page( 'MCL', 'MCL', 'manage_options', 'mcl-quick-post');
    add_submenu_page( 'mcl-quick-post', 'MCL - Quick Post', 'Quick Post', 'manage_options', 'mcl-quick-post', 'mcl_quick_post' );
    add_submenu_page( 'mcl-quick-post', 'MCL - Finished', 'Finished', 'manage_options', 'mcl-finished', 'mcl_finished' );
    add_submenu_page( 'mcl-quick-post', 'MCL - Settings', 'Settings', 'manage_options', 'mcl-settings', 'mcl_settings' );
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