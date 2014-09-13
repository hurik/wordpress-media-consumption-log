<?php

add_shortcode( 'mcl-stats', 'mcl_statistics' );

function mcl_statistics() {
    date_default_timezone_set( get_option( 'timezone_string' ) );

    $current_date = date( 'Y-m-d' );

    $start_date = date( 'Y-m-d', strtotime( "-" . get_option( 'mcl_settings_statistics_number_of_days' ) . " day", strtotime( $current_date ) ) );

    $dates = array();

    $i = $current_date;

    while ( true ) {
        array_push( $dates, $i );
        $i = date( 'Y-m-d', strtotime( "-1 day", strtotime( $i ) ) );

        if ( $i == $start_date ) {
            array_push( $dates, $i );
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

        foreach ( $dates as $date ) {
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

    $html = "
    <script type=\"text/javascript\" src=\"https://www.google.com/jsapi\"></script>
    <script type=\"text/javascript\">

    // Load the Visualization API and the piechart package.
    google.load('visualization', '1.0', {
        'packages': ['corechart']
    });

    // Set a callback to run when the Google Visualization API is loaded.
    google.setOnLoadCallback(drawChart);

    // Callback that creates and populates a data table,
    // instantiates the pie chart, passes in the data and
    // draws it.
    function drawChart() {
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

    for ( $i = 0; $i < count( $dates ); $i++ ) {
        $date = DateTime::createFromFormat( 'Y-m-d', $dates[$i] );

        $html .= "            ['{$date->format( get_option( 'mcl_settings_statistics_date_format' ) )}', ";

        foreach ( $categories as $category ) {
            $html .= "{$data[$category->name][$i]}";

            if ( end( $categories ) != $category ) {
                $html .= ", ";
            }
        }

        $html .= "]";

        if ( $i != count( $dates ) - 1 ) {
            $html .= ",
";
        }
    }

    $html .= "
        ]);

        var options = {
            " . get_option( 'mcl_settings_statistics_google_charts_options' ) . "
        };

        var chart = new google.visualization.BarChart(document.getElementById('chart_div'));
        chart.draw(data, options);

    }

    jQuery(document).ready(function($) {
        $(window).resize(function() {
            drawChart();
        });
    });
</script>

    <div>
        <a href=\"#consumption-chart\">Konsum Diagramm</a> | <a href=\"#average-consumption\">Durchschnittlicher Konsum</a> | <a href=\"#consumption-count\">Konsum Menge</a>
    </div>

    <h4 id=\"consumption-chart\">Konsum Diagramm</h4><hr />
    <div id=\"chart_div\"></div>

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
    <p>Durchschnittlich am Tag. Seit dem ersten Beitrag am {$date_first_post->format( get_option( 'mcl_settings_statistics_date_format' ) )}.</p>

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
        FROM wp_posts
        WHERE post_status = 'publish'
        AND post_type = 'post'
	" );

    return $min_date[0]->date;
}

function get_post_with_mcl_number_of_category_sorted_by_date( $category_id ) {
    global $wpdb;

    $stats = $wpdb->get_results( "
        SELECT DATE_FORMAT( post_date, '%Y-%m-%d' ) AS date, SUM( meta_value ) AS number
        FROM wp_posts p
        LEFT OUTER JOIN wp_term_relationships r ON r.object_id = p.ID
        LEFT OUTER JOIN wp_postmeta m ON m.post_id = p.ID
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
        FROM wp_posts p
        LEFT OUTER JOIN wp_term_relationships r ON r.object_id = p.ID
        WHERE post_status = 'publish'
        AND post_type = 'post'
        AND term_taxonomy_id = $category_id
        GROUP BY DATE_FORMAT( post_date, '%Y-%m-%d' )
        ORDER BY date
	" );

    return $stats;
}

function get_mcl_number_of_category( $category_id ) {
    global $wpdb;

    $stats = $wpdb->get_results( "
        SELECT SUM( meta_value ) AS number
        FROM wp_posts p
        LEFT OUTER JOIN wp_term_relationships r ON r.object_id = p.ID
        LEFT OUTER JOIN wp_postmeta m ON m.post_id = p.ID
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
        FROM wp_posts p
        LEFT OUTER JOIN wp_term_relationships r ON r.object_id = p.ID
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
                wp_posts AS p1
                LEFT JOIN wp_term_relationships AS r1 ON p1.ID = r1.object_ID
                LEFT JOIN wp_term_taxonomy AS t1 ON r1.term_taxonomy_id = t1.term_taxonomy_id
                LEFT JOIN wp_terms AS terms1 ON t1.term_id = terms1.term_id,
                wp_posts AS p2
                LEFT JOIN wp_term_relationships AS r2 ON p2.ID = r2.object_ID
                LEFT JOIN wp_term_taxonomy AS t2 ON r2.term_taxonomy_id = t2.term_taxonomy_id
                LEFT JOIN wp_terms AS terms2 ON t2.term_id = terms2.term_id
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