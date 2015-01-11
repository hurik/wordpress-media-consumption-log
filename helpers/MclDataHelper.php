<?php

class MclDataHelper {

    static function getTagsOfCategorySorted( $categories, $complete ) {
        // Group the data
        $data = array();

        foreach ( $categories as $category ) {
            // Get the tags of the category
            $tags = self::getTagsOfCategory( $category->term_id, $complete );

            // Group the tags by the first letter
            foreach ( $tags as $tag ) {
                // Tags which start with a number get their own group #
                if ( preg_match( '/^[a-z]/i', trim( $tag->name[0] ) ) ) {
                    $data[$category->term_id][strtoupper( $tag->name[0] )][] = $tag;
                } else {
                    $data[$category->term_id]['#'][] = $tag;
                }
            }
        }

        return $data;
    }

    private static function getTagsOfCategory( $category_id, $complete ) {
        global $wpdb;

        $tags = $wpdb->get_results( "
            Select 
                temp.tag_id,
                temp.taxonomy,
                temp.name,
                temp.cat_id,
                temp.post_id,
                temp.post_date,
                temp.post_title
            FROM 
		(
                    SELECT
                        terms2.term_id AS tag_id,
                        t2.taxonomy AS taxonomy,
                        terms2.name AS name,
                        t1.term_id AS cat_id,
                        p2.ID AS post_id,
                        p2.post_date,
                        p2.post_title
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
                        AND terms1.term_id = {$category_id}
                        AND t2.taxonomy = 'post_tag'
                        AND p2.post_status = 'publish'
                        AND p1.ID = p2.ID
                        AND p2.post_date = (
                            SELECT MAX(p3.post_date)
                            FROM {$wpdb->prefix}posts AS p3
                            LEFT JOIN {$wpdb->prefix}term_relationships AS r3 ON p3.ID = r3.object_ID
                            WHERE r3.term_taxonomy_id = terms2.term_id)
                    ORDER BY name
                ) AS temp
            LEFT JOIN {$wpdb->prefix}mcl_complete AS mcl ON temp.tag_id = mcl.tag_id AND temp.cat_id = mcl.cat_id
            WHERE
                IFNULL(mcl.complete, 0) = $complete
	" );

        // Replace the place holder with the commas
        if ( MclSettingsHelper::isOtherCommaInTags() ) {
            $tags = comma_tags_filter( $tags );
        }

        return $tags;
    }

    static function countTagsOfCategory( $data, $categorie_id ) {
        if ( array_key_exists( $categorie_id, $data ) ) {
            $count = 0;

            foreach ( array_keys( $data[$categorie_id] ) as $key ) {
                $count += count( $data[$categorie_id][$key] );
            }

            return $count;
        }

        return 0;
    }

}

?>