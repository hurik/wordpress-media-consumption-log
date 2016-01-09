<?php

/*
  Copyright (C) 2014-2016 Andreas Giemza <andreas@giemza.net>

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
        $data->first_post_date = new DateTime( (new DateTime( $posts[0]->post_date ) )->format( 'Y-m-d' ) );

        // Get the number of days since first post
        $date_current = new DateTime( date( 'Y-m-d' ) );
        $number_of_days = $date_current->diff( $data->first_post_date )->format( "%a" ) + 1;
        $data->number_of_days = $number_of_days;

        // Get the categories
        $monitored_categories_serials = MclSettings::get_monitored_categories_serials();
        $monitored_categories_non_serials = MclSettings::get_monitored_categories_non_serials();

        if ( !empty( $monitored_categories_serials ) && !empty( $monitored_categories_non_serials ) ) {
            $data->categories = get_categories( "hide_empty=0&include=" . MclSettings::get_monitored_categories_serials() . "," . MclSettings::get_monitored_categories_non_serials() );
        } else {
            $data->categories = array();
        }

        // Prepare fields for data
        $data->tags = array();
        $status = array();
        $data->total_consumption = array();
        $data->milestones = array();
        $hourly_consumption = array();
        $monthly_consumption = array();
        $daily_consumption = array();

        foreach ( $posts as $post ) {
            self::total_consumption( $data->total_consumption, $post );
            self::get_tags( $data->tags, $post );
            self::status( $status, $post );
            self::milestones( $data->milestones, $post );
            self::graphs( $post, $hourly_consumption, $monthly_consumption, $daily_consumption );
        }

        // Process data
        self::get_tag_links( $data->tags );
        self::sort_status( $status );
        self::process_data( $data, $status );
        self::hourly_consumption_pp( $data->categories, $hourly_consumption );
        self::monthly_consumption_pp( $data, $monthly_consumption );
        self::daily_consumption_pp( $data, $daily_consumption );
        $data->most_consumed = self::most_consumed( $data->tags );

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

    private static function graphs( &$post, &$hourly_consumption, &$monthly_consumption, &$daily_consumption ) {
        if ( !array_key_exists( $post->cat_id, $hourly_consumption ) ) {
            $hourly_consumption[$post->cat_id] = array();
            $monthly_consumption[$post->cat_id] = array();
            $daily_consumption[$post->cat_id] = array();
        }

        $date = new DateTime( $post->post_date );

        // Hourly graph
        if ( array_key_exists( $date->format( "G" ), $hourly_consumption[$post->cat_id] ) ) {
            $hourly_consumption[$post->cat_id][$date->format( "G" )] += $post->post_mcl;
        } else {
            $hourly_consumption[$post->cat_id][$date->format( "G" )] = $post->post_mcl;
        }

        // Monthly graph
        if ( array_key_exists( $date->format( "Y-m" ), $monthly_consumption[$post->cat_id] ) ) {
            $monthly_consumption[$post->cat_id][$date->format( "Y-m" )] += $post->post_mcl;
        } else {
            $monthly_consumption[$post->cat_id][$date->format( "Y-m" )] = $post->post_mcl;
        }

        // Daily graph
        if ( array_key_exists( $date->format( "Y-m-d" ), $daily_consumption[$post->cat_id] ) ) {
            $daily_consumption[$post->cat_id][$date->format( "Y-m-d" )] += $post->post_mcl;
        } else {
            $daily_consumption[$post->cat_id][$date->format( "Y-m-d" )] = $post->post_mcl;
        }
    }

    private static function hourly_consumption_pp( &$categories, &$hourly_consumption ) {
        foreach ( $categories as &$category ) {
            if ( array_key_exists( $category->term_id, $hourly_consumption ) ) {
                for ( $i = 0; $i < 24; $i++ ) {
                    if ( !array_key_exists( $i, $hourly_consumption[$category->term_id] ) ) {
                        $hourly_consumption[$category->term_id][$i] = 0;
                    }
                }

                ksort( $hourly_consumption[$category->term_id] );

                $category->mcl_hourly_data = $hourly_consumption[$category->term_id];
            }
        }
    }

    private static function monthly_consumption_pp( &$data, &$monthly_consumption ) {
        // Get all months needed months
        $dates_monthly = array();

        if ( MclSettings::get_statistics_monthly_count() != 0 ) {
            for ( $i = 0; $i < MclSettings::get_statistics_monthly_count(); $i++ ) {
                $month = date( 'Y-m', strtotime( "-" . $i . " month", strtotime( date( 'Y-m' ) ) ) );
                array_push( $dates_monthly, $month );
            }
        } else {
            $i = 0;

            while ( true ) {
                $month = date( 'Y-m', strtotime( "-" . $i . " month", strtotime( date( 'Y-m' ) ) ) );
                array_push( $dates_monthly, $month );

                $i++;

                if ( $month == $data->first_post_date->format( 'Y-m' ) ) {
                    break;
                }
            }
        }

        foreach ( $monthly_consumption as &$monthly_consumption_one_cat ) {
            // Add missing months
            foreach ( $dates_monthly as &$month ) {
                if ( !array_key_exists( $month, $monthly_consumption_one_cat ) ) {
                    $monthly_consumption_one_cat[$month] = 0;
                }
            }

            // Sort array in reverse order
            krsort( $monthly_consumption_one_cat );
        }

        foreach ( $data->categories as &$category ) {
            if ( MclSettings::get_statistics_monthly_count() != 0 ) {
                $category->mcl_monthly_data = array_slice( $monthly_consumption[$category->term_id], 0, MclSettings::get_statistics_monthly_count() );
            } else {
                $category->mcl_monthly_data = $monthly_consumption[$category->term_id];
            }
        }
    }

    private static function daily_consumption_pp( &$data, &$daily_consumption ) {
        // Get all months needed months
        $dates_daily = array();

        $date_current = new DateTime( date( 'Y-m-d' ) );
        $number_of_days = $date_current->diff( $data->first_post_date )->format( "%a" ) + 1;

        for ( $i = 0; $i < $number_of_days; $i++ ) {
            $day = date( 'Y-m-d', strtotime( "-" . $i . " day", strtotime( date( 'Y-m-d' ) ) ) );
            array_push( $dates_daily, $day );
        }

        foreach ( $daily_consumption as &$daily_consumption_one_cat ) {
            // Add missing months
            foreach ( $dates_daily as &$day ) {
                if ( !array_key_exists( $day, $daily_consumption_one_cat ) ) {
                    $daily_consumption_one_cat[$day] = 0;
                }
            }

            // Sort array
            ksort( $daily_consumption_one_cat );
        }

        // Average consumption development
        $data->average_consumption_development = self::get_average_consumption_development( $data, $daily_consumption );

        // Daily graph
        foreach ( $data->categories as &$category ) {
            if ( MclSettings::get_statistics_daily_count() != 0 ) {
                $category->mcl_daily_data = array_slice( $daily_consumption[$category->term_id], - MclSettings::get_statistics_daily_count(), MclSettings::get_statistics_daily_count() );
                krsort( $category->mcl_daily_data );
            } else {
                $category->mcl_daily_data = $daily_consumption[$category->term_id];
                krsort( $category->mcl_daily_data );
            }
        }
    }

    private static function total_consumption( &$total_consumption, $post ) {
        if ( array_key_exists( $post->cat_id, $total_consumption ) ) {
            $total_consumption[$post->cat_id] += $post->post_mcl;
        } else {
            $total_consumption[$post->cat_id] = $post->post_mcl;
        }
    }

    private static function get_tags( &$tags, $post ) {
        if ( array_key_exists( $post->tag_id, $tags ) ) {
            $post->mcl_total = $tags[$post->tag_id]->mcl_total + $post->post_mcl;
            $post->cats = $tags[$post->tag_id]->cats;

            if ( !in_array( $post->cat_id, $post->cats ) ) {
                $post->cats[] = $post->cat_id;
            }
        } else {
            $post->mcl_total = $post->post_mcl;
            $post->cats = array( $post->cat_id );
        }

        $tags[$post->tag_id] = $post;
    }

    private static function status( &$status, $post ) {
        if ( !array_key_exists( $post->cat_id, $status ) ) {
            $status[$post->cat_id] = array();
        }

        if ( !array_key_exists( $post->tag_in_cat_status, $status[$post->cat_id] ) ) {
            $status[$post->cat_id][$post->tag_in_cat_status] = array();
        }

        $firstletter = '#';
        if ( preg_match( '/^[a-z]/i', trim( $post->tag_name[0] ) ) ) {
            $firstletter = strtoupper( trim( $post->tag_name[0] ) );
        }

        if ( !array_key_exists( $firstletter, $status[$post->cat_id][$post->tag_in_cat_status] ) ) {
            $status[$post->cat_id][$post->tag_in_cat_status][$firstletter] = array();
        }

        if ( !array_key_exists( $post->tag_id, $status[$post->cat_id][$post->tag_in_cat_status][$firstletter] ) ) {
            $status[$post->cat_id][$post->tag_in_cat_status][$firstletter][$post->tag_id] = $post;
        } else {
            $old_number_array = explode( " ", trim( $status[$post->cat_id][$post->tag_in_cat_status][$firstletter][$post->tag_id]->post_title ) );
            $new_number_array = explode( " ", trim( $post->post_title ) );

            $old_number = end( $old_number_array );
            $new_number = end( $new_number_array );

            if ( !is_numeric( $old_number ) && (preg_match( '/[SE]/', $old_number ) || preg_match( '/[VC]/', $old_number ) || preg_match( '/[CP]/', $old_number )) ) {
                $old_number = preg_replace( "/[SEVCP]/", "", $old_number );
            }

            if ( !is_numeric( $new_number ) && (preg_match( '/[SE]/', $new_number ) || preg_match( '/[VC]/', $new_number ) || preg_match( '/[CP]/', $new_number )) ) {
                $new_number = preg_replace( "/[SEVCP]/", "", $new_number );
            }

            if ( !is_numeric( $new_number ) || !is_numeric( $old_number ) || $new_number >= $old_number ) {
                $status[$post->cat_id][$post->tag_in_cat_status][$firstletter][$post->tag_id] = $post;
            }
        }
    }

    private static function get_tag_links( &$tags ) {
        // Get link of the tags
        foreach ( $tags as $tag ) {
            $tag->tag_name = MclCommaInTags::replace( $tag->tag_name );
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

    private static function sort_status( &$categories ) {
        foreach ( $categories as &$category ) {
            foreach ( $category as &$stati ) {
                ksort( $stati, SORT_NATURAL );
            }
        }

        foreach ( $categories as &$category ) {
            foreach ( $category as &$stati ) {
                foreach ( $stati as &$status ) {
                    usort( $status, function($a, $b) {
                        return strcmp( $a->tag_name, $b->tag_name );
                    } );
                }
            }
        }
    }

    private static function process_data( &$data, $status ) {
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

        $data->tags_count_ongoing = 0;
        $data->tags_count_complete = 0;
        $data->tags_count_abandoned = 0;
        $data->tags_count_total = 0;

        $data->cat_serial_ongoing = false;
        $data->cat_serial_complete = false;
        $data->cat_serial_abandoned = false;
        $data->cat_non_serial = false;

        foreach ( $data->categories as &$category ) {
            if ( array_key_exists( MclSerialStatus::RUNNING, $status[$category->term_id] ) ) {
                $category->mcl_tags_ongoing = $status[$category->term_id][MclSerialStatus::RUNNING];

                foreach ( $category->mcl_tags_ongoing as $letter ) {
                    foreach ( $letter as $tag ) {
                        $tag->post_link = get_permalink( $tag->post_id );
                    }
                }
            }
            if ( array_key_exists( MclSerialStatus::COMPLETE, $status[$category->term_id] ) ) {
                $category->mcl_tags_complete = $status[$category->term_id][MclSerialStatus::COMPLETE];
            }
            if ( array_key_exists( MclSerialStatus::ABANDONED, $status[$category->term_id] ) ) {
                $category->mcl_tags_abandoned = $status[$category->term_id][MclSerialStatus::ABANDONED];
            }

            $category->mcl_tags_count_ongoing = self::count_tags_in_status( $category->mcl_tags_ongoing );
            $category->mcl_tags_count_complete = self::count_tags_in_status( $category->mcl_tags_complete );
            $category->mcl_tags_count_abandoned = self::count_tags_in_status( $category->mcl_tags_abandoned );
            $category->mcl_tags_count = $category->mcl_tags_count_ongoing + $category->mcl_tags_count_complete + $category->mcl_tags_count_abandoned;

            $data->tags_count_ongoing += $category->mcl_tags_count_ongoing;
            $data->tags_count_complete += $category->mcl_tags_count_complete;
            $data->tags_count_abandoned += $category->mcl_tags_count_abandoned;
            $data->tags_count_total += $category->mcl_tags_count;

            if ( MclHelpers::is_monitored_serial_category( $category->term_id ) && $category->mcl_tags_count_ongoing > 0 ) {
                $data->cat_serial_ongoing = true;
            }

            if ( MclHelpers::is_monitored_serial_category( $category->term_id ) && $category->mcl_tags_count_complete > 0 ) {
                $data->cat_serial_complete = true;
            }

            if ( MclHelpers::is_monitored_serial_category( $category->term_id ) && $category->mcl_tags_count_abandoned > 0 ) {
                $data->cat_serial_abandoned = true;
            }

            if ( MclHelpers::is_monitored_non_serial_category( $category->term_id ) && $category->mcl_tags_count_ongoing > 0 ) {
                $data->cat_non_serial = true;
            }
        }
    }

    private static function count_tags_in_status( $letters ) {
        $i = 0;

        if ( is_array( $letters ) ) {
            foreach ( $letters as $letter ) {
                foreach ( $letter as $tag ) {
                    $i++;
                }
            }
        }

        return $i;
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

    private static function get_average_consumption_development( &$data, &$daily_consumption ) {
        global $wpdb;

        // Data array
        $acd = array();

        // Legend
        $legend_array = array();
        $legend_array[] = "Date";
        foreach ( $data->categories as $category ) {
            $legend_array[] = $category->name;
        }
        $legend_array[] = __( 'Total', 'media-consumption-log' );
        $acd[] = $legend_array;

        // Add dates
        foreach ( reset( $daily_consumption ) as $key => $value ) {
            $dates_array = array();
            $date = DateTime::createFromFormat( 'Y-m-d', $key );
            $dates_array[] = $date->format( MclSettings::get_statistics_daily_date_format() );
            $acd[] = $dates_array;
        }

        // Sum array
        $sum = array();

        for ( $i = 0; $i < $data->number_of_days; $i++ ) {
            $sum[] = 0;
        }

        foreach ( $data->categories as $category ) {
            $cat_sum = 0;

            $i = 0;

            foreach ( $daily_consumption[$category->term_id] as &$day ) {
                $cat_sum += $day;
                $sum[$i] += $cat_sum;
                $acd[$i + 1][] = number_format( $cat_sum / ($i + 1), 2 );

                $i++;
            }
        }

        for ( $i = 0; $i < count( $sum ); $i++ ) {
            $acd[$i + 1][] = number_format( $sum[$i] / ($i + 1), 2 );
        }

        return $acd;
    }

}
