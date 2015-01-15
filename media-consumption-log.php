<?php

/*
  Plugin Name: Media Consumption Log
  Plugin URI: https://github.com/hurik/wordpress-media-consumption-log
  Description: This plugin helps you to keep track of the series, movies, mangas, comics you are reading.
  Version: 1.0.0
  Author: Andreas Giemza
  Author URI: http://www.andreasgiemza.de
  License: MIT
  Text Domain: media-consumption-log
  Domain Path: /languages
 */

/*
  The MIT License

  Copyright 2014 Andreas Giemza (andreas@giemza.net).

  Permission is hereby granted, free of charge, to any person obtaining a copy
  of this software and associated documentation files (the "Software"), to deal
  in the Software without restriction, including without limitation the rights
  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
  copies of the Software, and to permit persons to whom the Software is
  furnished to do so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in
  all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
  THE SOFTWARE.
 */

require_once dirname( __FILE__ ) . '/admin/MclAdminMenu.php';
require_once dirname( __FILE__ ) . '/admin/MclComplete.php';
require_once dirname( __FILE__ ) . '/admin/MclQuickPost.php';
require_once dirname( __FILE__ ) . '/admin/MclNumber.php';
require_once dirname( __FILE__ ) . '/admin/MclRebuildData.php';
require_once dirname( __FILE__ ) . '/admin/MclSettings.php';
require_once dirname( __FILE__ ) . '/admin/MclUnit.php';
require_once dirname( __FILE__ ) . '/helpers/MclSettingsHelper.php';
require_once dirname( __FILE__ ) . '/helpers/MclStatisticsHelper.php';
require_once dirname( __FILE__ ) . '/helpers/MclStatusHelper.php';
require_once dirname( __FILE__ ) . '/helpers/MclStringHelper.php';
require_once dirname( __FILE__ ) . '/MclCommaInTags.php';
require_once dirname( __FILE__ ) . '/MclData.php';
require_once dirname( __FILE__ ) . '/MclLanguages.php';
require_once dirname( __FILE__ ) . '/MclStatistics.php';
require_once dirname( __FILE__ ) . '/MclStatus.php';

register_activation_hook( __FILE__, 'mcl_install' );

function mcl_install() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'mcl_complete';

    if ( $wpdb->get_var( "SHOW TABLES LIKE {$table_name}" ) != $table_name ) {
        $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";

        if ( !empty( $wpdb->collate ) ) {
            $charset_collate .= " COLLATE {$wpdb->collate}";
        }

        $sql = "
            CREATE TABLE {$table_name} (
                `tag_id` bigint(20) unsigned NOT NULL,
                `cat_id` bigint(20) unsigned NOT NULL,
                `complete` tinyint(1) NOT NULL,
                PRIMARY KEY (`tag_id`,`cat_id`)
            ) $charset_collate;
        ";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        add_option( 'mcl_db_version', 1 );
    }
}

?>