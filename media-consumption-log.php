<?php
/*
Plugin Name: Media Consumption Log
Plugin URI: https://github.com/hurik/wordpress-media-consumption-log
Description: Shows table with tags of each category and the latest post in it.
Version: 1.0.0
Author: Andreas Giemza
Author URI: http://www.andreasgiemza.de
License: MIT
*/
/*
The MIT License

Copyright 2014 Andreas Giemza (andreas@giemza.net).

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

// Source:
// http://wordpress.org/support/topic/get-tags-specific-to-category?replies=38
// by various people in this thread
function get_tags_by_category($category_id) {
    global $wpdb;
    
    $tags = $wpdb->get_results("
		SELECT 
			terms2.term_id AS tag_id,
			terms2.name AS name,
			COUNT(*) AS count,
			NULL AS tag_link,
			t2.taxonomy AS taxonomy
		FROM
			wp_posts AS p1
			LEFT JOIN wp_term_relationships AS r1 ON p1.ID = r1.object_ID
			LEFT JOIN wp_term_taxonomy AS t1 ON
				r1.term_taxonomy_id = t1.term_taxonomy_id
			LEFT JOIN wp_terms AS terms1 ON t1.term_id = terms1.term_id,
			wp_posts AS p2
			LEFT JOIN wp_term_relationships AS r2 ON p2.ID = r2.object_ID
			LEFT JOIN wp_term_taxonomy AS t2 ON
				r2.term_taxonomy_id = t2.term_taxonomy_id
			LEFT JOIN wp_terms AS terms2 ON t2.term_id = terms2.term_id
		WHERE
			t1.taxonomy = 'category'
			AND p1.post_status = 'publish'
			AND terms1.term_id = $category_id
			AND t2.taxonomy = 'post_tag'
			AND p2.post_status = 'publish'
			AND p1.ID = p2.ID
		GROUP BY name
		ORDER BY name
	");
    
    // Get the link of every tag
    foreach ($tags as $tag) {
        $tag->tag_link = get_tag_link($tag->tag_id);
    }
    
    // Replace the place holder with the commas
    if (!is_admin()) {
        $tags = comma_tags_filter($tags);
    }
    
    return $tags;
}

function get_posts_stats($category_id) {
    global $wpdb;
    
    $stats = $wpdb->get_results("
        SELECT DATE_FORMAT( post_date, '%Y-%m-%d' ) AS date, COUNT( post_date ) AS number
        FROM wp_posts p
        LEFT OUTER JOIN wp_term_relationships r ON r.object_id = p.ID
        WHERE post_status = 'publish'
        AND post_type = 'post'
        AND term_taxonomy_id = $category_id
        GROUP BY DATE_FORMAT( post_date, '%Y-%m-%d' )
        ORDER BY date
	");
    
    return $stats;
}

function get_latest_post_of_tag_in_category_data($tag_id, $category_id) {
    // Get post with the tag
    $posts = get_posts("tag_id={$tag_id}&category={$category_id}");
    
    // Get the last post
    $post = array_shift($posts);
    
    return $post;
}

function get_latest_post_of_tag_in_category($tag_id, $category_id) {
    // Get the last post data
    $post = get_latest_post_of_tag_in_category_data($tag_id, $category_id);
    
    // Explode the title
    $title_exploded = explode(' - ', $post->post_title);
    
    // Get the last part, so we have the chapter/episode/...
    $status = array_pop($title_exploded);
    
    // Get link
    $link = get_permalink($post->ID);
    
    return "<a href='{$link}' title='{$post->post_title}'>{$status}</a>";
}

function media_consumption_log() {
    // Get the categories
    $categories = get_categories('exclude=45,75');
    
    // Group the data
    $data       = array();
    $data_count = array();
    foreach ($categories as $category) {
        // Get the tags of the category
        $tags                           = get_tags_by_category($category->term_id);
        $data_count[$category->term_id] = count($tags);
        
        // Group the tags by the first letter
        foreach ($tags as $tag) {
            
            // Tags which start with a number get their own group #
            if (preg_match('/^[a-z]/i', trim($tag->name[0]))) {
                $data[$category->term_id][$tag->name[0]][] = $tag;
            } else {
                $data[$category->term_id]['#'][] = $tag;
            }
        }
    }
    
    // Create categories navigation
    $html = "<table border=\"1\">";
    foreach ($categories as $category) {
        $html .= "<tr><td><div><strong><a href=\"#mediastatus-";
        $html .= "{$category->slug}\">{$category->name}</a></strong>";
        $html .= "</tr></td>";
        $html .= "<tr><td>";
        foreach (array_keys($data[$category->term_id]) as $key) {
            $html .= "<a href=\"#mediastatus-{$category->slug}-";
            $html .= strtolower($key) . "\">{$key}</a>";
            if ($key != end((array_keys($data[$category->term_id])))) {
                $html .= " | ";
            }
        }
        
        $html .= "</tr></td>";
    }
    
    $html .= "</table>";
    
    // Create the tables
    foreach ($categories as $category) {
        // Category header
        $html .= "<h4 id=\"mediastatus-{$category->slug}\">{$category->name}";
        $html .= " ({$data_count[$category->term_id]})</h4><hr />";
        
        // Create the navigation
        $html .= "<div>";
        foreach (array_keys($data[$category->term_id]) as $key) {
            $html .= "<a href=\"#mediastatus-{$category->slug}-";
            $html .= strtolower($key) . "\">{$key}</a>";
            if ($key != end((array_keys($data[$category->term_id])))) {
                $html .= " | ";
            }
        }
        
        $html .= "</div><br />";
        
        // Table
        $html .= "<table border=\"1\"><col width=\"98%\"><col width=\"1%\">";
        $html .= "<col width=\"1%\">";
        foreach (array_keys($data[$category->term_id]) as $key) {
            $html .= "<tr><th colspan=\"3\"><div id=\"mediastatus-";
            $html .= "{$category->slug}-" . strtolower($key) . "\">{$key}";
            $html .= " (" . count($data[$category->term_id][$key]) . ")";
            $html .= "</div></th></tr>";
            $html .= "<tr><th>Name</th><th nowrap>#</th>";
            $html .= "<th nowrap>Kapitel/Folge</th></tr>";
            foreach ($data[$category->term_id][$key] as $tag) {
                $last_post_data = get_latest_post_of_tag_in_category($tag->tag_id, $category->term_id);
                
                if (empty($last_post_data)) {
                    continue;
                }
                
                $name = htmlspecialchars($tag->name);
                $name = str_replace("&amp;", "&", $name);
                
                $html .= "<tr><td><a href=\"{$tag->tag_link}\" title=\"";
                $html .= "{$name}\">{$name}</a></td><th nowrap>{$tag->count}";
                $html .= "</th><td nowrap>{$last_post_data}</td></tr>";
            }
        }
        
        $html .= "</table>";
    }
    
    return $html;
}

add_shortcode('mcl', 'media_consumption_log');

// Source:
// http://blog.foobored.com/all/wordpress-tags-with-commas/
// by foo bored
// filter for tags with comma
//  replace '--' with ', ' in the output - allow tags with comma this way

if (!is_admin()) { // make sure the filters are only called in the frontend
    function comma_tag_filter($tag_arr) {
        $tag_arr_new = $tag_arr;
        if ($tag_arr->taxonomy == 'post_tag' && strpos($tag_arr->name, '--')) {
            $tag_arr_new->name = str_replace('--', ', ', $tag_arr->name);
        }
        
        return $tag_arr_new;
    }
    
    add_filter('get_post_tag', 'comma_tag_filter');
    
    function comma_tags_filter($tags_arr) {
        $tags_arr_new = array();
        foreach ($tags_arr as $tag_arr) {
            $tags_arr_new[] = comma_tag_filter($tag_arr);
        }
        
        return $tags_arr_new;
    }
    
    add_filter('get_terms', 'comma_tags_filter');
    add_filter('get_the_terms', 'comma_tags_filter');
}

/** Step 2 (from text above). */
add_action('admin_menu', 'mcl_plugin_menu');

