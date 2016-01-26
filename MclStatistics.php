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

add_shortcode( 'mcl-stats', array( 'MclStatistics', 'build_statistics' ) );

class MclStatistics {

    static function build_statistics() {
        $data = MclData::get_data_up_to_date();

        if ( !$data->cat_serial_ongoing && !$data->cat_serial_complete && !$data->cat_serial_abandoned && !$data->cat_non_serial ) {
            return "<p><strong>" . __( 'Nothing here yet!', 'media-consumption-log' ) . "</strong></p>";
        }

        // Daily graph
        // Daily data array
        $daily_data = array();

        for ( $i = 0; $i < count( reset( $data->categories )->mcl_daily_data ) + 1; $i++ ) {
            $daily_data[] = array();
        }

        // Monthly array header
        $daily_data[0][] = "Date";

        foreach ( $data->categories as $category ) {
            $daily_data[0][] = $category->name;
        }

        $daily_data[0][] = __( 'Total', 'media-consumption-log' );
        $daily_data[0][] = "ROLE_ANNOTATION";

        $first = true;

        foreach ( $data->categories as $category ) {
            $c = 1;

            if ( $first ) {
                foreach ( $category->mcl_daily_data as $key => $value ) {
                    $date = DateTime::createFromFormat( 'Y-m-d', $key );

                    $daily_data[$c][] = $date->format( MclSettings::get_statistics_daily_date_format() );
                    $daily_data[$c][] = intval( $value );

                    $c++;
                }

                $first = false;
            } else {
                foreach ( $category->mcl_daily_data as $value ) {
                    $daily_data[$c][] = intval( $value );

                    $c++;
                }
            }
        }

        for ( $i = 1; $i < count( $daily_data ); $i++ ) {
            $total = 0;

            for ( $j = 1; $j < count( $daily_data[0] ) - 2; $j++ ) {
                $total += $daily_data[$i][$j];
            }

            $daily_data[$i][] = $total;
            $daily_data[$i][] = $total;
        }

        // Monthly graph
        // Monthly data array
        $monthly_data = array();

        for ( $i = 0; $i < count( reset( $data->categories )->mcl_monthly_data ) + 1; $i++ ) {
            $monthly_data[] = array();
        }

        // Monthly array header
        $monthly_data[0][] = "Date";

        foreach ( $data->categories as $category ) {
            $monthly_data[0][] = $category->name;
        }

        $monthly_data[0][] = __( 'Total', 'media-consumption-log' );
        $monthly_data[0][] = "ROLE_ANNOTATION";

        $first = true;

        foreach ( $data->categories as $category ) {
            $c = 1;

            if ( $first ) {
                foreach ( $category->mcl_monthly_data as $key => $value ) {
                    $date = DateTime::createFromFormat( 'Y-m-d', $key . "-01" );

                    $monthly_data[$c][] = $date->format( MclSettings::get_statistics_monthly_date_format() );
                    $monthly_data[$c][] = intval( $value );

                    $c++;
                }

                $first = false;
            } else {
                foreach ( $category->mcl_monthly_data as $value ) {
                    $monthly_data[$c][] = intval( $value );

                    $c++;
                }
            }
        }

        for ( $i = 1; $i < count( $monthly_data ); $i++ ) {
            $total = 0;

            for ( $j = 1; $j < count( $monthly_data[0] ) - 2; $j++ ) {
                $total += $monthly_data[$i][$j];
            }

            $monthly_data[$i][] = $total;
            $monthly_data[$i][] = $total;
        }

        // Hourly graph
        // Hourly data array
        $hourly_data = array();

        // Hourly array header
        $hourly_data[0][] = "Date";

        foreach ( $data->categories as $category ) {
            $hourly_data[0][] = $category->name;
        }

        $hourly_data[0][] = __( 'Total', 'media-consumption-log' );
        $hourly_data[0][] = "ROLE_ANNOTATION";

        for ( $i = 0; $i < 24; $i++ ) {
            $hourly_data[$i + 1][] = sprintf( '%02d', $i ) . " - " . sprintf( '%02d', $i + 1 );

            $total = 0;

            foreach ( $data->categories as $category ) {
                $count = $category->mcl_hourly_data[$i];
                $total += $count;
                $hourly_data[$i + 1][] = $count;
            }

            $hourly_data[$i + 1][] = $total;
            $hourly_data[$i + 1][] = $total;
        }

        $js_params = array(
            'daily' => json_encode( $daily_data, JSON_NUMERIC_CHECK ),
            'monthly' => json_encode( $monthly_data, JSON_NUMERIC_CHECK ),
            'hourly' => json_encode( $hourly_data, JSON_NUMERIC_CHECK ),
            'average' => json_encode( $data->average_consumption_development, JSON_NUMERIC_CHECK ),
            'average_max_delta' => json_encode( MclSettings::get_statistics_average_consumption_development_max_delta(), JSON_NUMERIC_CHECK )
        );

        // Output js
        wp_enqueue_script( "google-charts", "https://www.google.com/jsapi" );
        wp_enqueue_script( "mcl-statistics", plugins_url( "js/mcl_statistics.js", __FILE__ ) );
        wp_localize_script( "mcl-statistics", 'js_params', $js_params );

        // Navigation
        $html = "\n<div>"
                . "\n  <ul>"
                . "\n    <li><a href=\"#daily-consumption-chart\">" . __( 'Daily consumption', 'media-consumption-log' ) . "</a></li>"
                . "\n    <li><a href=\"#hourly-consumption-chart\">" . __( 'Hourly consumption', 'media-consumption-log' ) . "</a></li>"
                . "\n    <li><a href=\"#monthly-consumption-chart\">" . __( 'Monthly consumption', 'media-consumption-log' ) . "</a></li>"
                . "\n    <li><a href=\"#total-consumption\">" . __( 'Total consumption', 'media-consumption-log' ) . "</a></li>"
                . "\n    <li><a href=\"#average-consumption\">" . __( 'Average consumption', 'media-consumption-log' ) . "</a></li>"
                . "\n    <li><a href=\"#average-consumption-development-chart\">" . __( 'Average consumption development', 'media-consumption-log' ) . "</a></li>"
                . "\n    <li><a href=\"#consumption-count\">" . __( 'Consumption amount', 'media-consumption-log' ) . "</a></li>"
                . "\n    <li><a href=\"#most-consumed\">" . __( 'Most consumed', 'media-consumption-log' ) . "</a></li>"
                . "\n    <li><a href=\"#milestones\">" . __( 'Milestones', 'media-consumption-log' ) . "</a></li>"
                . "\n   </ul>"
                . "\n</div>";

        // Daily graph
        $html .= "\n\n<h4 id=\"daily-consumption-chart\">" . __( 'Daily consumption', 'media-consumption-log' ) . "</h4><hr />"
                . "\n<div id=\"daily_chart_div\"></div>";

        // Hourly graph
        $html .= "\n\n<h4 id=\"hourly-consumption-chart\">" . __( 'Hourly consumption', 'media-consumption-log' ) . "</h4><hr />"
                . "\n<div id=\"hourly_chart_div\"></div>";

        // Monthly graph
        $html .= "\n\n<h4 id=\"monthly-consumption-chart\">" . __( 'Monthly consumption', 'media-consumption-log' ) . "</h4><hr />"
                . "\n<div id=\"monthly_chart_div\"></div>";

        // Total consumption
        $html .= "\n\n<h4 id=\"total-consumption\">" . __( 'Total consumption', 'media-consumption-log' ) . "</h4><hr />"
                . "\n<table class=\"mcl_table\">"
                . "\n  <colgroup>"
                . "\n    <col width=\"98%\">"
                . "\n    <col width=\"1%\">"
                . "\n    <col width=\"1%\">"
                . "\n  </colgroup>"
                . "\n  <thead>"
                . "\n    <tr>"
                . "\n      <th>" . __( 'Category', 'media-consumption-log' ) . "</th>"
                . "\n      <th nowrap>#</th>"
                . "\n      <th nowrap>" . __( 'Unit', 'media-consumption-log' ) . "</th>"
                . "\n    </tr>"
                . "\n  </thead>"
                . "\n  <tbody>";

        $sum = 0;

        foreach ( $data->categories as $category ) {
            $unit = MclSettings::get_unit_of_category( $category );

            if ( array_key_exists( $category->term_id, $data->total_consumption ) ) {
                $total = $data->total_consumption[$category->term_id];
            } else {
                $total = 0;
            }

            $sum += $total;

            $html .= "\n    <tr>"
                    . "\n      <td>{$category->name}</td>"
                    . "\n      <td nowrap>{$total}</td>"
                    . "\n      <td nowrap>{$unit}</td>"
                    . "\n    </tr>";
        }

        $since_total_string = str_replace( '%DATE%', $data->first_post_date->format( MclSettings::get_statistics_daily_date_format() ), __( 'Total comsumption, since the first post on the %DATE% (%DAYS% days).', 'media-consumption-log' ) );
        $since_total_string = str_replace( '%DAYS%', $data->number_of_days, $since_total_string );

        $html .= "\n  </tbody>"
                . "\n  <tfoot>"
                . "\n  <tr>"
                . "\n      <th>" . __( 'Total', 'media-consumption-log' ) . "</th>"
                . "\n      <th nowrap>{$sum}</th>"
                . "\n      <th></th>"
                . "\n    </tr>"
                . "\n  </tfoot>"
                . "\n</table>"
                . "\n<p>{$since_total_string}</p>";

        // Average Consumption
        $html .= "\n\n<h4 id=\"average-consumption\">" . __( 'Average consumption', 'media-consumption-log' ) . "</h4><hr />"
                . "\n<table class=\"mcl_table\">"
                . "\n  <colgroup>"
                . "\n    <col width=\"98%\">"
                . "\n    <col width=\"1%\">"
                . "\n    <col width=\"1%\">"
                . "\n  </colgroup>"
                . "\n  <thead>"
                . "\n    <tr>"
                . "\n      <th>" . __( 'Category', 'media-consumption-log' ) . "</th>"
                . "\n      <th nowrap>&#216</th>"
                . "\n      <th nowrap>" . __( 'Unit', 'media-consumption-log' ) . "</th>"
                . "\n    </tr>"
                . "\n  </thead>"
                . "\n  <tbody>";

        $sum = 0;

        foreach ( $data->categories as $category ) {
            $unit = MclSettings::get_unit_of_category( $category );

            if ( array_key_exists( $category->term_id, $data->total_consumption ) ) {
                $total = $data->total_consumption[$category->term_id];
            } else {
                $total = 0;
            }

            $sum += $total;

            $html .= "\n    <tr>"
                    . "\n      <td>{$category->name}</td>"
                    . "\n      <td nowrap>" . number_format( ($total / $data->number_of_days ), 2 ) . "</td>"
                    . "\n      <td nowrap>{$unit}</td>"
                    . "\n    </tr>";
        }

        $since_string = str_replace( '%DATE%', $data->first_post_date->format( MclSettings::get_statistics_daily_date_format() ), __( 'Average a day, since the first post on the %DATE% (%DAYS% days).', 'media-consumption-log' ) );
        $since_string = str_replace( '%DAYS%', $data->number_of_days, $since_string );

        $html .= "\n  </tbody>"
                . "\n  <tfoot>"
                . "\n    <tr>"
                . "\n      <th>" . __( 'Total', 'media-consumption-log' ) . "</th>"
                . "\n      <th nowrap>" . number_format( ($sum / $data->number_of_days ), 2 ) . "</th>"
                . "\n      <th></th>"
                . "\n    </tr>"
                . "\n  </tfoot>"
                . "\n</table>"
                . "\n<p>{$since_string}</p>";

        // Average consumption development
        $html .= "\n\n<h4 id=\"average-consumption-development-chart\">" . __( 'Average consumption development', 'media-consumption-log' ) . "</h4><hr />";
        if ( $data->number_of_days > 1 ) {
            $html .= "\n<div id=\"average_consumption_development_chart_div\"></div>";
        } else {
            $html .= "\n<p>" . __( 'Average consumption development graph will be visible tomorrow.', 'media-consumption-log' ) . "</p>";
        }

        // Consumption count
        $html .= "\n\n<h4 id=\"consumption-count\">" . __( 'Consumption amount', 'media-consumption-log' ) . "</h4><hr />"
                . "\n<table class=\"mcl_table\">"
                . "\n  <colgroup>"
                . "\n    <col width=\"20%\">"
                . "\n    <col width=\"20%\">"
                . "\n    <col width=\"20%\">"
                . "\n    <col width=\"20%\">"
                . "\n    <col width=\"20%\">"
                . "\n  </colgroup>"
                . "\n  <thead>"
                . "\n    <tr>"
                . "\n      <th nowrap>" . __( 'Category', 'media-consumption-log' ) . "</th>"
                . "\n      <th nowrap>" . __( 'Running', 'media-consumption-log' ) . "</th>"
                . "\n      <th nowrap>" . __( 'Complete', 'media-consumption-log' ) . "</th>"
                . "\n      <th nowrap>" . __( 'Abandoned', 'media-consumption-log' ) . "</th>"
                . "\n      <th nowrap>" . __( 'Total', 'media-consumption-log' ) . "</th>"
                . "\n    </tr>"
                . "\n  </thead>"
                . "\n  <tbody>";

        foreach ( $data->categories as $category ) {
            if ( MclHelpers::is_monitored_non_serial_category( $category->term_id ) ) {
                $html .= "\n    <tr>"
                        . "\n      <td nowrap colspan=\"4\">{$category->name}</td>"
                        . "\n      <td nowrap>{$category->mcl_tags_count}</td>"
                        . "\n    </tr>";
            } else {
                $html .= "\n    <tr>"
                        . "\n      <td nowrap>{$category->name}</td>"
                        . "\n      <td nowrap>{$category->mcl_tags_count_ongoing}</td>"
                        . "\n      <td nowrap>{$category->mcl_tags_count_complete}</td>"
                        . "\n      <td nowrap>{$category->mcl_tags_count_abandoned}</td>"
                        . "\n      <td nowrap>{$category->mcl_tags_count}</td>"
                        . "\n    </tr>";
            }
        }

        $categories_string = MclHelpers::build_all_categories_string( $data->categories, false );

        $since_count_string = str_replace( '%DATE%', $data->first_post_date->format( MclSettings::get_statistics_daily_date_format() ), __( 'Total count of different %CATEGORIES%, since the first post on the %DATE% (%DAYS% days).', 'media-consumption-log' ) );
        $since_count_string = str_replace( '%DAYS%', $data->number_of_days, $since_count_string );
        $since_count_string = str_replace( '%CATEGORIES%', $categories_string, $since_count_string );

        $html .= "\n  </tbody>"
                . "\n  <tfoot>"
                . "\n    <tr>"
                . "\n      <th nowrap>" . __( 'Total', 'media-consumption-log' ) . "</th>"
                . "\n      <th nowrap>{$data->tags_count_ongoing}</th>"
                . "\n      <th nowrap>{$data->tags_count_complete}</th>"
                . "\n      <th nowrap>{$data->tags_count_abandoned}</th>"
                . "\n      <th nowrap>{$data->tags_count_total}</th>"
                . "\n    </tr>"
                . "\n  </tfoot>"
                . "\n</table>"
                . "\n<p>{$since_count_string}</p>";

        // Most consumed
        $since_most_consumed_string = str_replace( '%DATE%', $data->first_post_date->format( MclSettings::get_statistics_daily_date_format() ), __( 'Most consumed serials, since the first post on the %DATE% (%DAYS% days).', 'media-consumption-log' ) );
        $since_most_consumed_string = str_replace( '%DAYS%', $data->number_of_days, $since_most_consumed_string );

        $html .= "\n\n<h4 id=\"most-consumed\">" . __( 'Most consumed', 'media-consumption-log' ) . "</h4><hr />"
                . "\n<table class=\"mcl_table\">"
                . "\n  <colgroup>"
                . "\n    <col width=\"98%\">"
                . "\n    <col width=\"1%\">"
                . "\n    <col width=\"1%\">"
                . "\n  </colgroup>"
                . "\n  <thead>"
                . "\n    <tr>"
                . "\n      <th nowrap>" . __( 'Serial', 'media-consumption-log' ) . "</th>"
                . "\n      <th nowrap>" . __( 'Count', 'media-consumption-log' ) . "</th>"
                . "\n      <th nowrap>" . __( 'Unit', 'media-consumption-log' ) . "</th>"
                . "\n    </tr>"
                . "\n  </thead>"
                . "\n  <tbody>";

        foreach ( $data->most_consumed as $tag ) {
            $href_tag_title = htmlspecialchars( htmlspecialchars_decode( $data->tags[$tag->tag_term_id]->tag_name ) );

            $units = array();

            foreach ( $tag->cats as $cat ) {
                $units[] = MclSettings::get_unit_of_category( get_category( $cat ) );
            }

            $categories = MclHelpers::build_list_from_array( $units );

            $html .= "\n    <tr>"
                    . "\n      <td><a href=\"{$tag->tag_link}\" title=\"{$href_tag_title}\">{$data->tags[$tag->tag_term_id]->tag_name}</a></td>"
                    . "\n      <td nowrap>{$tag->mcl_total}</td>"
                    . "\n      <td nowrap>{$categories}</td>"
                    . "\n    </tr>";
        }

        $html .= "\n  </tbody>"
                . "\n</table>"
                . "\n<p>{$since_most_consumed_string}</p>";

        $html .= "\n\n<h4 id=\"milestones\">" . __( 'Milestones', 'media-consumption-log' ) . "</h4><hr />"
                . "\n<table class=\"mcl_table\">"
                . "\n  <colgroup>"
                . "\n    <col width=\"1%\">"
                . "\n    <col width=\"98%\">"
                . "\n    <col width=\"1%\">"
                . "\n  </colgroup>"
                . "\n  <thead>"
                . "\n    <tr>"
                . "\n      <th nowrap>" . __( 'Milestone', 'media-consumption-log' ) . "</th>"
                . "\n      <th nowrap>" . __( 'Post', 'media-consumption-log' ) . "</th>"
                . "\n      <th nowrap>" . __( 'Date', 'media-consumption-log' ) . "</th>"
                . "\n    </tr>"
                . "\n  </thead>"
                . "\n  <tbody>";

        foreach ( $data->milestones as $milestone ) {
            $current_date = new DateTime();
            $current_date->setTime( 0, 0, 0 );
            $milestone_date = new DateTime( $milestone->post_date );
            $milestone_date->setTime( 0, 0, 0 );
            $interval = $milestone_date->diff( $current_date );
            $interval_days = $interval->format( '%a' );
            $current_milestone = $milestone->milestone;
            if ( $interval_days == 0 ) {
                $ago_string = "(" . __( 'today', 'media-consumption-log' ) . ")";
            } else if ( $interval_days == 1 ) {
                $ago_string = "(" . __( 'yesterday', 'media-consumption-log' ) . ")";
            } else {
                $ago_string = "({$interval_days} " . __( 'days ago', 'media-consumption-log' ) . ")";
            }

            $html .= "\n    <tr>"
                    . "\n      <td nowrap>{$current_milestone}</td>"
                    . "\n      <td><a href=\"{$milestone->post_link}\">{$milestone->post_title}</a>" . "</td>"
                    . "\n      <td nowrap>" . $milestone_date->format( MclSettings::get_statistics_daily_date_format() ) . "<br />{$ago_string}</td>"
                    . "\n    </tr>";
        }

        $html .= "\n  </tbody>"
                . "\n</table>";

        return $html;
    }

}
