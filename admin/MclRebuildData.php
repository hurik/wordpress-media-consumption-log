<?php

class MclRebuildData {

    public static function create_page() {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        if ( isset( $_GET["now"] ) && $_GET["now"] == 1 ) {
            MclData::updateData();
        }
        ?>
        <div class="wrap">
            <h2>Media Consumption Log - <?php _e( 'Rebuild data', 'media-consumption-log' ); ?></h2>

            <p>
                <input class="button-primary" type=button onClick="location.href = 'admin.php?page=mcl-rebuild-data&now=1'" value="<?php _e( 'Rebuild data now!', 'media-consumption-log' ); ?>" />
            </p>

            <h3><?php _e( 'Information', 'media-consumption-log' ); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e( 'Number of queries', 'media-consumption-log' ); ?></th>
                    <td><?php echo get_num_queries(); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e( 'Execution time', 'media-consumption-log' ); ?></th>
                    <td><?php timer_stop( 1 ); ?></td>
                </tr>
            </table>
        </div>	
        <?php
    }

}

?>