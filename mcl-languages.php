<?php

add_action( 'init', 'mcl_optimize_textdomain' );

function mcl_optimize_textdomain() {
    load_plugin_textdomain( 'media-consumption-log', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

?>
