<?php

class MclStatisticsHelper {

    const option_name = 'mcl_data_statistics';

    public static function getData() {
        if ( get_option( self::option_name ) === false ) {
            add_option( self::option_name, self::buildData(), null, 'no' );
        }

        return get_option( self::option_name );
    }

    public static function updateData() {
        if ( get_option( self::option_name ) !== false ) {
            update_option( self::option_name, self::buildData() );
        } else {
            add_option( self::option_name, self::buildData(), null, 'no' );
        }
    }

    public static function buildData() {
        // Set the default timezone
        date_default_timezone_set( get_option( 'timezone_string' ) );

        $data = new StdClass;

        // Get the categories
        $categories = get_categories( "exclude=" . MclSettingsHelper::getStatisticsExcludeCategory() );

        // Daily graph
        $first_date = date( 'Y-m-d', strtotime( "-" . MclSettingsHelper::getStatisticsNumberOfDays() - 1 . " day", strtotime( date( 'Y-m-d' ) ) ) );

        foreach ( $categories as $category ) {
            if ( MclSettingsHelper::isStatisticsMclNumber() ) {
                $category->mcl_daily_data = self::getMclNumberCountOfCategorySortedByDay( $category->term_id, $first_date );
            } else {
                $category->mcl_daily_data = self::getPostCountOfCategorySortedByDay( $category->term_id, $first_date );
            }
        }

        // Monthly graph
        $first_month = date( 'Y-m', strtotime( "-" . MclSettingsHelper::getStatisticsNumberOfMonths() - 1 . " month", strtotime( date( 'Y-m' ) ) ) );

        foreach ( $categories as $category ) {
            if ( MclSettingsHelper::isStatisticsMclNumber() ) {
                $category->mcl_monthly_data = self::getMclNumberCountOfCategorySortedByMonth( $category->term_id, $first_month );
            } else {
                $category->mcl_monthly_data = self::getPostCountOfCategorySortedByMonth( $category->term_id, $first_month );
            }
        }

        // Get the first post
        $first_post_array = get_posts( "posts_per_page=1&order=asc" );
        $first_post = array_shift( $first_post_array );
        $first_post_date = new DateTime( $first_post->post_date );

        $date_current = new DateTime( date( 'Y-m-d' ) );
        $number_of_days = $date_current->diff( $first_post_date )->format( "%a" ) + 1;

        $data->first_post_date = $first_post_date;

        $consumption_total = 0;
        $consumption_average = 0;

        $tags_count_ongoing = 0;
        $tags_count_complete = 0;

        foreach ( $categories as $category ) {
            // Stats
            if ( MclSettingsHelper::isStatisticsMclNumber() ) {
                $cat_com_tot = self::getTotalMclNumberCountOfCategory( $category->term_id );
                $car_com_avg = $cat_com_tot / $number_of_days;
            } else {
                $cat_com_tot = self::getTotalPostCountOfCategory( $category->term_id );
                $car_com_avg = $cat_com_tot / $number_of_days;
            }

            $consumption_total += $cat_com_tot;
            $consumption_average += $car_com_avg;

            $category->mcl_consumption_total = $cat_com_tot;
            $category->mcl_consumption_average = $car_com_avg;

            $cat_tag_cou_ong = self::getTagCountOfCategory( $category->term_id, 0 );
            $cat_tag_cou_com = self::getTagCountOfCategory( $category->term_id, 1 );

            $tags_count_ongoing +=$cat_tag_cou_ong;
            $tags_count_complete +=$cat_tag_cou_com;

            $category->mcl_tags_count_ongoing = $cat_tag_cou_ong;
            $category->mcl_tags_count_complete = $cat_tag_cou_com;
            $category->mcl_tags_count_total = $cat_tag_cou_ong + $cat_tag_cou_com;
        }

        $data->stats = $categories;
        $data->consumption_total = $consumption_total;
        $data->consumption_average = $consumption_average;

        $data->tags_count_ongoing = $tags_count_ongoing;
        $data->tags_count_complete = $tags_count_complete;
        $data->tags_count_total = $tags_count_ongoing + $tags_count_complete;

        return $data;
    }

