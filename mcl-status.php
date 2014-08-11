<?php

function mcl_status() {
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

add_shortcode('mcl', 'mcl_status');

?>