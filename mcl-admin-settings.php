<?php

function mcl_settings_register() {
    register_setting( 'mcl-settings-group', 'mcl_settings_status_exclude_category' );
    register_setting( 'mcl-settings-group', 'mcl_settings_statistics_exclude_category' );
    register_setting( 'mcl-settings-group', 'mcl_settings_statistics_mcl_number' );
    register_setting( 'mcl-settings-group', 'mcl_settings_statistics_start_date' );
    register_setting( 'mcl-settings-group', 'mcl_settings_statistics_google_charts_options' );
}

function mcl_settings() {
    ?>
    <div class="wrap">
        <h2>Media Consumption Log - Settings</h2>

        <form method="post" action="options.php">
            <?php settings_fields( 'mcl-settings-group' ); ?>
            <?php do_settings_sections( 'mcl-settings-group' ); ?>

            <h3>Status</h3>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Excluded Categories</th>
                    <td><input type="text" name="mcl_settings_status_exclude_category" value="<?php echo esc_attr( get_option( 'mcl_settings_status_exclude_category' ) ); ?>" style="width:100%;" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"></th>
                    <td>IDs of the category you want to exclude from the status page. Example: 45,75</td>
                </tr>
            </table>

            <h3>Statistics</h3>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Excluded Categories</th>
                    <td><input type="text" name="mcl_settings_statistics_exclude_category" value="<?php echo esc_attr( get_option( 'mcl_settings_statistics_exclude_category' ) ); ?>" style="width:100%;" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"></th>
                    <td>IDs of the category you want to exclude from the statistics page. Example: 45,75</td>
                </tr>

                <tr valign="top">
                    <th scope="row">Use mcl_number</th>
                    <td><input type="checkbox" name="mcl_settings_statistics_mcl_number" value="1" <?php checked( 1 == get_option( 'mcl_settings_statistics_mcl_number' ) ); ?> /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"></th>
                    <td>Do you want to use mcl_number when drawing the chart? Otherwiese it will use the post count.</td>
                </tr>
                
                <tr valign="top">
                    <th scope="row">Start date</th>
                    <td><input type="text" name="mcl_settings_statistics_start_date" value="<?php echo esc_attr( get_option( 'mcl_settings_statistics_start_date' ) ); ?>" style="width:100%;" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"></th>
                    <td>If empty first post date is used or insert an date in this format: 2014-12-05 (Format: Y-m-d)</td>
                </tr>

                <tr valign="top">
                    <th scope="row">Google Charts Options</th>
                    <td><textarea name="mcl_settings_statistics_google_charts_options" rows="6" style="width:100%;"><?php echo esc_attr( get_option( 'mcl_settings_statistics_google_charts_options' ) ); ?></textarea></td>
                </tr>
                <tr valign="top">
                    <th scope="row"></th>
                    <td>When the graph gets really big it is sometime necessary to change some Google Charts options. Check the documentation for more information: <a href="https://google-developers.appspot.com/chart/interactive/docs/gallery/barchart#StackedBars">Google Charts - Stacked Bar Charts</a></td>
                </tr>
            </table>

            <?php submit_button(); ?>

        </form>
    </div>	
    <?php
}

?>