<?php

add_shortcode( 'mcl-stats', 'mcl_statistics' );

function mcl_statistics() {
    date_default_timezone_set( get_option( 'timezone_string' ) );

    $current_date = date( 'Y-m-d' );

    $start_date = date( 'Y-m-d', strtotime( "-" . get_option( 'mcl_settings_statistics_number_of_days' ) . " day", strtotime( $current_date ) ) );

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
    $categories = get_categories( "exclude=" . get_option( 'mcl_settings_statistics_exclude_category' ) );

    $data = array();

    foreach ( $categories as $category ) {
        if ( get_option( 'mcl_settings_statistics_mcl_number' ) == "1" ) {
            $stats = get_post_with_mcl_number_of_category_sorted_by_date( $category->term_id );
        } else {
            $stats = get_post_of_category_sorted_by_date( $category->term_id );
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

    for ( $i = 0; $i < get_option( 'mcl_settings_statistics_number_of_months' ); $i++ ) {
        $month = date( 'Y-m', strtotime( "-" . $i . " month", strtotime( date( 'Y-m' ) ) ) );
        array_push( $monthly_dates, $month );
    }

    $data_month = array();

    foreach ( $categories as $category ) {
        if ( get_option( 'mcl_settings_statistics_mcl_number' ) == "1" ) {
            $stats = get_post_with_mcl_number_of_category_sorted_by_month( $category->term_id );
        } else {
            $stats = get_post_of_category_sorted_by_month( $category->term_id );
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

    $html = "
    <script type=\"text/javascript\" src=\"https://www.google.com/jsapi\"></script>
    <script type=\"text/javascript\">

    // Load the Visualization API and the piechart package.
    google.load('visualization', '1.0', {
        'packages': ['corechart']
    });

    // Set a callback to run when the Google Visualization API is loaded.
    google.setOnLoadCallback(drawDailyChart);
    google.setOnLoadCallback(drawMonthlyChart);

    // Callback that creates and populates a data table,
    // instantiates the pie chart, passes in the data and
    // draws it.
    function drawDailyChart() {
        // Some raw data (not necessarily accurate)
        var data = google.visualization.arrayToDataTable([
            ['Date', ";

    foreach ( $categories as $category ) {
        $html .= "'{$category->name}'";

        if ( end( $categories ) != $category ) {
            $html .= ", ";
        }
    }

    $html .= "],
";

    for ( $i = 0; $i < count( $daily_dates ); $i++ ) {
        $date = DateTime::createFromFormat( 'Y-m-d', $daily_dates[$i] );

        $html .= "            ['{$date->format( get_option( 'mcl_settings_statistics_daily_date_format' ) )}', ";

        foreach ( $categories as $category ) {
            $html .= "{$data[$category->name][$i]}";

            if ( end( $categories ) != $category ) {
                $html .= ", ";
            }
        }

        $html .= "]";

        if ( $i != count( $daily_dates ) - 1 ) {
            $html .= ",
";
        }
    }

    $html .= "
        ]);

        var options = {
            " . get_option( 'mcl_settings_statistics_google_charts_daily_options' ) . "
        };

        var chart = new google.visualization.BarChart(document.getElementById('daily_chart_div'));
        chart.draw(data, options);
    }
    
    function drawMonthlyChart() {
        // Some raw data (not necessarily accurate)
        var data = google.visualization.arrayToDataTable([
            ['Date', ";

    foreach ( $categories as $category ) {
        $html .= "'{$category->name}'";

        if ( end( $categories ) != $category ) {
            $html .= ", ";
        }
    }

    $html .= "],
";

    for ( $i = 0; $i < count( $monthly_dates ); $i++ ) {
        $date = DateTime::createFromFormat( 'Y-m', $monthly_dates[$i] );

        $html .= "            ['{$date->format( get_option( 'mcl_settings_statistics_monthly_date_format' ) )}', ";

        foreach ( $categories as $category ) {
            $html .= "{$data_month[$category->name][$i]}";

            if ( end( $categories ) != $category ) {
                $html .= ", ";
            }
        }

        $html .= "]";

        if ( $i != count( $daily_dates ) - 1 ) {
            $html .= ",
";
        }
    }

    $html .= "
        ]);

        var options = {
            " . get_option( 'mcl_settings_statistics_google_charts_monthly_options' ) . "
        };

        var chart = new google.visualization.BarChart(document.getElementById('monthly_chart_div'));
        chart.draw(data, options);
    }

    jQuery(document).ready(function($) {
        $(window).resize(function() {
            drawDailyChart();
            drawMonthlyChart();
        });
    });
</script>

    <div>
        <a href=\"#daily-consumption-chart\">Täglicher Konsum</a> | <a href=\"#monthly-consumption-chart\">Monatlicher Konsum</a> | <a href=\"#average-consumption\">Durchschnittlicher Konsum</a> | <a href=\"#consumption-count\">Konsum Menge</a>
    </div>

    <h4 id=\"daily-consumption-chart\">Täglicher Konsum</h4><hr />
    <div id=\"daily_chart_div\"></div>
    
    <h4 id=\"monthly-consumption-chart\">Monatlicher Konsum</h4><hr />
    <div id=\"monthly_chart_div\"></div>

    <h4 id=\"average-consumption\">Durchschnittlicher Konsum</h4><hr />
    <table border=\"1\"><col width=\"98%\"><col width=\"1%\">
    <tr>
        <th>Kategorie</th>
        <th nowrap>&#216</th>
    </tr>";

    $date_first_post = new DateTime( get_date_of_first_post() );
    $date_current = new DateTime( date( 'Y-m-d' ) );

    $number_of_days = $date_current->diff( $date_first_post )->format( "%a" ) + 1;

    $average_all = 0;

    foreach ( $categories as $category ) {
        if ( get_option( 'mcl_settings_statistics_mcl_number' ) == "1" ) {
            $average = round( get_mcl_number_of_category( $category->term_id ) / $number_of_days, 2 );
        } else {
            $average = round( get_posts_of_category( $category->term_id ) / $number_of_days, 2 );
        }

        $average_all += $average;

        $html .= "<tr><td>{$category->name}</td><td nowrap>{$average}</td></tr>";
    }

    $html .= "
        <tr>
            <th>Insgesamt</th>
            <th nowrap>{$average_all}</th>
        </tr>
    </table>
    <p>Durchschnittlich am Tag. Seit dem ersten Beitrag am {$date_first_post->format( get_option( 'mcl_settings_statistics_daily_date_format' ) )}.</p>

    <h4 id=\"consumption-count\">Konsum Menge</h4><hr />
    <table border=\"1\"><col width=\"98%\"><col width=\"1%\">
        <tr>
            <th>Kategorie</th>
            <th nowrap>#</th>
        </tr>";

    $count_all = 0;

    foreach ( $categories as $category ) {
        $count = get_tags_count_of_category( $category->term_id );
        $count_all += $count;

        $html .= "<tr><td>{$category->name}</td><td nowrap>{$count}</td></tr>";
    }

    $html .= "
        <tr>
            <th>Insgesamt</th>
            <th nowrap>{$count_all}</th>
        </tr>
    </table>";

    return $html;
}

function get_date_of_first_post() {
    global $wpdb;

    $min_date = $wpdb->get_results( "
        SELECT Min( DATE_FORMAT( post_date, '%Y-%m-%d' ) ) AS date
        FROM {$wpdb->prefix}posts
        WHERE post_status = 'publish'
        AND post_type = 'post'
	" );

    return $min_date[0]->date;
}

function get_post_with_mcl_number_of_category_sorted_by_date( $category_id ) {
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

function get_post_of_category_sorted_by_date( $category_id ) {
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

function get_post_with_mcl_number_of_category_sorted_by_month( $category_id ) {
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

function get_post_of_category_sorted_by_month( $category_id ) {
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

function get_mcl_number_of_category( $category_id ) {
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

function get_posts_of_category( $category_id ) {
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

function get_tags_count_of_category( $category_id ) {
    global $wpdb;

    $stats = $wpdb->get_results( "
        SELECT count(*) as number
        FROM (
            SELECT
                terms2.name AS name
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
    " );

    return $stats[0]->number;
}

?>