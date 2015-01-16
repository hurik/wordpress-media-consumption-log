<?php

MclHooks::on_start();

class MclHooks {

    public static function on_start() {
        add_action( 'init', array( get_called_class(), 'init' ) );

        if ( !is_admin() && MclSettingsHelper::isOtherCommaInTags() ) {
            add_filter( 'get_post_tag', array( 'MclCommaInTags', 'comma_tag_filter' ) );
            add_filter( 'get_terms', array( 'MclCommaInTags', 'comma_tags_filter' ) );
            add_filter( 'get_the_terms', array( 'MclCommaInTags', 'comma_tags_filter' ) );
        }

        add_shortcode( 'mcl-stats', array( 'MclStatistics', 'build_statistics' ) );
        add_shortcode( 'mcl', array( 'MclStatus', 'build_status' ) );
    }

    public static function init() {
        load_plugin_textdomain( 'media-consumption-log', false, basename( dirname( __FILE__ ) ) . '/languages' );
    }

}

?>