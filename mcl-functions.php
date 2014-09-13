<?php

function get_all_tags_sorted( $categories, $complete ) {
    // Group the data
    $data = array();

    foreach ( $categories as $category ) {
        // Get the tags of the category
        $tags = get_tags_of_category( $category->term_id, $complete );

        // Group the tags by the first letter
        foreach ( $tags as $tag ) {
            if ( $tag->count < 1 ) {
                continue;
            }

            // Tags which start with a number get their own group #
            if ( preg_match( '/^[a-z]/i', trim( $tag->name[0] ) ) ) {
                $data[$category->term_id][$tag->name[0]][] = $tag;
            } else {
                $data[$category->term_id]['#'][] = $tag;
            }
        }
    }

    return $data;
}

function get_tags_of_category( $category_id, $complete ) {
    global $wpdb;

    $tags = $wpdb->get_results( "
            Select 
                temp.tag_id,
                temp.cat_id,
                temp.name,
                temp.count,
                temp.tag_link,
                temp.taxonomy,
                IFNULL(mcl.complete, 0) AS complete
            FROM 
		(
                    SELECT
                        terms2.term_id AS tag_id,
                        t1.term_id AS cat_id,
                        terms2.name AS name,
                        COUNT(*) AS count,
                        NULL AS tag_link,
                        t2.taxonomy AS taxonomy
                    FROM
                        wp_posts AS p1
                        LEFT JOIN wp_term_relationships AS r1 ON p1.ID = r1.object_ID
                        LEFT JOIN wp_term_taxonomy AS t1 ON r1.term_taxonomy_id = t1.term_taxonomy_id
                        LEFT JOIN wp_terms AS terms1 ON t1.term_id = terms1.term_id,
                        wp_posts AS p2
                        LEFT JOIN wp_term_relationships AS r2 ON p2.ID = r2.object_ID
                        LEFT JOIN wp_term_taxonomy AS t2 ON r2.term_taxonomy_id = t2.term_taxonomy_id
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
                ) AS temp
            LEFT JOIN wp_mcl_complete AS mcl ON temp.tag_id = mcl.tag_id AND temp.cat_id = mcl.cat_id
            WHERE
                IFNULL(mcl.complete, 0) = $complete
	" );

    // Get the link of every tag
    foreach ( $tags as $tag ) {
        $tag->tag_link = get_tag_link( $tag->tag_id );
    }

    // Replace the place holder with the commas
    if ( !is_admin() && get_option( 'mcl_settings_other_comma_in_tags' ) == "1" ) {
        $tags = comma_tags_filter( $tags );
    }

    return $tags;
}

function count_tags_of_category( $data, $categorie_id ) {
    if ( array_key_exists( $categorie_id, $data ) ) {
        $count = 0;

        foreach ( array_keys( $data[$categorie_id] ) as $key ) {
            $count += count( $data[$categorie_id][$key] );
        }

        return $count;
    }

    return 0;
}

function get_last_post_of_tag_in_category_data( $tag_id, $category_id ) {
    // Get post with the tag
    $posts = get_posts( "tag_id={$tag_id}&category={$category_id}" );

    // Get the last post
    $post = array_shift( $posts );

    return $post;
}

?>