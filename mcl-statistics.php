<?php

add_shortcode( 'mcl-stats', 'mcl_statistics' );

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

    for ( $i = 0; $i < MclSettingsHelper::getStatisticsNumberOfMonths(); $i++ ) {
        $month = date( 'Y-m', strtotime( "-" . $i . " month", strtotime( date( 'Y-m' ) ) ) );
        array_push( $monthly_dates, $month );
    }

    $data_month = array();

    foreach ( $categories as $category ) {
        if ( MclSettingsHelper::isStatisticsMclNumber() ) {
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

    $html .= ", { role: 'annotation' }],
";

    for ( $i = 0; $i < count( $daily_dates ); $i++ ) {
        $date = DateTime::createFromFormat( 'Y-m-d', $daily_dates[$i] );

        $html .= "            ['{$date->format( MclSettingsHelper::getStatisticsDailyDateFormat() )}', ";

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
            $html .= ",
";
        }
    }

    $html .= "
        ]);

        var options = {
            " . MclSettingsHelper::getStatisticsGoogleChartsDailyOptions() . "
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

    $html .= ", { role: 'annotation' }],
";

    for ( $i = 0; $i < count( $monthly_dates ); $i++ ) {
        $date = DateTime::createFromFormat( 'Y-m', $monthly_dates[$i] );

        $html .= "            ['{$date->format( MclSettingsHelper::getStatisticsMonthlyDateFormat() )}', ";

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
            $html .= ",
";
        }
    }

    $html .= "
        ]);

        var options = {
            " . MclSettingsHelper::getStatisticsGoogleChartsMonthlyOptions() . "
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
        <ul>
            <li><a href=\"#daily-consumption-chart\">" . __( 'Daily consumption', 'media-consumption-log' ) . "</a></li>
            <li><a href=\"#monthly-consumption-chart\">" . __( 'Monthly consumption', 'media-consumption-log' ) . "</a></li>
            <li><a href=\"#average-consumption\">" . __( 'Average consumption', 'media-consumption-log' ) . "</a></li>
            <li><a href=\"#total-consumption\">" . __( 'Total consumption', 'media-consumption-log' ) . "</a></li>
            <li><a href=\"#consumption-count\">" . __( 'Consumption amount', 'media-consumption-log' ) . "</a></li>
        <ul>
    </div>

    <h4 id=\"daily-consumption-chart\">" . __( 'Daily consumption', 'media-consumption-log' ) . "</h4><hr />
    <div id=\"daily_chart_div\"></div>
    
    <h4 id=\"monthly-consumption-chart\">" . __( 'Monthly consumption', 'media-consumption-log' ) . "</h4><hr />
    <div id=\"monthly_chart_div\"></div>

    <h4 id=\"average-consumption\">" . __( 'Average consumption', 'media-consumption-log' ) . "</h4><hr />
    <table border=\"1\"><colgroup><col width=\"98%\"><col width=\"1%\"><col width=\"1%\"></colgroup>
    <tr>
        <th>" . __( 'Category', 'media-consumption-log' ) . "</th>
        <th nowrap>&#216</th>
        <th nowrap>" . __( 'Unit', 'media-consumption-log' ) . "</th>
    </tr>";

    $date_first_post = new DateTime( get_date_of_first_post() );
    $since_string = str_replace( '%DATE', $date_first_post->format( MclSettingsHelper::getStatisticsDailyDateFormat() ), __( 'Average a day, since the first post on the %DATE.', 'media-consumption-log' ) );
    $date_current = new DateTime( date( 'Y-m-d' ) );

    $number_of_days = $date_current->diff( $date_first_post )->format( "%a" ) + 1;

    $average_all = 0;

    foreach ( $categories as $category ) {
        if ( MclSettingsHelper::isStatisticsMclNumber() ) {
            $average = round( get_mcl_number_of_category( $category->term_id ) / $number_of_days, 2 );
        } else {
            $average = round( get_posts_of_category( $category->term_id ) / $number_of_days, 2 );
        }

        $average_all += $average;

        $unit = get_option( "mcl_unit_{$category->slug}" );
        if ( empty( $unit ) ) {
            $unit = $category->name;
        }

        $html .= "<tr><td>{$category->name}</td><td nowrap>{$average}</td><td nowrap>{$unit}</td></tr>";
    }

    $html .= "
        <tr>
            <th>" . __( 'Total', 'media-consumption-log' ) . "</th>
            <th nowrap>{$average_all}</th>
        </tr>
    </table>
    <p>{$since_string}</p>
        
    <h4 id=\"total-consumption\">" . __( 'Total consumption', 'media-consumption-log' ) . "</h4><hr />
    <table border=\"1\"><colgroup><col width=\"98%\"><col width=\"1%\"><col width=\"1%\"></colgroup>
    <tr>
        <th>" . __( 'Category', 'media-consumption-log' ) . "</th>
        <th nowrap>#</th>
        <th nowrap>" . __( 'Unit', 'media-consumption-log' ) . "</th>
    </tr>";

    $since_total_string = str_replace( '%DATE', $date_first_post->format( MclSettingsHelper::getStatisticsDailyDateFormat() ), __( 'Total comsumption, since the first post on the %DATE.', 'media-consumption-log' ) );
    $total_all = 0;

    foreach ( $categories as $category ) {
        if ( MclSettingsHelper::isStatisticsMclNumber() ) {
            $total = get_mcl_number_of_category( $category->term_id );
        } else {
            $total = get_posts_of_category( $category->term_id );
        }

        $total_all += $total;

        $unit = get_option( "mcl_unit_{$category->slug}" );
        if ( empty( $unit ) ) {
            $unit = $category->name;
        }

        $html .= "<tr><td>{$category->name}</td><td nowrap>{$total}</td><td nowrap>{$unit}</td></tr>";
    }

    $html .= "
        <tr>
            <th>" . __( 'Total', 'media-consumption-log' ) . "</th>
            <th nowrap>{$total_all}</th>
        </tr>
    </table>
    <p>{$since_total_string}</p>

    <h4 id=\"consumption-count\">" . __( 'Consumption amount', 'media-consumption-log' ) . "</h4><hr />
    <table border=\"1\"><colgroup><col width=\"97%\"><col width=\"1%\"><col width=\"1%\"><col width=\"1%\"></colgroup>
        <tr>
            <th>" . __( 'Category', 'media-consumption-log' ) . "</th>
            <th nowrap>" . __( 'Running', 'media-consumption-log' ) . "</th>
            <th nowrap>" . __( 'Complete', 'media-consumption-log' ) . "</th>
            <th nowrap>" . __( 'Total', 'media-consumption-log' ) . "</th>
        </tr>";

    $count_total_ongoing = 0;
    $count_total_complete = 0;
    $count_total = 0;

    foreach ( $categories as $category ) {
        $count_ongoing = get_tags_count_of_category( $category->term_id, 0 );
        $count_complete = get_tags_count_of_category( $category->term_id, 1 );

        $count_category_total = $count_ongoing + $count_complete;


        $count_total_ongoing += $count_ongoing;
        $count_total_complete += $count_complete;
        $count_total += $count_category_total;

        if ( $count_category_total == 0 ) {
            continue;
        }

        $cat_ids_status = explode( ",", MclSettingsHelper::getStatusExcludeCategory() );

        if ( in_array( $category->term_id, $cat_ids_status ) ) {
            $html .= "<tr><td colspan=\"3\">{$category->name}</td><td nowrap>{$count_category_total}</td></tr>";
        } else {
            $html .= "<tr><td>{$category->name}</td><td nowrap>{$count_ongoing}</td><td nowrap>{$count_complete}</td><td nowrap>{$count_category_total}</td></tr>";
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

    $html .= "
        <tr>
            <th nowrap>" . __( 'Total', 'media-consumption-log' ) . "</th>
            <th nowrap>{$count_total_ongoing}</th>
            <th nowrap>{$count_total_complete}</th>
            <th nowrap>{$count_total}</th>
        </tr>
    </table>
    <p>{$since_count_string}</p>";

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

function get_tags_count_of_category( $category_id, $complete ) {
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

?>