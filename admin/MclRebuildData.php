<?php

class MclRebuildData {

    const option_name = 'mcl_data';

    public static function get_data() {
        if ( get_option( self::option_name ) === false ) {
            add_option( self::option_name, self::build_data(), null, 'no' );
        }

        return get_option( self::option_name );
    }

    public static function update_data() {
        if ( get_option( self::option_name ) !== false ) {
            update_option( self::option_name, self::build_data() );
        } else {
            add_option( self::option_name, self::build_data(), null, 'no' );
        }
    }

    public static function create_page() {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        if ( isset( $_GET["now"] ) && $_GET["now"] == 1 ) {
            self::update_data();
        }
        ?>
        <div class="wrap">
            <h2>Media Consumption Log - <?php _e( 'Rebuild data', 'media-consumption-log' ); ?></h2>

            <p>
                <input class="button-primary" type=button onClick="location.href = 'admin.php?page=mcl-rebuild-data&now=1'" value="<?php _e( 'Rebuild data now!', 'media-consumption-log' ); ?>" />
            </p>

            <h3><?php _e( 'Information', 'media-consumption-log' ); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e( 'Number of queries', 'media-consumption-log' ); ?></th>
                    <td><?php echo get_num_queries(); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e( 'Execution time', 'media-consumption-log' ); ?></th>
                    <td><?php timer_stop( 1 ); ?></td>
                </tr>
            </table>
        </div>	
        <?php
    }

    public static function build_data() {
        // Set the default timezone
        date_default_timezone_set( get_option( 'timezone_string' ) );

        $data = new StdClass;

        // Get the categories
        $categories = get_categories( "include=" . MclSettings::get_monitored_categories_series() . "," . MclSettings::get_monitored_categories_non_series() );

        // Get first date an month for the graphs
        $first_date = date( 'Y-m-d', strtotime( "-" . (MclSettings::get_statistics_daily_count() - 1) . " day", strtotime( date( 'Y-m-d' ) ) ) );
        $first_month = date( 'Y-m', strtotime( "-" . (MclSettings::get_statistics_monthly_count() - 1) . " month", strtotime( date( 'Y-m' ) ) ) );

        // Get the first post
        $first_post_array = get_posts( "posts_per_page=1&order=asc" );
        $first_post = array_shift( $first_post_array );
        $first_post_date = new DateTime( $first_post->post_date );
        $data->first_post_date = $first_post_date;

        // Get the number of days since first post
        $date_current = new DateTime( date( 'Y-m-d' ) );
        $number_of_days = $date_current->diff( $first_post_date )->format( "%a" ) + 1;

        // Total consumption of category
        $consumption_total = 0;
        $consumption_average = 0;

        $tags_count_ongoing = 0;
        $tags_count_complete = 0;

        foreach ( $categories as $category ) {
            self::get_tags_of_category( $category );

            $tags_count_ongoing += $category->mcl_tags_count_ongoing;
            $tags_count_complete += $category->mcl_tags_count_complete;

            // Graph data
            $category->mcl_daily_data = self::get_mcl_number_count_of_category_sorted_by_day( $category->term_id, $first_date );
            $category->mcl_monthly_data = self::get_mcl_number_count_of_category_sorted_by_month( $category->term_id, $first_month );
            $category->mcl_consumption_total = self::get_total_mcl_mumber_count_of_category( $category->term_id );
            $category->mcl_consumption_average = $category->mcl_consumption_total / $number_of_days;

            $consumption_total += $category->mcl_consumption_total;
            $consumption_average += $category->mcl_consumption_average;
        }

        $data->categories = $categories;
        $data->consumption_total = $consumption_total;
        $data->consumption_average = $consumption_average;

        $data->tags_count_ongoing = $tags_count_ongoing;
        $data->tags_count_complete = $tags_count_complete;
        $data->tags_count_total = $tags_count_ongoing + $tags_count_complete;

        return $data;
    }

    private static function get_tags_of_category( $category ) {
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
                        AND terms1.term_id = '{$category->term_id}'
                        AND t2.taxonomy = 'post_tag'
                        AND p2.post_status = 'publish'
                        AND p1.ID = p2.ID
                    GROUP BY name
                    ORDER BY name
                ) AS temp
            LEFT JOIN {$wpdb->prefix}mcl_complete AS mcl ON temp.tag_id = mcl.tag_id AND temp.cat_id = mcl.cat_id
	" );

