<?php

add_shortcode( 'mcl-stats', array( 'MclStatistics', 'build_statistics' ) );

class MclStatistics {

    static function build_statistics() {
        $data = MclStatisticsHelper::getData();

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

        for ( $i = 0; $i < MclSettingsHelper::getStatisticsNumberOfDays(); $i++ ) {
            $day = date( 'Y-m-d', strtotime( "-" . $i . " day", strtotime( date( 'Y-m-d' ) ) ) );
            array_push( $dates_daily, $day );
        }

        // Data array header
        foreach ( $data->stats as $categoryWithData ) {
            $html .= "'{$categoryWithData->name}'";

            if ( end( $data->stats ) != $categoryWithData ) {
                $html .= ", ";
            }
        }

        $html .= ", { role: 'annotation' }],";

        // Data array
        for ( $i = 0; $i < count( $dates_daily ); $i++ ) {
            $date = DateTime::createFromFormat( 'Y-m-d', $dates_daily[$i] );

            $html .= "\n      ['{$date->format( MclSettingsHelper::getStatisticsDailyDateFormat() )}', ";

            $total = 0;

            foreach ( $data->stats as $categoryWithData ) {
                $count = 0;

                foreach ( $categoryWithData->mcl_daily_data as $cat_count ) {
                    if ( $dates_daily[$i] == $cat_count->date ) {
                        $count = $cat_count->number;
                        break;
                    }
                }

                $total += $count;
                $html .= "{$count}";

                if ( end( $data->stats ) != $categoryWithData ) {
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

        $dates_monthly = array();

        for ( $i = 0; $i < MclSettingsHelper::getStatisticsNumberOfMonths(); $i++ ) {
            $month = date( 'Y-m', strtotime( "-" . $i . " month", strtotime( date( 'Y-m' ) ) ) );
            array_push( $dates_monthly, $month );
        }

        foreach ( $data->stats as $categoryWithData ) {
            $html .= "'{$categoryWithData->name}'";

            if ( end( $data->stats ) != $categoryWithData ) {
                $html .= ", ";
            }
        }

        $html .= ", { role: 'annotation' }],";

        for ( $i = 0; $i < count( $dates_monthly ); $i++ ) {
            $date = DateTime::createFromFormat( 'Y-m', $dates_monthly[$i] );

            $html .= "\n      ['{$date->format( MclSettingsHelper::getStatisticsMonthlyDateFormat() )}', ";

            $total = 0;

            foreach ( $data->stats as $categoryWithData ) {
                $count = 0;

                foreach ( $categoryWithData->mcl_monthly_data as $cat_count ) {
                    if ( $dates_monthly[$i] == $cat_count->date ) {
                        $count = $cat_count->number;
                        break;
                    }
                }

                $total += $count;
                $html .= "{$count}";

                if ( end( $data->stats ) != $categoryWithData ) {
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

        foreach ( $data->stats as $categoryWithData ) {
            $unit = MclUnits::get_unit_of_category( $categoryWithData );

            $html .= "\n  <tr>"
                    . "\n    <td>{$categoryWithData->name}</td>"
                    . "\n    <td nowrap>{$categoryWithData->mcl_consumption_total}</td>"
                    . "\n    <td nowrap>{$unit}</td>"
                    . "\n  </tr>";
        }

        $since_total_string = str_replace( '%DATE', $data->first_post_date->format( MclSettingsHelper::getStatisticsDailyDateFormat() ), __( 'Total comsumption, since the first post on the %DATE.', 'media-consumption-log' ) );

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

        foreach ( $data->stats as $categoryWithData ) {
            $unit = MclUnits::get_unit_of_category( $categoryWithData );

            $html .= "\n  <tr>"
                    . "\n    <td>{$categoryWithData->name}</td>"
                    . "\n    <td nowrap>" . number_format( $categoryWithData->mcl_consumption_average, 2 ) . "</td>"
                    . "\n    <td nowrap>{$unit}</td>"
                    . "\n  </tr>";
        }

        $since_string = str_replace( '%DATE', $data->first_post_date->format( MclSettingsHelper::getStatisticsDailyDateFormat() ), __( 'Average a day, since the first post on the %DATE.', 'media-consumption-log' ) );

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

        foreach ( $data->stats as $categoryWithData ) {
            if ( $categoryWithData->mcl_tags_count_total == 0 ) {
                continue;
            }

            $cat_ids_status = explode( ",", MclSettingsHelper::getStatusExcludeCategory() );

            if ( in_array( $categoryWithData->term_id, $cat_ids_status ) ) {
                $html .= "\n  <tr>"
                        . "\n    <td colspan=\"3\">{$categoryWithData->name}</td>"
                        . "\n    <td nowrap>{$categoryWithData->mcl_tags_count_total}</td>"
                        . "\n  </tr>";
            } else {
                $html .= "\n  <tr>"
                        . "\n    <td>{$categoryWithData->name}</td>"
                        . "\n    <td nowrap>{$categoryWithData->mcl_tags_count_ongoing}</td>"
                        . "\n    <td nowrap>{$categoryWithData->mcl_tags_count_complete}</td>"
                        . "\n    <td nowrap>{$categoryWithData->mcl_tags_count_total}</td>"
                        . "\n  </tr>";
            }
        }

        $categories_string = "";
        $second_to_last_cat = $data->stats[count( $data->stats ) - 2];
        $last_cat = end( $data->stats );

        foreach ( $data->stats as $categoryWithData ) {
            if ( $categoryWithData != $last_cat ) {
                $categories_string .= "{$categoryWithData->name}";

                if ( $categoryWithData != $second_to_last_cat ) {
                    $categories_string .= ", ";
                }
            } else {
                $categories_string .= " " . __( 'and', 'media-consumption-log' ) . " {$categoryWithData->name}";
            }
        }

        $since_count_string = str_replace( '%DATE', $data->first_post_date->format( MclSettingsHelper::getStatisticsDailyDateFormat() ), __( 'Total count of different %CATEGORIES, since the first post on the %DATE.', 'media-consumption-log' ) );
        $since_count_string = str_replace( '%CATEGORIES', $categories_string, $since_count_string );

        $html .= "\n  <tr>"
                . "\n    <th nowrap>" . __( 'Total', 'media-consumption-log' ) . "</th>"
                . "\n    <th nowrap>{$data->tags_count_ongoing}</th>"
                . "\n    <th nowrap>{$data->tags_count_complete}</th>"
                . "\n    <th nowrap>{$data->tags_count_total}</th>"
                . "\n  </tr>"
                . "\n</table>"
                . "\n<p>{$since_count_string}</p>";

        return $html;
    }

}

?>