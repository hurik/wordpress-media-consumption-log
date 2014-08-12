<?php

// Source:
// http://wordpress.stackexchange.com/a/134711
// by Milo
function mcl_insert_mcl_number($post_id) {
    add_post_meta($post_id, 'mcl_number', '', true);
}

function mcl_show_mcl_number() {
    add_action('wp_insert_post', 'mcl_insert_mcl_number');
}

add_filter('load-post-new.php', 'mcl_show_mcl_number');


function mcl_check_mcl_number($post_id) {
    if (get_post_status($post_id) != 'auto-draft') {
        $mcl_number = get_post_meta($post_id, 'mcl_number', true);
        
        // Check if already set
        if (!empty($mcl_number)) {
            return;
        }
        
        // Set it to one
        $mcl_number = 1;
        
        $post           = get_post($post_id);
        $title_explode  = explode(' - ', $post->post_title);
        $current_number = end($title_explode);
        
        if (count($title_explode) < 2) {
            // Do nothing
        } else if (strpos($current_number, ' und ') !== false) {
            $mcl_number = 2;
        } else if (strpos($current_number, ' bis ') !== false) {
            preg_match_all('!\d+(?:\.\d+)?!', $current_number, $matches);
            
            if (count($matches[0]) == 2) {
                $mcl_number = ceil(floatval($matches[0][1]) - floatval($matches[0][0]) + 1);
            } else if (count($matches[0]) == 4) {
                $mcl_number = ceil(floatval($matches[0][3]) - floatval($matches[0][1]) + 1);
            }
        }
        
        update_post_meta($post_id, 'mcl_number', $mcl_number);
    }
}

add_action('save_post', 'mcl_check_mcl_number');


function mcl_stats() {
    date_default_timezone_set(get_option('timezone_string'));
    
    $current_date = date('Y-m-d');
    $min_date     = get_first_post_date();
    
    
    $dates = array();
    
    $i = $current_date;
    while (true) {
        array_push($dates, $i);
        $i = date('Y-m-d', strtotime("-1 day", strtotime($i)));
        
        if ($i == $min_date) {
            array_push($dates, $i);
            break;
        }
    }
    
    // Get the categories
    $categories = get_categories();
    
    $data = array();
    
    foreach ($categories as $category) {
        $stats = get_posts_stats_with_mcl_number($category->term_id);
        
        foreach ($dates as $date) {
            $found = 0;
            
            foreach ($stats as $stat) {
                if ($stat->date == $date) {
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
    
    foreach ($categories as $category) {
        $html .= "'{$category->name}'";
        
        if (end($categories) != $category) {
            $html .= ", ";
        }
    }
    
    $html .= "],
";
    
    for ($i = 0; $i < count($dates); $i++) {
        $date = DateTime::createFromFormat('Y-m-d', $dates[$i]);
        
        $html .= "            ['{$date->format('j.m.Y')}', ";
        
        foreach ($categories as $category) {
            $html .= "{$data[$category->name][$i]}";
            
            if (end($categories) != $category) {
                $html .= ", ";
            }
        }
        
        $html .= "]";
        
        if ($i != count($dates) - 1) {
            $html .= ",
";
        }
    }
    
    $html .= "
        ]);

        var options = {
            height: data.getNumberOfRows() * 15,
            legend: { position: 'top', maxLines: 4 },
            bar: { groupWidth: '75%' },
            chartArea:{left: 80, top: 80, width: '80%', height: '90%'},
            isStacked: true,
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

    <div id=\"chart_div\" style=\"width:100%\"></div>
    <br />
    ";
    
    return $html;
}

add_shortcode('mcl-stats', 'mcl_stats');

?>