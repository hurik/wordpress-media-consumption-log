<?php

MclLanguages::init();

class MclLanguages {

    public static function init() {
        add_action( 'init', array( get_called_class(), 'mcl_load_plugin_textdomain' ) );
    }

    public static function mcl_load_plugin_textdomain() {
        load_plugin_textdomain( 'media-consumption-log', false, basename( dirname( __FILE__ ) ) . '/languages' );
    }

}

?>