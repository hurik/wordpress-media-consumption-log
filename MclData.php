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

        // Check if plugin was updated, when yes update data
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
        if ( $data->creation_date != date( 'Y-m-d' ) ) {
            return self::update_data();
        }

        return $data;
    }

    public static function update_data() {
        $data = self::build_data();

        // Check if option already exists
        if ( get_option( self::option_name ) !== false ) {
            update_option( self::option_name, $data );
        } else {
            add_option( self::option_name, $data, null, 'no' );
        }

        return $data;
    }

    public static function build_data() {
        global $wpdb;

        // Set the default timezone
        date_default_timezone_set( get_option( 'timezone_string' ) );

        // Create the data field
        $data = new stdClass;

        // Save the plugin version
        $data->plugin_version = PLUGIN_VERSION;

        // Save the creation date of data for get_data_up_to_date()
        $data->creation_date = date( 'Y-m-d' );

        // Get all posts with category, tag, mcl_number and tag status
        $posts = $wpdb->get_results( "
            SELECT posts_with_data.*,
                   IFNULL(mcl_status.status, 0) AS tag_in_cat_status
            FROM
              (SELECT posts.*,
                      postmeta.meta_value AS post_mcl,
                      GROUP_CONCAT(IF(term_taxonomy.taxonomy = 'category', terms.term_id, NULL)) AS cat_id,
                      GROUP_CONCAT(IF(term_taxonomy.taxonomy = 'category', terms.name, NULL)) AS cat_name,
                      GROUP_CONCAT(IF(term_taxonomy.taxonomy = 'post_tag', terms.term_id, NULL)) AS tag_term_id,
                      GROUP_CONCAT(IF(term_taxonomy.taxonomy = 'post_tag', terms.name, NULL)) AS tag_name,
                      GROUP_CONCAT(IF(term_taxonomy.taxonomy = 'post_tag', terms.slug, NULL)) AS tag_slug,
                      GROUP_CONCAT(IF(term_taxonomy.taxonomy = 'post_tag', terms.term_group, NULL)) AS tag_term_group,
                      GROUP_CONCAT(IF(term_taxonomy.taxonomy = 'post_tag', term_taxonomy.term_taxonomy_id, NULL)) AS tag_term_taxonomy_id,
                      GROUP_CONCAT(IF(term_taxonomy.taxonomy = 'post_tag', term_taxonomy.taxonomy, NULL)) AS tag_taxonomy,
                      GROUP_CONCAT(IF(term_taxonomy.taxonomy = 'post_tag', term_taxonomy.description, NULL)) AS tag_description,
                      GROUP_CONCAT(IF(term_taxonomy.taxonomy = 'post_tag', term_taxonomy.parent, NULL)) AS tag_parent,
                      GROUP_CONCAT(IF(term_taxonomy.taxonomy = 'post_tag', term_taxonomy.count, NULL)) AS tag_count
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
            LEFT JOIN {$wpdb->prefix}mcl_status AS mcl_status ON posts_with_data.tag_term_id = mcl_status.tag_id
                                                             AND posts_with_data.cat_id = mcl_status.cat_id
        " );

        // Get first post date (with mcl number)
        $data->first_post_date = new DateTime( (new DateTime( $posts[0]->post_date ) )->format( 'Y-m-d' ) );

        // Get the number of days since first post
        $data->number_of_days = (new DateTime( date( 'Y-m-d' ) ) )->diff( $data->first_post_date )->format( "%a" ) + 1;

        // Get the categories
        $data->categories = MclSettings::get_all_monitored_categories();

        // mcl_number count of categories
        $data->total_consumption = array();

        // List of all tags, with mcl_number count (mcl_total) and categories in which they are used (cats)
        $data->tags = array();

        $data->milestones = array();
        $status = array();
        $hourly_consumption = array();
        $monthly_consumption = array();
        $daily_consumption = array();

        // Variables for milestones
        $current_mcl_count = 0;
        $milestone = 0;
        $milestone_year_int = 1;
        $milestone_year = new DateTime( $posts[0]->post_date );
        $milestone_year->modify( "+1 years" );

        foreach ( $posts as &$post ) {
            // Count mcl_number of categories
            if ( array_key_exists( $post->cat_id, $data->total_consumption ) ) {
                $data->total_consumption[$post->cat_id] += $post->post_mcl;
            } else {
                $data->total_consumption[$post->cat_id] = $post->post_mcl;
            }

            // Get tags, mcl_number of tag and cats in which they are used
            if ( array_key_exists( $post->tag_term_id, $data->tags ) ) {
                $post->mcl_total = $data->tags[$post->tag_term_id]->mcl_total + $post->post_mcl;
                $post->cats = $data->tags[$post->tag_term_id]->cats;

                if ( !in_array( $post->cat_id, $post->cats ) ) {
                    $post->cats[] = $post->cat_id;
                }
            } else {
                $post->mcl_total = $post->post_mcl;
                $post->cats = array( $post->cat_id );
            }

            $data->tags[$post->tag_term_id] = $post;

            // Sort tags by category, status and letter
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

            if ( !array_key_exists( $post->tag_term_id, $status[$post->cat_id][$post->tag_in_cat_status][$firstletter] ) ) {
                $status[$post->cat_id][$post->tag_in_cat_status][$firstletter][$post->tag_term_id] = $post;
            } else {
                $old_number_array = explode( " ", trim( $status[$post->cat_id][$post->tag_in_cat_status][$firstletter][$post->tag_term_id]->post_title ) );
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
                    $status[$post->cat_id][$post->tag_in_cat_status][$firstletter][$post->tag_term_id] = $post;
                }
            }

            // Get milestones
            $current_mcl_count += $post->post_mcl;
            $post_datetime = new DateTime( $post->post_date );

            if ( $milestone <= $current_mcl_count ) {
                $post->milestone = $milestone;
                $post->post_link = get_permalink( $post );
                $milestone += 2500;

                $data->milestones[] = $post;
            }

            if ( $post_datetime >= $milestone_year ) {
                $post->milestone = $milestone_year_int . " " . ($milestone_year_int == 1 ? __( 'year', 'media-consumption-log' ) : __( 'years', 'media-consumption-log' )) . "<br />(" . $current_mcl_count . ")";
                $post->post_link = get_permalink( $post );
                $milestone_year_int++;
                $milestone_year->modify( "+1 years" );

                $data->milestones[] = $post;
            }

            // Build graph data
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

        // Process data
        // Get link of the tags and replace "--" with ", "
        foreach ( $data->tags as &$tag ) {
            $small_tag = new stdClass();

            // To get link without query
            $temp_tag = new stdClass();
            $temp_tag->term_id = $tag->tag_term_id;
            $temp_tag->name = $tag->tag_name;
            $temp_tag->slug = $tag->tag_slug;
            $temp_tag->term_group = $tag->tag_term_group;
            $temp_tag->term_taxonomy_id = $tag->tag_term_taxonomy_id;
            $temp_tag->taxonomy = $tag->tag_taxonomy;
            $temp_tag->description = $tag->tag_description;
            $temp_tag->parent = $tag->tag_parent;
            $temp_tag->count = $tag->tag_count;

            $small_tag->tag_term_id = $tag->tag_term_id;
            $small_tag->tag_name = MclCommaInTags::replace( $tag->tag_name );
            $small_tag->tag_link = get_tag_link( $temp_tag );
            $small_tag->cats = $tag->cats;
            $small_tag->mcl_total = $tag->mcl_total;

            $tag = $small_tag;
        }

        // Sort status array
        foreach ( $status as &$category ) {
            ksort( $category, SORT_NATURAL );

            foreach ( $category as &$stati ) {
                ksort( $stati, SORT_NATURAL );

                foreach ( $stati as &$letter ) {
                    usort( $letter, function($a, $b) {
                        return strcmp( $a->tag_name, $b->tag_name );
                    } );
                }
            }
        }

        $data->tags_count_ongoing = 0;
        $data->tags_count_complete = 0;
        $data->tags_count_abandoned = 0;
        $data->tags_count_total = 0;
        $data->cat_serial_ongoing = false;
        $data->cat_serial_complete = false;
        $data->cat_serial_abandoned = false;
        $data->cat_non_serial = false;

        // Graphs variables
        if ( MclSettings::get_statistics_monthly_count() != 0 ) {
            $first_month = date( 'Y-m', strtotime( "-" . (MclSettings::get_statistics_monthly_count() - 1) . " month", strtotime( date( 'Y-m' ) ) ) );
        } else {
            $first_month = $data->first_post_date->format( 'Y-m' );
        }

        $monthly_dates = array();

        $i = 0;

        while ( true ) {
            $month = date( 'Y-m', strtotime( "-" . $i . " month", strtotime( date( 'Y-m' ) ) ) );

            $monthly_dates[] = $month;

            if ( $month == $first_month ) {
                break;
            }

            $i++;
        }

        $first_day = $data->first_post_date->format( 'Y-m-d' );

        $daily_dates = array();

        $i = 0;

        while ( true ) {
            $day = date( 'Y-m-d', strtotime( "-" . $i . " day", strtotime( date( 'Y-m-d' ) ) ) );

            $daily_dates[] = $day;

            if ( $day == $first_day ) {
                break;
            }

            $i++;
        }

        foreach ( $data->categories as &$category ) {
            $small_category = new stdClass();
            $small_category->term_id = $category->term_id;
            $small_category->name = $category->name;
            $small_category->slug = $category->slug;
            $category = $small_category;

            if ( array_key_exists( MclSerialStatus::RUNNING, $status[$category->term_id] ) ) {
                $category->mcl_tags_ongoing = $status[$category->term_id][MclSerialStatus::RUNNING];

                foreach ( $category->mcl_tags_ongoing as &$letter ) {
                    foreach ( $letter as &$tag_letter ) {
                        $small_tag_letter = new stdClass();
                        $small_tag_letter->tag_term_id = $tag_letter->tag_term_id;
                        $small_tag_letter->post_title = $tag_letter->post_title;
                        $small_tag_letter->post_date = $tag_letter->post_date;
                        $small_tag_letter->post_link = get_permalink( $tag_letter );

                        $tag_letter = $small_tag_letter;
                    }
                }
            } else {
                $category->mcl_tags_ongoing = array();
            }
            if ( array_key_exists( MclSerialStatus::COMPLETE, $status[$category->term_id] ) ) {
                $category->mcl_tags_complete = $status[$category->term_id][MclSerialStatus::COMPLETE];
            } else {
                $category->mcl_tags_complete = array();
            }
            if ( array_key_exists( MclSerialStatus::ABANDONED, $status[$category->term_id] ) ) {
                $category->mcl_tags_abandoned = $status[$category->term_id][MclSerialStatus::ABANDONED];
            } else {
                $category->mcl_tags_abandoned = array();
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

            // Hourly graph
            if ( !array_key_exists( $category->term_id, $hourly_consumption ) ) {
                $hourly_consumption[$category->term_id] = array();
            }

            // Add missing hours
            for ( $i = 0; $i < 24; $i++ ) {
                if ( !array_key_exists( $i, $hourly_consumption[$category->term_id] ) ) {
                    $hourly_consumption[$category->term_id][$i] = 0;
                }
            }

            // Sort hours
            ksort( $hourly_consumption[$category->term_id] );

            $category->mcl_hourly_data = $hourly_consumption[$category->term_id];

            // Monthly graph
            if ( !array_key_exists( $category->term_id, $monthly_consumption ) ) {
                $monthly_consumption[$category->term_id] = array();
            }

            foreach ( $monthly_dates as $monthly_date ) {
                if ( !array_key_exists( $monthly_date, $monthly_consumption[$category->term_id] ) ) {
                    $monthly_consumption[$category->term_id][$monthly_date] = 0;
                }
            }

            krsort( $monthly_consumption[$category->term_id] );

            if ( MclSettings::get_statistics_monthly_count() != 0 ) {
                $category->mcl_monthly_data = array_slice( $monthly_consumption[$category->term_id], 0, MclSettings::get_statistics_monthly_count() );
            } else {
                $category->mcl_monthly_data = $monthly_consumption[$category->term_id];
            }

            // Daily cgraph
            if ( !array_key_exists( $category->term_id, $daily_consumption ) ) {
                $daily_consumption[$category->term_id] = array();
            }

            foreach ( $daily_dates as $daily_date ) {
                if ( !array_key_exists( $daily_date, $daily_consumption[$category->term_id] ) ) {
                    $daily_consumption[$category->term_id][$daily_date] = 0;
                }
            }

            ksort( $daily_consumption[$category->term_id] );

            if ( MclSettings::get_statistics_daily_count() != 0 ) {
                $category->mcl_daily_data = array_slice( $daily_consumption[$category->term_id], - MclSettings::get_statistics_daily_count(), MclSettings::get_statistics_daily_count() );
                krsort( $category->mcl_daily_data );
            } else {
                $category->mcl_daily_data = $daily_consumption[$category->term_id];
                krsort( $category->mcl_daily_data );
            }
        }

        $data->average_consumption_development = self::get_average_consumption_development( $data, $daily_consumption );

        // Most consumed
        uasort( $data->tags, function($a, $b) {
            if ( $a->mcl_total == $b->mcl_total ) {
                return 0;
            }
            return $a->mcl_total < $b->mcl_total ? 1 : -1;
        } );

        // Get only the needed data
        $data->most_consumed = array_slice( $data->tags, 0, MclSettings::get_statistics_most_consumed_count(), true );

        return $data;
    }

    private static function count_tags_in_status( &$letters ) {
        $i = 0;

        foreach ( $letters as &$letter ) {
            foreach ( $letter as &$tag ) {
                $i++;
            }
        }

        return $i;
    }

    private static function get_average_consumption_development( &$data, &$daily_consumption ) {
        global $wpdb;

        // Data array
        $acd = array();

        // Legend
        $legend_array = array();
        $legend_array[] = "Date";
        foreach ( $data->categories as &$category ) {
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

        foreach ( $data->categories as &$category ) {
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
