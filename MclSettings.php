<?php
/*
  Copyright (C) 2014-2025 Andreas Giemza <andreas@giemza.net>

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License along
  with this program; if not, write to the Free Software Foundation, Inc.,
  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

class MclSettings
{

    // Setting group name
    const SETTINGS_GROUP = "mcl-settings-group";
    const SETTINGS_UNITS_GROUP = "mcl-settings-units-group";
    // Setting names
    const SETTING_MONITORED_CATEGORIES_SERIAL = "mcl_setting_monitored_categories_serials";
    const SETTING_MONITORED_CATEGORIES_RECURRING = "mcl_setting_monitored_categories_recurring";
    const SETTING_MONITORED_CATEGORIES_NON_SERIAL = "mcl_setting_monitored_categories_non_serials";
    const SETTING_STATISTICS_DAILY_COUNT = "mcl_setting_statistics_daily_count";
    const SETTING_STATISTICS_DAILY_DATE_FORMAT = "mcl_setting_statistics_daily_date_format";
    const SETTING_STATISTICS_MONTHLY_COUNT = "mcl_setting_statistics_monthly_count";
    const SETTING_STATISTICS_MONTHLY_DATE_FORMAT = "mcl_setting_statistics_monthly_date_format";
    const SETTING_STATISTICS_YEARLY_COUNT = "mcl_setting_statistics_yearly_count";
    const SETTING_STATISTICS_AVERAGE_CONSUMPTION_DEVELOPMENT_MAX_DELTA = "mcl_setting_statistics_average_consumption_development_max_delta";
    const SETTING_STATISTICS_MOST_CONSUMED_COUNT = "mcl_setting_statistics_most_consumed_count";
    const SETTING_FORGOTTEN_MIN_DAYS = "mcl_setting_forgotten_min_days";
    const SETTING_OTHER_SEPARATOR = "mcl_setting_other_separator";
    const SETTING_OTHER_AND = "mcl_setting_other_and";
    const SETTING_OTHER_TO = "mcl_setting_other_to";
    // Setting unit prefix
    const SETTING_UNIT_PREFIX = "mcl_unit_";
    // Default values
    const default_statistics_daily_count = 31;
    const default_statistics_monthly_count = 12;
    const default_statistics_yearly_count = 0;
    const default_statistics_average_consumption_development_max_delta = 5;
    const default_statistics_most_consumed_count = 10;
    const default_forgotten_min_days = 91;
    const default_other_separator = "-";

    private static function default_statistics_daily_date_format()
    {
        return __('Y-m-d', 'media-consumption-log');
    }

    private static function default_statistics_monthly_date_format()
    {
        return __('Y-m', 'media-consumption-log');
    }

    private static function default_other_and()
    {
        return __('and', 'media-consumption-log');
    }

    private static function default_other_to()
    {
        return __('to', 'media-consumption-log');
    }

    // Getter
    public static function get_monitored_categories_serials()
    {
        return get_option(self::SETTING_MONITORED_CATEGORIES_SERIAL);
    }

    public static function get_monitored_categories_recurring()
    {
        return get_option(self::SETTING_MONITORED_CATEGORIES_RECURRING);
    }

    public static function get_monitored_categories_non_serials()
    {
        return get_option(self::SETTING_MONITORED_CATEGORIES_NON_SERIAL);
    }

    public static function get_all_monitored_categories()
    {
        $monitored_categories_serials = MclSettings::get_monitored_categories_serials();
        $monitored_categories_recurring = MclSettings::get_monitored_categories_recurring();
        $monitored_categories_non_serials = MclSettings::get_monitored_categories_non_serials();

        if (!empty($monitored_categories_serials) || !empty($monitored_categories_non_serials) || !empty($monitored_categories_recurring)) {
            return get_categories("hide_empty=0&include=" . $monitored_categories_serials . "," . $monitored_categories_recurring . "," . $monitored_categories_non_serials);
        } else {
            return array();
        }
    }

    public static function get_statistics_daily_count()
    {
        $value = get_option(self::SETTING_STATISTICS_DAILY_COUNT);

        if ((string) (int) $value === $value && (int) $value >= 0) {
            return $value;
        } else {
            return self::default_statistics_daily_count;
        }
    }

    public static function get_statistics_daily_date_format()
    {
        $value = get_option(self::SETTING_STATISTICS_DAILY_DATE_FORMAT);

        if (empty($value)) {
            return self::default_statistics_daily_date_format();
        } else {
            return $value;
        }
    }

    public static function get_statistics_monthly_count()
    {
        $value = get_option(self::SETTING_STATISTICS_MONTHLY_COUNT);

        if ((string) (int) $value === $value && (int) $value >= 0) {
            return $value;
        } else {
            return self::default_statistics_monthly_count;
        }
    }

    public static function get_statistics_monthly_date_format()
    {
        $value = get_option(self::SETTING_STATISTICS_MONTHLY_DATE_FORMAT);

        if (empty($value)) {
            return self::default_statistics_monthly_date_format();
        } else {
            return $value;
        }
    }

    public static function get_statistics_yearly_count()
    {
        $value = get_option(self::SETTING_STATISTICS_YEARLY_COUNT);

        if ((string) (int) $value === $value && (int) $value >= 0) {
            return $value;
        } else {
            return self::default_statistics_yearly_count;
        }
    }

    public static function get_statistics_average_consumption_development_max_delta()
    {
        $value = get_option(self::SETTING_STATISTICS_AVERAGE_CONSUMPTION_DEVELOPMENT_MAX_DELTA);

        if (is_numeric($value) && $value >= 0) {
            return $value;
        } else {
            return self::default_statistics_average_consumption_development_max_delta;
        }
    }

    public static function get_statistics_most_consumed_count()
    {
        $value = get_option(self::SETTING_STATISTICS_MOST_CONSUMED_COUNT);

        if ((string) (int) $value === $value && (int) $value >= 0) {
            return $value;
        } else {
            return self::default_statistics_most_consumed_count;
        }
    }

    public static function get_forgotten_min_days()
    {
        $value = get_option(self::SETTING_FORGOTTEN_MIN_DAYS);

        if ((string) (int) $value === $value && (int) $value >= 0) {
            return $value;
        } else {
            return self::default_forgotten_min_days;
        }
    }

    public static function get_other_separator()
    {
        $value = get_option(self::SETTING_OTHER_SEPARATOR);

        if (empty($value)) {
            return " " . trim(self::default_other_separator) . " ";
        } else {
            return " " . trim($value) . " ";
        }
    }

    public static function get_other_and()
    {
        $value = get_option(self::SETTING_OTHER_AND);

        if (empty($value)) {
            return " " . trim(self::default_other_and()) . " ";
        } else {
            return " " . trim($value) . " ";
        }
    }

    public static function get_other_to()
    {
        $value = get_option(self::SETTING_OTHER_TO);

        if (empty($value)) {
            return " " . trim(self::default_other_to()) . " ";
        } else {
            return " " . trim($value) . " ";
        }
    }

    // Unit getter
    public static function get_unit_of_category($category)
    {
        $unit = get_option(self::SETTING_UNIT_PREFIX . "{$category->term_id}");

        if (empty($unit)) {
            $unit = $category->name;
        }

        return $unit;
    }

    // Setting page
    public static function register_settings()
    {
        register_setting(self::SETTINGS_GROUP, self::SETTING_MONITORED_CATEGORIES_SERIAL);
        register_setting(self::SETTINGS_GROUP, self::SETTING_MONITORED_CATEGORIES_RECURRING);
        register_setting(self::SETTINGS_GROUP, self::SETTING_MONITORED_CATEGORIES_NON_SERIAL);
        register_setting(self::SETTINGS_GROUP, self::SETTING_STATISTICS_DAILY_COUNT);
        register_setting(self::SETTINGS_GROUP, self::SETTING_STATISTICS_DAILY_DATE_FORMAT);
        register_setting(self::SETTINGS_GROUP, self::SETTING_STATISTICS_MONTHLY_COUNT);
        register_setting(self::SETTINGS_GROUP, self::SETTING_STATISTICS_MONTHLY_DATE_FORMAT);
        register_setting(self::SETTINGS_GROUP, self::SETTING_STATISTICS_YEARLY_COUNT);
        register_setting(self::SETTINGS_GROUP, self::SETTING_STATISTICS_AVERAGE_CONSUMPTION_DEVELOPMENT_MAX_DELTA);
        register_setting(self::SETTINGS_GROUP, self::SETTING_STATISTICS_MOST_CONSUMED_COUNT);
        register_setting(self::SETTINGS_GROUP, self::SETTING_FORGOTTEN_MIN_DAYS);
        register_setting(self::SETTINGS_GROUP, self::SETTING_OTHER_SEPARATOR);
        register_setting(self::SETTINGS_GROUP, self::SETTING_OTHER_AND);
        register_setting(self::SETTINGS_GROUP, self::SETTING_OTHER_TO);

        // Register units
        $get_monitored_categories_serials = MclSettings::get_monitored_categories_serials();
        $get_monitored_categories_recurring = MclSettings::get_monitored_categories_recurring();

        if (!empty($get_monitored_categories_serials) || !empty($get_monitored_categories_recurring)) {
            $categories = get_categories("hide_empty=0&include=" . $get_monitored_categories_serials . "," . $get_monitored_categories_recurring);

            foreach ($categories as $category) {
                register_setting(self::SETTINGS_UNITS_GROUP, self::SETTING_UNIT_PREFIX . "{$category->term_id}");
            }
        }
    }

    public static function rebuild_data()
    {
        MclData::update_data();

        // From WP Page Load Stats by Mike Jolley
        // https://wordpress.org/plugins/wp-page-load-stats/
        $timer_stop = timer_stop();
        $query_count = get_num_queries();
        $memory_usage = memory_get_usage();
        $memory_peak_usage = memory_get_peak_usage();
        $memory_limit = self::let_to_num(WP_MEMORY_LIMIT);

        $text = sprintf(__("Rebuilding data done!\n\n%s queries in %s seconds.\n%s out of %s (%s%%) memory used.\nPeak memory usage %s.", 'media-consumption-log'), $query_count, $timer_stop, self::convert_bytes_to_hr($memory_usage), self::convert_bytes_to_hr($memory_limit), (round(($memory_usage / $memory_limit), 2) * 100), self::convert_bytes_to_hr($memory_peak_usage));
        
        echo $text;
        wp_die();
    }

    public static function rename_serial()
    {
        global $wpdb;

        $old_name = trim($_POST['current_name']);
        $new_name = trim($_POST['new_name']);

        if (empty($old_name) || empty($new_name)) {
            echo __("\"Current serial name\" and/or \"New serial name\" can't be empty!", 'media-consumption-log');
            wp_die();
        }

        // Check if tag exists
        $tag = get_term_by("name", $old_name, "post_tag");

        if (empty($tag)) {
            echo __("Can't find the serial!", 'media-consumption-log');
            wp_die();
        }

        // chekc if the new title isn't in use already
        $is_tag_free = get_term_by("name", $new_name, "post_tag");

        if (!empty($is_tag_free)) {
            echo __("There is already a serial with this name!", 'media-consumption-log');
            wp_die();
        }

        // Build strings for the str_replace
        $old_title = $tag->name . MclSettings::get_other_separator();
        $new_title = $new_name . MclSettings::get_other_separator();

        $args = array(
            'tag__in' => $tag->term_id,
            'posts_per_page' => -1);

        $posts = get_posts($args);

        // Remove the rebuild of MclData after post was changed
        remove_action('save_post', array('MclAdminHooks', 'save_post'));

        foreach ($posts as $post) {
            $updated_post = array(
                'ID' => $post->ID,
                'post_title' => str_replace($old_title, $new_title, $post->post_title),
                'post_name' => '',
            );

            wp_update_post($updated_post);
        }

        // Readd the rebuild of MclData after post was changed
        add_action('save_post', array('MclAdminHooks', 'save_post'));

        // Rename the tag and also update the slug
        wp_update_term($tag->term_id, 'post_tag', array(
            'name' => $new_name,
            'slug' => ''
        ));

        // Update data
        MclData::update_data();

        echo sprintf(__("Renamed \"%s\" to \"%s\"!\n%s post titles changed.", 'media-consumption-log'), $old_name, $new_name, count($posts));
        wp_die();
    }

    /**
     * From WP Page Load Stats by Mike Jolley
     * https://wordpress.org/plugins/wp-page-load-stats/
     * 
     * let_to_num function.
     *
     * This function transforms the php.ini notation for numbers (like '2M') to an integer
     *
     * @access public
     * @param $size
     * @return int
     */
    private static function let_to_num($size)
    {
        $l = substr($size, -1);
        $ret = substr($size, 0, -1);
        switch (strtoupper($l)) {
            case 'P':
                $ret *= 1024;
            case 'T':
                $ret *= 1024;
            case 'G':
                $ret *= 1024;
            case 'M':
                $ret *= 1024;
            case 'K':
                $ret *= 1024;
        }
        return $ret;
    }

    /**
     * From WP Page Load Stats by Mike Jolley
     * https://wordpress.org/plugins/wp-page-load-stats/
     * 
     * convert_bytes_to_hr function.
     *
     * @access public
     * @param mixed $bytes
     */
    private static function convert_bytes_to_hr($bytes)
    {
        $units = array(0 => 'B', 1 => 'kB', 2 => 'MB', 3 => 'GB');
        $log = log($bytes, 1024);
        $power = (int) $log;
        $size = pow(1024, $log - $power);
        return round($size, 2) . $units[$power];
    }

    public static function create_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'media-consumption-log'));
        }

        if (isset($_GET["settings-updated"]) && $_GET["settings-updated"] == "true") {
            MclData::update_data();
        }

        $categories = get_categories('hide_empty=0');
        $cats_text = MclHelpers::build_all_categories_string($categories, true);

        // Get posts in monitored categories without mcl_number
        $posts_without_mcl_number = self::get_posts_without_mcl_number();
        ?>
        <div class="wrap">
            <h2>Media Consumption Log - <?php _e('Settings', 'media-consumption-log'); ?></h2><br />

            <table class="widefat">
                <colgroup>
                    <col width="1%">
                    <col width="99%">
                </colgroup>
                <tr>
                    <th nowrap valign="top"><strong><?php _e('Quick Navigation', 'media-consumption-log'); ?></strong></th>
                    <td><a href="#general-settings"><?php _e('General settings', 'media-consumption-log'); ?></a> | <a href="#units"><?php _e('Units', 'media-consumption-log'); ?></a> | <a href="#rebuild-data"><?php _e('Rebuild data', 'media-consumption-log'); ?></a> | <a href="#posts-without-mcl-number"><?php _e('Posts without mcl_number', 'media-consumption-log'); ?></a></td>
                </tr>
            </table>

            <h3 id="general-settings"><?php _e('General settings', 'media-consumption-log'); ?></h3><hr />
            <form method="post" action="options.php">
                <?php settings_fields(self::SETTINGS_GROUP); ?>
                <?php do_settings_sections(self::SETTINGS_GROUP); ?>

                <h3><?php _e('Monitored categories', 'media-consumption-log'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Serials', 'media-consumption-log'); ?></th>
                        <td><input type="text" name="<?php echo self::SETTING_MONITORED_CATEGORIES_SERIAL; ?>" value="<?php echo esc_attr(self::get_monitored_categories_serials()); ?>" style="width:100%;" />
                            <p class="description"><?php _e('IDs of the categories which have epoisodes or chapters. This categories will be visible in the Statistics, Status, Quick Post and Serials Status. Example: 2,4,43,50,187,204,548,727', 'media-consumption-log'); ?></p></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Recurring', 'media-consumption-log'); ?></th>
                        <td><input type="text" name="<?php echo self::SETTING_MONITORED_CATEGORIES_RECURRING; ?>" value="<?php echo esc_attr(self::get_monitored_categories_recurring()); ?>" style="width:100%;" />
                            <p class="description"><?php _e('IDs of the categories which have reacurring things but have no numbering. This categories will be visible in the Statistics, Status and Quick Post, but not in Serials Status. Example: 1012', 'media-consumption-log'); ?></p></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Non serials', 'media-consumption-log'); ?></th>
                        <td><input type="text" name="<?php echo self::SETTING_MONITORED_CATEGORIES_NON_SERIAL; ?>" value="<?php echo esc_attr(self::get_monitored_categories_non_serials()); ?>" style="width:100%;" />
                            <p class="description"><?php _e('IDs of the categories which are visible in Statistics and Status, but not in Quick Post and Serials Status. Example: 45,75,284,873', 'media-consumption-log'); ?></p></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('IDs of the categories:', 'media-consumption-log'); ?></th>
                        <td><?php echo $cats_text; ?></td>
                    </tr>
                </table>

                <h3><?php _e('Statistics', 'media-consumption-log'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Daily statistics size', 'media-consumption-log'); ?></th>
                        <td><input type="text" name="<?php echo self::SETTING_STATISTICS_DAILY_COUNT; ?>" value="<?php echo esc_attr(self::get_statistics_daily_count()); ?>" style="width:100%;" />
                            <p class="description"><?php _e('Please insert number of days the daily statistic should cover. If you insert 0 the days since the first post will be covered. <strong>Attention:</strong> The graph can get really big! Default:', 'media-consumption-log'); ?> <?php echo self::default_statistics_daily_count; ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Daily date format', 'media-consumption-log'); ?></th>
                        <td><input type="text" name="<?php echo self::SETTING_STATISTICS_DAILY_DATE_FORMAT; ?>" value="<?php echo esc_attr(self::get_statistics_daily_date_format()); ?>" style="width:100%;" />
                            <p class="description"><?php _e('Format for dates on the daily statistics page. Default:', 'media-consumption-log'); ?> <?php echo self::default_statistics_daily_date_format(); ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Monthly statistics size', 'media-consumption-log'); ?></th>
                        <td><input type="text" name="<?php echo self::SETTING_STATISTICS_MONTHLY_COUNT; ?>" value="<?php echo esc_attr(self::get_statistics_monthly_count()); ?>" style="width:100%;" />
                            <p class="description"><?php _e('Please insert number of months the statistic should cover. If you insert 0 the months since the first post will be covered. Default:', 'media-consumption-log'); ?> <?php echo self::default_statistics_monthly_count; ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Monthly date format', 'media-consumption-log'); ?></th>
                        <td><input type="text" name="<?php echo self::SETTING_STATISTICS_MONTHLY_DATE_FORMAT; ?>" value="<?php echo esc_attr(self::get_statistics_monthly_date_format()); ?>" style="width:100%;" />
                            <p class="description"><?php _e('Format for dates on the monthly statistics page. Default:', 'media-consumption-log'); ?> <?php echo self::default_statistics_monthly_date_format(); ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Yearly statistics size', 'media-consumption-log'); ?></th>
                        <td><input type="text" name="<?php echo self::SETTING_STATISTICS_YEARLY_COUNT; ?>" value="<?php echo esc_attr(self::get_statistics_yearly_count()); ?>" style="width:100%;" />
                            <p class="description"><?php _e('Please insert number of years the statistic should cover. If you insert 0 the years since the first post will be covered. Default:', 'media-consumption-log'); ?> <?php echo self::default_statistics_yearly_count; ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Average consumption development max delta', 'media-consumption-log'); ?></th>
                        <td><input type="text" name="<?php echo self::SETTING_STATISTICS_AVERAGE_CONSUMPTION_DEVELOPMENT_MAX_DELTA; ?>" value="<?php echo esc_attr(self::get_statistics_average_consumption_development_max_delta()); ?>" style="width:100%;" />
                            <p class="description"><?php _e('When you start to track your consumption, there could be big fluctuation of the average consumption development. So with this option you can restrict the max y-value, the max delta is added to the last value. When you enter 0 Google Chart API automatically show all data. Default:', 'media-consumption-log'); ?> <?php echo self::default_statistics_average_consumption_development_max_delta; ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Most consumed count', 'media-consumption-log'); ?></th>
                        <td><input type="text" name="<?php echo self::SETTING_STATISTICS_MOST_CONSUMED_COUNT; ?>" value="<?php echo esc_attr(self::get_statistics_most_consumed_count()); ?>" style="width:100%;" />
                            <p class="description"><?php _e('Number of entries in the most cunsumed statistic. Default:', 'media-consumption-log'); ?> <?php echo self::default_statistics_most_consumed_count; ?></p></td>
                    </tr>
                </table>

                <h3><?php _e('Forgotten', 'media-consumption-log'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Minimal count of days', 'media-consumption-log'); ?></th>
                        <td><input type="text" name="<?php echo self::SETTING_FORGOTTEN_MIN_DAYS; ?>" value="<?php echo esc_attr(trim(self::get_forgotten_min_days())); ?>" style="width:100%;" />
                            <p class="description"><?php _e('Minimal number of days the last post of a serial must be old to be shown in forgotten. Default:', 'media-consumption-log'); ?> <?php echo self::default_forgotten_min_days; ?></p></td>
                    </tr>
                </table>

                <h3><?php _e('Other settings', 'media-consumption-log'); ?></h3>
                <p class="description"><?php _e('<strong>Attention:</strong> This settings should be changed after using the plugin for some time! This should only been altered after the installation.', 'media-consumption-log'); ?></p>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Separator', 'media-consumption-log'); ?></th>
                        <td><input type="text" name="<?php echo self::SETTING_OTHER_SEPARATOR; ?>" value="<?php echo esc_attr(trim(self::get_other_separator())); ?>" style="width:100%;" />
                            <p class="description"><?php _e('Define a seperator which separates the title from the episode/chapter number. Spaces are added on both side. Default:', 'media-consumption-log'); ?> <?php echo self::default_other_separator; ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('mcl_number "and"', 'media-consumption-log'); ?></th>
                        <td><input type="text" name="<?php echo self::SETTING_OTHER_AND; ?>" value="<?php echo esc_attr(trim(self::get_other_and())); ?>" style="width:100%;" />
                            <p class="description"><?php _e('When the keyword is in the episode/chapter number the mcl_number will be set to 2. Spaces are added on both side. Default:', 'media-consumption-log'); ?> <?php echo self::default_other_and(); ?></p></td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('mcl_number "to"', 'media-consumption-log'); ?></th>
                        <td><input type="text" name="<?php echo self::SETTING_OTHER_TO; ?>" value="<?php echo esc_attr(trim(self::get_other_to())); ?>" style="width:100%;" />
                            <p class="description"><?php _e('When the keyword is in the episode/chapter number the mcl_number will be set to last number - first number + 1. Spaces are added on both side. Default:', 'media-consumption-log'); ?> <?php echo self::default_other_to(); ?></p></td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>

            <h3 id="units"><?php _e('Units', 'media-consumption-log'); ?></h3><hr />
            <p class="description"><?php _e('Please define the units of the serial categories.', 'media-consumption-log'); ?></p>

            <form method="post" action="options.php">
                <?php settings_fields(self::SETTINGS_UNITS_GROUP); ?>
                <?php do_settings_sections(self::SETTINGS_UNITS_GROUP); ?>

                <table class="form-table">
                    <?php
                    $get_monitored_categories_serials = MclSettings::get_monitored_categories_serials();
                    $get_monitored_categories_recurring = MclSettings::get_monitored_categories_recurring();

                    if (!empty($get_monitored_categories_serials) || !empty($get_monitored_categories_recurring)) {
                        $categories = get_categories("hide_empty=0&include=" . $get_monitored_categories_serials . "," . $get_monitored_categories_recurring);

                        foreach ($categories as $category) {
                            ?>
                            <tr>
                                <th scope="row"><?php echo $category->name; ?></th>
                                <td><input type="text" name="<?php echo self::SETTING_UNIT_PREFIX . "{$category->term_id}"; ?>" value="<?php echo esc_attr(self::get_unit_of_category($category)); ?>" style="width:100%;" />
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <th scope="row"><?php _e('No monitored serial category!', 'media-consumption-log'); ?></th>
                        </tr>
                        <?php
                    }
                    ?>
                </table>

                <?php
                if (!empty($get_monitored_categories_serials)) {
                    submit_button();
                }
                ?>
            </form>

            <h3 id="rebuild-data"><?php _e('Rebuild data', 'media-consumption-log'); ?></h3><hr />
            <input class="button button-primary mcl_css_rebuild_data" value="<?php _e('Now!', 'media-consumption-log'); ?>" type="submit"><br /><br />

            <h3 id="posts-without-mcl-number"><?php _e('Posts without mcl_number', 'media-consumption-log'); ?></h3><hr />
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Posts', 'media-consumption-log'); ?></th>
                    <td><?php
                        if (count($posts_without_mcl_number) > 0) {
                            foreach ($posts_without_mcl_number as $post_without_mcl_number) {
                                edit_post_link($post_without_mcl_number->post_title, "", "", $post_without_mcl_number->ID);

                                if ($post_without_mcl_number != end($posts_without_mcl_number)) {
                                    echo "<br />";
                                }
                            }
                        } else {
                            echo _e('Nothing found!', 'media-consumption-log');
                        }
                        ?></td>
                </tr>   
            </table>

            <h3 id="posts-without-mcl-number"><?php _e('Rename serial', 'media-consumption-log'); ?></h3><hr />
            <p class="description"><?php _e('<strong>Attention:</strong> This will rename the tag and all post which have the serial name in the titel!', 'media-consumption-log'); ?></p>

            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Current serial name', 'media-consumption-log'); ?></th>
                    <td><input type="text" id="mcl_rename_serial_current_name" style="width:100%;" /></td>
                </tr>

                <tr>
                    <th scope="row"><?php _e('New serial name', 'media-consumption-log'); ?></th>
                    <td><input type="text" id="mcl_rename_serial_new_name" style="width:100%;" /></td>
                </tr>     
            </table>

            <input class="button button-primary mcl_css_rename_serial" value="<?php _e('Rename serial', 'media-consumption-log'); ?>" type="submit"><br /><br />

            <div id="mcl_loading"></div>
        </div>
        <?php
    }

    private static function get_posts_without_mcl_number()
    {
        global $wpdb;

        $monitored_categories_serials = MclSettings::get_monitored_categories_serials();
        $monitored_categories_recurring = MclSettings::get_monitored_categories_recurring();
        $monitored_categories_non_serials = MclSettings::get_monitored_categories_non_serials();

        if (!empty($monitored_categories_serials) || !empty($monitored_categories_non_serials) || !empty($monitored_categories_recurring)) {
            $monitored_categories = $monitored_categories_serials . "," . $monitored_categories_recurring . "," . $monitored_categories_non_serials;

            $posts_without_mcl_number = $wpdb->get_results("
				SELECT *
				FROM {$wpdb->prefix}posts AS p
				LEFT JOIN {$wpdb->prefix}term_relationships AS r ON p.ID = r.object_ID
				LEFT JOIN {$wpdb->prefix}term_taxonomy AS t ON r.term_taxonomy_id = t.term_taxonomy_id
				LEFT JOIN {$wpdb->prefix}postmeta AS m ON p.ID = m.post_id
				AND m.meta_key = 'mcl_number'
				WHERE p.post_type = 'post'
				  AND p.post_status = 'publish'
				  AND t.taxonomy = 'category'
				  AND t.term_id IN ({$monitored_categories})
				  AND (m.meta_value IS NULL
					   OR TRIM(m.meta_value) = '')
            ");

            return $posts_without_mcl_number;
        } else {
            return array();
        }
    }
}