        $tags_count_ongoing = 0;
        $tags_count_complete = 0;
        $tags_ongoing = array();
        $tags_complete = array();

        foreach ( $tags as $tag ) {
            // Get tag data
            $tag->tag_data = get_tag( $tag->tag_id );
            // Comma in tags
            $tag->tag_data = MclCommaInTags::comma_tag_filter( $tag->tag_data );
            // Get tag link
            $tag->tag_link = get_tag_link( $tag->tag_data );

            // Get last post data
            $post_data = get_posts( "posts_per_page=1&tag_id={$tag->tag_id}&category={$tag->cat_id}" );
            $tag->post_data = array_shift( $post_data );
            // Get post link
            $tag->post_link = get_permalink( $tag->post_data );

            if ( $tag->complete == false ) {
                $tags_count_ongoing++;

                // Tags which start with a number get their own group #
                if ( preg_match( '/^[a-z]/i', trim( $tag->tag_data->name[0] ) ) ) {
                    $tags_ongoing[strtoupper( $tag->tag_data->name[0] )][] = $tag;
                } else {
                    $tags_ongoing['#'][] = $tag;
                }
            } else {
                $tags_count_complete++;

                // Tags which start with a number get their own group #
                if ( preg_match( '/^[a-z]/i', trim( $tag->tag_data->name[0] ) ) ) {
                    $tags_complete[strtoupper( $tag->tag_data->name[0] )][] = $tag;
                } else {
                    $tags_complete['#'][] = $tag;
                }
            }
        }

        // Sort tag arrays
        $category->mcl_tags_count = $tags_count_ongoing + $tags_count_complete;
        $category->mcl_tags_count_ongoing = $tags_count_ongoing;
        $category->mcl_tags_count_complete = $tags_count_complete;
        $category->mcl_tags_ongoing = $tags_ongoing;
        $category->mcl_tags_complete = $tags_complete;

        return $category;
    }

    private static function get_mcl_number_count_of_category_sorted_by_day( $category_id, $first_date ) {
        global $wpdb;

        $stats = $wpdb->get_results( "
            SELECT DATE_FORMAT(post_date, '%Y-%m-%d') AS date, SUM(meta_value) AS number
            FROM {$wpdb->prefix}posts p
            LEFT OUTER JOIN {$wpdb->prefix}term_relationships r ON r.object_id = p.ID
            LEFT OUTER JOIN {$wpdb->prefix}postmeta m ON m.post_id = p.ID
            WHERE post_status = 'publish'
              AND post_type = 'post'
              AND meta_key = 'mcl_number'
              AND term_taxonomy_id = '{$category_id}'
              AND post_date >= '{$first_date}'
            GROUP BY DATE_FORMAT(post_date, '%Y-%m-%d')
            ORDER BY date DESC
	" );

        return $stats;
    }

    private static function get_mcl_number_count_of_category_sorted_by_month( $category_id, $first_month ) {
        global $wpdb;

        $stats = $wpdb->get_results( "
            SELECT DATE_FORMAT(post_date, '%Y-%m') AS date, SUM(meta_value) AS number
            FROM {$wpdb->prefix}posts p
            LEFT OUTER JOIN {$wpdb->prefix}term_relationships r ON r.object_id = p.ID
            LEFT OUTER JOIN {$wpdb->prefix}postmeta m ON m.post_id = p.ID
            WHERE post_status = 'publish'
              AND post_type = 'post'
              AND meta_key = 'mcl_number'
              AND term_taxonomy_id = '{$category_id}'
              AND post_date >= '{$first_month}'
            GROUP BY DATE_FORMAT(post_date, '%Y-%m')
            ORDER BY date DESC
	" );

        return $stats;
    }

    private static function get_total_mcl_mumber_count_of_category( $category_id ) {
        global $wpdb;

        $stats = $wpdb->get_results( "
            SELECT SUM(meta_value) AS number
            FROM {$wpdb->prefix}posts p
            LEFT OUTER JOIN {$wpdb->prefix}term_relationships r ON r.object_id = p.ID
            LEFT OUTER JOIN {$wpdb->prefix}postmeta m ON m.post_id = p.ID
            WHERE post_status = 'publish'
              AND post_type = 'post'
              AND meta_key = 'mcl_number'
              AND term_taxonomy_id = '{$category_id}'
	" );

        return $stats[0]->number;
    }

}

?>