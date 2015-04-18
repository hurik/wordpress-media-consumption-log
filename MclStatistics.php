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

add_shortcode( 'mcl-stats', array( 'MclStatistics', 'build_statistics' ) );

class MclStatistics {

    static function build_statistics() {
        // Set the default timezone
        date_default_timezone_set( get_option( 'timezone_string' ) );

        $data = MclData::get_data();

        if ( !$data->cat_serial_ongoing && !$data->cat_serial_complete && !$data->cat_serial_abandoned && !$data->cat_non_serial ) {
            $html = "<p><strong>" . __( 'Nothing here yet!', 'media-consumption-log' ) . "</strong></p>";

            return $html;
        }

        // Javascript start
        $html = "\n<script type=\"text/javascript\" src=\"https://www.google.com/jsapi\"></script>"
                . "\n<script type=\"text/javascript\">"
                . "\n  // Load the Visualization API and the piechart package."
                . "\n  google.load('visualization', '1.0', {"
                . "\n    'packages': ['corechart']"
                . "\n  });";

        // Daily graph
        $html .= "\n\n  // Set a callback to run when the Google Visualization API is loaded."
                . "\n  google.setOnLoadCallback(drawDailyChart);"
                . "\n  google.setOnLoadCallback(drawMonthlyChart);"
                . "\n\n  // Callback that creates and populates a data table,"
                . "\n  // instantiates the pie chart, passes in the data and"
                . "\n  // draws it."
                . "\n  function drawDailyChart() {"
                . "\n    // Some raw data (not necessarily accurate)"
                . "\n    var data = google.visualization.arrayToDataTable(["
                . "\n      ['Date', ";

        // Get the last dates
        $dates_daily = array();

        if ( MclSettings::get_statistics_daily_count() != 0 ) {
            for ( $i = 0; $i < MclSettings::get_statistics_daily_count(); $i++ ) {
                $day = date( 'Y-m-d', strtotime( "-" . $i . " day", strtotime( date( 'Y-m-d' ) ) ) );
                array_push( $dates_daily, $day );
            }
        } else {
            $date_current = new DateTime( date( 'Y-m-d' ) );
            $number_of_days = $date_current->diff( $data->first_post_date )->format( "%a" ) + 1;

            for ( $i = 0; $i < $number_of_days; $i++ ) {
                $day = date( 'Y-m-d', strtotime( "-" . $i . " day", strtotime( date( 'Y-m-d' ) ) ) );
                array_push( $dates_daily, $day );
            }
        }

        // Data array header
        foreach ( $data->categories as $category ) {
            $html .= "'{$category->name}'";

            if ( end( $data->categories ) != $category ) {
                $html .= ", ";
            }
        }

        $html .= ", { role: 'annotation' }],";

        // Data array
        for ( $i = 0; $i < count( $dates_daily ); $i++ ) {
            $date = DateTime::createFromFormat( 'Y-m-d', $dates_daily[$i] );

            $html .= "\n      ['{$date->format( MclSettings::get_statistics_daily_date_format() )}', ";

            $total = 0;

            foreach ( $data->categories as $category ) {
                $count = 0;

                foreach ( $category->mcl_daily_data as $cat_count ) {
                    if ( $dates_daily[$i] == $cat_count->date ) {
                        $count = $cat_count->number;
                        break;
                    }
                }

                $total += $count;
                $html .= "{$count}";

                if ( end( $data->categories ) != $category ) {
                    $html .= ", ";
                }
            }

            $html .= ", '{$total}']";

            if ( $i != count( $dates_daily ) - 1 ) {
                $html .= ", ";
            }
        }

        $html .= "\n    ]);"
                . "\n\n    var options = {"
                . "\n      " . MclSettings::get_statistics_daily_options()
                . "\n    };"
                . "\n\n    var chart = new google.visualization.BarChart(document.getElementById('daily_chart_div'));"
                . "\n    chart.draw(data, options);"
                . "\n  }";

        // Monthly graph
        $html .= "\n\n  function drawMonthlyChart() {"
                . "\n    // Some raw data (not necessarily accurate)"
                . "\n    var data = google.visualization.arrayToDataTable(["
                . "\n      ['Date', ";

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

        foreach ( $data->categories as $category ) {
            $html .= "'{$category->name}'";

            if ( end( $data->categories ) != $category ) {
                $html .= ", ";
            }
        }

        $html .= ", { role: 'annotation' }],";

        for ( $i = 0; $i < count( $dates_monthly ); $i++ ) {
            $date = DateTime::createFromFormat( 'Y-m', $dates_monthly[$i] );

            $html .= "\n      ['{$date->format( MclSettings::get_statistics_monthly_date_format() )}', ";

            $total = 0;

            foreach ( $data->categories as $category ) {
                $count = 0;

                foreach ( $category->mcl_monthly_data as $cat_count ) {
                    if ( $dates_monthly[$i] == $cat_count->date ) {
                        $count = $cat_count->number;
                        break;
                    }
                }

                $total += $count;
                $html .= "{$count}";

                if ( end( $data->categories ) != $category ) {
                    $html .= ", ";
                }
            }

            $html .= ", '{$total}']";

            if ( $i != count( $dates_monthly ) - 1 ) {
                $html .= ", ";
            }
        }

        $html .= "\n    ]);"
                . "\n\n    var options = {"
                . "\n      " . MclSettings::get_statistics_monthly_options()
                . "\n    };"
                . "\n\n    var chart = new google.visualization.BarChart(document.getElementById('monthly_chart_div'));"
                . "\n    chart.draw(data, options);"
                . "\n  }";

        // Javascript end
        $html .= "\n\n  jQuery(document).ready(function($) {"
                . "\n    $(window).resize(function() {"
                . "\n      drawDailyChart();"
                . "\n      drawMonthlyChart();"
                . "\n    });"
                . "\n  });"
                . "\n</script>";

        // Navigation
        $html .= "\n<div>"
                . "\n  <ul>"
                . "\n    <li><a href=\"#daily-consumption-chart\">" . __( 'Daily consumption', 'media-consumption-log' ) . "</a></li>"
                . "\n    <li><a href=\"#monthly-consumption-chart\">" . __( 'Monthly consumption', 'media-consumption-log' ) . "</a></li>"
                . "\n    <li><a href=\"#total-consumption\">" . __( 'Total consumption', 'media-consumption-log' ) . "</a></li>"
                . "\n    <li><a href=\"#average-consumption\">" . __( 'Average consumption', 'media-consumption-log' ) . "</a></li>"
                . "\n    <li><a href=\"#consumption-count\">" . __( 'Consumption amount', 'media-consumption-log' ) . "</a></li>"
                . "\n   <ul>"
                . "\n</div>";

        // Daily graph
        $html .= "\n\n<h4 id=\"daily-consumption-chart\">" . __( 'Daily consumption', 'media-consumption-log' ) . "</h4><hr />"
                . "\n<div id=\"daily_chart_div\"></div>";

        // Monthly graph
        $html .= "\n\n<h4 id=\"monthly-consumption-chart\">" . __( 'Monthly consumption', 'media-consumption-log' ) . "</h4><hr />"
                . "\n<div id=\"monthly_chart_div\"></div>";

        // Total consumption
        $html .= "\n\n<h4 id=\"total-consumption\">" . __( 'Total consumption', 'media-consumption-log' ) . "</h4><hr />"
                . "\n<table border=\"1\">"
                . "\n  <colgroup>"
                . "\n    <col width=\"98%\">"
                . "\n    <col width=\"1%\">"
                . "\n    <col width=\"1%\">"
                . "\n  </colgroup>"
                . "\n  <tr>"
                . "\n    <th>" . __( 'Category', 'media-consumption-log' ) . "</th>"
                . "\n    <th nowrap>#</th><th nowrap>" . __( 'Unit', 'media-consumption-log' ) . "</th>"
                . "\n  </tr>";

        foreach ( $data->categories as $category ) {
            $unit = MclUnits::get_unit_of_category( $category );

            $html .= "\n  <tr>"
                    . "\n    <td>{$category->name}</td>"
                    . "\n    <td nowrap>{$category->mcl_consumption_total}</td>"
                    . "\n    <td nowrap>{$unit}</td>"
                    . "\n  </tr>";
        }

        $since_total_string = str_replace( '%DATE%', $data->first_post_date->format( MclSettings::get_statistics_daily_date_format() ), __( 'Total comsumption, since the first post on the %DATE% (%DAYS% days).', 'media-consumption-log' ) );
        $since_total_string = str_replace( '%DAYS%', $data->number_of_days, $since_total_string );

        $html .= "\n  <tr>"
                . "\n    <th>" . __( 'Total', 'media-consumption-log' ) . "</th>"
                . "\n    <th nowrap>{$data->consumption_total}</th>"
                . "\n  </tr>"
                . "\n</table>"
                . "\n<p>{$since_total_string}</p>";

        // Average Consumption
        $html .= "\n\n<h4 id=\"average-consumption\">" . __( 'Average consumption', 'media-consumption-log' ) . "</h4><hr />"
                . "\n<table border=\"1\">"
                . "\n  <colgroup>"
                . "\n    <col width=\"98%\">"
                . "\n    <col width=\"1%\">"
                . "\n    <col width=\"1%\">"
                . "\n  </colgroup>"
                . "\n  <tr>"
                . "\n    <th>" . __( 'Category', 'media-consumption-log' ) . "</th>"
                . "\n    <th nowrap>&#216</th>"
                . "\n    <th nowrap>" . __( 'Unit', 'media-consumption-log' ) . "</th>"
                . "\n  </tr>";

        foreach ( $data->categories as $category ) {
            $unit = MclUnits::get_unit_of_category( $category );

            $html .= "\n  <tr>"
                    . "\n    <td>{$category->name}</td>"
                    . "\n    <td nowrap>" . number_format( $category->mcl_consumption_average, 2 ) . "</td>"
                    . "\n    <td nowrap>{$unit}</td>"
                    . "\n  </tr>";
        }

        $since_string = str_replace( '%DATE%', $data->first_post_date->format( MclSettings::get_statistics_daily_date_format() ), __( 'Average a day, since the first post on the %DATE% (%DAYS% days).', 'media-consumption-log' ) );
        $since_string = str_replace( '%DAYS%', $data->number_of_days, $since_string );

        $html .= "\n  <tr>"
                . "\n    <th>" . __( 'Total', 'media-consumption-log' ) . "</th>"
                . "\n    <th nowrap>" . number_format( $data->consumption_average, 2 ) . "</th>"
                . "\n  </tr>"
                . "\n</table>"
                . "\n<p>{$since_string}</p>";

        // Consumption count
        $html .= "\n\n<h4 id=\"consumption-count\">" . __( 'Consumption amount', 'media-consumption-log' ) . "</h4><hr />"
                . "\n<table border=\"1\">"
                . "\n  <colgroup>"
                . "\n    <col width=\"96%\">"
                . "\n    <col width=\"1%\">"
                . "\n    <col width=\"1%\">"
                . "\n    <col width=\"1%\">"
                . "\n    <col width=\"1%\">"
                . "\n  </colgroup>"
                . "\n  <tr>"
                . "\n    <th>" . __( 'Category', 'media-consumption-log' ) . "</th>"
                . "\n    <th nowrap>" . __( 'Running', 'media-consumption-log' ) . "</th>"
                . "\n    <th nowrap>" . __( 'Complete', 'media-consumption-log' ) . "</th>"
                . "\n    <th nowrap>" . __( 'Abandoned', 'media-consumption-log' ) . "</th>"
                . "\n    <th nowrap>" . __( 'Total', 'media-consumption-log' ) . "</th>"
                . "\n  </tr>";

        foreach ( $data->categories as $category ) {
            if ( $category->mcl_tags_count == 0 ) {
                continue;
            }

            if ( MclHelper::is_monitored_non_serial_category( $category->term_id ) ) {
                $html .= "\n  <tr>"
                        . "\n    <td colspan=\"4\">{$category->name}</td>"
                        . "\n    <td nowrap>{$category->mcl_tags_count}</td>"
                        . "\n  </tr>";
            } else {
                $html .= "\n  <tr>"
                        . "\n    <td>{$category->name}</td>"
                        . "\n    <td nowrap>{$category->mcl_tags_count_ongoing}</td>"
                        . "\n    <td nowrap>{$category->mcl_tags_count_complete}</td>"
                        . "\n    <td nowrap>{$category->mcl_tags_count_abandoned}</td>"
                        . "\n    <td nowrap>{$category->mcl_tags_count}</td>"
                        . "\n  </tr>";
            }
        }

        $categories_string = MclHelper::build_all_categories_string( $data->categories, false );

        $since_count_string = str_replace( '%DATE%', $data->first_post_date->format( MclSettings::get_statistics_daily_date_format() ), __( 'Total count of different %CATEGORIES%, since the first post on the %DATE% (%DAYS% days).', 'media-consumption-log' ) );
        $since_count_string = str_replace( '%DAYS%', $data->number_of_days, $since_count_string );
        $since_count_string = str_replace( '%CATEGORIES%', $categories_string, $since_count_string );

        $html .= "\n  <tr>"
                . "\n    <th nowrap>" . __( 'Total', 'media-consumption-log' ) . "</th>"
                . "\n    <th nowrap>{$data->tags_count_ongoing}</th>"
                . "\n    <th nowrap>{$data->tags_count_complete}</th>"
                . "\n    <th nowrap>{$data->tags_count_abandoned}</th>"
                . "\n    <th nowrap>{$data->tags_count_total}</th>"
                . "\n  </tr>"
                . "\n</table>"
                . "\n<p>{$since_count_string}</p>";

        return $html;
    }

}