/** Step 1. */
function mcl_plugin_menu() {
    add_posts_page('Media Consumption Log - Quick Post', 'MCL - Quick Post', 'manage_options', 'mcl', 'mcl_plugin_options');
}

/** Step 3. */
function mcl_plugin_options() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    
    // Get the categories
    $categories = get_categories('exclude=45,75');
    
    // Group the data
    $data       = array();
    $data_count = array();
    foreach ($categories as $category) {
        // Get the tags of the category
        $tags                           = get_tags_by_category($category->term_id);
        $data_count[$category->term_id] = count($tags);
        
        // Group the tags by the first letter
        foreach ($tags as $tag) {
            
            // Tags which start with a number get their own group #
            if (preg_match('/^[a-z]/i', trim($tag->name[0]))) {
                $data[$category->term_id][$tag->name[0]][] = $tag;
            } else {
                $data[$category->term_id]['#'][] = $tag;
            }
        }
    }
    
    // Create categories navigation
    $cat_nav_html = "";
    
    foreach ($categories as $category) {
        $cat_nav_html .= "<tr><th><div><a href=\"#mediastatus-";
        $cat_nav_html .= "{$category->slug}\">{$category->name}</a>";
        $cat_nav_html .= "</tr></th>";
        $cat_nav_html .= "<tr><td>";
        foreach (array_keys($data[$category->term_id]) as $key) {
            $cat_nav_html .= "<a href=\"#mediastatus-{$category->slug}-";
            $cat_nav_html .= strtolower($key) . "\">{$key}</a>";
            if ($key != end((array_keys($data[$category->term_id]))))
                $cat_nav_html .= " | ";
        }
        
        $cat_nav_html .= "</tr></td>";
    }
    
    $lists_html = "";
    
    // Create the tables
    foreach ($categories as $category) {
        
        // Category header
        $lists_html .= "<h3 id=\"mediastatus-{$category->slug}\">{$category->name}";
        $lists_html .= " ({$data_count[$category->term_id]})</h3><hr />";
        
        // Create the navigation
        $lists_html .= "<div>";
        foreach (array_keys($data[$category->term_id]) as $key) {
            $lists_html .= "<a href=\"#mediastatus-{$category->slug}-";
            $lists_html .= strtolower($key) . "\">{$key}</a>";
            if ($key != end((array_keys($data[$category->term_id]))))
                $lists_html .= " | ";
        }
        
        $lists_html .= "</div><br />";
        
        // Table
        $lists_html .= "<table class=\"widefat fixed\">";
        foreach (array_keys($data[$category->term_id]) as $key) {
            $lists_html .= "<tr><th colspan=\"2\"><div id=\"mediastatus-";
            $lists_html .= "{$category->slug}-" . strtolower($key) . "\">{$key}";
            $lists_html .= " (" . count($data[$category->term_id][$key]) . ")";
            $lists_html .= "</div></th></tr>";
            $lists_html .= "<tr><th>Next Post</th><th>Last Post</th></tr>";
            foreach ($data[$category->term_id][$key] as $tag) {
                $last_post_data = get_latest_post_of_tag_in_category_data($tag->tag_id, $category->term_id);
                
                if (empty($last_post_data))
                    continue;
                
                $title = trim($last_post_data->post_title);
                $title = preg_replace("/[A-Z0-9]+ bis /", "", $title);
                $title = preg_replace("/[A-Z0-9]+ und /", "", $title);
                
                /*if (preg_match('/' . preg_quote(" 9", '/') . '$/', $title)) {
                $title = str_replace ( " 9", " 10" , $title);
                } else {
                $title++;
                }*/
                
                $title_explode = explode(' ', $title);
                $number        = end($title_explode);
                
                if (is_numeric($number)) {
                    $number = floatval($number);
                    $number++;
                    $number = floor($number);
                }
                
                if (preg_match('/[SE]/', $number)) {
                    $number++;
                }
                
                $title = substr($title, 0, strrpos($title, " "));
                
                $title .= " {$number}";
                
                $link = get_permalink($last_post_data->ID);
                
                $lists_html .= "<tr><td><a href=\"post-new.php?post_title=";
                $lists_html .= "{$title}&tag={$tag->tag_id}";
                $lists_html .= "&category={$category->term_id}\" title=\"";
                $lists_html .= "{$title}\">{$title}</a></td><td><a ";
                $lists_html .= "href='{$link}' title='";
                $lists_html .= "{$last_post_data->post_title}'>";
                $lists_html .= "{$last_post_data->post_title}</a></td></tr>";
            }
        }
        
        $lists_html .= "</table>";
    }
    
