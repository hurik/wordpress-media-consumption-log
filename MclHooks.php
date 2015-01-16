<?php

MclHooks::on_start();

class MclHooks {

    public static function on_start() {
        add_action( 'init', array( get_called_class(), 'init' ) );

        if ( !is_admin() && MclSettings::isOtherCommaInTags() ) {
            add_filter( 'get_post_tag', array( 'MclCommaInTags', 'comma_tag_filter' ) );
            add_filter( 'get_terms', array( 'MclCommaInTags', 'comma_tags_filter' ) );
            add_filter( 'get_the_terms', array( 'MclCommaInTags', 'comma_tags_filter' ) );
        }

        add_action( 'admin_bar_menu', array( get_called_class(), 'admin_bar_menu' ), 75 );
    }

    public static function init() {
        load_plugin_textdomain( 'media-consumption-log', false, basename( dirname( __FILE__ ) ) . '/languages' );
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

}

?>