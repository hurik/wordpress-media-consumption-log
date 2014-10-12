<?php

function mcl_settings_register() {
    register_setting( 'mcl-settings-group', 'mcl_settings_status_exclude_category' );
    register_setting( 'mcl-settings-group', 'mcl_settings_statistics_exclude_category' );
    register_setting( 'mcl-settings-group', 'mcl_settings_statistics_mcl_number' );
    register_setting( 'mcl-settings-group', 'mcl_settings_statistics_number_of_days' );
    register_setting( 'mcl-settings-group', 'mcl_settings_statistics_daily_date_format' );
    register_setting( 'mcl-settings-group', 'mcl_settings_statistics_google_charts_daily_options' );
    register_setting( 'mcl-settings-group', 'mcl_settings_statistics_number_of_months' );
    register_setting( 'mcl-settings-group', 'mcl_settings_statistics_monthly_date_format' );
    register_setting( 'mcl-settings-group', 'mcl_settings_statistics_google_charts_monthly_options' );
    register_setting( 'mcl-settings-group', 'mcl_settings_other_comma_in_tags' );
    register_setting( 'mcl-settings-group', 'mcl_settings_other_separator' );
    register_setting( 'mcl-settings-group', 'mcl_settings_other_mcl_number_and' );
    register_setting( 'mcl-settings-group', 'mcl_settings_other_mcl_number_to' );
}

