<?php

/*
  Copyright (C) 2014-2015 Andreas Giemza <andreas@giemza.net>

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

class MclData {

    const option_name = 'mcl_data';

    public static function get_data() {
        $data = get_option( self::option_name );

        if ( $data === false || $data->plugin_version != PLUGIN_VERSION ) {
            return self::update_data();
        }

        return $data;
    }

    public static function get_data_up_to_date() {
        $data = self::get_data();

        // Set the default timezone
        date_default_timezone_set( get_option( 'timezone_string' ) );
        // Check if mcl_data is up to date
        $date_current = new DateTime( date( 'Y-m-d' ) );
        $number_of_days = $date_current->diff( $data->first_post_date )->format( "%a" ) + 1;

        if ( $data->number_of_days != $number_of_days ) {
            return self::update_data();
        }

        return $data;
    }

    public static function update_data() {
        $data = self::build_data();

        if ( get_option( self::option_name ) !== false ) {
            update_option( self::option_name, $data );
        } else {
            add_option( self::option_name, $data, null, 'no' );
        }

        return $data;
    }

    public static function build_data() {
        // Set the default timezone
        date_default_timezone_set( get_option( 'timezone_string' ) );

        $data = new stdClass;

        $data->plugin_version = PLUGIN_VERSION;

        // Get all posts with category, tag, mcl_number and tag status
        $posts = self::get_posts();

        // Get first post date (with mcl number)
        $first_post_date = new DateTime( (new DateTime( $posts[0]->post_date ) )->format( 'Y-m-d' ) );
        $data->first_post_date = $first_post_date;

        // Get the categories
        $monitored_categories_serials = MclSettings::get_monitored_categories_serials();
        $monitored_categories_non_serials = MclSettings::get_monitored_categories_non_serials();

        if ( !empty( $monitored_categories_serials ) && !empty( $monitored_categories_non_serials ) ) {
            $categories = get_categories( "hide_empty=0&include=" . MclSettings::get_monitored_categories_serials() . "," . MclSettings::get_monitored_categories_non_serials() );
        } else {
            $categories = array();
        }

        // Prepare fields for data
        $data->tags = array();
        $data->total_consumption = array();
        $data->milestones = array();

        foreach ( $posts as $post ) {
            self::total_consumption( $data->total_consumption, $post );
            self::get_tags( $data->tags, $post );
            self::milestones( $data->milestones, $post );
        }

        // Process data
        self::get_tag_links( $data->tags );
        $data->most_consumed = self::most_consumed( $data->tags );

        // Get first date an month for the graphs
        if ( MclSettings::get_statistics_daily_count() != 0 ) {
            $first_date = date( 'Y-m-d', strtotime( "-" . (MclSettings::get_statistics_daily_count() - 1) . " day", strtotime( date( 'Y-m-d' ) ) ) );
        } else {
            $first_date = $data->first_post_date->format( 'Y-m-d' );
        }


        if ( MclSettings::get_statistics_monthly_count() != 0 ) {
            $first_month = date( 'Y-m', strtotime( "-" . (MclSettings::get_statistics_monthly_count() - 1) . " month", strtotime( date( 'Y-m' ) ) ) );
        } else {
            $first_month = $data->first_post_date->format( 'Y-m' );
        }

        // Get the number of days since first post
        $date_current = new DateTime( date( 'Y-m-d' ) );
        $number_of_days = $date_current->diff( $first_post_date )->format( "%a" ) + 1;
        $data->number_of_days = $number_of_days;

        // Total consumption of category
        $consumption_total = 0;
        $consumption_average = 0;

        $tags_count_ongoing = 0;
        $tags_count_complete = 0;
        $tags_count_abandoned = 0;

        $cat_serial_ongoing = false;
        $cat_serial_complete = false;
        $cat_serial_abandoned = false;
        $cat_non_serial = false;

        $data->categories = array();

        foreach ( $categories as $wp_category ) {
            $category = new stdClass;
            $category->term_id = $wp_category->term_id;
            $category->name = $wp_category->name;
            $category->slug = $wp_category->slug;

            self::get_tags_of_category( $category );

            $tags_count_ongoing += $category->mcl_tags_count_ongoing;
            $tags_count_complete += $category->mcl_tags_count_complete;
            $tags_count_abandoned += $category->mcl_tags_count_abandoned;

            // Graph data
            $category->mcl_daily_data = self::get_mcl_number_count_of_category_sorted_by_day( $category->term_id, $first_date );
            $category->mcl_monthly_data = self::get_mcl_number_count_of_category_sorted_by_month( $category->term_id, $first_month );
            $category->mcl_hourly_data = self::get_mcl_number_count_of_category_sorted_by_hour( $category->term_id );

            $data->categories[] = $category;

            if ( MclHelpers::is_monitored_serial_category( $category->term_id ) && $category->mcl_tags_count_ongoing > 0 ) {
                $cat_serial_ongoing = true;
            }

            if ( MclHelpers::is_monitored_serial_category( $category->term_id ) && $category->mcl_tags_count_complete > 0 ) {
                $cat_serial_complete = true;
            }

            if ( MclHelpers::is_monitored_serial_category( $category->term_id ) && $category->mcl_tags_count_abandoned > 0 ) {
                $cat_serial_abandoned = true;
            }

            if ( MclHelpers::is_monitored_non_serial_category( $category->term_id ) && $category->mcl_tags_count_ongoing > 0 ) {
                $cat_non_serial = true;
            }
        }

        $data->consumption_total = $consumption_total;
        $data->consumption_average = $consumption_average;

        $data->tags_count_ongoing = $tags_count_ongoing;
        $data->tags_count_complete = $tags_count_complete;
        $data->tags_count_abandoned = $tags_count_abandoned;
        $data->tags_count_total = $tags_count_ongoing + $tags_count_complete + $tags_count_abandoned;

        $data->cat_serial_ongoing = $cat_serial_ongoing;
        $data->cat_serial_complete = $cat_serial_complete;
        $data->cat_serial_abandoned = $cat_serial_abandoned;
        $data->cat_non_serial = $cat_non_serial;

        $data->average_consumption_development = self::get_average_consumption_development( $categories, $first_post_date->format( 'Y-m-d' ), $number_of_days );

        return $data;
    }

    private static function get_posts() {
        global $wpdb;

        $posts = $wpdb->get_results( "
            SELECT posts_with_data.post_id,
                   posts_with_data.post_date,
                   posts_with_data.post_title,
                   posts_with_data.post_mcl,
                   posts_with_data.cat_id,
                   posts_with_data.cat_name,
                   posts_with_data.tag_id,
                   posts_with_data.tag_name,
                   IFNULL(mcl_status.status, 0) AS tag_in_cat_status
            FROM
              (SELECT posts.ID AS post_id,
                      posts.post_date,
                      posts.post_title,
                      postmeta.meta_value AS post_mcl,
                      GROUP_CONCAT(IF(term_taxonomy.taxonomy = 'category', terms.term_id, NULL)) AS cat_id,
                      GROUP_CONCAT(IF(term_taxonomy.taxonomy = 'category', terms.name, NULL)) AS cat_name,
                      GROUP_CONCAT(IF(term_taxonomy.taxonomy = 'post_tag', terms.term_id, NULL)) AS tag_id,
                      GROUP_CONCAT(IF(term_taxonomy.taxonomy = 'post_tag', terms.name, NULL)) AS tag_name
               FROM {$wpdb->prefix}posts posts
               LEFT OUTER JOIN {$wpdb->prefix}term_relationships term_relationships ON term_relationships.object_id = posts.ID
               LEFT OUTER JOIN {$wpdb->prefix}terms terms ON terms.term_id = term_relationships.term_taxonomy_id
               LEFT OUTER JOIN {$wpdb->prefix}term_taxonomy term_taxonomy ON term_taxonomy.term_taxonomy_id = term_relationships.term_taxonomy_id
               LEFT OUTER JOIN {$wpdb->prefix}postmeta postmeta ON postmeta.post_id = posts.ID
               WHERE posts.post_status = 'publish'
                 AND posts.post_type = 'post'
                 AND postmeta.meta_key = 'mcl_number'
               GROUP BY posts.ID
               ORDER BY posts.post_date ASC) AS posts_with_data
            LEFT JOIN {$wpdb->prefix}mcl_status AS mcl_status ON posts_with_data.tag_id = mcl_status.tag_id
                                                             AND posts_with_data.cat_id = mcl_status.cat_id
        " );

        return $posts;
    }

    private static function total_consumption( &$total_consumption, $post ) {
        if ( array_key_exists( $post->cat_id, $total_consumption ) ) {
            $total_consumption[$post->cat_id] += $post->post_mcl;
        } else {
            $total_consumption[$post->cat_id] = $post->post_mcl;
        }
    }

    private static function get_tags( &$data, $post ) {
        if ( array_key_exists( $post->tag_id, $data ) ) {
            $post->mcl_total = $data[$post->tag_id]->mcl_total + $post->post_mcl;
            $post->cats = $data[$post->tag_id]->cats;

            if ( !in_array( $post->cat_id, $post->cats ) ) {
                $post->cats[] = $post->cat_id;
            }
        } else {
            $post->mcl_total = $post->post_mcl;
            $post->cats = array( $post->cat_id );
        }

        $data[$post->tag_id] = $post;
    }

    private static function get_tag_links( &$tags ) {
        // Get link of the tags
        foreach ( $tags as $tag ) {
            $tag->tag_link = get_tag_link( $tag->tag_id );
        }
    }

    private static function most_consumed( $tags ) {
        // Sort
        usort( $tags, function($a, $b) {
            if ( $a->mcl_total == $b->mcl_total ) {
                return 0;
            }
            return $a->mcl_total < $b->mcl_total ? 1 : -1;
        } );

        // Get only the needed data
        $most_consumed = array_slice( $tags, 0, MclSettings::get_statistics_most_consumed_count() );

        return $most_consumed;
    }

    private static function milestones( &$data, $post ) {
        static $current_mcl_count = 0;
        static $milestone = 0;

        $current_mcl_count += $post->post_mcl;

        if ( $milestone <= $current_mcl_count ) {
            $post->milestone = $milestone;
            $post->post_link = get_permalink( $post->post_id );
            $milestone += 2500;

            $data[] = $post;
        }
    }

    private static function get_tags_of_category( $category ) {
        global $wpdb;

        $tags = $wpdb->get_results( "
            Select
                temp.tag_id,
                temp.taxonomy,
                temp.name,
                temp.post_id,
                temp.post_date,
                temp.post_title,
                IFNULL(mcl.status, 0) AS status
            FROM
		(
                    SELECT
                        terms2.term_id AS tag_id,
                        t2.taxonomy AS taxonomy,
                        terms2.name AS name,
                        t1.term_id AS cat_id,
                        p2.ID AS post_id,
                        p2.post_date,
                        p2.post_title
                    FROM
                        {$wpdb->prefix}posts AS p1
                        LEFT JOIN {$wpdb->prefix}term_relationships AS r1 ON p1.ID = r1.object_ID
                        LEFT JOIN {$wpdb->prefix}term_taxonomy AS t1 ON r1.term_taxonomy_id = t1.term_taxonomy_id
                        LEFT JOIN {$wpdb->prefix}terms AS terms1 ON t1.term_id = terms1.term_id,
                        {$wpdb->prefix}posts AS p2
                        LEFT JOIN {$wpdb->prefix}term_relationships AS r2 ON p2.ID = r2.object_ID
                        LEFT JOIN {$wpdb->prefix}term_taxonomy AS t2 ON r2.term_taxonomy_id = t2.term_taxonomy_id
                        LEFT JOIN {$wpdb->prefix}terms AS terms2 ON t2.term_id = terms2.term_id
                    WHERE
                        t1.taxonomy = 'category'
                        AND p1.post_status = 'publish'
                        AND terms1.term_id = '{$category->term_id}'
                        AND t2.taxonomy = 'post_tag'
                        AND p2.post_status = 'publish'
                        AND p1.ID = p2.ID
                        AND p2.post_date = (
                            SELECT
                                MAX(dp1.post_date)
                            FROM
                                {$wpdb->prefix}posts AS dp1
                                LEFT JOIN {$wpdb->prefix}term_relationships AS dr1 ON dp1.ID = dr1.object_ID
                                LEFT JOIN {$wpdb->prefix}term_taxonomy AS dt1 ON dr1.term_taxonomy_id = dt1.term_taxonomy_id,
                                {$wpdb->prefix}posts AS dp2
                                LEFT JOIN {$wpdb->prefix}term_relationships AS dr2 ON dp2.ID = dr2.object_ID
                                LEFT JOIN {$wpdb->prefix}term_taxonomy AS dt2 ON dr2.term_taxonomy_id = dt2.term_taxonomy_id
                            WHERE
                                dp1.ID = dp2.ID
                                AND dt1.taxonomy = 'category'
                                AND dp1.post_status = 'publish'
                                AND dt2.taxonomy = 'post_tag'
                                AND dp2.post_status = 'publish'
                                AND dt1.term_id = t1.term_id
                                AND dt2.term_id = t2.term_id)
                    ORDER BY name
                ) AS temp
            LEFT JOIN {$wpdb->prefix}mcl_status AS mcl ON temp.tag_id = mcl.tag_id AND temp.cat_id = mcl.cat_id
	" );

        $tags_count_ongoing = 0;
        $tags_count_complete = 0;
        $tags_count_abandoned = 0;
        $tags_ongoing = array();
        $tags_complete = array();
        $tags_abandoned = array();

        foreach ( $tags as $tag ) {
            // Comma in tags
            $tag = MclCommaInTags::comma_tag_filter( $tag );
            // Get tag link
            $tag->tag_link = get_tag_link( $tag->tag_id );

            // Get last post data, only of tag which are running
            if ( $tag->status == MclSerialStatus::RUNNING ) {
                $tag->post_link = get_permalink( $tag->post_id );
            }

            if ( $tag->status == MclSerialStatus::RUNNING ) {
                $tags_count_ongoing++;

                // Tags which start with a number get their own group #
                if ( preg_match( '/^[a-z]/i', trim( $tag->name[0] ) ) ) {
                    $tags_ongoing[strtoupper( $tag->name[0] )][] = $tag;
                } else {
                    $tags_ongoing['#'][] = $tag;
                }
            } else if ( $tag->status == MclSerialStatus::COMPLETE ) {
                $tags_count_complete++;

                // Tags which start with a number get their own group #
                if ( preg_match( '/^[a-z]/i', trim( $tag->name[0] ) ) ) {
                    $tags_complete[strtoupper( $tag->name[0] )][] = $tag;
                } else {
                    $tags_complete['#'][] = $tag;
                }
            } else {
                $tags_count_abandoned++;

                // Tags which start with a number get their own group #
                if ( preg_match( '/^[a-z]/i', trim( $tag->name[0] ) ) ) {
                    $tags_abandoned[strtoupper( $tag->name[0] )][] = $tag;
                } else {
                    $tags_abandoned['#'][] = $tag;
                }
            }
        }

        // Sort tag arrays
        $category->mcl_tags_count_ongoing = $tags_count_ongoing;
        $category->mcl_tags_count_complete = $tags_count_complete;
        $category->mcl_tags_count_abandoned = $tags_count_abandoned;
        $category->mcl_tags_count = $tags_count_ongoing + $tags_count_complete + $tags_count_abandoned;

        $category->mcl_tags_ongoing = $tags_ongoing;
        $category->mcl_tags_complete = $tags_complete;
        $category->mcl_tags_abandoned = $tags_abandoned;

        return $category;
    }

    private static function get_mcl_number_count_of_category_sorted_by_day( $category_id, $first_date ) {
        global $wpdb;

        $stats = $wpdb->get_results( "
            SELECT DATE_FORMAT(post_date, '%Y-%m-%d') AS date, SUM(meta_value) AS number
            FROM {$wpdb->prefix}posts p
            LEFT OUTER JOIN {$wpdb->prefix}term_relationships r ON r.object_id = p.ID
            LEFT OUTER JOIN {$wpdb->prefix}postmeta m ON m.post_id = p.ID
            WHERE post_status = 'publish'
              AND post_type = 'post'
              AND meta_key = 'mcl_number'
              AND term_taxonomy_id = '{$category_id}'
              AND post_date >= '{$first_date}'
            GROUP BY DATE_FORMAT(post_date, '%Y-%m-%d')
            ORDER BY date DESC
	" );

        return $stats;
    }

    private static function get_mcl_number_count_of_category_sorted_by_month( $category_id, $first_month ) {
        global $wpdb;

        $stats = $wpdb->get_results( "
            SELECT DATE_FORMAT(post_date, '%Y-%m') AS date, SUM(meta_value) AS number
            FROM {$wpdb->prefix}posts p
            LEFT OUTER JOIN {$wpdb->prefix}term_relationships r ON r.object_id = p.ID
            LEFT OUTER JOIN {$wpdb->prefix}postmeta m ON m.post_id = p.ID
            WHERE post_status = 'publish'
              AND post_type = 'post'
              AND meta_key = 'mcl_number'
              AND term_taxonomy_id = '{$category_id}'
              AND post_date >= '{$first_month}'
            GROUP BY DATE_FORMAT(post_date, '%Y-%m')
            ORDER BY date DESC
	" );

        return $stats;
    }

    private static function get_mcl_number_count_of_category_sorted_by_hour( $category_id ) {
        global $wpdb;

        $stats = $wpdb->get_results( "
            SELECT a.hour AS hour,
                   IFNULL(b.number, 0) number
            FROM
              (SELECT 0 AS hour
               UNION ALL SELECT 1
               UNION ALL SELECT 2
               UNION ALL SELECT 3
               UNION ALL SELECT 4
               UNION ALL SELECT 5
               UNION ALL SELECT 6
               UNION ALL SELECT 7
               UNION ALL SELECT 8
               UNION ALL SELECT 9
               UNION ALL SELECT 10
               UNION ALL SELECT 11
               UNION ALL SELECT 12
               UNION ALL SELECT 13
               UNION ALL SELECT 14
               UNION ALL SELECT 15
               UNION ALL SELECT 16
               UNION ALL SELECT 17
               UNION ALL SELECT 18
               UNION ALL SELECT 19
               UNION ALL SELECT 20
               UNION ALL SELECT 21
               UNION ALL SELECT 22
               UNION ALL SELECT 23
              ) a
            LEFT JOIN
              (SELECT HOUR(post_date) as hour, SUM(meta_value) AS number
               FROM {$wpdb->prefix}posts p
               LEFT OUTER JOIN {$wpdb->prefix}term_relationships r ON r.object_id = p.ID
               LEFT OUTER JOIN {$wpdb->prefix}postmeta m ON m.post_id = p.ID
               WHERE (post_status = 'publish'
                 AND post_type = 'post'
                 AND meta_key = 'mcl_number')
                 AND term_taxonomy_id = '{$category_id}'
               GROUP BY HOUR(post_date)
              ) b ON b.hour = a.hour
            ORDER BY hour
	" );

        return $stats;
    }

    private static function get_average_consumption_development( $categories, $first_date, $number_of_days ) {
        global $wpdb;

        // Dates array
        $all_dates = array();
        for ( $i = 0; $i < $number_of_days; $i++ ) {
            $day = date( 'Y-m-d', strtotime( "-" . $i . " day", strtotime( date( 'Y-m-d' ) ) ) );
            array_push( $all_dates, $day );
        }
        $all_dates = array_reverse( $all_dates );

        // Data array
        $data = array();

        // Legend
        $legend_array = array();
        $legend_array[] = "Date";
        foreach ( $categories as $wp_category ) {
            $legend_array[] = $wp_category->name;
        }
        $legend_array[] = __( 'Total', 'media-consumption-log' );
        $data[] = $legend_array;

        // Add dates
        for ( $i = 0; $i < count( $all_dates ); $i++ ) {
            $dates_array = array();
            $date = DateTime::createFromFormat( 'Y-m-d', $all_dates[$i] );
            $dates_array[] = $date->format( MclSettings::get_statistics_daily_date_format() );
            $data[] = $dates_array;
        }

        // Sum array
        $sum = array();

        for ( $i = 0; $i < count( $all_dates ); $i++ ) {
            $sum[] = 0;
        }

        foreach ( $categories as $wp_category ) {
            $db_data = $wpdb->get_results( "
                SELECT DATE_FORMAT(post_date, '%Y-%m-%d') AS date, SUM(meta_value) AS number
                FROM {$wpdb->prefix}posts p
                LEFT OUTER JOIN {$wpdb->prefix}term_relationships r ON r.object_id = p.ID
                LEFT OUTER JOIN {$wpdb->prefix}postmeta m ON m.post_id = p.ID
                WHERE post_status = 'publish'
                  AND post_type = 'post'
                  AND meta_key = 'mcl_number'
                  AND post_date >= '{$first_date}'
                  AND term_taxonomy_id = '{$wp_category->term_id}'
                GROUP BY DATE_FORMAT(post_date, '%Y-%m-%d')
                ORDER BY date DESC
            " );

            $cat_sum = 0;

            for ( $i = 0; $i < count( $all_dates ); $i++ ) {
                $value = null;

                foreach ( $db_data as $db_day ) {
                    if ( $db_day->date == $all_dates[$i] ) {
                        $value = $db_day;
                        break;
                    }
                }

                if ( $value == null ) {
                    $value = new stdClass();
                    $value->date = $all_dates[$i];
                    $value->number = 0;
                }

                $cat_sum += $value->number;

                $sum[$i] += $cat_sum;

                $data[$i + 1][] = number_format( $cat_sum / ($i + 1), 2 );
            }
        }

        for ( $i = 0; $i < count( $sum ); $i++ ) {
            $data[$i + 1][] = number_format( $sum[$i] / ($i + 1), 2 );
        }

        return $data;
    }

}
