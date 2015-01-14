<?php

class MclStatusHelper {

    const option_name = 'mcl_data_status';

    public static function getData() {
        if ( get_option( self::option_name ) === false ) {
            add_option( self::option_name, self::getAllTags(), null, 'no' );
        }

        return get_option( self::option_name );
    }

    public static function updateData() {
        if ( get_option( self::option_name ) !== false ) {
            update_option( self::option_name, self::getAllTags() );
        } else {
            add_option( self::option_name, self::getAllTags(), null, 'no' );
        }
    }

    private static function getAllTags() {
        $categories = get_categories( "exclude=" . MclSettingsHelper::getStatusExcludeCategory() );

        $data = array();

        foreach ( $categories as $category ) {
            $data[] = self::getTagsOfCategory( $category );
        }

        return $data;
    }

    private static function getTagsOfCategory( $category ) {
        global $wpdb;

        $tags = $wpdb->get_results( "
            Select 
                temp.tag_id,
                temp.cat_id,
                temp.count,
                IFNULL(mcl.complete, 0) AS complete
            FROM 
		(
                    SELECT
                        terms2.term_id AS tag_id,
                        t1.term_id AS cat_id,
                        COUNT(*) AS count,
                        terms2.name AS name
                    FROM
                        {$wpdb->prefix}posts AS p1
                        LEFT JOIN {$wpdb->prefix}term_relationships AS r1 ON p1.ID = r1.object_ID
                        LEFT JOIN {$wpdb->prefix}term_taxonomy AS t1 ON r1.term_taxonomy_id = t1.term_taxonomy_id
                        LEFT JOIN {$wpdb->prefix}terms AS terms1 ON t1.term_id = terms1.term_id,
                        {$wpdb->prefix}posts AS p2
                        LEFT JOIN {$wpdb->prefix}term_relationships AS r2 ON p2.ID = r2.object_ID
                        LEFT JOIN {$wpdb->prefix}term_taxonomy AS t2 ON r2.term_taxonomy_id = t2.term_taxonomy_id
                        LEFT JOIN {$wpdb->prefix}terms AS terms2 ON t2.term_id = terms2.term_id
                    WHERE
                        t1.taxonomy = 'category'
                        AND p1.post_status = 'publish'
                        AND terms1.term_id = {$category->term_id}
                        AND t2.taxonomy = 'post_tag'
                        AND p2.post_status = 'publish'
                        AND p1.ID = p2.ID
                    GROUP BY name
                    ORDER BY name
                ) AS temp
            LEFT JOIN {$wpdb->prefix}mcl_complete AS mcl ON temp.tag_id = mcl.tag_id AND temp.cat_id = mcl.cat_id
	" );

        $tags_ongoing = array();
        $ongoing = 0;
        $tags_complete = array();
        $complete = 0;

        foreach ( $tags as $tag ) {
            // Get tag data
            $tag->tag_data = get_tag( $tag->tag_id );
            //
            if ( MclSettingsHelper::isOtherCommaInTags() ) {
                $tag->tag_data = comma_tag_filter( $tag->tag_data );
            }
            // Get tag link
            $tag->tag_link = get_tag_link( $tag->tag_data );

            // Get last post data
            $post_data = get_posts( "posts_per_page=1&tag_id={$tag->tag_id}&category={$tag->cat_id}" );
            $tag->post_data = array_shift( $post_data );
            // Get post link
            $tag->post_link = get_permalink( $tag->post_data );

            if ( $tag->complete == false ) {
                $ongoing++;

                // Tags which start with a number get their own group #
                if ( preg_match( '/^[a-z]/i', trim( $tag->tag_data->name[0] ) ) ) {
                    $tags_ongoing[strtoupper( $tag->tag_data->name[0] )][] = $tag;
                } else {
                    $tags_ongoing['#'][] = $tag;
                }
            } else {
                $complete++;

                // Tags which start with a number get their own group #
                if ( preg_match( '/^[a-z]/i', trim( $tag->tag_data->name[0] ) ) ) {
                    $tags_complete[strtoupper( $tag->tag_data->name[0] )][] = $tag;
                } else {
                    $tags_complete['#'][] = $tag;
                }
            }
        }

        // Sort tag arrays
        ksort( $tags_ongoing );
        ksort( $tags_complete );

        $category->mcl_count = $ongoing + $complete;
        $category->mcl_ongoing = $ongoing;
        $category->mcl_complete = $complete;
        $category->mcl_tags_ongoing = $tags_ongoing;
        $category->mcl_tags_complete = $tags_complete;

        return $category;
    }

}

?>