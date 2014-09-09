<?php

add_filter( 'load-post-new.php', 'mcl_load_mcl_number_in_new_post' );

function mcl_load_mcl_number_in_new_post() {
    add_action( 'wp_insert_post', 'mcl_insert_mcl_number_in_new_post' );
}

function mcl_insert_mcl_number_in_new_post( $post_id ) {
    add_post_meta( $post_id, 'mcl_number', '', true );
}

add_action( 'save_post', 'mcl_check_mcl_number_after_saving' );

function mcl_check_mcl_number_after_saving( $post_id ) {
    if ( get_post_status( $post_id ) == 'publish' ) {
        $mcl_number = get_post_meta( $post_id, 'mcl_number', true );

        // Check if already set
        if ( !empty( $mcl_number ) ) {
            return;
        }

        // Set it to one
        $mcl_number = 1;

        $post = get_post( $post_id );
        $title_ecplode = explode( ' - ', $post->post_title );
        $current_number = end( $title_ecplode );

        if ( count( $title_ecplode ) < 2 ) {
            // Do nothing
        } else if ( strpos( $current_number, ' und ' ) !== false ) {
            $mcl_number = 2;
        } else if ( strpos( $current_number, ' bis ' ) !== false ) {
            preg_match_all( '!\d+(?:\.\d+)?!', $current_number, $matches );

            if ( count( $matches[0] ) == 2 ) {
                $mcl_number = ceil( floatval( $matches[0][1] ) - floatval( $matches[0][0] ) + 1 );
            } else if ( count( $matches[0] ) == 4 ) {
                $mcl_number = ceil( floatval( $matches[0][3] ) - floatval( $matches[0][1] ) + 1 );
            }
        }

        update_post_meta( $post_id, 'mcl_number', $mcl_number );
    }
}

add_shortcode( 'mcl-stats', 'mcl_statistics' );

function mcl_statistics() {
    date_default_timezone_set( get_option( 'timezone_string' ) );

    $current_date = date( 'Y-m-d' );
    
    $start_date = get_option('mcl_settings_statistics_start_date');
    
    if (empty($start_date)) {
        $start_date = get_date_of_first_post();
    }

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
    $categories = get_categories();

    $data = array();

    foreach ( $categories as $category ) {
        $stats = get_post_with_mcl_number_of_category_sorted_by_date( $category->term_id );

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

        $html .= "            ['{$date->format( 'j.m.Y' )}', ";

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
            " . get_option('mcl_settings_statistics_google_charts_options') . "
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

    <div id=\"chart_div\"></div>
    <br />
    ";

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

?>