function mcl_settings() {
    ?>
    <div class="wrap">
        <h2>Media Consumption Log - <?php _e( 'Settings', 'media-consumption-log' ); ?></h2>

        <form method="post" action="options.php">
            <?php settings_fields( 'mcl-settings-group' ); ?>
            <?php do_settings_sections( 'mcl-settings-group' ); ?>

            <h3><?php _e( 'Status', 'media-consumption-log' ); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e( 'Excluded Categories', 'media-consumption-log' ); ?></th>
                    <td><input type="text" name="mcl_settings_status_exclude_category" value="<?php echo esc_attr( get_option( 'mcl_settings_status_exclude_category' ) ); ?>" style="width:100%;" />
                        <p class="description"><?php _e( 'IDs of the category you want to exclude from the status page. Example: 1,45,75', 'media-consumption-log' ); ?></p>
                </tr>
            </table>

            <h3><?php _e( 'Statistics', 'media-consumption-log' ); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e( 'Excluded Categories', 'media-consumption-log' ); ?></th>
                    <td><input type="text" name="mcl_settings_statistics_exclude_category" value="<?php echo esc_attr( get_option( 'mcl_settings_statistics_exclude_category' ) ); ?>" style="width:100%;" />
                        <p class="description"><?php _e( 'IDs of the category you want to exclude from the statistics page. Example: 1', 'media-consumption-log' ); ?></p>
                </tr>

                <tr>
                    <th scope="row"><?php _e( 'Use mcl_numbers', 'media-consumption-log' ); ?></th>
                    <td><input type="checkbox" name="mcl_settings_statistics_mcl_number" value="1" <?php checked( 1 == get_option( 'mcl_settings_statistics_mcl_number' ) ); ?> />
                        <p class="description"><?php _e( 'Do you want to use mcl_number when drawing the chart? Otherwiese it will use the post count. Default: Checked', 'media-consumption-log' ); ?></p>
                </tr>

                <tr>
                    <th scope="row"><?php _e( 'Daily statistics size', 'media-consumption-log' ); ?></th>
                    <td><input type="text" name="mcl_settings_statistics_number_of_days" value="<?php echo esc_attr( get_option( 'mcl_settings_statistics_number_of_days' ) ); ?>" style="width:100%;" />
                        <p class="description"><?php _e( 'Please insert number of days the daily statistic should cover. Default: 30', 'media-consumption-log' ); ?></p>
                </tr>

                <tr>
                    <th scope="row"><?php _e( 'Daily date format', 'media-consumption-log' ); ?></th>
                    <td><input type="text" name="mcl_settings_statistics_daily_date_format" value="<?php echo esc_attr( get_option( 'mcl_settings_statistics_daily_date_format' ) ); ?>" style="width:100%;" />
                        <p class="description"><?php _e( 'Format for dates on the daily statistics page. Default: j.m.Y', 'media-consumption-log' ); ?></p>
                </tr>

                <tr>
                    <th scope="row"><?php _e( 'Daily Google Charts Options', 'media-consumption-log' ); ?></th>
                    <td><textarea name="mcl_settings_statistics_google_charts_daily_options" rows="6" style="width:100%;"><?php echo esc_attr( get_option( 'mcl_settings_statistics_google_charts_daily_options' ) ); ?></textarea>
                        <p class="description"><?php _e( 'When the daily graph gets really big it is sometime necessary to change some Google Charts options. Check the documentation for more information: <a href="https://google-developers.appspot.com/chart/interactive/docs/gallery/barchart#StackedBars">Google Charts - Stacked Bar Charts</a><br />Default:<br />', 'media-consumption-log' ); ?>
                            height: data.getNumberOfRows() * 15 + 100,<br />legend: { position: 'top', maxLines: 4, alignment: 'center' },<br />bar: { groupWidth: '70%' },<br />focusTarget: 'category',<br />chartArea:{left: 100, top: 80, width: '75%', height: data.getNumberOfRows() * 15},<br />isStacked: true,</p>
                </tr>

                <tr>
                    <th scope="row"><?php _e( 'Monthly statistics size', 'media-consumption-log' ); ?></th>
                    <td><input type="text" name="mcl_settings_statistics_number_of_months" value="<?php echo esc_attr( get_option( 'mcl_settings_statistics_number_of_months' ) ); ?>" style="width:100%;" />
                        <p class="description"><?php _e( 'Please insert number of months the statistic should cover. Default: 6', 'media-consumption-log' ); ?></p>
                </tr>

                <tr>
                    <th scope="row"><?php _e( 'Monthly date format', 'media-consumption-log' ); ?></th>
                    <td><input type="text" name="mcl_settings_statistics_monthly_date_format" value="<?php echo esc_attr( get_option( 'mcl_settings_statistics_monthly_date_format' ) ); ?>" style="width:100%;" />
                        <p class="description"><?php _e( 'Format for dates on the monthly statistics page. Default: n.Y', 'media-consumption-log' ); ?></p>
                </tr>

                <tr>
                    <th scope="row"><?php _e( 'Monthly Google Charts Options', 'media-consumption-log' ); ?></th>
                    <td><textarea name="mcl_settings_statistics_google_charts_monthly_options" rows="6" style="width:100%;"><?php echo esc_attr( get_option( 'mcl_settings_statistics_google_charts_monthly_options' ) ); ?></textarea>
                        <p class="description"><?php _e( 'When the monthly graph gets really big it is sometime necessary to change some Google Charts options. Check the documentation for more information: <a href="https://google-developers.appspot.com/chart/interactive/docs/gallery/barchart#StackedBars">Google Charts - Stacked Bar Charts</a><br />Default:<br />', 'media-consumption-log' ); ?>
                            height: data.getNumberOfRows() * 15 + 100,<br />legend: { position: 'top', maxLines: 4, alignment: 'center' },<br />bar: { groupWidth: '70%' },<br />focusTarget: 'category',<br />chartArea:{left: 60, top: 80, width: '75%', height: data.getNumberOfRows() * 15},<br />isStacked: true,</p>
                </tr>
            </table>

            <h3><?php _e( 'Other settings', 'media-consumption-log' ); ?></h3>
            <p class="description"><?php _e( '<strong>Attention:</strong> This settings should be changed after using the plugin for some time! This should only been altered after the installation.', 'media-consumption-log' ); ?></p>

            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e( 'Activate comma in tags', 'media-consumption-log' ); ?></th>
                    <td><input type="checkbox" name="mcl_settings_other_comma_in_tags" value="1" <?php checked( 1 == get_option( 'mcl_settings_other_comma_in_tags' ) ); ?> />
                        <p class="description"><?php _e( 'When activated, "--" will be replaced with ", " in the frontend. Default: Checked', 'media-consumption-log' ); ?></p>
                </tr>

                <tr>
                    <th scope="row"><?php _e( 'Separator', 'media-consumption-log' ); ?></th>
                    <td><input type="text" name="mcl_settings_other_separator" value="<?php echo esc_attr( get_option( 'mcl_settings_other_separator' ) ); ?>" style="width:100%;" />
                        <p class="description"><?php _e( 'Define a seperator which separates the title from the episode/chapter number. Spaces are added on both side. Default: -', 'media-consumption-log' ); ?></p>
                </tr>

                <tr>
                    <th scope="row"><?php _e( 'mcl_number "and"', 'media-consumption-log' ); ?></th>
                    <td><input type="text" name="mcl_settings_other_mcl_number_and" value="<?php echo esc_attr( get_option( 'mcl_settings_other_mcl_number_and' ) ); ?>" style="width:100%;" />
                        <p class="description"><?php _e( 'When the keyword is in the episode/chapter number the mcl_number will be set to 2. Spaces are added on both side. Default: and', 'media-consumption-log' ); ?></p>
                </tr>

                <tr>
                    <th scope="row"><?php _e( 'mcl_number "to"', 'media-consumption-log' ); ?></th>
                    <td><input type="text" name="mcl_settings_other_mcl_number_to" value="<?php echo esc_attr( get_option( 'mcl_settings_other_mcl_number_to' ) ); ?>" style="width:100%;" />
                        <p class="description"><?php _e( 'When the keyword is in the episode/chapter number the mcl_number will be set to last number - first number + 1. Spaces are added on both side. Default: to', 'media-consumption-log' ); ?></p>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>	
    <?php
}

?>