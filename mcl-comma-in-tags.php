<?php

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

?>