?>
	<div class="wrap">
        <h2>Media Consumption Log - Quick Post</h2>
        
        <h3>Navigation</h3>
        <table class="widefat fixed">
        <?php
    echo $cat_nav_html;
?>
        </table>
        
        <?php
    echo $lists_html;
?>
	</div>	
	<?php
}

// Source:
// http://wordpress.stackexchange.com/a/134711
// by Milo
function mcl_load_post_new() {
    if (array_key_exists('tag', $_REQUEST)) {
        add_action('wp_insert_post', 'mcl_insert_post_tag');
    }
    
    if (array_key_exists('category', $_REQUEST)) {
        add_action('wp_insert_post', 'mcl_insert_post_category');
    }
}

add_filter('load-post-new.php', 'mcl_load_post_new');

function mcl_insert_post_tag($post_id) {
    wp_set_post_tags($post_id, get_tag($_REQUEST['tag'])->name);
}

function mcl_insert_post_category($post_id) {
    wp_set_post_categories($post_id, array(
        $_REQUEST['category']
    ));
}

// Source:
// http://stanislav.it/how-to-add-a-custom-button-in-wordpress-admin-bar/
// by Stanislav Kostadinov
function mcl_admin_bar_button($wp_admin_bar) {
    $args = array(
        'id' => 'mcl_admin_bar_button',
        'title' => 'MCL - Quick Post',
        'href' => admin_url("edit.php?page=mcl"),
        'meta' => array(
            'class' => 'mcl_admin_bar_button_class'
        )
    );
    $wp_admin_bar->add_node($args);
}

add_action('admin_bar_menu', 'mcl_admin_bar_button', 75);



function mcl_chart() {
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

add_shortcode('mcl-chart', 'mcl_chart');

?>