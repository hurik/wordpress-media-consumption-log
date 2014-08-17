<?php

function getAllTagsSortedByCategoryAndName($categories) {
    // Group the data
    $data = array();

    foreach ($categories as $category) {
        // Get the tags of the category
        $tags = getTagsOfCategory($category->term_id);

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
    
    return $data;
}

function getTagsOfCategory($category_id) {
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

function countTagsOfCategory($data, $categorie_id) {
    $count = 0;

    foreach (array_keys($data[$categorie_id]) as $key) {
        $count += count($data[$categorie_id][$key]);
    }

    return $count;
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

function get_posts_stats_with_mcl_number($category_id) {
    global $wpdb;

    $stats = $wpdb->get_results("
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
	");

    return $stats;
}

function get_first_post_date() {
    global $wpdb;

    $min_date = $wpdb->get_results("
        SELECT Min( DATE_FORMAT( post_date, '%Y-%m-%d' ) ) AS date
        FROM wp_posts
        WHERE post_status = 'publish'
        AND post_type = 'post'
	");

    return $min_date[0]->date;
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

?>