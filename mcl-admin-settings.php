<?php

function mcl_settings_register() {
    register_setting( 'mcl-settings-group', 'mcl_settings_status_exclude_category' );
    register_setting( 'mcl-settings-group', 'mcl_settings_statistics_exclude_category' );
    register_setting( 'mcl-settings-group', 'mcl_settings_statistics_mcl_number' );
    register_setting( 'mcl-settings-group', 'mcl_settings_statistics_number_of_days' );
    register_setting( 'mcl-settings-group', 'mcl_settings_statistics_date_format' );
    register_setting( 'mcl-settings-group', 'mcl_settings_statistics_google_charts_options' );
    register_setting( 'mcl-settings-group', 'mcl_settings_other_comma_in_tags' );
    register_setting( 'mcl-settings-group', 'mcl_settings_other_separator' );
    register_setting( 'mcl-settings-group', 'mcl_settings_other_mcl_number_and' );
    register_setting( 'mcl-settings-group', 'mcl_settings_other_mcl_number_to' );
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
                <tr>
                    <th scope="row">Excluded Categories</th>
                    <td><input type="text" name="mcl_settings_status_exclude_category" value="<?php echo esc_attr( get_option( 'mcl_settings_status_exclude_category' ) ); ?>" style="width:100%;" />
                        <p class="description">IDs of the category you want to exclude from the status page. Example: 45,75</p>
                </tr>
            </table>

            <h3>Statistics</h3>
            <table class="form-table">
                <tr>
                    <th scope="row">Excluded Categories</th>
                    <td><input type="text" name="mcl_settings_statistics_exclude_category" value="<?php echo esc_attr( get_option( 'mcl_settings_statistics_exclude_category' ) ); ?>" style="width:100%;" />
                        <p class="description">IDs of the category you want to exclude from the statistics page. Example: 45,75</p>
                </tr>

                <tr>
                    <th scope="row">Use mcl_numbers</th>
                    <td><input type="checkbox" name="mcl_settings_statistics_mcl_number" value="1" <?php checked( 1 == get_option( 'mcl_settings_statistics_mcl_number' ) ); ?> />
                        <p class="description">Do you want to use mcl_number when drawing the chart? Otherwiese it will use the post count.</p>
                </tr>

                <tr>
                    <th scope="row">Statistics size</th>
                    <td><input type="text" name="mcl_settings_statistics_number_of_days" value="<?php echo esc_attr( get_option( 'mcl_settings_statistics_number_of_days' ) ); ?>" style="width:100%;" />
                        <p class="description">Please inser number of days the statistic should cover. Example: 30</p>
                </tr>
                
                <tr>
                    <th scope="row">Date format</th>
                    <td><input type="text" name="mcl_settings_statistics_date_format" value="<?php echo esc_attr( get_option( 'mcl_settings_statistics_date_format' ) ); ?>" style="width:100%;" />
                        <p class="description">Format for dates on the statistics page. Example: j.m.Y</p>
                </tr>

                <tr>
                    <th scope="row">Google Charts Options</th>
                    <td><textarea name="mcl_settings_statistics_google_charts_options" rows="6" style="width:100%;"><?php echo esc_attr( get_option( 'mcl_settings_statistics_google_charts_options' ) ); ?></textarea>
                        <p class="description">When the graph gets really big it is sometime necessary to change some Google Charts options. Check the documentation for more information: <a href="https://google-developers.appspot.com/chart/interactive/docs/gallery/barchart#StackedBars">Google Charts - Stacked Bar Charts</a><br />
                            Default:<br />
                            height: data.getNumberOfRows() * 15 + 100,<br />legend: { position: 'top', maxLines: 4, alignment: 'center' },<br />bar: { groupWidth: '70%' },<br />focusTarget: 'category',<br />chartArea:{left: 100, top: 80, width: '75%', height: data.getNumberOfRows() * 15},<br />isStacked: true,</p>
                </tr>
            </table>

            <h3>Other settings</h3>
            <p class="description"><strong>Attention:</strong> This settings should be changed after using the plugin for some time! This should only been altered after the installation.</p>

            <table class="form-table">
                <tr>
                    <th scope="row">Activate comma in tags</th>
                    <td><input type="checkbox" name="mcl_settings_other_comma_in_tags" value="1" <?php checked( 1 == get_option( 'mcl_settings_other_comma_in_tags' ) ); ?> />
                        <p class="description">When activated, "--" will be replaced with ", " in the frontend.</p>
                </tr>

                <tr>
                    <th scope="row">Separator</th>
                    <td><input type="text" name="mcl_settings_other_separator" value="<?php echo esc_attr( get_option( 'mcl_settings_other_separator' ) ); ?>" style="width:100%;" />
                        <p class="description">Define a seperator which separates the title from the episode/chapter number. Spaces are added on both side. Example: -</p>
                </tr>

                <tr>
                    <th scope="row">mcl_number "and"</th>
                    <td><input type="text" name="mcl_settings_other_mcl_number_and" value="<?php echo esc_attr( get_option( 'mcl_settings_other_mcl_number_and' ) ); ?>" style="width:100%;" />
                        <p class="description">When the keyword is in the episode/chapter number the mcl_number will be set to 2. Spaces are added on both side. Example: and</p>
                </tr>

                <tr>
                    <th scope="row">mcl_number "to"</th>
                    <td><input type="text" name="mcl_settings_other_mcl_number_to" value="<?php echo esc_attr( get_option( 'mcl_settings_other_mcl_number_to' ) ); ?>" style="width:100%;" />
                        <p class="description">When the keyword is in the episode/chapter number the mcl_number will be set to last number - first number + 1. Spaces are added on both side. Example: to</p>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>	
    <?php
}

?>