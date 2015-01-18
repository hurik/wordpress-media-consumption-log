<?php

class MclSettings {

    // Option group name
    const option_group_name = "mcl-settings-group";
    // Default values
    const default_statistics_daily_count = 31;
    const default_statistics_daily_options = "annotations: { textStyle: { color: '#000000', fontSize: 9, bold: true }, highContrast: true, alwaysOutside: true },
height: data.getNumberOfRows() * 15 + 100,
legend: { position: 'top', maxLines: 4, alignment: 'center' },
bar: { groupWidth: '70%' },
focusTarget: 'category',
chartArea: {left: 70, top: 80, width: '80%', height: data.getNumberOfRows() * 15},
isStacked: true,";
    const default_statistics_monthly_count = 6;
    const default_statistics_monthly_options = "annotations: { textStyle: { color: '#000000', fontSize: 9, bold: true }, highContrast: true, alwaysOutside: true },
height: data.getNumberOfRows() * 15 + 100,
legend: { position: 'top', maxLines: 4, alignment: 'center' },
bar: { groupWidth: '70%' },
focusTarget: 'category',
chartArea: { left: 50, top: 80, width: '80%', height: data.getNumberOfRows() * 15 },
isStacked: true,";
    const default_other_separator = "-";

    private static function default_statistics_daily_date_format() {
        return __( 'Y-m-j', 'media-consumption-log' );
    }

    private static function default_statistics_monthly_date_format() {
        return __( 'Y-m', 'media-consumption-log' );
    }

    private static function default_other_and() {
        return __( 'and', 'media-consumption-log' );
    }

    private static function default_other_to() {
        return __( 'to', 'media-consumption-log' );
    }

    // Getter
    public static function get_status_exclude_category() {
        return get_option( 'mcl_setting_status_exclude_category' );
    }

    public static function get_statistics_exclude_category() {
        return get_option( 'mcl_setting_statistics_exclude_category' );
    }

    public static function get_statistics_daily_count() {
        $value = get_option( 'mcl_setting_statistics_daily_count' );

        if ( empty( $value ) ) {
            return self::default_statistics_daily_count;
        } else {
            return $value;
        }
    }

    public static function get_statistics_daily_date_format() {
        $value = get_option( 'mcl_setting_statistics_daily_date_format' );

        if ( empty( $value ) ) {
            return self::default_statistics_daily_date_format();
        } else {
            return $value;
        }
    }

    public static function get_statistics_daily_options() {
        $value = get_option( 'mcl_setting_statistics_daily_options' );

        if ( empty( $value ) ) {
            return self::default_statistics_daily_options;
        } else {
            return $value;
        }
    }

    public static function get_statistics_monthly_count() {
        $value = get_option( 'mcl_setting_statistics_monthly_count' );

        if ( empty( $value ) ) {
            return self::default_statistics_monthly_count;
        } else {
            return $value;
        }
    }

    public static function get_statistics_monthly_date_format() {
        $value = get_option( 'mcl_setting_statistics_monthly_date_format' );

        if ( empty( $value ) ) {
            return self::default_statistics_monthly_date_format();
        } else {
            return $value;
        }
    }

    public static function get_statistics_monthly_options() {
        $value = get_option( 'mcl_setting_statistics_monthly_options' );

        if ( empty( $value ) ) {
            return self::default_statistics_monthly_options;
        } else {
            return $value;
        }
    }

    public static function get_other_separator() {
        $value = get_option( 'mcl_setting_other_separator' );

        if ( empty( $value ) ) {
            return self::default_other_separator;
        } else {
            return $value;
        }
    }

    public static function get_other_and() {
        $value = get_option( 'mcl_setting_other_and' );

        if ( empty( $value ) ) {
            return self::default_other_and();
        } else {
            return $value;
        }
    }

    public static function get_other_to() {
        $value = get_option( 'mcl_setting_other_to' );

        if ( empty( $value ) ) {
            return self::default_other_to();
        } else {
            return $value;
        }
    }

