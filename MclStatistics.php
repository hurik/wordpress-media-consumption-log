<?php

add_shortcode( 'mcl-stats', array( 'MclStatistics', 'mcl_statistics' ) );

class MclStatistics {

    function mcl_statistics() {
        date_default_timezone_set( get_option( 'timezone_string' ) );

        $current_date = date( 'Y-m-d' );

        $start_date = date( 'Y-m-d', strtotime( "-" . MclSettingsHelper::getStatisticsNumberOfDays() . " day", strtotime( $current_date ) ) );

        $daily_dates = array();

        $i = $current_date;

        while ( true ) {
            array_push( $daily_dates, $i );
            $i = date( 'Y-m-d', strtotime( "-1 day", strtotime( $i ) ) );

            if ( $i == $start_date ) {
                array_push( $daily_dates, $i );
                break;
            }
        }

        // Get the categories
        $categories = get_categories( "exclude=" . MclSettingsHelper::getStatisticsExcludeCategory() );

        $data = array();

        foreach ( $categories as $category ) {
            if ( MclSettingsHelper::isStatisticsMclNumber() ) {
                $stats = self::get_post_with_mcl_number_of_category_sorted_by_date( $category->term_id );
            } else {
                $stats = self::get_post_of_category_sorted_by_date( $category->term_id );
            }

            foreach ( $daily_dates as $date ) {
                $found = 0;

                foreach ( $stats as $stat ) {
                    if ( $stat->date == $date ) {
                        $found = $stat->number;
                        break;
                    }
                }

                $data[$category->name][] = $found;
            }
        }

        $monthly_dates = array();

        for ( $i = 0; $i < MclSettingsHelper::getStatisticsNumberOfMonths(); $i++ ) {
            $month = date( 'Y-m', strtotime( "-" . $i . " month", strtotime( date( 'Y-m' ) ) ) );
            array_push( $monthly_dates, $month );
        }

        $data_month = array();

        foreach ( $categories as $category ) {
            if ( MclSettingsHelper::isStatisticsMclNumber() ) {
                $stats = self::get_post_with_mcl_number_of_category_sorted_by_month( $category->term_id );
            } else {
                $stats = self::get_post_of_category_sorted_by_month( $category->term_id );
            }

            foreach ( $monthly_dates as $date ) {
                $found = 0;

                foreach ( $stats as $stat ) {
                    if ( $stat->date == $date ) {
                        $found = $stat->number;
                        break;
                    }
                }

                $data_month[$category->name][] = $found;
            }
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

        foreach ( $categories as $category ) {
            $html .= "'{$category->name}'";

            if ( end( $categories ) != $category ) {
                $html .= ", ";
            }
        }

        $html .= ", { role: 'annotation' }],";

        for ( $i = 0; $i < count( $daily_dates ); $i++ ) {
            $date = DateTime::createFromFormat( 'Y-m-d', $daily_dates[$i] );

            $html .= "\n      ['{$date->format( MclSettingsHelper::getStatisticsDailyDateFormat() )}', ";

            $total = 0;

            foreach ( $categories as $category ) {
                $total += $data[$category->name][$i];
                $html .= "{$data[$category->name][$i]}";

                if ( end( $categories ) != $category ) {
                    $html .= ", ";
                }
            }

            $html .= ", '{$total}']";

            if ( $i != count( $daily_dates ) - 1 ) {
                $html .= ", ";
            }
        }

        $html .= "\n    ]);"
                . "\n\n    var options = {"
                . "\n      " . MclSettingsHelper::getStatisticsGoogleChartsDailyOptions()
                . "\n    };"
                . "\n\n    var chart = new google.visualization.BarChart(document.getElementById('daily_chart_div'));"
                . "\n    chart.draw(data, options);"
                . "\n  }";

        // Monthly graph
        $html .= "\n\n  function drawMonthlyChart() {"
                . "\n    // Some raw data (not necessarily accurate)"
                . "\n    var data = google.visualization.arrayToDataTable(["
                . "\n      ['Date', ";

        foreach ( $categories as $category ) {
            $html .= "'{$category->name}'";

            if ( end( $categories ) != $category ) {
                $html .= ", ";
            }
        }

        $html .= ", { role: 'annotation' }],";

        for ( $i = 0; $i < count( $monthly_dates ); $i++ ) {
            $date = DateTime::createFromFormat( 'Y-m', $monthly_dates[$i] );

            $html .= "\n      ['{$date->format( MclSettingsHelper::getStatisticsMonthlyDateFormat() )}', ";

            $total = 0;

            foreach ( $categories as $category ) {
                $total += $data_month[$category->name][$i];
                $html .= "{$data_month[$category->name][$i]}";

                if ( end( $categories ) != $category ) {
                    $html .= ", ";
                }
            }

            $html .= ", '{$total}']";

            if ( $i != count( $daily_dates ) - 1 ) {
                $html .= ", ";
            }
        }

        $html .= "\n    ]);"
                . "\n\n    var options = {"
                . "\n      " . MclSettingsHelper::getStatisticsGoogleChartsMonthlyOptions()
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

        $date_first_post = new DateTime( self::get_date_of_first_post() );
        $since_total_string = str_replace( '%DATE', $date_first_post->format( MclSettingsHelper::getStatisticsDailyDateFormat() ), __( 'Total comsumption, since the first post on the %DATE.', 'media-consumption-log' ) );
        $total_all = 0;

        foreach ( $categories as $category ) {
            if ( MclSettingsHelper::isStatisticsMclNumber() ) {
                $total = self::get_mcl_number_of_category( $category->term_id );
            } else {
                $total = self::get_posts_of_category( $category->term_id );
            }

            $total_all += $total;

            $unit = get_option( "mcl_unit_{$category->slug}" );
            if ( empty( $unit ) ) {
                $unit = $category->name;
            }

            $html .= "\n  <tr>"
                    . "\n    <td>{$category->name}</td>"
                    . "\n    <td nowrap>{$total}</td>"
                    . "\n    <td nowrap>{$unit}</td>"
                    . "\n  </tr>";
        }

        $html .= "\n  <tr>"
                . "\n    <th>" . __( 'Total', 'media-consumption-log' ) . "</th>"
                . "\n    <th nowrap>{$total_all}</th>"
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

        $since_string = str_replace( '%DATE', $date_first_post->format( MclSettingsHelper::getStatisticsDailyDateFormat() ), __( 'Average a day, since the first post on the %DATE.', 'media-consumption-log' ) );
        $date_current = new DateTime( date( 'Y-m-d' ) );

        $number_of_days = $date_current->diff( $date_first_post )->format( "%a" ) + 1;

        $average_all = 0;

        foreach ( $categories as $category ) {
            if ( MclSettingsHelper::isStatisticsMclNumber() ) {
                $average = round( self::get_mcl_number_of_category( $category->term_id ) / $number_of_days, 2 );
            } else {
                $average = round( self::get_posts_of_category( $category->term_id ) / $number_of_days, 2 );
            }

            $average_all += $average;

            $unit = get_option( "mcl_unit_{$category->slug}" );
            if ( empty( $unit ) ) {
                $unit = $category->name;
            }

            $html .= "\n  <tr>"
                    . "\n    <td>{$category->name}</td>"
                    . "\n    <td nowrap>{$average}</td>"
                    . "\n    <td nowrap>{$unit}</td>"
                    . "\n  </tr>";
        }

        $html .= "\n  <tr>"
                . "\n    <th>" . __( 'Total', 'media-consumption-log' ) . "</th>"
                . "\n    <th nowrap>{$average_all}</th>"
                . "\n  </tr>"
                . "\n</table>"
                . "\n<p>{$since_string}</p>";

        // Consumption count
        $html .= "\n\n<h4 id=\"consumption-count\">" . __( 'Consumption amount', 'media-consumption-log' ) . "</h4><hr />"
                . "\n<table border=\"1\">"
                . "\n  <colgroup>"
                . "\n    <col width=\"97%\">"
                . "\n    <col width=\"1%\">"
                . "\n    <col width=\"1%\">"
                . "\n    <col width=\"1%\">"
                . "\n  </colgroup>"
                . "\n  <tr>"
                . "\n    <th>" . __( 'Category', 'media-consumption-log' ) . "</th>"
                . "\n    <th nowrap>" . __( 'Running', 'media-consumption-log' ) . "</th>"
                . "\n    <th nowrap>" . __( 'Complete', 'media-consumption-log' ) . "</th>"
                . "\n    <th nowrap>" . __( 'Total', 'media-consumption-log' ) . "</th>"
                . "\n  </tr>";

        $count_total_ongoing = 0;
        $count_total_complete = 0;
        $count_total = 0;

        foreach ( $categories as $category ) {
            $count_ongoing = self::get_tags_count_of_category( $category->term_id, 0 );
            $count_complete = self::get_tags_count_of_category( $category->term_id, 1 );

            $count_category_total = $count_ongoing + $count_complete;


            $count_total_ongoing += $count_ongoing;
            $count_total_complete += $count_complete;
            $count_total += $count_category_total;

            if ( $count_category_total == 0 ) {
                continue;
            }

            $cat_ids_status = explode( ",", MclSettingsHelper::getStatusExcludeCategory() );

            if ( in_array( $category->term_id, $cat_ids_status ) ) {
                $html .= "\n  <tr>"
                        . "\n    <td colspan=\"3\">{$category->name}</td>"
                        . "\n    <td nowrap>{$count_category_total}</td>"
                        . "\n  </tr>";
            } else {
                $html .= "\n  <tr>"
                        . "\n    <td>{$category->name}</td>"
                        . "\n    <td nowrap>{$count_ongoing}</td>"
                        . "\n    <td nowrap>{$count_complete}</td>"
                        . "\n    <td nowrap>{$count_category_total}</td>"
                        . "\n  </tr>";
            }
        }

        $categories_string = "";
        $second_to_last_cat = $categories[count( $categories ) - 2];
        $last_cat = end( $categories );

        foreach ( $categories as $category ) {
            if ( $category != $last_cat ) {
                $categories_string .= "{$category->name}";

                if ( $category != $second_to_last_cat ) {
                    $categories_string .= ", ";
                }
            } else {
                $categories_string .= " " . __( 'and', 'media-consumption-log' ) . " {$category->name}";
            }
        }

        $since_count_string = str_replace( '%DATE', $date_first_post->format( MclSettingsHelper::getStatisticsDailyDateFormat() ), __( 'Total count of different %CATEGORIES, since the first post on the %DATE.', 'media-consumption-log' ) );
        $since_count_string = str_replace( '%CATEGORIES', $categories_string, $since_count_string );

        $html .= "\n  <tr>"
                . "\n    <th nowrap>" . __( 'Total', 'media-consumption-log' ) . "</th>"
                . "\n    <th nowrap>{$count_total_ongoing}</th>"
                . "\n    <th nowrap>{$count_total_complete}</th>"
                . "\n    <th nowrap>{$count_total}</th>"
                . "\n  </tr>"
                . "\n</table>"
                . "\n<p>{$since_count_string}</p>";

        return $html;
    }

    private function get_date_of_first_post() {
        global $wpdb;

        $min_date = $wpdb->get_results( "
            SELECT Min( DATE_FORMAT( post_date, '%Y-%m-%d' ) ) AS date
            FROM {$wpdb->prefix}posts
            WHERE post_status = 'publish'
            AND post_type = 'post'
	" );

        return $min_date[0]->date;
    }

    private function get_post_with_mcl_number_of_category_sorted_by_date( $category_id ) {
        global $wpdb;

        $stats = $wpdb->get_results( "
            SELECT DATE_FORMAT( post_date, '%Y-%m-%d' ) AS date, SUM( meta_value ) AS number
            FROM {$wpdb->prefix}posts p
            LEFT OUTER JOIN {$wpdb->prefix}term_relationships r ON r.object_id = p.ID
            LEFT OUTER JOIN {$wpdb->prefix}postmeta m ON m.post_id = p.ID
            WHERE post_status = 'publish'
            AND post_type = 'post'
            AND meta_key = 'mcl_number'
            AND term_taxonomy_id = $category_id
            GROUP BY DATE_FORMAT( post_date, '%Y-%m-%d' )
            ORDER BY date
	" );

        return $stats;
    }

    private function get_post_of_category_sorted_by_date( $category_id ) {
        global $wpdb;

        $stats = $wpdb->get_results( "
            SELECT DATE_FORMAT( post_date, '%Y-%m-%d' ) AS date, COUNT( post_date ) AS number
            FROM {$wpdb->prefix}posts p
            LEFT OUTER JOIN {$wpdb->prefix}term_relationships r ON r.object_id = p.ID
            WHERE post_status = 'publish'
            AND post_type = 'post'
            AND term_taxonomy_id = $category_id
            GROUP BY DATE_FORMAT( post_date, '%Y-%m-%d' )
            ORDER BY date
	" );

        return $stats;
    }

    private function get_post_with_mcl_number_of_category_sorted_by_month( $category_id ) {
        global $wpdb;

        $stats = $wpdb->get_results( "
            SELECT DATE_FORMAT( post_date, '%Y-%m' ) AS date, SUM( meta_value ) AS number
            FROM {$wpdb->prefix}posts p
            LEFT OUTER JOIN {$wpdb->prefix}term_relationships r ON r.object_id = p.ID
            LEFT OUTER JOIN {$wpdb->prefix}postmeta m ON m.post_id = p.ID
            WHERE post_status = 'publish'
            AND post_type = 'post'
            AND meta_key = 'mcl_number'
            AND term_taxonomy_id = $category_id
            GROUP BY DATE_FORMAT( post_date, '%Y-%m' )
            ORDER BY date
	" );

        return $stats;
    }

    private function get_post_of_category_sorted_by_month( $category_id ) {
        global $wpdb;

        $stats = $wpdb->get_results( "
            SELECT DATE_FORMAT( post_date, '%Y-%m' ) AS date, COUNT( post_date ) AS number
            FROM {$wpdb->prefix}posts p
            LEFT OUTER JOIN {$wpdb->prefix}term_relationships r ON r.object_id = p.ID
            WHERE post_status = 'publish'
            AND post_type = 'post'
            AND term_taxonomy_id = $category_id
            GROUP BY DATE_FORMAT( post_date, '%Y-%m' )
            ORDER BY date
	" );

        return $stats;
    }

    private function get_mcl_number_of_category( $category_id ) {
        global $wpdb;

        $stats = $wpdb->get_results( "
            SELECT SUM( meta_value ) AS number
            FROM {$wpdb->prefix}posts p
            LEFT OUTER JOIN {$wpdb->prefix}term_relationships r ON r.object_id = p.ID
            LEFT OUTER JOIN {$wpdb->prefix}postmeta m ON m.post_id = p.ID
            WHERE post_status = 'publish'
            AND post_type = 'post'
            AND meta_key = 'mcl_number'
            AND term_taxonomy_id = $category_id
	" );

        return $stats[0]->number;
    }

    private function get_posts_of_category( $category_id ) {
        global $wpdb;

        $stats = $wpdb->get_results( "
            SELECT COUNT(*) AS number
            FROM {$wpdb->prefix}posts p
            LEFT OUTER JOIN {$wpdb->prefix}term_relationships r ON r.object_id = p.ID
            WHERE post_status = 'publish'
            AND post_type = 'post'
            AND term_taxonomy_id = $category_id
	" );

        return $stats[0]->number;
    }

    private function get_tags_count_of_category( $category_id, $complete ) {
        global $wpdb;

        $stats = $wpdb->get_results( "
            SELECT count(*) as number
            FROM (
                SELECT
                    terms2.name AS name,
                    terms2.term_id AS tag_id,
                    t1.term_id AS cat_id
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
                    AND terms1.term_id = $category_id
                    AND t2.taxonomy = 'post_tag'
                    AND p2.post_status = 'publish'
                    AND p1.ID = p2.ID
                GROUP BY name
            ) AS temp
            LEFT JOIN {$wpdb->prefix}mcl_complete AS mcl ON temp.tag_id = mcl.tag_id AND temp.cat_id = mcl.cat_id
            WHERE
                IFNULL(mcl.complete, 0) = $complete
        " );

        return $stats[0]->number;
    }

}

?>