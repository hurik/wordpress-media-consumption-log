<?php

class MclSettings {

    // Option group name
    const option_group_name = "mcl-settings-group";
    // Default values
    const defaultStatisticsMclNumber = true;
    const defaultStatisticsNumberOfDays = 31;
    const defaultStatisticsGoogleChartsDailyOptions = "annotations: { textStyle: { color: '#000000', fontSize: 9, bold: true }, highContrast: true, alwaysOutside: true },
height: data.getNumberOfRows() * 15 + 100,
legend: { position: 'top', maxLines: 4, alignment: 'center' },
bar: { groupWidth: '70%' },
focusTarget: 'category',
chartArea: {left: 70, top: 80, width: '80%', height: data.getNumberOfRows() * 15},
isStacked: true,";
    const defaultStatisticsNumberOfMonths = 6;
    const defaultStatisticsGoogleChartsMonthlyOptions = "annotations: { textStyle: { color: '#000000', fontSize: 9, bold: true }, highContrast: true, alwaysOutside: true },
height: data.getNumberOfRows() * 15 + 100,
legend: { position: 'top', maxLines: 4, alignment: 'center' },
bar: { groupWidth: '70%' },
focusTarget: 'category',
chartArea: { left: 50, top: 80, width: '80%', height: data.getNumberOfRows() * 15 },
isStacked: true,";
    const defaultOtherCommaInTags = true;
    const defaultOtherSeparator = "-";

    static function defaultStatisticsDailyDateFormat() {
        return __( 'Y-m-j', 'media-consumption-log' );
    }

    static function defaultStatisticsMonthlyDateFormat() {
        return __( 'Y-m', 'media-consumption-log' );
    }

    static function defaultOtherMclNumberAnd() {
        return __( 'and', 'media-consumption-log' );
    }

    static function defaultOtherMclNumberTo() {
        return __( 'to', 'media-consumption-log' );
    }

    // Getter
    static function getStatusExcludeCategory() {
        return get_option( 'mcl_settings_status_exclude_category' );
    }

    static function getStatisticsExcludeCategory() {
        return get_option( 'mcl_settings_statistics_exclude_category' );
    }

    static function isStatisticsMclNumber() {
        $value = get_option( 'mcl_settings_statistics_mcl_number', self::defaultStatisticsMclNumber );

        if ( empty( $value ) ) {
            return false;
        } else {
            return true;
        }
    }

    static function getStatisticsNumberOfDays() {
        $value = get_option( 'mcl_settings_statistics_number_of_days' );

        if ( empty( $value ) ) {
            return self::defaultStatisticsNumberOfDays;
        } else {
            return $value;
        }
    }

    static function getStatisticsDailyDateFormat() {
        $value = get_option( 'mcl_settings_statistics_daily_date_format' );

        if ( empty( $value ) ) {
            return self::defaultStatisticsDailyDateFormat();
        } else {
            return $value;
        }
    }

    static function getStatisticsGoogleChartsDailyOptions() {
        $value = get_option( 'mcl_settings_statistics_google_charts_daily_options' );

        if ( empty( $value ) ) {
            return self::defaultStatisticsGoogleChartsDailyOptions;
        } else {
            return $value;
        }
    }

    static function getStatisticsNumberOfMonths() {
        $value = get_option( 'mcl_settings_statistics_number_of_months' );

        if ( empty( $value ) ) {
            return self::defaultStatisticsNumberOfMonths;
        } else {
            return $value;
        }
    }

    static function getStatisticsMonthlyDateFormat() {
        $value = get_option( 'mcl_settings_statistics_monthly_date_format' );

        if ( empty( $value ) ) {
            return self::defaultStatisticsMonthlyDateFormat();
        } else {
            return $value;
        }
    }

    static function getStatisticsGoogleChartsMonthlyOptions() {
        $value = get_option( 'mcl_settings_statistics_google_charts_monthly_options' );

        if ( empty( $value ) ) {
            return self::defaultStatisticsGoogleChartsMonthlyOptions;
        } else {
            return $value;
        }
    }

    static function isOtherCommaInTags() {
        $value = get_option( 'mcl_settings_other_comma_in_tags', self::defaultOtherCommaInTags );

        if ( empty( $value ) ) {
            return false;
        } else {
            return true;
        }
    }

    static function getOtherSeprator() {
        $value = get_option( 'mcl_settings_other_separator' );

        if ( empty( $value ) ) {
            return self::defaultOtherSeparator;
        } else {
            return $value;
        }
    }

    static function getOtherMclNumberAnd() {
        $value = get_option( 'mcl_settings_other_mcl_number_and' );

        if ( empty( $value ) ) {
            return self::defaultOtherMclNumberAnd();
        } else {
            return $value;
        }
    }