    // Setting page
    public static function register_settings() {
        register_setting( self::option_group_name, 'mcl_setting_status_exclude_category' );
        register_setting( self::option_group_name, 'mcl_setting_statistics_exclude_category' );
        register_setting( self::option_group_name, 'mcl_setting_statistics_daily_count' );
        register_setting( self::option_group_name, 'mcl_setting_statistics_daily_date_format' );
        register_setting( self::option_group_name, 'mcl_setting_statistics_daily_options' );
        register_setting( self::option_group_name, 'mcl_setting_statistics_monthly_count' );
        register_setting( self::option_group_name, 'mcl_setting_statistics_monthly_date_format' );
        register_setting( self::option_group_name, 'mcl_setting_statistics_monthly_options' );
        register_setting( self::option_group_name, 'mcl_setting_other_separator' );
        register_setting( self::option_group_name, 'mcl_setting_other_and' );
        register_setting( self::option_group_name, 'mcl_setting_other_to' );
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
                        <td><input type="text" name="mcl_setting_status_exclude_category" value="<?php echo esc_attr( self::get_status_exclude_category() ); ?>" style="width:100%;" />
                            <p class="description"><?php _e( 'IDs of the category you want to exclude from the status page. Example: 1,45,75,284', 'media-consumption-log' ); ?><br />
                                <?php _e( 'IDs of the categories:', 'media-consumption-log' ); ?> <?php echo $cats_text; ?></p></td>
                    </tr>
                </table>

                <h3><?php _e( 'Statistics', 'media-consumption-log' ); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e( 'Excluded Categories', 'media-consumption-log' ); ?></th>
                        <td><input type="text" name="mcl_setting_statistics_exclude_category" value="<?php echo esc_attr( self::get_statistics_exclude_category() ); ?>" style="width:100%;" />
                            <p class="description"><?php _e( 'IDs of the category you want to exclude from the statistics page. Example: 1', 'media-consumption-log' ); ?><br />
                                <?php _e( 'IDs of the categories:', 'media-consumption-log' ); ?> <?php echo $cats_text; ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e( 'Daily statistics size', 'media-consumption-log' ); ?></th>
                        <td><input type="text" name="mcl_setting_statistics_daily_count" value="<?php echo esc_attr( self::get_statistics_daily_count() ); ?>" style="width:100%;" />
                            <p class="description"><?php _e( 'Please insert number of days the daily statistic should cover. Default:', 'media-consumption-log' ); ?> <?php echo self::default_statistics_daily_count; ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e( 'Daily date format', 'media-consumption-log' ); ?></th>
                        <td><input type="text" name="mcl_setting_statistics_daily_date_format" value="<?php echo esc_attr( self::get_statistics_daily_date_format() ); ?>" style="width:100%;" />
                            <p class="description"><?php _e( 'Format for dates on the daily statistics page. Default:', 'media-consumption-log' ); ?> <?php echo self::default_statistics_daily_date_format(); ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e( 'Daily Google Charts Options', 'media-consumption-log' ); ?></th>
                        <td><textarea name="mcl_setting_statistics_daily_options" rows="6" style="width:100%;"><?php echo esc_attr( self::get_statistics_daily_options() ); ?></textarea>
                            <p class="description"><?php _e( 'When the daily graph gets really big it is sometime necessary to change some Google Charts options. Check the documentation for more information: <a href="https://google-developers.appspot.com/chart/interactive/docs/gallery/barchart#StackedBars">Google Charts - Stacked Bar Charts</a><br />Default:<br />', 'media-consumption-log' ); ?>
                                <?php echo str_replace( "\n", "<br />", self::default_statistics_daily_options ); ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e( 'Monthly statistics size', 'media-consumption-log' ); ?></th>
                        <td><input type="text" name="mcl_setting_statistics_monthly_count" value="<?php echo esc_attr( self::get_statistics_monthly_count() ); ?>" style="width:100%;" />
                            <p class="description"><?php _e( 'Please insert number of months the statistic should cover. Default:', 'media-consumption-log' ); ?> <?php echo self::default_statistics_monthly_count; ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e( 'Monthly date format', 'media-consumption-log' ); ?></th>
                        <td><input type="text" name="mcl_setting_statistics_monthly_date_format" value="<?php echo esc_attr( self::get_statistics_monthly_date_format() ); ?>" style="width:100%;" />
                            <p class="description"><?php _e( 'Format for dates on the monthly statistics page. Default:', 'media-consumption-log' ); ?> <?php echo self::default_statistics_monthly_date_format(); ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e( 'Monthly Google Charts Options', 'media-consumption-log' ); ?></th>
                        <td><textarea name="mcl_setting_statistics_monthly_options" rows="6" style="width:100%;"><?php echo esc_attr( self::get_statistics_monthly_options() ); ?></textarea>
                            <p class="description"><?php _e( 'When the monthly graph gets really big it is sometime necessary to change some Google Charts options. Check the documentation for more information: <a href="https://google-developers.appspot.com/chart/interactive/docs/gallery/barchart#StackedBars">Google Charts - Stacked Bar Charts</a><br />Default:<br />', 'media-consumption-log' ); ?>
                                <?php echo str_replace( "\n", "<br />", self::default_statistics_monthly_options ); ?></p></td>
                    </tr>
                </table>

                <h3><?php _e( 'Other settings', 'media-consumption-log' ); ?></h3>
                <p class="description"><?php _e( '<strong>Attention:</strong> This settings should be changed after using the plugin for some time! This should only been altered after the installation.', 'media-consumption-log' ); ?></p>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e( 'Separator', 'media-consumption-log' ); ?></th>
                        <td><input type="text" name="mcl_setting_other_separator" value="<?php echo esc_attr( self::get_other_separator() ); ?>" style="width:100%;" />
                            <p class="description"><?php _e( 'Define a seperator which separates the title from the episode/chapter number. Spaces are added on both side. Default:', 'media-consumption-log' ); ?> <?php echo self::default_other_separator; ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e( 'mcl_number "and"', 'media-consumption-log' ); ?></th>
                        <td><input type="text" name="mcl_setting_other_and" value="<?php echo esc_attr( self::get_other_and() ); ?>" style="width:100%;" />
                            <p class="description"><?php _e( 'When the keyword is in the episode/chapter number the mcl_number will be set to 2. Spaces are added on both side. Default:', 'media-consumption-log' ); ?> <?php echo self::default_other_and(); ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e( 'mcl_number "to"', 'media-consumption-log' ); ?></th>
                        <td><input type="text" name="mcl_setting_other_to" value="<?php echo esc_attr( self::get_other_to() ); ?>" style="width:100%;" />
                            <p class="description"><?php _e( 'When the keyword is in the episode/chapter number the mcl_number will be set to last number - first number + 1. Spaces are added on both side. Default:', 'media-consumption-log' ); ?> <?php echo self::default_other_to(); ?></p></td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>	
        <?php
    }

}

?>