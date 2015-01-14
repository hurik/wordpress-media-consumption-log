<?php

MclData::init();

class MclData {

    public static function init() {
        add_action( 'transition_post_status', array( get_called_class(), 'mcl_data_transition_post_status' ), 999999, 3 );
        add_action( 'save_post', array( get_called_class(), 'mcl_data_save_post' ), 999999 );
        add_action( 'delete_post', array( get_called_class(), 'mcl_data_delete_post' ), 999999 );
    }

    function mcl_data_transition_post_status( $new_status, $old_status, $post ) {
        if ( ($old_status == 'publish' && $new_status != 'publish' ) ) {
            self::updateData();
        }
    }

    function mcl_data_save_post( $pid ) {
        if ( get_post_status( $pid ) == 'publish' ) {
            self::updateData();
        }
    }

    function mcl_data_delete_post( $pid ) {
        self::updateData();
    }

    function updateData() {
        MclStatusHelper::updateData();
        MclStatisticsHelper::updateData();
    }

}

?>