    static function getOtherMclNumberTo() {
        $value = get_option( 'mcl_settings_other_mcl_number_to' );

        if ( empty( $value ) ) {
            return self::defaultOtherMclNumberTo();
        } else {
            return $value;
        }
    }

    // Setting page
    public static function register_settings() {
        register_setting( self::option_group_name, 'mcl_settings_status_exclude_category' );
        register_setting( self::option_group_name, 'mcl_settings_statistics_exclude_category' );
        register_setting( self::option_group_name, 'mcl_settings_statistics_mcl_number' );
        register_setting( self::option_group_name, 'mcl_settings_statistics_number_of_days' );
        register_setting( self::option_group_name, 'mcl_settings_statistics_daily_date_format' );
        register_setting( self::option_group_name, 'mcl_settings_statistics_google_charts_daily_options' );
        register_setting( self::option_group_name, 'mcl_settings_statistics_number_of_months' );
        register_setting( self::option_group_name, 'mcl_settings_statistics_monthly_date_format' );
        register_setting( self::option_group_name, 'mcl_settings_statistics_google_charts_monthly_options' );
        register_setting( self::option_group_name, 'mcl_settings_other_comma_in_tags' );
        register_setting( self::option_group_name, 'mcl_settings_other_separator' );
        register_setting( self::option_group_name, 'mcl_settings_other_mcl_number_and' );
        register_setting( self::option_group_name, 'mcl_settings_other_mcl_number_to' );
    }

