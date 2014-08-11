<?php

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
        $stats = get_posts_stats($category->term_id);
        
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
    
    $html .= "],";
    
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
            height: data.getNumberOfRows() * 18,
            legend: { position: 'top', maxLines: 4 },
            bar: { groupWidth: '75%' },
            chartArea:{left: 100, top: 80, width: '70%', height: '90%'},
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