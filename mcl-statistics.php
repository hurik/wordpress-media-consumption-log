<?php

function mcl_stats() {
    // Get the categories
    $categories = get_categories();
    
    $current_date = date('Y-m-d');
    $min_date     = date('Y-m-d', strtotime("-9 day", strtotime($current_date)));
    
    $dates = array();
    
    $i = $min_date;
    while (true) {
        array_push($dates, $i);
        $i = date('Y-m-d', strtotime("+1 day", strtotime($i)));
        
        if ($i == $current_date) {
            array_push($dates, $i);
            break;
        }
    }
    
    $html = "

<script type=\"text/javascript\" src=\"http://xn--inderhlle-57a.de/wp-content/plugins/media-consumption-log/Chart.min.js\"></script>

<style type=\"text/css\">
#fork {
    position: absolute;
    top: 0;
    right: 0;
    border: 0;
}

.legend {
    width: 10em;
    border: 1px solid black;
}

.legend .title {
    display: block;
    margin: 0.5em;
    border-style: solid;
    border-width: 0 0 0 1em;
    padding: 0 0.3em;
}
</style>

<div>
    <canvas id=\"myChart\" width=\"500\" height=\"500\"></canvas>
    <div id=\"myChartLegend\"></div>
</div>

<script type=\"text/javascript\">
function legend(parent, data) {
    parent.className = 'legend';
    var datas = data.hasOwnProperty('datasets') ? data.datasets : data;

    // remove possible children of the parent
    while(parent.hasChildNodes()) {
        parent.removeChild(parent.lastChild);
    }

    datas.forEach(function(d) {
        var title = document.createElement('span');
        title.className = 'title';
        title.style.borderColor = d.hasOwnProperty('strokeColor') ? d.strokeColor : d.color;
        title.style.borderStyle = 'solid';
        parent.appendChild(title);

        var text = document.createTextNode(d.title);
        title.appendChild(text);
    });
}

var data = {
    labels: [";
    
    foreach ($dates as $date) {
        $html .= "\"{$date}\"";
        
        if ($date != end($dates)) {
            $html .= ", ";
        }
    }
    
    $html .= "],
    datasets: [";
    
    $colors = array(
        "84,109,255",
        "255,51,15",
        "127,255,136",
        "255,225,61",
        "246,79,255",
        "124,255,237",
        "228,112,255"
    );
    
    $col_num = 0;
    
    foreach ($categories as $category) {
        $html .= "{
            title : \"{$category->name}\",
            fillColor: \"rgba({$colors[$col_num]},0.5)\",
            strokeColor: \"rgba({$colors[$col_num]},0.8)\",
            highlightFill: \"rgba({$colors[$col_num]},0.75)\",
            highlightStroke: \"rgba({$colors[$col_num]},1)\",
            data: [";
        
        $stats = get_posts_stats($category->term_id);
        
        foreach ($dates as $date) {
            $found = 0;
            
            foreach ($stats as $stat) {
                if ($stat->date == $date) {
                    $found = $stat->number;
                    break;
                }
            }
            
            $html .= "{$found}";
            
            if ($date != end($dates)) {
                $html .= ", ";
            }
        }
        
        $html .= "]
        }";
        
        if ($category != end($categories)) {
            $html .= ",
            ";
        }
        
        $col_num++;
    }
    $html .= "    ]
};

var options = {
    //Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
    scaleBeginAtZero : true,

    //Boolean - Whether grid lines are shown across the chart
    scaleShowGridLines : true,

    //String - Colour of the grid lines
    scaleGridLineColor : \"rgba(0,0,0,.05)\",

    //Number - Width of the grid lines
    scaleGridLineWidth : 1,

    //Boolean - If there is a stroke on each bar
    barShowStroke : true,

    //Number - Pixel width of the bar stroke
    barStrokeWidth : 2,

    //Number - Spacing between each of the X value sets
    barValueSpacing : 5,

    //Number - Spacing between data sets within X values
    barDatasetSpacing : 1,

    //String - A legend template
    legendTemplate : \"<ul class=\\\"<%=name.toLowerCase()%>-legend\\\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\\\"background-color:<%=datasets[i].lineColor%>\\\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>\"

};

var ctx = document.getElementById(\"myChart\").getContext(\"2d\");
var myBarChart = new Chart(ctx).Bar(data, options);

legend(document.getElementById(\"myChartLegend\"), data)
</script>

<br />

";
    
    return $html;
}

add_shortcode('mcl-stats', 'mcl_stats');

?>