    public static function create_page() {
        $categories = get_categories( 'hide_empty=0' );
        $cats_text = MclStringHelper::build_all_categories_string( $categories, true );
        ?>
        <div class="wrap">
            <h2>Media Consumption Log - <?php _e( 'Settings', 'media-consumption-log' ); ?></h2>

            <form method="post" action="options.php">
                <?php settings_fields( self::option_group_name ); ?>
                <?php do_settings_sections( self::option_group_name ); ?>

                <h3><?php _e( 'Status', 'media-consumption-log' ); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e( 'Excluded Categories', 'media-consumption-log' ); ?></th>
                        <td><input type="text" name="mcl_settings_status_exclude_category" value="<?php echo esc_attr( self::getStatusExcludeCategory() ); ?>" style="width:100%;" />
                            <p class="description"><?php _e( 'IDs of the category you want to exclude from the status page. Example: 1,45,75', 'media-consumption-log' ); ?><br />
                                <?php _e( 'IDs of the categories:', 'media-consumption-log' ); ?> <?php echo $cats_text; ?></p></td>
                    </tr>
                </table>

                <h3><?php _e( 'Statistics', 'media-consumption-log' ); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e( 'Excluded Categories', 'media-consumption-log' ); ?></th>
                        <td><input type="text" name="mcl_settings_statistics_exclude_category" value="<?php echo esc_attr( self::getStatisticsExcludeCategory() ); ?>" style="width:100%;" />
                            <p class="description"><?php _e( 'IDs of the category you want to exclude from the statistics page. Example: 1', 'media-consumption-log' ); ?><br />
                                <?php _e( 'IDs of the categories:', 'media-consumption-log' ); ?> <?php echo $cats_text; ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e( 'Use mcl_numbers', 'media-consumption-log' ); ?></th>
                        <td><input type="checkbox" name="mcl_settings_statistics_mcl_number" value="1" <?php checked( self::isStatisticsMclNumber() ); ?> />
                            <p class="description"><?php _e( 'Do you want to use mcl_number when drawing the chart? Otherwiese it will use the post count. Default: ', 'media-consumption-log' ); ?><?php MclStringHelper::echo_checked_or_unchecked( self::defaultStatisticsMclNumber ); ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e( 'Daily statistics size', 'media-consumption-log' ); ?></th>
                        <td><input type="text" name="mcl_settings_statistics_number_of_days" value="<?php echo esc_attr( self::getStatisticsNumberOfDays() ); ?>" style="width:100%;" />
                            <p class="description"><?php _e( 'Please insert number of days the daily statistic should cover. Default: ', 'media-consumption-log' ); ?><?php echo self::defaultStatisticsNumberOfDays; ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e( 'Daily date format', 'media-consumption-log' ); ?></th>
                        <td><input type="text" name="mcl_settings_statistics_daily_date_format" value="<?php echo esc_attr( self::getStatisticsDailyDateFormat() ); ?>" style="width:100%;" />
                            <p class="description"><?php _e( 'Format for dates on the daily statistics page. Default: ', 'media-consumption-log' ); ?><?php echo self::defaultStatisticsDailyDateFormat(); ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e( 'Daily Google Charts Options', 'media-consumption-log' ); ?></th>
                        <td><textarea name="mcl_settings_statistics_google_charts_daily_options" rows="6" style="width:100%;"><?php echo esc_attr( self::getStatisticsGoogleChartsDailyOptions() ); ?></textarea>
                            <p class="description"><?php _e( 'When the daily graph gets really big it is sometime necessary to change some Google Charts options. Check the documentation for more information: <a href="https://google-developers.appspot.com/chart/interactive/docs/gallery/barchart#StackedBars">Google Charts - Stacked Bar Charts</a><br />Default:<br />', 'media-consumption-log' ); ?>
                                <?php echo str_replace( "\n", "<br />", self::defaultStatisticsGoogleChartsDailyOptions ); ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e( 'Monthly statistics size', 'media-consumption-log' ); ?></th>
                        <td><input type="text" name="mcl_settings_statistics_number_of_months" value="<?php echo esc_attr( self::getStatisticsNumberOfMonths() ); ?>" style="width:100%;" />
                            <p class="description"><?php _e( 'Please insert number of months the statistic should cover. Default: ', 'media-consumption-log' ); ?><?php echo self::defaultStatisticsNumberOfMonths; ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e( 'Monthly date format', 'media-consumption-log' ); ?></th>
                        <td><input type="text" name="mcl_settings_statistics_monthly_date_format" value="<?php echo esc_attr( self::getStatisticsMonthlyDateFormat() ); ?>" style="width:100%;" />
                            <p class="description"><?php _e( 'Format for dates on the monthly statistics page. Default: ', 'media-consumption-log' ); ?><?php echo self::defaultStatisticsMonthlyDateFormat(); ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e( 'Monthly Google Charts Options', 'media-consumption-log' ); ?></th>
                        <td><textarea name="mcl_settings_statistics_google_charts_monthly_options" rows="6" style="width:100%;"><?php echo esc_attr( self::getStatisticsGoogleChartsMonthlyOptions() ); ?></textarea>
                            <p class="description"><?php _e( 'When the monthly graph gets really big it is sometime necessary to change some Google Charts options. Check the documentation for more information: <a href="https://google-developers.appspot.com/chart/interactive/docs/gallery/barchart#StackedBars">Google Charts - Stacked Bar Charts</a><br />Default:<br />', 'media-consumption-log' ); ?>
                                <?php echo str_replace( "\n", "<br />", self::defaultStatisticsGoogleChartsMonthlyOptions ); ?></p></td>
                    </tr>
                </table>

                <h3><?php _e( 'Other settings', 'media-consumption-log' ); ?></h3>
                <p class="description"><?php _e( '<strong>Attention:</strong> This settings should be changed after using the plugin for some time! This should only been altered after the installation.', 'media-consumption-log' ); ?></p>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e( 'Activate comma in tags', 'media-consumption-log' ); ?></th>
                        <td><input type="checkbox" name="mcl_settings_other_comma_in_tags" value="1" <?php checked( self::isOtherCommaInTags() ); ?> />
                            <p class="description"><?php _e( 'When activated, "--" will be replaced with ", " in the frontend. Default: ', 'media-consumption-log' ); ?><?php MclStringHelper::echo_checked_or_unchecked( self::defaultOtherCommaInTags ); ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e( 'Separator', 'media-consumption-log' ); ?></th>
                        <td><input type="text" name="mcl_settings_other_separator" value="<?php echo esc_attr( self::getOtherSeprator() ); ?>" style="width:100%;" />
                            <p class="description"><?php _e( 'Define a seperator which separates the title from the episode/chapter number. Spaces are added on both side. Default: ', 'media-consumption-log' ); ?><?php echo self::defaultOtherSeparator; ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e( 'mcl_number "and"', 'media-consumption-log' ); ?></th>
                        <td><input type="text" name="mcl_settings_other_mcl_number_and" value="<?php echo esc_attr( self::getOtherMclNumberAnd() ); ?>" style="width:100%;" />
                            <p class="description"><?php _e( 'When the keyword is in the episode/chapter number the mcl_number will be set to 2. Spaces are added on both side. Default: ', 'media-consumption-log' ); ?><?php echo self::defaultOtherMclNumberAnd(); ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e( 'mcl_number "to"', 'media-consumption-log' ); ?></th>
                        <td><input type="text" name="mcl_settings_other_mcl_number_to" value="<?php echo esc_attr( self::getOtherMclNumberTo() ); ?>" style="width:100%;" />
                            <p class="description"><?php _e( 'When the keyword is in the episode/chapter number the mcl_number will be set to last number - first number + 1. Spaces are added on both side. Default: ', 'media-consumption-log' ); ?><?php echo self::defaultOtherMclNumberTo(); ?></p></td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>	
        <?php
    }

}

?>