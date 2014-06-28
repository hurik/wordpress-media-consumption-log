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
    
    foreach ($tags as $tag) {
        $tag->tag_link = get_tag_link($tag->tag_id);
    }
    
    return $tags;
}

function get_latest_post_of_tag_in_category($tag_id, $category_id) {
    // Get post with the tag
    $posts          = get_posts("tag_id={$tag_id}&category={$category_id}");
    // Get the last post
    $post           = array_shift($posts);
    // Explode the title
    $title_exploded = explode(' - ', $post->post_title);
    // Get the last part, so we have the chapter/episode/...
    $status         = array_pop($title_exploded);
    // Get link
    $link           = get_permalink($post->ID);
    
    return "<a href='{$link}' title='{$post->post_title}'>{$status}</a>";
}

function media_consumption_log() {
    // Get the categories
    $categories = get_categories('exclude=45,75');
    
    // Group the data
    $data = array();
    foreach ($categories as $category) {
        // Get the tags of the category
        $tags = get_tags_by_category($category->term_id);
        
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
            
            if ($key != end((array_keys($data[$category->term_id]))))
                $html .= " | ";
        }
        
        $html .= "</tr></td>";
    }
    
    $html .= "</table>";
    
    // Create the tables
    foreach ($categories as $category) {
        // Category header
        $html .= "<h4 id=\"mediastatus-{$category->slug}\">";
        $html .= "{$category->name}</h4><hr />";
        
        // Create the navigation
        $html .= "<div>";
        
        foreach (array_keys($data[$category->term_id]) as $key) {
            $html .= "<a href=\"#mediastatus-{$category->slug}-";
            $html .= strtolower($key) . "\">{$key}</a>";
            
            if ($key != end((array_keys($data[$category->term_id]))))
                $html .= " | ";
        }
        
        $html .= "</div><br>";
        
        // Table
        $html .= "<table border=\"1\"><col width=\"98%\"><col width=\"1%\">";
        $html .= "<col width=\"1%\">";
        
        foreach (array_keys($data[$category->term_id]) as $key) {
            $html .= "<tr><th colspan=\"3\"><div id=\"mediastatus-";
            $html .= "{$category->slug}-" . strtolower($key) . "\">{$key}";
            $html .= "</div></th></tr>";
            
            $html .= "<tr><th>Name</th><th nowrap>#</th>";
            $html .= "<th nowrap>Kapitel/Folge</th></tr>";
            
            foreach ($data[$category->term_id][$key] as $tag) {
                $last_post_data = get_latest_post_of_tag_in_category(
                    $tag->tag_id,
                    $category->term_id);
                
                if (empty($last_post_data))
                    continue;
                
                $tag_filtered = apply_filters('get_post_tag', $tag);
                $name         = $tag_filtered = htmlspecialchars(
                    $tag_filtered->name);
                
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

?>