    private static function getMclNumberCountOfCategorySortedByDay( $category_id, $first_date ) {
        global $wpdb;

        $stats = $wpdb->get_results( "
            SELECT DATE_FORMAT(post_date, '%Y-%m-%d') AS date, SUM(meta_value) AS number
            FROM {$wpdb->prefix}posts p
            LEFT OUTER JOIN {$wpdb->prefix}term_relationships r ON r.object_id = p.ID
            LEFT OUTER JOIN {$wpdb->prefix}postmeta m ON m.post_id = p.ID
            WHERE post_status = 'publish'
              AND post_type = 'post'
              AND meta_key = 'mcl_number'
              AND term_taxonomy_id = '$category_id'
              AND post_date >= '$first_date'
            GROUP BY DATE_FORMAT(post_date, '%Y-%m-%d')
            ORDER BY date DESC
	" );

        return $stats;
    }

    private static function getPostCountOfCategorySortedByDay( $category_id, $first_date ) {
        global $wpdb;

        $stats = $wpdb->get_results( "
            SELECT DATE_FORMAT(post_date, '%Y-%m-%d') AS date, COUNT(post_date) AS number
            FROM {$wpdb->prefix}posts p
            LEFT OUTER JOIN {$wpdb->prefix}term_relationships r ON r.object_id = p.ID
            WHERE post_status = 'publish'
              AND post_type = 'post'
              AND term_taxonomy_id = '$category_id'
              AND post_date >= '$first_date'
            GROUP BY DATE_FORMAT(post_date, '%Y-%m-%d')
            ORDER BY date DESC
	" );

        return $stats;
    }

    private static function getMclNumberCountOfCategorySortedByMonth( $category_id, $first_month ) {
        global $wpdb;

        $stats = $wpdb->get_results( "
            SELECT DATE_FORMAT(post_date, '%Y-%m') AS date, SUM(meta_value) AS number
            FROM {$wpdb->prefix}posts p
            LEFT OUTER JOIN {$wpdb->prefix}term_relationships r ON r.object_id = p.ID
            LEFT OUTER JOIN {$wpdb->prefix}postmeta m ON m.post_id = p.ID
            WHERE post_status = 'publish'
              AND post_type = 'post'
              AND meta_key = 'mcl_number'
              AND term_taxonomy_id = '$category_id'
              AND post_date >= '$first_month'
            GROUP BY DATE_FORMAT(post_date, '%Y-%m')
            ORDER BY date DESC
	" );

        return $stats;
    }

    private static function getPostCountOfCategorySortedByMonth( $category_id, $first_month ) {
        global $wpdb;

        $stats = $wpdb->get_results( "
            SELECT DATE_FORMAT(post_date, '%Y-%m') AS date, COUNT(post_date) AS number
            FROM {$wpdb->prefix}posts p
            LEFT OUTER JOIN {$wpdb->prefix}term_relationships r ON r.object_id = p.ID
            WHERE post_status = 'publish'
              AND post_type = 'post'
              AND term_taxonomy_id = '$category_id'
              AND post_date >= '$first_month'
            GROUP BY DATE_FORMAT(post_date, '%Y-%m')
            ORDER BY date DESC
	" );

        return $stats;
    }

    private static function getTotalMclNumberCountOfCategory( $category_id ) {
        global $wpdb;

        $stats = $wpdb->get_results( "
            SELECT SUM(meta_value) AS number
            FROM {$wpdb->prefix}posts p
            LEFT OUTER JOIN {$wpdb->prefix}term_relationships r ON r.object_id = p.ID
            LEFT OUTER JOIN {$wpdb->prefix}postmeta m ON m.post_id = p.ID
            WHERE post_status = 'publish'
              AND post_type = 'post'
              AND meta_key = 'mcl_number'
              AND term_taxonomy_id = $category_id
	" );

        return $stats[0]->number;
    }

    private static function getTotalPostCountOfCategory( $category_id ) {
        global $wpdb;

        $stats = $wpdb->get_results( "
            SELECT COUNT(*) AS number
            FROM {$wpdb->prefix}posts p
            LEFT OUTER JOIN {$wpdb->prefix}term_relationships r ON r.object_id = p.ID
            WHERE post_status = 'publish'
              AND post_type = 'post'
              AND term_taxonomy_id = $category_id
	" );

        return $stats[0]->number;
    }

    private static function getTagCountOfCategory( $category_id, $complete ) {
        global $wpdb;

        $stats = $wpdb->get_results( "
            SELECT count(*) as number
            FROM (
                SELECT terms2.term_id AS tag_id, t1.term_id AS cat_id
                FROM {$wpdb->prefix}posts AS p1
                LEFT JOIN {$wpdb->prefix}term_relationships AS r1 ON p1.ID = r1.object_ID
                LEFT JOIN {$wpdb->prefix}term_taxonomy AS t1 ON r1.term_taxonomy_id = t1.term_taxonomy_id
                LEFT JOIN {$wpdb->prefix}terms AS terms1 ON t1.term_id = terms1.term_id, {$wpdb->prefix}posts AS p2
                LEFT JOIN {$wpdb->prefix}term_relationships AS r2 ON p2.ID = r2.object_ID
                LEFT JOIN {$wpdb->prefix}term_taxonomy AS t2 ON r2.term_taxonomy_id = t2.term_taxonomy_id
                LEFT JOIN {$wpdb->prefix}terms AS terms2 ON t2.term_id = terms2.term_id
                WHERE t1.taxonomy = 'category'
                  AND p1.post_status = 'publish'
                  AND terms1.term_id = $category_id
                  AND t2.taxonomy = 'post_tag'
                  AND p2.post_status = 'publish'
                  AND p1.ID = p2.ID
                GROUP BY tag_id
            ) AS temp
            LEFT JOIN {$wpdb->prefix}mcl_complete AS mcl ON temp.tag_id = mcl.tag_id
            AND temp.cat_id = mcl.cat_id
            WHERE IFNULL(mcl.complete, 0) = $complete
        " );

        return $stats[0]->number;
    }

}

?>